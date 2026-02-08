<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ======================
// HALAMAN WELCOME
// ======================
Route::get('/', function () {

    if (auth()->check()) {

        $user = auth()->user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'guru') {
            return redirect()->route('guru.dashboard');
        }

        if ($user->role === 'kepala_sarana') {
            return redirect()->route('kepala_sarana.dashboard');
        }

        if ($user->role === 'kepala_sekolah') {
            return redirect()->route('kepala_sekolah.dashboard');
        }
    }

    return view('welcome');
});


// ======================
// ROUTE SETELAH LOGIN
// ======================
Route::middleware(['auth'])->group(function () {

    // ================= ADMIN =================
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');
    });

    // ================= GURU =================
    Route::middleware(['role:guru'])->group(function () {
        Route::get('/guru/dashboard', function () {
            return view('guru.dashboard');
        })->name('guru.dashboard');
    });

    // ================= KEPALA SARANA =================
    Route::middleware(['role:kepala_sarana'])->group(function () {
        Route::get('/kepala_sarana/dashboard', function () {
            return view('kepala_sarana.dashboard');
        })->name('kepala_sarana.dashboard');
    });

    // ================= KEPALA SEKOLAH =================
    Route::middleware(['role:kepala_sekolah'])->group(function () {
        Route::get('/kepala_sekolah/dashboard', function () {
            return view('kepala_sekolah.dashboard');
        })->name('kepala_sekolah.dashboard');
    });
});

require __DIR__ . '/auth.php';
