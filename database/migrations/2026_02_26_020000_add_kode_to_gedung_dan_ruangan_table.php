<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('gedung', function (Blueprint $table) {
            $table->string('kode_gedung', 3)->nullable()->after('nama_gedung');
        });

        Schema::table('ruangan', function (Blueprint $table) {
            $table->string('kode_ruangan', 3)->nullable()->after('nama_ruangan');
        });

        $usedGedungCodes = [];
        $gedungRows = DB::table('gedung')
            ->select(['id', 'nama_gedung', 'kode_gedung'])
            ->orderBy('id')
            ->get();

        foreach ($gedungRows as $gedung) {
            $preferred = $this->buildPreferredCode($gedung->kode_gedung ?: $gedung->nama_gedung, 'GDG');
            $code = $this->generateUniqueCode($preferred, $usedGedungCodes);

            DB::table('gedung')
                ->where('id', $gedung->id)
                ->update(['kode_gedung' => $code]);
        }

        $usedRuanganCodes = [];
        $ruanganRows = DB::table('ruangan')
            ->select(['id', 'nama_ruangan', 'kode_ruangan'])
            ->orderBy('id')
            ->get();

        foreach ($ruanganRows as $ruangan) {
            $preferred = $this->buildPreferredCode($ruangan->kode_ruangan ?: $ruangan->nama_ruangan, 'RGN');
            $code = $this->generateUniqueCode($preferred, $usedRuanganCodes);

            DB::table('ruangan')
                ->where('id', $ruangan->id)
                ->update(['kode_ruangan' => $code]);
        }

        Schema::table('gedung', function (Blueprint $table) {
            $table->unique('kode_gedung', 'gedung_kode_gedung_unique');
        });

        Schema::table('ruangan', function (Blueprint $table) {
            $table->unique('kode_ruangan', 'ruangan_kode_ruangan_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ruangan', function (Blueprint $table) {
            $table->dropUnique('ruangan_kode_ruangan_unique');
            $table->dropColumn('kode_ruangan');
        });

        Schema::table('gedung', function (Blueprint $table) {
            $table->dropUnique('gedung_kode_gedung_unique');
            $table->dropColumn('kode_gedung');
        });
    }

    private function buildPreferredCode(?string $name, string $fallback): string
    {
        $name = trim((string) $name);

        if ($name === '') {
            return strtoupper(substr($fallback, 0, 3));
        }

        $sanitized = preg_replace('/[^A-Za-z0-9\\s]/', '', $name) ?? '';
        $words = preg_split('/\\s+/', trim($sanitized), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $initials = '';

        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }

        $joined = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $sanitized) ?? '');
        $base = strtoupper(substr($initials . $joined, 0, 3));

        if ($base === '') {
            $base = strtoupper(substr($fallback, 0, 3));
        }

        return str_pad($base, 3, 'X');
    }

    private function generateUniqueCode(string $preferred, array &$used): string
    {
        $preferred = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $preferred) ?? '', 0, 3));
        $preferred = str_pad($preferred, 3, 'X');

        if (!isset($used[$preferred])) {
            $used[$preferred] = true;
            return $preferred;
        }

        for ($i = 0; $i <= 46655; $i++) {
            $candidate = strtoupper(str_pad(base_convert((string) $i, 10, 36), 3, '0', STR_PAD_LEFT));

            if (!isset($used[$candidate])) {
                $used[$candidate] = true;
                return $candidate;
            }
        }

        throw new RuntimeException('Tidak dapat membuat kode unik 3 karakter.');
    }
};
