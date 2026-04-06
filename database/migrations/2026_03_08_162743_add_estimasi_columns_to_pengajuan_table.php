<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pengajuan', function (Blueprint $table) {
            if (!Schema::hasColumn('pengajuan', 'anggaran_disetujui')) {
                $table->decimal('anggaran_disetujui', 15, 2)->nullable()->after('estimasi_biaya');
            }
            if (!Schema::hasColumn('pengajuan', 'biaya_realisasi')) {
                $table->decimal('biaya_realisasi', 15, 2)->nullable()->after('anggaran_disetujui');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan', function (Blueprint $table) {
            $table->dropColumn(['anggaran_disetujui', 'biaya_realisasi']);
        });
    }
};
