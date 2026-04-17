<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perawatan', function (Blueprint $table) {
            if (!Schema::hasColumn('perawatan', 'foto_bukti')) {
                $table->string('foto_bukti')->nullable()->after('foto_sesudah');
            }
            if (Schema::hasColumn('perawatan', 'alamat_vendor')) {
                $table->dropColumn('alamat_vendor');
            }
        });

        Schema::table('penggantian', function (Blueprint $table) {
            if (!Schema::hasColumn('penggantian', 'foto_bukti')) {
                $table->string('foto_bukti')->nullable()->after('foto_aset_baru');
            }
            if (Schema::hasColumn('penggantian', 'alamat_vendor')) {
                $table->dropColumn('alamat_vendor');
            }
        });
    }

    public function down(): void
    {
        Schema::table('perawatan', function (Blueprint $table) {
            if (Schema::hasColumn('perawatan', 'foto_bukti')) {
                $table->dropColumn('foto_bukti');
            }
            if (!Schema::hasColumn('perawatan', 'alamat_vendor')) {
                $table->text('alamat_vendor')->nullable()->after('kontak_vendor');
            }
        });

        Schema::table('penggantian', function (Blueprint $table) {
            if (Schema::hasColumn('penggantian', 'foto_bukti')) {
                $table->dropColumn('foto_bukti');
            }
            if (!Schema::hasColumn('penggantian', 'alamat_vendor')) {
                $table->text('alamat_vendor')->nullable()->after('kontak_vendor');
            }
        });
    }
};

