<?php

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

require __DIR__ . '/web/admin.php';
require __DIR__ . '/web/guru.php';
require __DIR__ . '/web/kepala_sarana.php';
require __DIR__ . '/web/bendahara.php';
require __DIR__ . '/web/kepala_sekolah.php';
require __DIR__ . '/web/notifikasi.php';
require __DIR__ . '/auth.php';
