<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE aset MODIFY kondisi_terkini ENUM('BAIK','KR1','KR2','KR3','RINGAN','BERAT','TIDAK_LAYAK') NOT NULL DEFAULT 'BAIK'");
            DB::statement("ALTER TABLE riwayat_kondisi_aset MODIFY tingkat_kerusakan ENUM('KR1','KR2','KR3','RINGAN','BERAT','TIDAK_LAYAK') NOT NULL");
        }

        DB::table('aset')->where('kondisi_terkini', 'KR1')->update(['kondisi_terkini' => 'RINGAN']);
        DB::table('aset')->where('kondisi_terkini', 'KR2')->update(['kondisi_terkini' => 'BERAT']);
        DB::table('aset')->where('kondisi_terkini', 'KR3')->update(['kondisi_terkini' => 'TIDAK_LAYAK']);

        DB::table('riwayat_kondisi_aset')->where('tingkat_kerusakan', 'KR1')->update(['tingkat_kerusakan' => 'RINGAN']);
        DB::table('riwayat_kondisi_aset')->where('tingkat_kerusakan', 'KR2')->update(['tingkat_kerusakan' => 'BERAT']);
        DB::table('riwayat_kondisi_aset')->where('tingkat_kerusakan', 'KR3')->update(['tingkat_kerusakan' => 'TIDAK_LAYAK']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE aset MODIFY kondisi_terkini ENUM('BAIK','RINGAN','BERAT','TIDAK_LAYAK') NOT NULL DEFAULT 'BAIK'");
            DB::statement("ALTER TABLE riwayat_kondisi_aset MODIFY tingkat_kerusakan ENUM('RINGAN','BERAT','TIDAK_LAYAK') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE aset MODIFY kondisi_terkini ENUM('BAIK','KR1','KR2','KR3','RINGAN','BERAT','TIDAK_LAYAK') NOT NULL DEFAULT 'BAIK'");
            DB::statement("ALTER TABLE riwayat_kondisi_aset MODIFY tingkat_kerusakan ENUM('KR1','KR2','KR3','RINGAN','BERAT','TIDAK_LAYAK') NOT NULL");
        }

        DB::table('aset')->where('kondisi_terkini', 'RINGAN')->update(['kondisi_terkini' => 'KR1']);
        DB::table('aset')->where('kondisi_terkini', 'BERAT')->update(['kondisi_terkini' => 'KR2']);
        DB::table('aset')->where('kondisi_terkini', 'TIDAK_LAYAK')->update(['kondisi_terkini' => 'KR3']);

        DB::table('riwayat_kondisi_aset')->where('tingkat_kerusakan', 'RINGAN')->update(['tingkat_kerusakan' => 'KR1']);
        DB::table('riwayat_kondisi_aset')->where('tingkat_kerusakan', 'BERAT')->update(['tingkat_kerusakan' => 'KR2']);
        DB::table('riwayat_kondisi_aset')->where('tingkat_kerusakan', 'TIDAK_LAYAK')->update(['tingkat_kerusakan' => 'KR3']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE aset MODIFY kondisi_terkini ENUM('BAIK','KR1','KR2','KR3') NOT NULL DEFAULT 'BAIK'");
            DB::statement("ALTER TABLE riwayat_kondisi_aset MODIFY tingkat_kerusakan ENUM('KR1','KR2','KR3') NOT NULL");
        }
    }
};
