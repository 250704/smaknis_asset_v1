<?php

use App\Http\Controllers\BlueprintPageController;
use App\Http\Controllers\KerusakanController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\ScanQrController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:bendahara'])->group(function () {
    Route::view('/bendahara/dashboard', 'bendahara.dashboard')->name('bendahara.dashboard');
    Route::get('/bendahara/scan-qr', [ScanQrController::class, 'index'])
        ->defaults('role', 'bendahara')
        ->name('bendahara.scan');
    Route::get('/bendahara/scan-qr/aksi/{aset}/{action}', [ScanQrController::class, 'quickAction'])
        ->defaults('role', 'bendahara')
        ->name('bendahara.scan.action');
    Route::get('/bendahara/kerusakan/create', [KerusakanController::class, 'create'])
        ->defaults('role', 'bendahara')
        ->name('bendahara.kerusakan.create');
    Route::post('/bendahara/kerusakan', [KerusakanController::class, 'store'])
        ->defaults('role', 'bendahara')
        ->name('bendahara.kerusakan.store');
    Route::get('/bendahara/pengajuan', [PengajuanController::class, 'reviewIndex'])
        ->defaults('role', 'bendahara')
        ->defaults('mode', 'all')
        ->name('bendahara.pengajuan.index');
    Route::get('/bendahara/pengajuan-saya', [PengajuanController::class, 'bendaharaMineIndex'])->name('bendahara.pengajuan.mine');
    Route::get('/bendahara/pengajuan/create', [PengajuanController::class, 'bendaharaCreate'])->name('bendahara.pengajuan.create');
    Route::post('/bendahara/pengajuan', [PengajuanController::class, 'bendaharaStore'])->name('bendahara.pengajuan.store');
    Route::get('/bendahara/approval-anggaran', [PengajuanController::class, 'reviewIndex'])
        ->defaults('role', 'bendahara')
        ->defaults('mode', 'approval')
        ->name('bendahara.pengajuan.approval');
    Route::get('/bendahara/pengajuan/{pengajuan}', [PengajuanController::class, 'show'])
        ->defaults('role', 'bendahara')
        ->name('bendahara.pengajuan.show');
    Route::post('/bendahara/pengajuan/{pengajuan}/approve', [PengajuanController::class, 'approve'])
        ->defaults('role', 'bendahara')
        ->name('bendahara.pengajuan.approve');
    Route::post('/bendahara/pengajuan/{pengajuan}/reject', [PengajuanController::class, 'reject'])
        ->defaults('role', 'bendahara')
        ->name('bendahara.pengajuan.reject');
    Route::post('/bendahara/pengajuan/{pengajuan}/verifikasi-keuangan', [PengajuanController::class, 'verifikasiKeuangan'])
        ->name('bendahara.pengajuan.verifikasi-keuangan');
    Route::get('/bendahara/notifikasi', [NotifikasiController::class, 'index'])->name('bendahara.notifikasi.index');
    Route::get('/bendahara/laporan', [BlueprintPageController::class, 'laporan'])
        ->defaults('role', 'bendahara')
        ->name('bendahara.laporan.index');
    Route::get('/bendahara/laporan/export/excel', [BlueprintPageController::class, 'laporanExportExcel'])
        ->defaults('role', 'bendahara')
        ->name('bendahara.laporan.export.excel');
    Route::get('/bendahara/laporan/export/pdf', [BlueprintPageController::class, 'laporanExportPdf'])
        ->defaults('role', 'bendahara')
        ->name('bendahara.laporan.export.pdf');

    Route::get('/bendahara/fitur/{feature}', function (Request $request, string $feature, BlueprintPageController $controller) {
        if ($feature === 'scan-qr') {
            return redirect()->route('bendahara.scan');
        }

        if ($feature === 'semua-review') {
            return redirect()->route('bendahara.pengajuan.index');
        }

        if ($feature === 'approval-anggaran') {
            return redirect()->route('bendahara.pengajuan.approval');
        }

        if ($feature === 'notifikasi') {
            return redirect()->route('bendahara.notifikasi.index');
        }

        if ($feature === 'pelaporan') {
            return redirect()->route('bendahara.laporan.index');
        }

        return $controller->show($request, 'bendahara', $feature);
    })->name('bendahara.feature');
});
