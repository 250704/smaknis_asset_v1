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
        // 1. Rename table kategori_aset -> kategori_sarana
        Schema::rename('kategori_aset', 'kategori_sarana');

        // 2. Rename table aset -> sarana
        Schema::rename('aset', 'sarana');
        Schema::table('sarana', function (Blueprint $table) {
            $table->renameColumn('kode_aset', 'kode_sarana');
            $table->renameColumn('status_aset', 'status_sarana');
            $table->renameColumn('foto_aset', 'foto_sarana');
        });

        // 3. Rename table riwayat_kondisi_aset -> riwayat_kondisi_sarana
        Schema::rename('riwayat_kondisi_aset', 'riwayat_kondisi_sarana');
        Schema::table('riwayat_kondisi_sarana', function (Blueprint $table) {
            $table->renameColumn('aset_id', 'sarana_id');
        });

        // 4. Rename columns in table pengajuan
        Schema::table('pengajuan', function (Blueprint $table) {
            $table->renameColumn('aset_id', 'sarana_id');
        });

        // 5. Rename columns in table detail_pengadaan
        Schema::table('detail_pengadaan', function (Blueprint $table) {
            $table->renameColumn('nama_aset_rencana', 'nama_sarana_rencana');
        });

        // 6. Rename columns in table penggantian
        Schema::table('penggantian', function (Blueprint $table) {
            $table->renameColumn('aset_lama_id', 'sarana_lama_id');
            $table->renameColumn('aset_baru_id', 'sarana_baru_id');
            $table->renameColumn('foto_aset_lama', 'foto_sarana_lama');
            $table->renameColumn('foto_aset_baru', 'foto_sarana_baru');
        });

        // 7. Rename table mutasi_aset -> mutasi_sarana
        Schema::rename('mutasi_aset', 'mutasi_sarana');
        Schema::table('mutasi_sarana', function (Blueprint $table) {
            $table->renameColumn('aset_id', 'sarana_id');
        });

        // 8. Update log_aktivitas enum values if mysql
        if (Schema::connection(null)->getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE log_aktivitas MODIFY COLUMN aktivitas ENUM('LOGIN', 'LOGOUT', 'SCAN_QR', 'BUAT_PENGAJUAN', 'APPROVAL', 'TOLAK', 'REALISASI', 'UPDATE_SARANA', 'MUTASI_SARANA') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::connection(null)->getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE log_aktivitas MODIFY COLUMN aktivitas ENUM('LOGIN', 'LOGOUT', 'SCAN_QR', 'BUAT_PENGAJUAN', 'APPROVAL', 'TOLAK', 'REALISASI', 'UPDATE_ASET', 'MUTASI_ASET') NOT NULL");
        }

        Schema::table('mutasi_sarana', function (Blueprint $table) {
            $table->renameColumn('sarana_id', 'aset_id');
        });
        Schema::rename('mutasi_sarana', 'mutasi_aset');

        Schema::table('penggantian', function (Blueprint $table) {
            $table->renameColumn('sarana_lama_id', 'aset_lama_id');
            $table->renameColumn('sarana_baru_id', 'aset_baru_id');
            $table->renameColumn('foto_sarana_lama', 'foto_aset_lama');
            $table->renameColumn('foto_sarana_baru', 'foto_aset_baru');
        });

        Schema::table('detail_pengadaan', function (Blueprint $table) {
            $table->renameColumn('nama_sarana_rencana', 'nama_aset_rencana');
        });

        Schema::table('pengajuan', function (Blueprint $table) {
            $table->renameColumn('sarana_id', 'aset_id');
        });

        Schema::table('riwayat_kondisi_sarana', function (Blueprint $table) {
            $table->renameColumn('sarana_id', 'aset_id');
        });
        Schema::rename('riwayat_kondisi_sarana', 'riwayat_kondisi_aset');

        Schema::table('sarana', function (Blueprint $table) {
            $table->renameColumn('kode_sarana', 'kode_aset');
            $table->renameColumn('status_sarana', 'status_aset');
            $table->renameColumn('foto_sarana', 'foto_aset');
        });
        Schema::rename('sarana', 'aset');

        Schema::rename('kategori_sarana', 'kategori_aset');
    }
};
