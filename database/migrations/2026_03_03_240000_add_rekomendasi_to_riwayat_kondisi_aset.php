<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riwayat_kondisi_aset', function (Blueprint $table) {
            $table->enum('rekomendasi_tindakan', ['PERAWATAN', 'PENGGANTIAN'])->nullable()->after('validated_at');
            $table->decimal('estimasi_biaya', 15, 2)->nullable()->after('rekomendasi_tindakan');
            $table->text('catatan_validasi')->nullable()->after('estimasi_biaya');
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_kondisi_aset', function (Blueprint $table) {
            $table->dropColumn(['rekomendasi_tindakan', 'estimasi_biaya', 'catatan_validasi']);
        });
    }
};
