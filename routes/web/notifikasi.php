<?php

use App\Http\Controllers\NotifikasiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::post('/notifikasi/{notifikasi}/read', [NotifikasiController::class, 'markRead'])->name('notifikasi.read');
    Route::post('/notifikasi/read-all', [NotifikasiController::class, 'markAll'])->name('notifikasi.markAll');
});
