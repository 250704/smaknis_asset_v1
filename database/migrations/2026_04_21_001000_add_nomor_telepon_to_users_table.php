<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'nomor_telepon')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('nomor_telepon', 30)->nullable()->after('email');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'nomor_telepon')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('nomor_telepon');
            });
        }
    }
};
