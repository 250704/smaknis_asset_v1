<?php

namespace App\Http\Controllers\KepalaSarana;

use App\Http\Controllers\Controller;
use App\Models\Sarana;
use App\Models\Gedung;
use App\Models\KategoriSarana;
use App\Models\RiwayatKondisiSarana;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaranaController extends Controller
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
            'status_sarana' => $request->query('status_sarana'),
        ];

        $sarana = Sarana::query()
            ->with(['kategori', 'ruangan.gedung'])
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($q) use ($filters) {
                    $q->where('kode_sarana', 'like', "%{$filters['q']}%")
                        ->orWhere('nama_sarana', 'like', "%{$filters['q']}%");
                });
            })
            ->when($filters['kategori_id'], fn ($query, $kategoriId) => $query->where('kategori_id', $kategoriId))
            ->when($filters['ruangan_id'], fn ($query, $ruanganId) => $query->where('ruangan_id', $ruanganId))
            ->when($filters['kondisi_terkini'], fn ($query, $kondisi) => $query->where('kondisi_terkini', $kondisi))
            ->when($filters['status_sarana'], fn ($query, $status) => $query->where('status_sarana', $status))
            ->when($filters['gedung_id'], function ($query, $gedungId) {
                $query->whereHas('ruangan', fn ($ruanganQuery) => $ruanganQuery->where('gedung_id', $gedungId));
            })
            ->orderBy('kode_sarana')
            ->paginate(12)
            ->withQueryString();

        $kategoriList = KategoriSarana::query()->orderBy('nama_kategori')->get();
        $gedungList = Gedung::query()->orderBy('nama_gedung')->get();
        $ruanganList = Ruangan::query()
            ->with('gedung')
            ->when($filters['gedung_id'], fn ($query, $gedungId) => $query->where('gedung_id', $gedungId))
            ->orderBy('nama_ruangan')
            ->get();

        return view('kepala_sarana.sarana.index', [
            'sarana' => $sarana,
            'filters' => $filters,
            'kategoriList' => $kategoriList,
            'gedungList' => $gedungList,
            'ruanganList' => $ruanganList,
            'kondisiList' => Sarana::KONDISI_LIST,
            'statusList' => Sarana::STATUS_LIST,
        ]);
    }

    public function show(Sarana $sarana): View
    {
        $sarana->load(['kategori', 'ruangan.gedung']);

        $riwayatKondisi = RiwayatKondisiSarana::query()
            ->with(['user', 'validator'])
            ->where('sarana_id', $sarana->id)
            ->latest()
            ->limit(10)
            ->get();

        return view('kepala_sarana.sarana.show', [
            'sarana' => $sarana,
            'riwayatKondisi' => $riwayatKondisi,
        ]);
    }

    public function histori(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => (string) $request->query('status', ''),
        ];

        $histori = RiwayatKondisiSarana::query()
            ->with(['sarana.ruangan.gedung', 'user', 'validator'])
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->whereHas('sarana', function ($saranaQuery) use ($filters) {
                    $saranaQuery->where('kode_sarana', 'like', "%{$filters['q']}%")
                        ->orWhere('nama_sarana', 'like', "%{$filters['q']}%");
                });
            })
            ->when(in_array($filters['status'], self::HISTORI_STATUS_LIST, true), fn ($query) => $query->where('status', $filters['status']))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('kepala_sarana.sarana.histori', [
            'histori' => $histori,
            'filters' => $filters,
            'statusList' => self::HISTORI_STATUS_LIST,
        ]);
    }
}
