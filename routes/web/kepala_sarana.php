<?php

use App\Http\Controllers\BlueprintPageController;
use App\Http\Controllers\KepalaSarana\AsetController as KepalaSaranaAsetController;
use App\Http\Controllers\KerusakanController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\ScanQrController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:kepala_sarana'])->group(function () {
    Route::view('/kepala_sarana/dashboard', 'kepala_sarana.dashboard')->name('kepala_sarana.dashboard');
    Route::get('/kepala_sarana/aset', [KepalaSaranaAsetController::class, 'index'])->name('kepala_sarana.aset.index');
    Route::get('/kepala_sarana/aset/histori', [KepalaSaranaAsetController::class, 'histori'])->name('kepala_sarana.aset.histori');
    Route::get('/kepala_sarana/scan-qr', [ScanQrController::class, 'index'])
        ->defaults('role', 'kepala_sarana')
        ->name('kepala_sarana.scan');
    Route::get('/kepala_sarana/scan-qr/aksi/{aset}/{action}', [ScanQrController::class, 'quickAction'])
        ->defaults('role', 'kepala_sarana')
        ->name('kepala_sarana.scan.action');
    Route::get('/kepala_sarana/kerusakan/create', [KerusakanController::class, 'create'])
        ->defaults('role', 'kepala_sarana')
        ->name('kepala_sarana.kerusakan.create');
    Route::post('/kepala_sarana/kerusakan', [KerusakanController::class, 'store'])
        ->defaults('role', 'kepala_sarana')
        ->name('kepala_sarana.kerusakan.store');
    Route::get('/kepala_sarana/pengajuan/approval-teknis', [PengajuanController::class, 'reviewIndex'])
        ->defaults('role', 'kepala_sarana')
        ->defaults('mode', 'approval')
        ->name('kepala_sarana.pengajuan.approval');
    Route::get('/kepala_sarana/pengajuan-saya', [PengajuanController::class, 'kepalaSaranaMineIndex'])->name('kepala_sarana.pengajuan.mine');
    Route::get('/kepala_sarana/pengajuan/create', [PengajuanController::class, 'kepalaSaranaCreate'])->name('kepala_sarana.pengajuan.create');
    Route::post('/kepala_sarana/pengajuan', [PengajuanController::class, 'kepalaSaranaStore'])->name('kepala_sarana.pengajuan.store');
    Route::get('/kepala_sarana/validasi/semua-proses', [KerusakanController::class, 'kepalaSaranaSemuaProses'])->name('kepala_sarana.validasi.semua');
    Route::get('/kepala_sarana/pengajuan/{pengajuan}', [PengajuanController::class, 'show'])
        ->defaults('role', 'kepala_sarana')
        ->name('kepala_sarana.pengajuan.show');
    Route::post('/kepala_sarana/pengajuan/{pengajuan}/approve', [PengajuanController::class, 'approve'])
        ->defaults('role', 'kepala_sarana')
        ->name('kepala_sarana.pengajuan.approve');
    Route::post('/kepala_sarana/pengajuan/{pengajuan}/reject', [PengajuanController::class, 'reject'])
        ->defaults('role', 'kepala_sarana')
        ->name('kepala_sarana.pengajuan.reject');
    Route::post('/kepala_sarana/pengajuan/{pengajuan}/verifikasi-teknis', [PengajuanController::class, 'verifikasiTeknis'])
        ->name('kepala_sarana.pengajuan.verifikasi-teknis');
    Route::get('/kepala_sarana/kerusakan-realisasi', [KerusakanController::class, 'kepalaSaranaRealisasiIndex'])->name('kepala_sarana.kerusakan.realisasi');
    Route::get('/kepala_sarana/kerusakan', [KerusakanController::class, 'kepalaSaranaIndex'])->name('kepala_sarana.kerusakan.index');
    Route::post('/kepala_sarana/kerusakan/{riwayat}/validate', [KerusakanController::class, 'validateKerusakan'])->name('kepala_sarana.kerusakan.validate');
    Route::get('/kepala_sarana/notifikasi', [NotifikasiController::class, 'index'])->name('kepala_sarana.notifikasi.index');
    Route::get('/kepala_sarana/laporan', [BlueprintPageController::class, 'laporan'])
        ->defaults('role', 'kepala_sarana')
        ->name('kepala_sarana.laporan.index');
    Route::get('/kepala_sarana/laporan/export/excel', [BlueprintPageController::class, 'laporanExportExcel'])
        ->defaults('role', 'kepala_sarana')
        ->name('kepala_sarana.laporan.export.excel');
    Route::get('/kepala_sarana/laporan/export/pdf', [BlueprintPageController::class, 'laporanExportPdf'])
        ->defaults('role', 'kepala_sarana')
        ->name('kepala_sarana.laporan.export.pdf');

    Route::get('/kepala_sarana/fitur/{feature}', function (Request $request, string $feature, BlueprintPageController $controller) {
        if ($feature === 'scan-qr') {
            return redirect()->route('kepala_sarana.scan');
        }

        if ($feature === 'data-aset') {
            return redirect()->route('kepala_sarana.aset.index');
        }

        if ($feature === 'histori-aset') {
            return redirect()->route('kepala_sarana.aset.histori');
        }

        if ($feature === 'validasi-kerusakan') {
            return redirect()->route('kepala_sarana.kerusakan.index');
        }

        if ($feature === 'approval-teknis') {
            return redirect()->route('kepala_sarana.pengajuan.approval');
        }

        if ($feature === 'semua-proses') {
            return redirect()->route('kepala_sarana.validasi.semua');
        }

        if ($feature === 'notifikasi') {
            return redirect()->route('kepala_sarana.notifikasi.index');
        }

        if ($feature === 'pelaporan') {
            return redirect()->route('kepala_sarana.laporan.index');
        }

        return $controller->show($request, 'kepala_sarana', $feature);
    })->name('kepala_sarana.feature');
});
