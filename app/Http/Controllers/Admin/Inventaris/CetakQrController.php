<?php

namespace App\Http\Controllers\Admin\Inventaris;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\Gedung;
use App\Models\KategoriAset;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CetakQrController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'kategori_id' => $request->query('kategori_id'),
            'gedung_id' => $request->query('gedung_id'),
            'ruangan_id' => $request->query('ruangan_id'),
            'status_aset' => $request->query('status_aset'),
        ];

        $aset = Aset::query()
            ->with(['kategori', 'ruangan.gedung'])
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($q) use ($filters) {
                    $q->where('kode_aset', 'like', "%{$filters['q']}%")
                        ->orWhere('nama_aset', 'like', "%{$filters['q']}%");
                });
            })
            ->when($filters['kategori_id'], fn ($query, $kategoriId) => $query->where('kategori_id', $kategoriId))
            ->when($filters['ruangan_id'], fn ($query, $ruanganId) => $query->where('ruangan_id', $ruanganId))
            ->when($filters['status_aset'], fn ($query, $status) => $query->where('status_aset', $status))
            ->when($filters['gedung_id'], function ($query, $gedungId) {
                $query->whereHas('ruangan', fn ($ruanganQuery) => $ruanganQuery->where('gedung_id', $gedungId));
            })
            ->latest()
            ->paginate(24)
            ->withQueryString();

        return view('admin.cetak_qr.index', [
            'aset' => $aset,
            'filters' => $filters,
            'kategoriList' => KategoriAset::query()->orderBy('nama_kategori')->get(),
            'gedungList' => Gedung::query()->orderBy('nama_gedung')->get(),
            'ruanganList' => Ruangan::query()
                ->with('gedung')
                ->when($filters['gedung_id'], fn ($query, $gedungId) => $query->where('gedung_id', $gedungId))
                ->orderBy('nama_ruangan')
                ->get(),
            'statusList' => Aset::STATUS_LIST,
        ]);
    }
}
