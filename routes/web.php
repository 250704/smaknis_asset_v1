<?php

use App\Http\Controllers\Admin\MasterData\GedungController;
use App\Http\Controllers\Admin\Inventaris\AsetController;
use App\Http\Controllers\Admin\Inventaris\CetakQrController;
use App\Http\Controllers\Admin\MasterData\KategoriAsetController;
use App\Http\Controllers\Admin\MasterData\RuanganController;
use App\Http\Controllers\BlueprintPageController;
use App\Http\Controllers\ScanQrController;
use Illuminate\Support\Facades\Route;

$redirectByRole = function ($user) {
    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasRole('guru')) {
        return redirect()->route('guru.dashboard');
    }

    if ($user->hasRole('kepala_sarana')) {
        return redirect()->route('kepala_sarana.dashboard');
    }

    if ($user->hasRole('bendahara')) {
        return redirect()->route('bendahara.dashboard');
    }

    if ($user->hasRole('kepala_sekolah')) {
        return redirect()->route('kepala_sekolah.dashboard');
    }

    abort(403, 'AKSES DITOLAK');
};

Route::get('/', function () use ($redirectByRole) {
    if (auth()->check()) {
        return $redirectByRole(auth()->user());
    }

    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::middleware(['role:admin'])->group(function () {
        Route::view('/admin/dashboard', 'admin.dashboard')->name('admin.dashboard');
        Route::get('/admin/scan-qr', [ScanQrController::class, 'index'])
            ->defaults('role', 'admin')
            ->name('admin.scan');
        Route::get('/admin/scan-qr/aksi/{aset}/{action}', [ScanQrController::class, 'quickAction'])
            ->defaults('role', 'admin')
            ->name('admin.scan.action');

        Route::prefix('/admin/inventaris')
            ->name('admin.')
            ->group(function () {
                Route::get('/aset/create-unit', [AsetController::class, 'createUnit'])->name('aset.create-unit');
                Route::get('/aset/import-massal', [AsetController::class, 'createImportMassal'])->name('aset.import-massal.create');
                Route::post('/aset/import-massal', [AsetController::class, 'storeImportMassal'])->name('aset.import-massal.store');
                Route::post('/aset/per-ruangan', [AsetController::class, 'storePerRuangan'])->name('aset.store-per-ruangan');
                Route::post('/aset/per-kategori', [AsetController::class, 'storePerKategori'])->name('aset.store-per-kategori');
                Route::resource('/aset', AsetController::class);
                Route::get('/cetak-qr', [CetakQrController::class, 'index'])->name('cetak-qr.index');
            });

        Route::prefix('/admin/master')
            ->name('admin.master.')
            ->group(function () {
                Route::resource('/gedung', GedungController::class)
                    ->except(['create', 'show']);

                Route::resource('/ruangan', RuanganController::class)
                    ->except(['create', 'show']);

                Route::resource('/kategori-aset', KategoriAsetController::class)
                    ->except(['create', 'show']);
            });

        Route::get('/admin/fitur/{feature}', function (string $feature, BlueprintPageController $controller) {
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

            return $controller->show('admin', $feature);
        })->name('admin.feature');
    });

    Route::middleware(['role:guru'])->group(function () {
        Route::view('/guru/dashboard', 'guru.dashboard')->name('guru.dashboard');
        Route::get('/guru/scan-qr', [ScanQrController::class, 'index'])
            ->defaults('role', 'guru')
            ->name('guru.scan');
        Route::get('/guru/scan-qr/aksi/{aset}/{action}', [ScanQrController::class, 'quickAction'])
            ->defaults('role', 'guru')
            ->name('guru.scan.action');
        Route::get('/guru/fitur/{feature}', function (string $feature, BlueprintPageController $controller) {
            if ($feature === 'scan-qr') {
                return redirect()->route('guru.scan');
            }

            return $controller->show('guru', $feature);
        })->name('guru.feature');
    });

    Route::middleware(['role:kepala_sarana'])->group(function () {
        Route::view('/kepala_sarana/dashboard', 'kepala_sarana.dashboard')->name('kepala_sarana.dashboard');
        Route::get('/kepala_sarana/scan-qr', [ScanQrController::class, 'index'])
            ->defaults('role', 'kepala_sarana')
            ->name('kepala_sarana.scan');
        Route::get('/kepala_sarana/scan-qr/aksi/{aset}/{action}', [ScanQrController::class, 'quickAction'])
            ->defaults('role', 'kepala_sarana')
            ->name('kepala_sarana.scan.action');
        Route::get('/kepala_sarana/fitur/{feature}', function (string $feature, BlueprintPageController $controller) {
            if ($feature === 'scan-qr') {
                return redirect()->route('kepala_sarana.scan');
            }

            return $controller->show('kepala_sarana', $feature);
        })->name('kepala_sarana.feature');
    });

    Route::middleware(['role:bendahara'])->group(function () {
        Route::view('/bendahara/dashboard', 'bendahara.dashboard')->name('bendahara.dashboard');
        Route::get('/bendahara/scan-qr', [ScanQrController::class, 'index'])
            ->defaults('role', 'bendahara')
            ->name('bendahara.scan');
        Route::get('/bendahara/scan-qr/aksi/{aset}/{action}', [ScanQrController::class, 'quickAction'])
            ->defaults('role', 'bendahara')
            ->name('bendahara.scan.action');
        Route::get('/bendahara/fitur/{feature}', function (string $feature, BlueprintPageController $controller) {
            if ($feature === 'scan-qr') {
                return redirect()->route('bendahara.scan');
            }

            return $controller->show('bendahara', $feature);
        })->name('bendahara.feature');
    });

    Route::middleware(['role:kepala_sekolah'])->group(function () {
        Route::view('/kepala_sekolah/dashboard', 'kepala_sekolah.dashboard')->name('kepala_sekolah.dashboard');
        Route::get('/kepala_sekolah/scan-qr', [ScanQrController::class, 'index'])
            ->defaults('role', 'kepala_sekolah')
            ->name('kepala_sekolah.scan');
        Route::get('/kepala_sekolah/scan-qr/aksi/{aset}/{action}', [ScanQrController::class, 'quickAction'])
            ->defaults('role', 'kepala_sekolah')
            ->name('kepala_sekolah.scan.action');
        Route::get('/kepala_sekolah/fitur/{feature}', function (string $feature, BlueprintPageController $controller) {
            if ($feature === 'scan-qr') {
                return redirect()->route('kepala_sekolah.scan');
            }

            return $controller->show('kepala_sekolah', $feature);
        })->name('kepala_sekolah.feature');
    });
});

require __DIR__ . '/auth.php';
