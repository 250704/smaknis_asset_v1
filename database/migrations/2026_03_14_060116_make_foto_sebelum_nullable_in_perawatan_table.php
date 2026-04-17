<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perawatan', function (Blueprint $table) {
            $table->string('foto_sebelum')->nullable()->change();
        });

        Schema::table('penggantian', function (Blueprint $table) {
            $table->string('foto_aset_lama')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('perawatan', function (Blueprint $table) {
            $table->string('foto_sebelum')->nullable(false)->change();
        });

        Schema::table('penggantian', function (Blueprint $table) {
            $table->string('foto_aset_lama')->nullable(false)->change();
        });
    }
};
