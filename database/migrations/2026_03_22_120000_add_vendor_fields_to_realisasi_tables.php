<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perawatan', function (Blueprint $table) {
            if (!Schema::hasColumn('perawatan', 'nama_vendor')) {
                $table->string('nama_vendor')->nullable()->after('kontak_teknisi');
            }
            if (!Schema::hasColumn('perawatan', 'kontak_vendor')) {
                $table->string('kontak_vendor', 50)->nullable()->after('nama_vendor');
            }
            if (!Schema::hasColumn('perawatan', 'alamat_vendor')) {
                $table->text('alamat_vendor')->nullable()->after('kontak_vendor');
            }
        });

        Schema::table('penggantian', function (Blueprint $table) {
            if (!Schema::hasColumn('penggantian', 'nama_vendor')) {
                $table->string('nama_vendor')->nullable()->after('kontak_teknisi');
            }
            if (!Schema::hasColumn('penggantian', 'kontak_vendor')) {
                $table->string('kontak_vendor', 50)->nullable()->after('nama_vendor');
            }
            if (!Schema::hasColumn('penggantian', 'alamat_vendor')) {
                $table->text('alamat_vendor')->nullable()->after('kontak_vendor');
            }
        });
    }

    public function down(): void
    {
        Schema::table('perawatan', function (Blueprint $table) {
            if (Schema::hasColumn('perawatan', 'alamat_vendor')) {
                $table->dropColumn('alamat_vendor');
            }
            if (Schema::hasColumn('perawatan', 'kontak_vendor')) {
                $table->dropColumn('kontak_vendor');
            }
            if (Schema::hasColumn('perawatan', 'nama_vendor')) {
                $table->dropColumn('nama_vendor');
            }
        });

        Schema::table('penggantian', function (Blueprint $table) {
            if (Schema::hasColumn('penggantian', 'alamat_vendor')) {
                $table->dropColumn('alamat_vendor');
            }
            if (Schema::hasColumn('penggantian', 'kontak_vendor')) {
                $table->dropColumn('kontak_vendor');
            }
            if (Schema::hasColumn('penggantian', 'nama_vendor')) {
                $table->dropColumn('nama_vendor');
            }
        });
    }
};
