<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE pengajuan
                MODIFY status_pengajuan ENUM(
                    'DIAJUKAN',
                    'DISETUJUI_KASARANA',
                    'DISETUJUI_BENDAHARA',
                    'DISETUJUI_KEPSEK',
                    'DITOLAK',
                    'DIPROSES',
                    'MENUNGGU_VERIFIKASI_TEKNIS',
                    'MENUNGGU_VERIFIKASI_KEUANGAN',
                    'SELESAI'
                ) NOT NULL DEFAULT 'DIAJUKAN'
            ");

            DB::statement("
                ALTER TABLE approval_pengajuan
                MODIFY role_approval ENUM(
                    'KASARANA',
                    'BENDAHARA',
                    'KEPSEK',
                    'KASARANA_VERIFIKASI',
                    'BENDAHARA_VERIFIKASI'
                ) NOT NULL
            ");
        }
    }

    public function down(): void
    {
        DB::table('pengajuan')
            ->whereIn('status_pengajuan', ['MENUNGGU_VERIFIKASI_TEKNIS', 'MENUNGGU_VERIFIKASI_KEUANGAN'])
            ->update(['status_pengajuan' => 'DIPROSES']);

        DB::table('approval_pengajuan')
            ->where('role_approval', 'KASARANA_VERIFIKASI')
            ->update(['role_approval' => 'KASARANA']);
        DB::table('approval_pengajuan')
            ->where('role_approval', 'BENDAHARA_VERIFIKASI')
            ->update(['role_approval' => 'BENDAHARA']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE pengajuan
                MODIFY status_pengajuan ENUM(
                    'DIAJUKAN',
                    'DISETUJUI_KASARANA',
                    'DISETUJUI_BENDAHARA',
                    'DISETUJUI_KEPSEK',
                    'DITOLAK',
                    'DIPROSES',
                    'SELESAI'
                ) NOT NULL DEFAULT 'DIAJUKAN'
            ");

            DB::statement("
                ALTER TABLE approval_pengajuan
                MODIFY role_approval ENUM(
                    'KASARANA',
                    'BENDAHARA',
                    'KEPSEK'
                ) NOT NULL
            ");
        }
    }
};

