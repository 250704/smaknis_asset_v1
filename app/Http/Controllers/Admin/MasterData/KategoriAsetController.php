<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\KategoriAset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriAsetController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $kategori = KategoriAset::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('nama_kategori', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.master.kategori_aset.index', compact('kategori', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:100'],
        ]);

        KategoriAset::query()->create($data);

        return redirect()
            ->route('admin.master.kategori-aset.index')
            ->with('success', 'Kategori aset berhasil ditambahkan.');
    }

    public function edit(KategoriAset $kategori_aset): View
    {
        $kategoriAset = $kategori_aset;
        return view('admin.master.kategori_aset.edit', compact('kategoriAset'));
    }

    public function update(Request $request, KategoriAset $kategori_aset): RedirectResponse
    {
        $kategoriAset = $kategori_aset;
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:100'],
        ]);

        $kategoriAset->update($data);

        return redirect()
            ->route('admin.master.kategori-aset.index')
            ->with('success', 'Kategori aset berhasil diperbarui.');
    }

    public function destroy(KategoriAset $kategori_aset): RedirectResponse
    {
        $kategoriAset = $kategori_aset;
        if ($kategoriAset->aset()->exists()) {
            return redirect()
                ->route('admin.master.kategori-aset.index')
                ->with('error', 'Kategori aset tidak bisa dihapus karena masih digunakan pada data aset.');
        }

        $kategoriAset->delete();

        return redirect()
            ->route('admin.master.kategori-aset.index')
            ->with('success', 'Kategori aset berhasil dihapus.');
    }
}
