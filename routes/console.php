<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\StorageCleanupService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('storage:cleanup-orphans {--path= : Optional folder prefix inside storage/app/public} {--delete : Delete orphan files after reporting} {--limit=20 : Maximum orphan files to display}', function () {
    $scanPath = trim((string) $this->option('path'));
    $scanPath = $scanPath !== '' ? trim(str_replace('\\', '/', $scanPath), '/') : null;
    $limit = max(1, (int) $this->option('limit'));

    /** @var StorageCleanupService $service */
    $service = app(StorageCleanupService::class);
    $report = $service->buildReport($scanPath);

    $this->info('Storage cleanup report');
    $this->line('Scanned path : ' . ($report['scan_path'] ?? 'all'));
    $this->line('Used files    : ' . $report['used_count'] . ' (' . number_format((int) $report['used_bytes']) . ' bytes)');
    $this->line('Orphan files  : ' . $report['orphan_count'] . ' (' . number_format((int) $report['orphan_bytes']) . ' bytes)');

    if ($report['orphan_count'] > 0) {
        $this->line('');
        $this->line('Orphan preview:');
        foreach (array_slice($report['orphan_files'], 0, $limit) as $file) {
            $this->line(' - ' . $file);
        }

        if ($report['orphan_count'] > $limit) {
            $this->line(' ... and ' . ($report['orphan_count'] - $limit) . ' more');
        }
    }

    if (!$this->option('delete')) {
        $this->warn('Run again with --delete if you want to remove the orphan files.');
        return 0;
    }

    if ($report['orphan_count'] === 0) {
        $this->info('No orphan files found. Nothing to delete.');
        return 0;
    }

    if (!$this->confirm('Delete all orphan files from storage/app/public?')) {
        $this->warn('Deletion cancelled.');
        return 0;
    }

    $deletedCount = $service->deleteOrphans($report['orphan_files']);
    $this->info('Deleted ' . $deletedCount . ' orphan file(s).');

    return 0;
})->purpose('Report or delete orphan files from storage/app/public');
