<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Gedung;
use App\Models\Ruangan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RuanganController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $ruangan = Ruangan::query()
            ->with('gedung')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('nama_ruangan', 'like', "%{$search}%")
                    ->orWhere('kode_ruangan', 'like', "%{$search}%")
                    ->orWhere('lantai', 'like', "%{$search}%")
                    ->orWhereHas('gedung', function ($q) use ($search) {
                        $q->where('nama_gedung', 'like', "%{$search}%")
                            ->orWhere('kode_gedung', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $gedungList = Gedung::query()
            ->orderBy('nama_gedung')
            ->get();

        return view('admin.master.ruangan.index', compact('ruangan', 'gedungList', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'gedung_id' => ['required', 'exists:gedung,id'],
            'nama_ruangan' => ['required', 'string', 'max:100'],
            'kode_ruangan' => ['required', 'string', 'size:3', 'alpha_num', 'unique:ruangan,kode_ruangan'],
            'lantai' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $data['kode_ruangan'] = Str::upper($data['kode_ruangan']);
        Ruangan::query()->create($data);

        return redirect()
            ->route('admin.master.ruangan.index')
            ->with('success', 'Data ruangan berhasil ditambahkan.');
    }

    public function edit(Ruangan $ruangan): View
    {
        $gedungList = Gedung::query()
            ->orderBy('nama_gedung')
            ->get();

        return view('admin.master.ruangan.edit', compact('ruangan', 'gedungList'));
    }

    public function update(Request $request, Ruangan $ruangan): RedirectResponse
    {
        $data = $request->validate([
            'gedung_id' => ['required', 'exists:gedung,id'],
            'nama_ruangan' => ['required', 'string', 'max:100'],
            'kode_ruangan' => [
                'required',
                'string',
                'size:3',
                'alpha_num',
                Rule::unique('ruangan', 'kode_ruangan')->ignore($ruangan->id),
            ],
            'lantai' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $data['kode_ruangan'] = Str::upper($data['kode_ruangan']);
        $ruangan->update($data);

        return redirect()
            ->route('admin.master.ruangan.index')
            ->with('success', 'Data ruangan berhasil diperbarui.');
    }

    public function destroy(Ruangan $ruangan): RedirectResponse
    {
        if ($ruangan->aset()->exists()) {
            return redirect()
                ->route('admin.master.ruangan.index')
                ->with('error', 'Ruangan tidak bisa dihapus karena masih digunakan pada data aset.');
        }

        $ruangan->delete();

        return redirect()
            ->route('admin.master.ruangan.index')
            ->with('success', 'Data ruangan berhasil dihapus.');
    }
}
