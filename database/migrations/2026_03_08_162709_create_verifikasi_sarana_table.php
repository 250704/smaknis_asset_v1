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
        Schema::create('verifikasi_sarana', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan')->onDelete('cascade');
            $table->foreignId('verifikator_id')->constrained('users')->onDelete('cascade');
            $table->enum('status_verifikasi', ['LAYAK', 'TIDAK_LAYAK'])->default('LAYAK');
            $table->text('rekomendasi')->nullable();
            $table->decimal('estimasi_biaya', 15, 2)->nullable();
            $table->json('detail_estimasi')->nullable(); // Rincian estimasi (optional)
            $table->text('catatan')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index('pengajuan_id');
            $table->index('verifikator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifikasi_sarana');
    }
};
