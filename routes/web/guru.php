<?php

use App\Http\Controllers\BlueprintPageController;
use App\Http\Controllers\KerusakanController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\ScanQrController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:guru'])->group(function () {
    Route::view('/guru/dashboard', 'guru.dashboard')->name('guru.dashboard');
    Route::get('/guru/scan-qr', [ScanQrController::class, 'index'])
        ->defaults('role', 'guru')
        ->name('guru.scan');
    Route::get('/guru/scan-qr/aksi/{aset}/{action}', [ScanQrController::class, 'quickAction'])
        ->defaults('role', 'guru')
        ->name('guru.scan.action');
    Route::get('/guru/pengajuan', [PengajuanController::class, 'guruIndex'])->name('guru.pengajuan.index');
    Route::get('/guru/pengajuan/create', [PengajuanController::class, 'guruCreate'])->name('guru.pengajuan.create');
    Route::post('/guru/pengajuan', [PengajuanController::class, 'guruStore'])->name('guru.pengajuan.store');
    Route::get('/guru/pengajuan/{pengajuan}', [PengajuanController::class, 'show'])->name('guru.pengajuan.show');
    Route::get('/guru/kerusakan/create', [KerusakanController::class, 'guruCreate'])->name('guru.kerusakan.create');
    Route::post('/guru/kerusakan', [KerusakanController::class, 'guruStore'])->name('guru.kerusakan.store');
    Route::get('/guru/notifikasi', [NotifikasiController::class, 'index'])->name('guru.notifikasi.index');

    Route::get('/guru/fitur/{feature}', function (Request $request, string $feature, BlueprintPageController $controller) {
        if ($feature === 'scan-qr') {
            return redirect()->route('guru.scan');
        }

        if ($feature === 'buat-pengajuan') {
            return redirect()->route('guru.pengajuan.create');
        }

        if ($feature === 'lapor-kerusakan') {
            return redirect()->route('guru.kerusakan.create');
        }

        if ($feature === 'riwayat-pengajuan') {
            return redirect()->route('guru.pengajuan.index');
        }

        if ($feature === 'notifikasi') {
            return redirect()->route('guru.notifikasi.index');
        }

        if ($feature === 'pelaporan') {
            abort(403, 'Akses laporan hanya untuk admin, kepala sarana, bendahara, dan kepala sekolah.');
        }

        return $controller->show($request, 'guru', $feature);
    })->name('guru.feature');
});
