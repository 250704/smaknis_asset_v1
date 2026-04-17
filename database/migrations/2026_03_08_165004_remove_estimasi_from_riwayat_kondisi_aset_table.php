<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Hapus estimasi_biaya dari riwayat_kondisi_aset karena
     * estimasi seharusnya hanya ada di tabel pengajuan untuk konsistensi
     */
    public function up(): void
    {
        Schema::table('riwayat_kondisi_aset', function (Blueprint $table) {
            // Hapus kolom estimasi_biaya - estimasi hanya di tabel pengajuan
            $table->dropColumn('estimasi_biaya');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_kondisi_aset', function (Blueprint $table) {
            $table->decimal('estimasi_biaya', 15, 2)->nullable()->after('catatan_validasi');
        });
    }
};
