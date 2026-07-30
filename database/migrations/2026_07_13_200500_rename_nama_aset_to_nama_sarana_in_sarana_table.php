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
        if (Schema::hasColumn('sarana', 'nama_aset')) {
            Schema::table('sarana', function (Blueprint $table) {
                $table->renameColumn('nama_aset', 'nama_sarana');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sarana', 'nama_sarana')) {
            Schema::table('sarana', function (Blueprint $table) {
                $table->renameColumn('nama_sarana', 'nama_aset');
            });
        }
    }
};
