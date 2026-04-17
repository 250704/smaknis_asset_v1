<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MasterData\GedungController;
use App\Http\Controllers\Admin\Inventaris\AsetController;
use App\Http\Controllers\Admin\Inventaris\CetakQrController;
use App\Http\Controllers\Admin\MasterData\KategoriAsetController;
use App\Http\Controllers\Admin\MasterData\RuanganController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\BlueprintPageController;
use App\Http\Controllers\KerusakanController;
use App\Http\Controllers\KepalaSarana\AsetController as KepalaSaranaAsetController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\ScanQrController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

Route::get('/logout', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::middleware(['role:admin'])->group(function () {
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
        Route::post('/admin/manajemen-user/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('admin.users.reset-password');
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
                Route::resource('/gedung', GedungController::class)
                    ->except(['create', 'show']);

                Route::resource('/ruangan', RuanganController::class)
                    ->except(['create', 'show']);

                Route::resource('/kategori-aset', KategoriAsetController::class)
                    ->except(['create', 'show']);
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

    Route::middleware(['role:guru'])->group(function () {
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

    Route::middleware(['role:kepala_sarana'])->group(function () {
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
        Route::get('/kepala_sarana/pengajuan-saya', [PengajuanController::class, 'kepalaSaranaMineIndex'])
            ->name('kepala_sarana.pengajuan.mine');
        Route::get('/kepala_sarana/pengajuan/create', [PengajuanController::class, 'kepalaSaranaCreate'])->name('kepala_sarana.pengajuan.create');
        Route::post('/kepala_sarana/pengajuan', [PengajuanController::class, 'kepalaSaranaStore'])->name('kepala_sarana.pengajuan.store');
        Route::get('/kepala_sarana/validasi/semua-proses', [KerusakanController::class, 'kepalaSaranaSemuaProses'])
            ->name('kepala_sarana.validasi.semua');
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

    Route::middleware(['role:bendahara'])->group(function () {
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
        Route::get('/bendahara/pengajuan-saya', [PengajuanController::class, 'bendaharaMineIndex'])
            ->name('bendahara.pengajuan.mine');
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

    Route::middleware(['role:kepala_sekolah'])->group(function () {
        Route::view('/kepala_sekolah/dashboard', 'kepala_sekolah.dashboard')->name('kepala_sekolah.dashboard');
        Route::get('/kepala_sekolah/scan-qr', [ScanQrController::class, 'index'])
            ->defaults('role', 'kepala_sekolah')
            ->name('kepala_sekolah.scan');
        Route::get('/kepala_sekolah/scan-qr/aksi/{aset}/{action}', [ScanQrController::class, 'quickAction'])
            ->defaults('role', 'kepala_sekolah')
            ->name('kepala_sekolah.scan.action');
        Route::get('/kepala_sekolah/kerusakan/create', [KerusakanController::class, 'create'])
            ->defaults('role', 'kepala_sekolah')
            ->name('kepala_sekolah.kerusakan.create');
        Route::post('/kepala_sekolah/kerusakan', [KerusakanController::class, 'store'])
            ->defaults('role', 'kepala_sekolah')
            ->name('kepala_sekolah.kerusakan.store');
        Route::get('/kepala_sekolah/kerusakan', [KerusakanController::class, 'kepalaSaranaIndex'])
            ->name('kepala_sekolah.kerusakan.index');
        Route::post('/kepala_sekolah/kerusakan/{riwayat}/validate', [KerusakanController::class, 'validateKerusakan'])
            ->name('kepala_sekolah.kerusakan.validate');
        Route::get('/kepala_sekolah/pengajuan', [PengajuanController::class, 'reviewIndex'])
            ->defaults('role', 'kepala_sekolah')
            ->defaults('mode', 'approval')
            ->name('kepala_sekolah.pengajuan.index');
        Route::get('/kepala_sekolah/pengajuan-saya', [PengajuanController::class, 'kepalaSekolahMineIndex'])
            ->name('kepala_sekolah.pengajuan.mine');
        Route::get('/kepala_sekolah/pengajuan/create', [PengajuanController::class, 'kepalaSekolahCreate'])->name('kepala_sekolah.pengajuan.create');
        Route::post('/kepala_sekolah/pengajuan', [PengajuanController::class, 'kepalaSekolahStore'])->name('kepala_sekolah.pengajuan.store');
        Route::get('/kepala_sekolah/pengajuan/{pengajuan}', [PengajuanController::class, 'show'])
            ->defaults('role', 'kepala_sekolah')
            ->name('kepala_sekolah.pengajuan.show');
        Route::post('/kepala_sekolah/pengajuan/{pengajuan}/approve', [PengajuanController::class, 'approve'])
            ->defaults('role', 'kepala_sekolah')
            ->name('kepala_sekolah.pengajuan.approve');
        Route::post('/kepala_sekolah/pengajuan/{pengajuan}/reject', [PengajuanController::class, 'reject'])
            ->defaults('role', 'kepala_sekolah')
            ->name('kepala_sekolah.pengajuan.reject');
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

    Route::post('/notifikasi/{notifikasi}/read', [NotifikasiController::class, 'markRead'])->name('notifikasi.read');
    Route::post('/notifikasi/read-all', [NotifikasiController::class, 'markAll'])->name('notifikasi.markAll');
});

require __DIR__ . '/auth.php';
