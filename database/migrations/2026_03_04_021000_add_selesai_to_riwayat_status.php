<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE riwayat_kondisi_aset MODIFY status ENUM('DILAPORKAN','DIVALIDASI','DITINDAKLANJUTI','SELESAI','DITOLAK') NOT NULL DEFAULT 'DILAPORKAN'");
    }

    public function down(): void
    {
        DB::table('riwayat_kondisi_aset')
            ->where('status', 'SELESAI')
            ->update(['status' => 'DITINDAKLANJUTI']);

        DB::statement("ALTER TABLE riwayat_kondisi_aset MODIFY status ENUM('DILAPORKAN','DIVALIDASI','DITINDAKLANJUTI','DITOLAK') NOT NULL DEFAULT 'DILAPORKAN'");
    }
};

