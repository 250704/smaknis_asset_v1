<?php

use App\Http\Controllers\BlueprintPageController;
use App\Http\Controllers\KerusakanController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\ScanQrController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:kepala_sekolah'])->group(function () {
    Route::view('/kepala_sekolah/dashboard', 'kepala_sekolah.dashboard')->name('kepala_sekolah.dashboard');
    Route::get('/kepala_sekolah/scan-qr', [ScanQrController::class, 'index'])
        ->defaults('role', 'kepala_sekolah')
        ->name('kepala_sekolah.scan');
    Route::get('/kepala_sekolah/scan-qr/aksi/{sarana}/{action}', [ScanQrController::class, 'quickAction'])
        ->defaults('role', 'kepala_sekolah')
        ->name('kepala_sekolah.scan.action');
    Route::get('/kepala_sekolah/kerusakan/create', [KerusakanController::class, 'create'])
        ->defaults('role', 'kepala_sekolah')
        ->name('kepala_sekolah.kerusakan.create');
    Route::post('/kepala_sekolah/kerusakan', [KerusakanController::class, 'store'])
        ->defaults('role', 'kepala_sekolah')
        ->name('kepala_sekolah.kerusakan.store');
    Route::get('/kepala_sekolah/kerusakan', [KerusakanController::class, 'kepalaSaranaIndex'])->name('kepala_sekolah.kerusakan.index');
    Route::post('/kepala_sekolah/kerusakan/{riwayat}/validate', [KerusakanController::class, 'validateKerusakan'])->name('kepala_sekolah.kerusakan.validate');
    Route::get('/kepala_sekolah/pengajuan', [PengajuanController::class, 'reviewIndex'])
        ->defaults('role', 'kepala_sekolah')
        ->defaults('mode', 'approval')
        ->name('kepala_sekolah.pengajuan.index');
    Route::get('/kepala_sekolah/pengajuan-semua', [PengajuanController::class, 'adminIndex'])->name('kepala_sekolah.pengajuan.semua');
    Route::get('/kepala_sekolah/pengajuan-saya', [PengajuanController::class, 'kepalaSekolahMineIndex'])->name('kepala_sekolah.pengajuan.mine');
    Route::get('/kepala_sekolah/pengajuan/create', [PengajuanController::class, 'kepalaSekolahCreate'])->name('kepala_sekolah.pengajuan.create');
    Route::post('/kepala_sekolah/pengajuan', [PengajuanController::class, 'kepalaSekolahStore'])->name('kepala_sekolah.pengajuan.store');
    Route::get('/kepala_sekolah/pengajuan/{pengajuan}', [PengajuanController::class, 'show'])
        ->defaults('role', 'kepala_sekolah')
        ->missing(function () {
            return redirect()->route('kepala_sekolah.pengajuan.index')->with('info', 'Data pengajuan tidak ditemukan atau sudah dihapus.');
        })
        ->name('kepala_sekolah.pengajuan.show');
    Route::post('/kepala_sekolah/pengajuan/{pengajuan}/approve', [PengajuanController::class, 'approve'])
        ->defaults('role', 'kepala_sekolah')
        ->name('kepala_sekolah.pengajuan.approve');
    Route::post('/kepala_sekolah/pengajuan/{pengajuan}/reject', [PengajuanController::class, 'reject'])
        ->defaults('role', 'kepala_sekolah')
        ->name('kepala_sekolah.pengajuan.reject');
    Route::get('/kepala_sekolah/mutasi', [\App\Http\Controllers\MutasiController::class, 'index'])->name('kepala_sekolah.mutasi.index');
    Route::get('/kepala_sekolah/mutasi/create', [\App\Http\Controllers\MutasiController::class, 'create'])->name('kepala_sekolah.mutasi.create');
    Route::post('/kepala_sekolah/mutasi', [\App\Http\Controllers\MutasiController::class, 'store'])->name('kepala_sekolah.mutasi.store');
    Route::get('/kepala_sekolah/mutasi/{mutasi}', [\App\Http\Controllers\MutasiController::class, 'show'])->name('kepala_sekolah.mutasi.show');
    Route::get('/kepala_sekolah/notifikasi', [NotifikasiController::class, 'index'])->name('kepala_sekolah.notifikasi.index');
    Route::get('/kepala_sekolah/laporan', [BlueprintPageController::class, 'laporan'])
        ->defaults('role', 'kepala_sekolah')
        ->name('kepala_sekolah.laporan.index');
    Route::get('/kepala_sekolah/laporan/export/excel', [BlueprintPageController::class, 'laporanExportExcel'])
        ->defaults('role', 'kepala_sekolah')
        ->name('kepala_sekolah.laporan.export.excel');
    Route::get('/kepala_sekolah/laporan/export/pdf', [BlueprintPageController::class, 'laporanExportPdf'])
        ->defaults('role', 'kepala_sekolah')
        ->name('kepala_sekolah.laporan.export.pdf');

    Route::get('/kepala_sekolah/fitur/{feature}', function (Request $request, string $feature, BlueprintPageController $controller) {
        if ($feature === 'scan-qr') {
            return redirect()->route('kepala_sekolah.scan');
        }

        if ($feature === 'approval-final') {
            return redirect()->route('kepala_sekolah.pengajuan.index');
        }

        if ($feature === 'notifikasi') {
            return redirect()->route('kepala_sekolah.notifikasi.index');
        }

        if ($feature === 'pelaporan') {
            return redirect()->route('kepala_sekolah.laporan.index');
        }

        return $controller->show($request, 'kepala_sekolah', $feature);
    })->name('kepala_sekolah.feature');
});
