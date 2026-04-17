<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nama_role', 50)->unique();
            $table->timestamps();
        });

        $now = now();
        DB::table('roles')->insert([
            ['nama_role' => 'admin', 'created_at' => $now, 'updated_at' => $now],
            ['nama_role' => 'guru', 'created_at' => $now, 'updated_at' => $now],
            ['nama_role' => 'kepala_sarana', 'created_at' => $now, 'updated_at' => $now],
            ['nama_role' => 'bendahara', 'created_at' => $now, 'updated_at' => $now],
            ['nama_role' => 'kepala_sekolah', 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained('roles')->nullOnDelete();
            $table->string('nama', 100)->nullable()->after('role_id');
            $table->enum('status_akun', ['AKTIF', 'NONAKTIF'])->default('AKTIF')->after('role');
            $table->softDeletes();
        });

        DB::table('users')->update(['nama' => DB::raw('COALESCE(nama, name)')]);

        $roleMap = DB::table('roles')->pluck('id', 'nama_role');
        foreach ($roleMap as $namaRole => $roleId) {
            DB::table('users')
                ->where('role', $namaRole)
                ->update(['role_id' => $roleId]);
        }

        Schema::create('gedung', function (Blueprint $table) {
            $table->id();
            $table->string('nama_gedung', 100);
            $table->timestamps();
        });

        Schema::create('ruangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gedung_id')->constrained('gedung')->cascadeOnDelete();
            $table->string('nama_ruangan', 100);
            $table->timestamps();
        });

        Schema::create('kategori_aset', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori', 100);
            $table->timestamps();
        });

        Schema::create('aset', function (Blueprint $table) {
            $table->id();
            $table->string('kode_aset', 50)->unique();
            $table->string('nama_aset', 200);
            $table->foreignId('kategori_id')->constrained('kategori_aset')->restrictOnDelete();
            $table->foreignId('ruangan_id')->constrained('ruangan')->restrictOnDelete();
            $table->year('tahun_perolehan');
            $table->decimal('harga_perolehan', 15, 2)->nullable();
            $table->enum('kondisi_terkini', ['BAIK', 'RINGAN', 'BERAT', 'TIDAK_LAYAK'])->default('BAIK');
            $table->enum('status_aset', ['AKTIF', 'NONAKTIF'])->default('AKTIF');
            $table->string('foto_aset')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('riwayat_kondisi_aset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aset_id')->constrained('aset')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->enum('tingkat_kerusakan', ['RINGAN', 'BERAT', 'TIDAK_LAYAK']);
            $table->text('deskripsi');
            $table->string('foto_kerusakan');
            $table->enum('status', ['DILAPORKAN', 'DIVALIDASI', 'DITINDAKLANJUTI'])->default('DILAPORKAN');
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pengajuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aset_id')->nullable()->constrained('aset')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('judul_pengajuan', 200);
            $table->enum('jenis_pengajuan', ['PERAWATAN', 'PENGGANTIAN', 'PENGADAAN']);
            $table->text('deskripsi');
            $table->decimal('estimasi_biaya', 15, 2)->nullable();
            $table->date('target_realisasi')->nullable();
            $table->enum('status_pengajuan', [
                'DIAJUKAN',
                'DISETUJUI_KASARANA',
                'DISETUJUI_BENDAHARA',
                'DISETUJUI_KEPSEK',
                'DITOLAK',
                'DIPROSES',
                'MENUNGGU_VERIFIKASI_TEKNIS',
                'MENUNGGU_VERIFIKASI_KEUANGAN',
                'SELESAI',
            ])->default('DIAJUKAN');
            $table->timestamps();
        });

        Schema::create('detail_pengadaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan')->cascadeOnDelete();
            $table->string('nama_aset_rencana', 200);
            $table->foreignId('kategori_id')->constrained('kategori_aset')->restrictOnDelete();
            $table->foreignId('ruangan_id')->constrained('ruangan')->restrictOnDelete();
            $table->unsignedInteger('jumlah');
            $table->text('spesifikasi')->nullable();
            $table->decimal('estimasi_harga_satuan', 15, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('approval_pengajuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan')->cascadeOnDelete();
            $table->foreignId('approver_id')->constrained('users')->restrictOnDelete();
            $table->enum('role_approval', ['KASARANA', 'BENDAHARA', 'KEPSEK', 'KASARANA_VERIFIKASI', 'BENDAHARA_VERIFIKASI']);
            $table->enum('status', ['DISETUJUI', 'DITOLAK']);
            $table->text('catatan')->nullable();
            $table->timestamp('approved_at');
            $table->timestamps();
        });

        Schema::create('perawatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->unique()->constrained('pengajuan')->cascadeOnDelete();
            $table->date('tanggal_perawatan');
            $table->string('foto_sebelum');
            $table->string('foto_sesudah');
            $table->decimal('biaya_realisasi', 15, 2)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('penggantian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->unique()->constrained('pengajuan')->cascadeOnDelete();
            $table->foreignId('aset_lama_id')->constrained('aset')->restrictOnDelete();
            $table->foreignId('aset_baru_id')->nullable()->constrained('aset')->nullOnDelete();
            $table->string('foto_aset_lama');
            $table->string('foto_aset_baru');
            $table->decimal('biaya_realisasi', 15, 2)->nullable();
            $table->enum('status_realisasi', ['MENUNGGU_ASET_BARU', 'SELESAI'])->default('MENUNGGU_ASET_BARU');
            $table->date('tanggal_penggantian')->nullable();
            $table->timestamps();
        });

        Schema::create('mutasi_aset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aset_id')->constrained('aset')->cascadeOnDelete();
            $table->foreignId('pengajuan_id')->nullable()->constrained('pengajuan')->nullOnDelete();
            $table->foreignId('ruangan_asal')->constrained('ruangan')->restrictOnDelete();
            $table->foreignId('ruangan_tujuan')->constrained('ruangan')->restrictOnDelete();
            $table->foreignId('user_pengaju_id')->constrained('users')->restrictOnDelete();
            $table->enum('status_mutasi', ['DIAJUKAN', 'DISETUJUI', 'DITOLAK'])->default('DIAJUKAN');
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal_mutasi')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul', 200);
            $table->text('isi');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('aktivitas', [
                'LOGIN',
                'LOGOUT',
                'SCAN_QR',
                'BUAT_PENGAJUAN',
                'APPROVAL',
                'TOLAK',
                'REALISASI',
                'UPDATE_ASET',
                'MUTASI_ASET',
            ]);
            $table->string('modul', 100);
            $table->text('deskripsi')->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
        Schema::dropIfExists('notifikasi');
        Schema::dropIfExists('mutasi_aset');
        Schema::dropIfExists('penggantian');
        Schema::dropIfExists('perawatan');
        Schema::dropIfExists('approval_pengajuan');
        Schema::dropIfExists('detail_pengadaan');
        Schema::dropIfExists('pengajuan');
        Schema::dropIfExists('riwayat_kondisi_aset');
        Schema::dropIfExists('aset');
        Schema::dropIfExists('kategori_aset');
        Schema::dropIfExists('ruangan');
        Schema::dropIfExists('gedung');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn(['nama', 'status_akun', 'deleted_at']);
        });

        Schema::dropIfExists('roles');
    }
};
