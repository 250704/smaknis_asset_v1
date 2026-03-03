<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Gedung;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GedungController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $gedung = Gedung::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('nama_gedung', 'like', "%{$search}%")
                    ->orWhere('kode_gedung', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.master.gedung.index', compact('gedung', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_gedung' => ['required', 'string', 'max:100'],
            'kode_gedung' => ['required', 'string', 'size:3', 'alpha_num', 'unique:gedung,kode_gedung'],
        ]);

        $data['kode_gedung'] = Str::upper($data['kode_gedung']);
        Gedung::query()->create($data);

        return redirect()
            ->route('admin.master.gedung.index')
            ->with('success', 'Data gedung berhasil ditambahkan.');
    }

    public function edit(Gedung $gedung): View
    {
        return view('admin.master.gedung.edit', compact('gedung'));
    }

    public function update(Request $request, Gedung $gedung): RedirectResponse
    {
        $data = $request->validate([
            'nama_gedung' => ['required', 'string', 'max:100'],
            'kode_gedung' => [
                'required',
                'string',
                'size:3',
                'alpha_num',
                Rule::unique('gedung', 'kode_gedung')->ignore($gedung->id),
            ],
        ]);

        $data['kode_gedung'] = Str::upper($data['kode_gedung']);
        $gedung->update($data);

        return redirect()
            ->route('admin.master.gedung.index')
            ->with('success', 'Data gedung berhasil diperbarui.');
    }

    public function destroy(Gedung $gedung): RedirectResponse
    {
        if ($gedung->ruangan()->exists()) {
            return redirect()
                ->route('admin.master.gedung.index')
                ->with('error', 'Gedung tidak bisa dihapus karena masih memiliki data ruangan.');
        }

        $gedung->delete();

        return redirect()
            ->route('admin.master.gedung.index')
            ->with('success', 'Data gedung berhasil dihapus.');
    }
}
