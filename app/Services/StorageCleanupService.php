<?php

namespace App\Services;

use App\Models\Aset;
use App\Models\Pengajuan;
use App\Models\Penggantian;
use App\Models\Perawatan;
use App\Models\RiwayatKondisiAset;
use Illuminate\Support\Facades\Storage;

class StorageCleanupService
{
    public function buildReport(?string $scanPath = null): array
    {
        $disk = Storage::disk('public');
        $scanPath = $this->normalizePathPrefix($scanPath);
        $usedPaths = $this->collectUsedPaths();
        $files = $disk->allFiles($scanPath ?? '');

        $usedFiles = [];
        $orphanFiles = [];
        $usedBytes = 0;
        $orphanBytes = 0;

        foreach ($files as $path) {
            if ($this->shouldIgnore($path)) {
                continue;
            }

            $size = (int) $disk->size($path);

            if (isset($usedPaths[$path])) {
                $usedFiles[] = $path;
                $usedBytes += $size;
                continue;
            }

            $orphanFiles[] = $path;
            $orphanBytes += $size;
        }

        sort($usedFiles);
        sort($orphanFiles);

        return [
            'scan_path' => $scanPath,
            'used_files' => $usedFiles,
            'orphan_files' => $orphanFiles,
            'used_count' => count($usedFiles),
            'orphan_count' => count($orphanFiles),
            'used_bytes' => $usedBytes,
            'orphan_bytes' => $orphanBytes,
        ];
    }

    public function deleteOrphans(array $orphanFiles): int
    {
        $files = array_values(array_filter($orphanFiles, fn ($path) => is_string($path) && trim($path) !== ''));

        if ($files === []) {
            return 0;
        }

        Storage::disk('public')->delete($files);

        return count($files);
    }

    private function collectUsedPaths(): array
    {
        $used = [];

        foreach (Aset::withTrashed()->select(['foto_aset'])->cursor() as $asset) {
            $this->registerPath($used, $asset->foto_aset ?? null);
        }

        foreach (RiwayatKondisiAset::query()->select(['foto_kerusakan'])->cursor() as $riwayat) {
            $this->registerPath($used, $riwayat->foto_kerusakan ?? null);
        }

        foreach (Perawatan::query()->select(['foto_sesudah', 'foto_bukti'])->cursor() as $perawatan) {
            $this->registerPath($used, $perawatan->foto_sesudah ?? null);
            $this->registerPath($used, $perawatan->foto_bukti ?? null);
        }

        foreach (Penggantian::query()->select(['foto_aset_baru', 'foto_bukti'])->cursor() as $penggantian) {
            $this->registerPath($used, $penggantian->foto_aset_baru ?? null);
            $this->registerPath($used, $penggantian->foto_bukti ?? null);
        }

        foreach (Pengajuan::query()->select(['lampiran'])->cursor() as $pengajuan) {
            $lampiran = $pengajuan->lampiran ?? [];
            if (!is_array($lampiran)) {
                continue;
            }

            foreach ($lampiran as $item) {
                if (is_array($item)) {
                    $this->registerPath($used, $item['path'] ?? null);
                } elseif (is_string($item)) {
                    $this->registerPath($used, $item);
                }
            }
        }

        return $used;
    }

    private function registerPath(array &$used, mixed $path): void
    {
        if (!is_string($path)) {
            return;
        }

        $path = trim(str_replace('\\', '/', $path));
        if ($path === '') {
            return;
        }

        $used[$path] = true;
    }

    private function normalizePathPrefix(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }

        return trim(str_replace('\\', '/', $path), '/');
    }

    private function shouldIgnore(string $path): bool
    {
        return str_starts_with(basename($path), '.');
    }
}
