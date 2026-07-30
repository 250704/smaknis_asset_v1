<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Gedung;
use App\Models\Ruangan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'lantai' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $gedung = Gedung::query()->findOrFail((int) $data['gedung_id']);
        $kodeRuangan = $this->generateKodeRuangan(
            $gedung->kode_gedung,
            (int) $data['lantai'],
            $data['nama_ruangan']
        );

        Ruangan::query()->create([
            'gedung_id' => $data['gedung_id'],
            'nama_ruangan' => $data['nama_ruangan'],
            'kode_ruangan' => $kodeRuangan,
            'lantai' => $data['lantai'],
        ]);

        return redirect()
            ->route('admin.master.ruangan.index')
            ->with('success', 'Data ruangan berhasil ditambahkan.');
    }

    public function edit(Ruangan $ruangan): View
    {
        $gedungList = Gedung::query()
            ->orderBy('nama_gedung')
            ->get();

        return view('admin.master.ruangan.edit', [
            'ruangan' => $ruangan,
            'gedungList' => $gedungList,
        ]);
    }

    public function update(Request $request, Ruangan $ruangan): RedirectResponse
    {
        $data = $request->validate([
            'gedung_id' => ['required', 'exists:gedung,id'],
            'nama_ruangan' => ['required', 'string', 'max:100'],
            'lantai' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $gedung = Gedung::query()->findOrFail((int) $data['gedung_id']);
        $kodeRuangan = $this->generateKodeRuangan(
            $gedung->kode_gedung,
            (int) $data['lantai'],
            $data['nama_ruangan'],
            $ruangan->id
        );

        $ruangan->update([
            'gedung_id' => $data['gedung_id'],
            'nama_ruangan' => $data['nama_ruangan'],
            'kode_ruangan' => $kodeRuangan,
            'lantai' => $data['lantai'],
        ]);

        return redirect()
            ->route('admin.master.ruangan.index')
            ->with('success', 'Data ruangan berhasil diperbarui.');
    }

    public function destroy(Ruangan $ruangan): RedirectResponse
    {
        if ($ruangan->sarana()->exists()) {
            return redirect()
                ->route('admin.master.ruangan.index')
                ->with('error', 'Ruangan tidak bisa dihapus karena masih digunakan pada data sarana.');
        }

        $ruangan->delete();

        return redirect()
            ->route('admin.master.ruangan.index')
            ->with('success', 'Data ruangan berhasil dihapus.');
    }

    private function generateKodeRuangan(?string $gedungCode, int $lantai, string $namaRuangan, ?int $ignoreId = null): string
    {
        $gedung = $this->sanitizeShortCode($gedungCode, 'GDG');
        $lt = str_pad((string) max(1, $lantai), 2, '0', STR_PAD_LEFT);
        $baseShort = $this->deriveRuanganShortCode($namaRuangan);
        $ruanganShort = $this->resolveUniqueRuanganShortCode($gedung, $lt, $baseShort, $ignoreId);

        return $this->composeKodeRuangan($gedung, $lt, $ruanganShort);
    }

    private function composeKodeRuangan(string $gedungCode, string $lt, string $ruanganShortCode): string
    {
        return "{$gedungCode}-L{$lt}-{$ruanganShortCode}";
    }

    private function sanitizeShortCode(?string $value, string $fallback): string
    {
        $clean = strtoupper(trim((string) $value));
        $clean = preg_replace('/[^A-Z0-9]/', '', $clean) ?? '';
        if ($clean === '') {
            $clean = strtoupper($fallback);
        }

        return str_pad(substr($clean, 0, 3), 3, 'X');
    }

    private function deriveRuanganShortCode(string $namaRuangan): string
    {
        $clean = strtoupper(trim($namaRuangan));
        $clean = preg_replace('/[^A-Z0-9\\s]/', '', $clean) ?? '';
        $words = preg_split('/\\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $initials = '';

        foreach ($words as $word) {
            $initials .= substr($word, 0, 1);
        }

        $base = $initials !== '' ? $initials : str_replace(' ', '', $clean);
        return $this->sanitizeShortCode($base, 'RGN');
    }

    private function resolveUniqueRuanganShortCode(string $gedungCode, string $lt, string $baseShort, ?int $ignoreId = null): string
    {
        $initialCode = $this->composeKodeRuangan($gedungCode, $lt, $baseShort);
        if (!$this->isKodeRuanganExists($initialCode, $ignoreId)) {
            return $baseShort;
        }

        $prefix = substr($baseShort, 0, 2);
        for ($i = 1; $i <= 35; $i++) {
            $suffix = strtoupper(base_convert((string) $i, 10, 36));
            $candidateShort = $prefix . $suffix;
            $candidateCode = $this->composeKodeRuangan($gedungCode, $lt, $candidateShort);
            if (!$this->isKodeRuanganExists($candidateCode, $ignoreId)) {
                return $candidateShort;
            }
        }

        for ($i = 0; $i <= 46655; $i++) {
            $candidateShort = strtoupper(str_pad(base_convert((string) $i, 10, 36), 3, '0', STR_PAD_LEFT));
            $candidateCode = $this->composeKodeRuangan($gedungCode, $lt, $candidateShort);
            if (!$this->isKodeRuanganExists($candidateCode, $ignoreId)) {
                return $candidateShort;
            }
        }

        return $baseShort;
    }

    private function isKodeRuanganExists(string $kodeRuangan, ?int $ignoreId = null): bool
    {
        return Ruangan::query()
            ->where('kode_ruangan', $kodeRuangan)
            ->when($ignoreId, fn ($query, $id) => $query->where('id', '!=', $id))
            ->exists();
    }
}
