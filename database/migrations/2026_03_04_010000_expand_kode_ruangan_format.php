<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE ruangan MODIFY kode_ruangan VARCHAR(20) NOT NULL');
        }

        $rows = DB::table('ruangan')
            ->join('gedung', 'gedung.id', '=', 'ruangan.gedung_id')
            ->select([
                'ruangan.id',
                'ruangan.kode_ruangan',
                'ruangan.lantai',
                'gedung.kode_gedung',
            ])
            ->orderBy('ruangan.id')
            ->get();

        foreach ($rows as $row) {
            $gedungCode = $this->sanitizeShortCode($row->kode_gedung, 'GDG');
            $ruanganShort = $this->extractRuanganShortCode($row->kode_ruangan);
            $lantai = str_pad((string) max(1, (int) ($row->lantai ?? 1)), 2, '0', STR_PAD_LEFT);
            $newCode = "{$gedungCode}-L{$lantai}-{$ruanganShort}";

            DB::table('ruangan')
                ->where('id', $row->id)
                ->update(['kode_ruangan' => $newCode]);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE ruangan DROP INDEX ruangan_kode_ruangan_unique');
        }

        $rows = DB::table('ruangan')
            ->select(['id', 'kode_ruangan'])
            ->orderBy('id')
            ->get();

        $used = [];
        foreach ($rows as $row) {
            $preferred = $this->extractRuanganShortCode($row->kode_ruangan);
            $short = $this->generateUniqueShortCode($preferred, $used);

            DB::table('ruangan')
                ->where('id', $row->id)
                ->update(['kode_ruangan' => $short]);
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE ruangan MODIFY kode_ruangan VARCHAR(3) NOT NULL');
            DB::statement('ALTER TABLE ruangan ADD UNIQUE ruangan_kode_ruangan_unique (kode_ruangan)');
        }
    }

    private function extractRuanganShortCode(?string $code): string
    {
        $normalized = strtoupper(trim((string) $code));
        if ($normalized === '') {
            return 'RGN';
        }

        if (preg_match('/^[A-Z0-9]{3}-L\d{2}-([A-Z0-9]{3})$/', $normalized, $match)) {
            return $match[1];
        }

        $parts = array_values(array_filter(explode('-', $normalized)));
        $last = end($parts);

        if (is_string($last) && $last !== '') {
            return $this->sanitizeShortCode($last, 'RGN');
        }

        return $this->sanitizeShortCode($normalized, 'RGN');
    }

    private function sanitizeShortCode(?string $value, string $fallback): string
    {
        $clean = strtoupper(trim((string) $value));
        $clean = preg_replace('/[^A-Z0-9]/', '', $clean) ?? '';

        if ($clean === '') {
            $clean = strtoupper($fallback);
        }

        return str_pad(substr($clean, 0, 3), 3, 'X');
    }

    private function generateUniqueShortCode(string $preferred, array &$used): string
    {
        $base = $this->sanitizeShortCode($preferred, 'RGN');
        if (!isset($used[$base])) {
            $used[$base] = true;
            return $base;
        }

        for ($i = 0; $i <= 46655; $i++) {
            $candidate = strtoupper(str_pad(base_convert((string) $i, 10, 36), 3, '0', STR_PAD_LEFT));
            if (!isset($used[$candidate])) {
                $used[$candidate] = true;
                return $candidate;
            }
        }

        return $base;
    }
};
