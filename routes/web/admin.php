<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Inventaris\SaranaController;
use App\Http\Controllers\Admin\Inventaris\CetakQrController;
use App\Http\Controllers\Admin\MasterData\GedungController;
use App\Http\Controllers\Admin\MasterData\KategoriSaranaController;
use App\Http\Controllers\Admin\MasterData\RuanganController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\LogAktivitasController;
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
    Route::get('/admin/scan-qr/aksi/{sarana}/{action}', [ScanQrController::class, 'quickAction'])
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
            Route::get('/sarana/create-unit', [SaranaController::class, 'createUnit'])->name('sarana.create-unit');
            Route::get('/sarana/import-massal', [SaranaController::class, 'createImportMassal'])->name('sarana.import-massal.create');
            Route::post('/sarana/import-massal', [SaranaController::class, 'storeImportMassal'])->name('sarana.import-massal.store');
            Route::post('/sarana/per-ruangan', [SaranaController::class, 'storePerRuangan'])->name('sarana.store-per-ruangan');
            Route::post('/sarana/per-kategori', [SaranaController::class, 'storePerKategori'])->name('sarana.store-per-kategori');
            Route::delete('/sarana/destroy-selected', [SaranaController::class, 'destroySelected'])->name('sarana.destroy-selected');
            Route::delete('/sarana/destroy-all', [SaranaController::class, 'destroyAll'])->name('sarana.destroy-all');
            Route::resource('/sarana', SaranaController::class);
            Route::get('/cetak-qr', [CetakQrController::class, 'index'])->name('cetak-qr.index');
        });

    Route::get('/admin/pengajuan', [PengajuanController::class, 'adminIndex'])->name('admin.pengajuan.index');
    Route::get('/admin/pengajuan-saya', [PengajuanController::class, 'adminMineIndex'])->name('admin.pengajuan.mine');
    Route::get('/admin/pengajuan/create', [PengajuanController::class, 'adminCreate'])->name('admin.pengajuan.create');
    Route::post('/admin/pengajuan', [PengajuanController::class, 'adminStore'])->name('admin.pengajuan.store');
    Route::get('/admin/pengajuan/{pengajuan}', [PengajuanController::class, 'show'])
        ->missing(function () {
            return redirect()->route('admin.pengajuan.index')->with('info', 'Data pengajuan tidak ditemukan atau sudah dihapus.');
        })
        ->name('admin.pengajuan.show');
    Route::get('/admin/realisasi', [PengajuanController::class, 'adminRealisasiIndex'])->name('admin.realisasi.index');
    Route::get('/admin/realisasi/{pengajuan}', [PengajuanController::class, 'adminRealisasiShow'])
        ->missing(function () {
            return redirect()->route('admin.realisasi.index')->with('info', 'Data realisasi tidak ditemukan atau sudah dihapus.');
        })
        ->name('admin.realisasi.show');
    Route::post('/admin/pengajuan/{pengajuan}/perawatan', [PengajuanController::class, 'realisasiPerawatan'])->name('admin.pengajuan.perawatan');
    Route::post('/admin/pengajuan/{pengajuan}/penggantian', [PengajuanController::class, 'realisasiPenggantian'])->name('admin.pengajuan.penggantian');
    Route::get('/admin/notifikasi', [NotifikasiController::class, 'index'])->name('admin.notifikasi.index');

    Route::get('/admin/manajemen-user', [UserManagementController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/manajemen-user', [UserManagementController::class, 'store'])->name('admin.users.store');
    Route::patch('/admin/manajemen-user/{user}', [UserManagementController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/manajemen-user/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/admin/log-aktivitas', [LogAktivitasController::class, 'index'])->name('admin.log-aktivitas.index');

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
            Route::resource('/kategori-sarana', KategoriSaranaController::class)->except(['create', 'show']);
        });

    Route::get('/admin/mutasi', [\App\Http\Controllers\MutasiController::class, 'index'])->name('admin.mutasi.index');
    Route::get('/admin/mutasi/create', [\App\Http\Controllers\MutasiController::class, 'create'])->name('admin.mutasi.create');
    Route::post('/admin/mutasi', [\App\Http\Controllers\MutasiController::class, 'store'])->name('admin.mutasi.store');
    Route::get('/admin/mutasi/{mutasi}', [\App\Http\Controllers\MutasiController::class, 'show'])->name('admin.mutasi.show');
    Route::post('/admin/mutasi/{mutasi}/approve', [\App\Http\Controllers\MutasiController::class, 'approve'])->name('admin.mutasi.approve');
    Route::post('/admin/mutasi/{mutasi}/reject', [\App\Http\Controllers\MutasiController::class, 'reject'])->name('admin.mutasi.reject');

    Route::get('/admin/fitur/{feature}', function (Request $request, string $feature, BlueprintPageController $controller) {
        if ($feature === 'scan-qr') {
            return redirect()->route('admin.scan');
        }

        if ($feature === 'data-sarana') {
            return redirect()->route('admin.sarana.index');
        }

        if ($feature === 'tambah-sarana') {
            return redirect()->route('admin.sarana.create');
        }

        if ($feature === 'mutasi-sarana') {
            return redirect()->route('admin.mutasi.index');
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

        if ($feature === 'log-aktivitas') {
            return redirect()->route('admin.log-aktivitas.index');
        }

        return $controller->show($request, 'admin', $feature);
    })->name('admin.feature');
});
