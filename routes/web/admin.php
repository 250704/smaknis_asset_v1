<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Inventaris\AsetController;
use App\Http\Controllers\Admin\Inventaris\CetakQrController;
use App\Http\Controllers\Admin\MasterData\GedungController;
use App\Http\Controllers\Admin\MasterData\KategoriAsetController;
use App\Http\Controllers\Admin\MasterData\RuanganController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\BlueprintPageController;
use App\Http\Controllers\KerusakanController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\ScanQrController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/scan-qr', [ScanQrController::class, 'index'])
        ->defaults('role', 'admin')
        ->name('admin.scan');
    Route::get('/admin/scan-qr/aksi/{aset}/{action}', [ScanQrController::class, 'quickAction'])
        ->defaults('role', 'admin')
        ->name('admin.scan.action');
    Route::get('/admin/kerusakan/create', [KerusakanController::class, 'create'])
        ->defaults('role', 'admin')
        ->name('admin.kerusakan.create');
    Route::post('/admin/kerusakan', [KerusakanController::class, 'store'])
        ->defaults('role', 'admin')
        ->name('admin.kerusakan.store');

    Route::prefix('/admin/inventaris')
        ->name('admin.')
        ->group(function () {
            Route::get('/aset/create-unit', [AsetController::class, 'createUnit'])->name('aset.create-unit');
            Route::get('/aset/import-massal', [AsetController::class, 'createImportMassal'])->name('aset.import-massal.create');
            Route::post('/aset/import-massal', [AsetController::class, 'storeImportMassal'])->name('aset.import-massal.store');
            Route::post('/aset/per-ruangan', [AsetController::class, 'storePerRuangan'])->name('aset.store-per-ruangan');
            Route::post('/aset/per-kategori', [AsetController::class, 'storePerKategori'])->name('aset.store-per-kategori');
            Route::delete('/aset/destroy-selected', [AsetController::class, 'destroySelected'])->name('aset.destroy-selected');
            Route::delete('/aset/destroy-all', [AsetController::class, 'destroyAll'])->name('aset.destroy-all');
            Route::resource('/aset', AsetController::class);
            Route::get('/cetak-qr', [CetakQrController::class, 'index'])->name('cetak-qr.index');
        });

    Route::get('/admin/pengajuan', [PengajuanController::class, 'adminIndex'])->name('admin.pengajuan.index');
    Route::get('/admin/pengajuan/create', [PengajuanController::class, 'adminCreate'])->name('admin.pengajuan.create');
    Route::post('/admin/pengajuan', [PengajuanController::class, 'adminStore'])->name('admin.pengajuan.store');
    Route::get('/admin/pengajuan/{pengajuan}', [PengajuanController::class, 'show'])->name('admin.pengajuan.show');
    Route::get('/admin/realisasi', [PengajuanController::class, 'adminRealisasiIndex'])->name('admin.realisasi.index');
    Route::get('/admin/realisasi/{pengajuan}', [PengajuanController::class, 'adminRealisasiShow'])->name('admin.realisasi.show');
    Route::post('/admin/pengajuan/{pengajuan}/perawatan', [PengajuanController::class, 'realisasiPerawatan'])->name('admin.pengajuan.perawatan');
    Route::post('/admin/pengajuan/{pengajuan}/penggantian', [PengajuanController::class, 'realisasiPenggantian'])->name('admin.pengajuan.penggantian');
    Route::get('/admin/notifikasi', [NotifikasiController::class, 'index'])->name('admin.notifikasi.index');

    Route::get('/admin/manajemen-user', [UserManagementController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/manajemen-user', [UserManagementController::class, 'store'])->name('admin.users.store');
    Route::patch('/admin/manajemen-user/{user}', [UserManagementController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/manajemen-user/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');

    Route::get('/admin/laporan', [BlueprintPageController::class, 'laporan'])
        ->defaults('role', 'admin')
        ->name('admin.laporan.index');
    Route::get('/admin/laporan/export/excel', [BlueprintPageController::class, 'laporanExportExcel'])
        ->defaults('role', 'admin')
        ->name('admin.laporan.export.excel');
    Route::get('/admin/laporan/export/pdf', [BlueprintPageController::class, 'laporanExportPdf'])
        ->defaults('role', 'admin')
        ->name('admin.laporan.export.pdf');

    Route::prefix('/admin/master')
        ->name('admin.master.')
        ->group(function () {
            Route::resource('/gedung', GedungController::class)->except(['create', 'show']);
            Route::resource('/ruangan', RuanganController::class)->except(['create', 'show']);
            Route::resource('/kategori-aset', KategoriAsetController::class)->except(['create', 'show']);
        });

    Route::get('/admin/fitur/{feature}', function (Request $request, string $feature, BlueprintPageController $controller) {
        if ($feature === 'scan-qr') {
            return redirect()->route('admin.scan');
        }

        if ($feature === 'data-aset') {
            return redirect()->route('admin.aset.index');
        }

        if ($feature === 'tambah-aset') {
            return redirect()->route('admin.aset.create');
        }

        if ($feature === 'cetak-qr') {
            return redirect()->route('admin.cetak-qr.index');
        }

        if ($feature === 'semua-pengajuan') {
            return redirect()->route('admin.pengajuan.index');
        }

        if ($feature === 'realisasi') {
            return redirect()->route('admin.realisasi.index');
        }

        if ($feature === 'notifikasi') {
            return redirect()->route('admin.notifikasi.index');
        }

        if ($feature === 'pelaporan') {
            return redirect()->route('admin.laporan.index');
        }

        if ($feature === 'manajemen-user') {
            return redirect()->route('admin.users.index');
        }

        return $controller->show($request, 'admin', $feature);
    })->name('admin.feature');
});
