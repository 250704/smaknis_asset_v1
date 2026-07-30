<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogAktivitasController extends Controller
{
    public function index(Request $request): View
    {
        $query = LogAktivitas::query()->with('user');

        // Filter pencarian
        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function($q) use ($search) {
                $q->where('deskripsi', 'like', "%{$search}%")
                  ->orWhere('aktivitas', 'like', "%{$search}%")
                  ->orWhere('modul', 'like', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter modul
        if ($request->filled('modul')) {
            $query->where('modul', $request->input('modul'));
        }

        // Filter tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('created_at', '>=', $request->input('tanggal_mulai'));
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('created_at', '<=', $request->input('tanggal_selesai'));
        }

        $logs = $query->latest()->paginate(25)->withQueryString();

        // Ambil daftar modul unik untuk filter
        $modules = LogAktivitas::query()->distinct()->pluck('modul')->filter()->toArray();

        return view('admin.log-aktivitas.index', [
            'logs' => $logs,
            'modules' => $modules,
            'filters' => $request->only(['q', 'modul', 'tanggal_mulai', 'tanggal_selesai']),
        ]);
    }
}
