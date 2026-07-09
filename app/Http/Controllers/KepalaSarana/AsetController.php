<?php

namespace App\Http\Controllers\KepalaSarana;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\Gedung;
use App\Models\KategoriAset;
use App\Models\RiwayatKondisiAset;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AsetController extends Controller
{
    private const HISTORI_STATUS_LIST = ['DILAPORKAN', 'DIVALIDASI', 'DITINDAKLANJUTI', 'SELESAI', 'DITOLAK'];

    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'kategori_id' => $request->query('kategori_id'),
            'gedung_id' => $request->query('gedung_id'),
            'ruangan_id' => $request->query('ruangan_id'),
            'kondisi_terkini' => $request->query('kondisi_terkini'),
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
            ->when($filters['kondisi_terkini'], fn ($query, $kondisi) => $query->where('kondisi_terkini', $kondisi))
            ->when($filters['status_aset'], fn ($query, $status) => $query->where('status_aset', $status))
            ->when($filters['gedung_id'], function ($query, $gedungId) {
                $query->whereHas('ruangan', fn ($ruanganQuery) => $ruanganQuery->where('gedung_id', $gedungId));
            })
            ->orderBy('kode_aset')
            ->paginate(12)
            ->withQueryString();

        $kategoriList = KategoriAset::query()->orderBy('nama_kategori')->get();
        $gedungList = Gedung::query()->orderBy('nama_gedung')->get();
        $ruanganList = Ruangan::query()
            ->with('gedung')
            ->when($filters['gedung_id'], fn ($query, $gedungId) => $query->where('gedung_id', $gedungId))
            ->orderBy('nama_ruangan')
            ->get();

        return view('kepala_sarana.aset.index', [
            'aset' => $aset,
            'filters' => $filters,
            'kategoriList' => $kategoriList,
            'gedungList' => $gedungList,
            'ruanganList' => $ruanganList,
            'kondisiList' => Aset::KONDISI_LIST,
            'statusList' => Aset::STATUS_LIST,
        ]);
    }

    public function show(Aset $aset): View
    {
        $aset->load(['kategori', 'ruangan.gedung']);

        $riwayatKondisi = RiwayatKondisiAset::query()
            ->with(['user', 'validator'])
            ->where('aset_id', $aset->id)
            ->latest()
            ->limit(10)
            ->get();

        return view('kepala_sarana.aset.show', [
            'aset' => $aset,
            'riwayatKondisi' => $riwayatKondisi,
        ]);
    }

    public function histori(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => (string) $request->query('status', ''),
        ];

        $histori = RiwayatKondisiAset::query()
            ->with(['aset.ruangan.gedung', 'user', 'validator'])
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->whereHas('aset', function ($asetQuery) use ($filters) {
                    $asetQuery->where('kode_aset', 'like', "%{$filters['q']}%")
                        ->orWhere('nama_aset', 'like', "%{$filters['q']}%");
                });
            })
            ->when(in_array($filters['status'], self::HISTORI_STATUS_LIST, true), fn ($query) => $query->where('status', $filters['status']))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('kepala_sarana.aset.histori', [
            'histori' => $histori,
            'filters' => $filters,
            'statusList' => self::HISTORI_STATUS_LIST,
        ]);
    }
}
