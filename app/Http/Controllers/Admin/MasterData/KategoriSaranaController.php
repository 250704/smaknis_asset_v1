<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\KategoriSarana;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriSaranaController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $kategori = KategoriSarana::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('nama_kategori', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.master.kategori_sarana.index', compact('kategori', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:100'],
        ]);

        KategoriSarana::query()->create($data);

        return redirect()
            ->route('admin.master.kategori-sarana.index')
            ->with('success', 'Kategori sarana berhasil ditambahkan.');
    }

    public function edit(KategoriSarana $kategori_sarana): View
    {
        $kategoriSarana = $kategori_sarana;
        return view('admin.master.kategori_sarana.edit', compact('kategoriSarana'));
    }

    public function update(Request $request, KategoriSarana $kategori_sarana): RedirectResponse
    {
        $kategoriSarana = $kategori_sarana;
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:100'],
        ]);

        $kategoriSarana->update($data);

        return redirect()
            ->route('admin.master.kategori-sarana.index')
            ->with('success', 'Kategori sarana berhasil diperbarui.');
    }

    public function destroy(KategoriSarana $kategori_sarana): RedirectResponse
    {
        $kategoriSarana = $kategori_sarana;
        if ($kategoriSarana->sarana()->exists()) {
            return redirect()
                ->route('admin.master.kategori-sarana.index')
                ->with('error', 'Kategori sarana tidak bisa dihapus karena masih digunakan pada data sarana.');
        }

        $kategoriSarana->delete();

        return redirect()
            ->route('admin.master.kategori-sarana.index')
            ->with('success', 'Kategori sarana berhasil dihapus.');
    }
}
