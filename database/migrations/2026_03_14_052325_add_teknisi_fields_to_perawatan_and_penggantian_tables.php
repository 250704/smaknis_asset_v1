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
        // Tambah kolom keterangan di penggantian jika belum ada
        if (!Schema::hasColumn('penggantian', 'keterangan')) {
            Schema::table('penggantian', function (Blueprint $table) {
                $table->text('keterangan')->nullable()->after('biaya_realisasi');
            });
        }

        // Tambah kolom teknisi di perawatan jika belum ada
        if (!Schema::hasColumn('perawatan', 'nama_teknisi')) {
            Schema::table('perawatan', function (Blueprint $table) {
                $table->string('nama_teknisi')->nullable()->after('keterangan');
                $table->string('kontak_teknisi')->nullable()->after('nama_teknisi');
            });
        }

        // Tambah kolom teknisi di penggantian jika belum ada
        if (!Schema::hasColumn('penggantian', 'nama_teknisi')) {
            Schema::table('penggantian', function (Blueprint $table) {
                $table->string('nama_teknisi')->nullable()->after('keterangan');
                $table->string('kontak_teknisi')->nullable()->after('nama_teknisi');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perawatan', function (Blueprint $table) {
            if (Schema::hasColumn('perawatan', 'kontak_teknisi')) {
                $table->dropColumn('kontak_teknisi');
            }
            if (Schema::hasColumn('perawatan', 'nama_teknisi')) {
                $table->dropColumn('nama_teknisi');
            }
        });

        Schema::table('penggantian', function (Blueprint $table) {
            if (Schema::hasColumn('penggantian', 'kontak_teknisi')) {
                $table->dropColumn('kontak_teknisi');
            }
            if (Schema::hasColumn('penggantian', 'nama_teknisi')) {
                $table->dropColumn('nama_teknisi');
            }
        });
    }
};
