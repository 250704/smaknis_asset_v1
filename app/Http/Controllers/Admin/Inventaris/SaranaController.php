<?php

namespace App\Http\Controllers\Admin\Inventaris;

use App\Http\Controllers\Controller;
use App\Models\Sarana;
use App\Models\Gedung;
use App\Models\KategoriSarana;
use App\Models\Ruangan;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use ZipArchive;

class SaranaController extends Controller
{
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
            ->paginate(10)
            ->withQueryString();

        $kategoriList = KategoriSarana::query()->orderBy('nama_kategori')->get();
        $gedungList = Gedung::query()->orderBy('nama_gedung')->get();
        $ruanganList = Ruangan::query()
            ->with('gedung')
            ->when($filters['gedung_id'], fn ($query, $gedungId) => $query->where('gedung_id', $gedungId))
            ->orderBy('nama_ruangan')
            ->get();

        return view('admin.sarana.index', [
            'sarana' => $sarana,
            'filters' => $filters,
            'kategoriList' => $kategoriList,
            'gedungList' => $gedungList,
            'ruanganList' => $ruanganList,
            'kondisiList' => Sarana::KONDISI_LIST,
            'statusList' => Sarana::STATUS_LIST,
        ]);
    }

    public function create(): View
    {
        return view('admin.sarana.create', [
            'kategoriList' => KategoriSarana::query()->orderBy('nama_kategori')->get(),
            'ruanganList' => Ruangan::query()->with('gedung')->orderBy('nama_ruangan')->get(),
            'kondisiList' => Sarana::KONDISI_LIST,
            'statusList' => Sarana::STATUS_LIST,
        ]);
    }

    public function createUnit(): View
    {
        return view('admin.sarana.create-unit', [
            'kategoriList' => KategoriSarana::query()->orderBy('nama_kategori')->get(),
            'ruanganList' => Ruangan::query()->with('gedung')->orderBy('nama_ruangan')->get(),
            'kondisiList' => Sarana::KONDISI_LIST,
            'statusList' => Sarana::STATUS_LIST,
            'kodeSaranaPattern' => 'SRN-{GDG}-{RGN}-L{LT}-{YYYY}-{NNNN}',
        ]);
    }

    public function createImportMassal(): View
    {
        return view('admin.sarana.import-massal', [
            'kategoriList' => KategoriSarana::orderBy('nama_kategori')->get(),
            'ruanganList' => Ruangan::with('gedung')->orderBy('nama_ruangan')->get(),
            'kondisiList' => Sarana::KONDISI_LIST,
            'statusList' => Sarana::STATUS_LIST,
        ]);
    }

    public function storeImportMassal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rows' => ['required', 'array', 'min:1', 'max:50'],
            'rows.*.nama_sarana' => ['required', 'string', 'max:200'],
            'rows.*.kategori_id' => ['required', 'exists:kategori_sarana,id'],
            'rows.*.ruangan_id' => ['required', 'exists:ruangan,id'],
            'rows.*.tahun_perolehan' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'rows.*.kondisi_terkini' => ['required', Rule::in(Sarana::KONDISI_LIST)],
            'rows.*.status_sarana' => ['required', Rule::in(Sarana::STATUS_LIST)],
            'rows.*.jumlah_unit' => ['required', 'integer', 'min:1', 'max:500'],
            'rows.*.harga_perolehan' => ['nullable', 'numeric', 'min:0'],
        ]);

        $totalRows = count($validated['rows']);
        $totalUnits = 0;

        try {
            DB::transaction(function () use ($validated, &$totalUnits): void {
                foreach ($validated['rows'] as $row) {
                    $ruangan = Ruangan::with('gedung')->findOrFail((int) $row['ruangan_id']);
                    $jumlahUnit = (int) $row['jumlah_unit'];
                    
                    $payload = [
                        'nama_sarana' => $row['nama_sarana'],
                        'kategori_id' => (int) $row['kategori_id'],
                        'ruangan_id' => (int) $row['ruangan_id'],
                        'tahun_perolehan' => (int) $row['tahun_perolehan'],
                        'harga_perolehan' => $row['harga_perolehan'] ?? null,
                        'kondisi_terkini' => $row['kondisi_terkini'],
                        'status_sarana' => $row['status_sarana'],
                        'foto_sarana' => null,
                    ];

                    for ($i = 0; $i < $jumlahUnit; $i++) {
                        $this->createSaranaRecord($payload, $ruangan);
                        $totalUnits++;
                    }
                }
            });
        } catch (QueryException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Import gagal. Silakan periksa kembali data Anda.');
        }

        return redirect()
            ->route('admin.sarana.index')
            ->with('success', "Berhasil import {$totalRows} baris data ({$totalUnits} unit sarana).");
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedPayload($request);
        $ruangan = Ruangan::query()->with('gedung')->findOrFail((int) $data['ruangan_id']);
        $data['kode_sarana'] = $this->generateKodeSarana($ruangan);
        $data['nama_sarana'] = $this->generateNamaSarana($data['nama_sarana']);

        if ($request->hasFile('foto_sarana')) {
            $data['foto_sarana'] = $this->storeMediaFile($request->file('foto_sarana'), 'sarana', 'public');
        }

        try {
            $sarana = Sarana::query()->create($data);
        } catch (QueryException $e) {
            if ($request->hasFile('foto_sarana') && !empty($data['foto_sarana'])) {
                Storage::disk('public')->delete($data['foto_sarana']);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan sarana. Silakan ulangi proses.');
        }

        return redirect()
            ->route('admin.sarana.show', $sarana)
            ->with('success', "Sarana berhasil ditambahkan dengan kode {$sarana->kode_sarana}.");
    }

    public function show(Sarana $sarana): View
    {
        $sarana->load(['kategori', 'ruangan.gedung']);

        $riwayatKondisi = $sarana->riwayatKondisiSarana()
            ->with(['user', 'validator'])
            ->latest()
            ->limit(10)
            ->get();

        $riwayatMutasi = $sarana->mutasiSarana()
            ->with(['ruanganAsal.gedung', 'ruanganTujuan.gedung', 'userPengaju', 'validator'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.sarana.show', compact('sarana', 'riwayatKondisi', 'riwayatMutasi'));
    }

    public function storePerRuangan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ruangan_ruangan_id' => ['required', 'exists:ruangan,id'],
            'ruangan_kategori_id' => ['required', 'exists:kategori_sarana,id'],
            'ruangan_nama_sarana' => ['required', 'string', 'max:200'],
            'ruangan_jumlah_unit' => ['required', 'integer', 'min:1', 'max:500'],
            'ruangan_tahun_perolehan' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'ruangan_harga_perolehan' => ['nullable', 'numeric', 'min:0'],
            'ruangan_kondisi_terkini' => ['required', Rule::in(Sarana::KONDISI_LIST)],
            'ruangan_status_sarana' => ['required', Rule::in(Sarana::STATUS_LIST)],
        ]);

        $ruangan = Ruangan::query()->with('gedung')->findOrFail((int) $validated['ruangan_ruangan_id']);
        $jumlahUnit = (int) $validated['ruangan_jumlah_unit'];
        $basePayload = [
            'nama_sarana' => $validated['ruangan_nama_sarana'],
            'kategori_id' => (int) $validated['ruangan_kategori_id'],
            'ruangan_id' => (int) $validated['ruangan_ruangan_id'],
            'tahun_perolehan' => (int) $validated['ruangan_tahun_perolehan'],
            'harga_perolehan' => $validated['ruangan_harga_perolehan'] ?? null,
            'kondisi_terkini' => $validated['ruangan_kondisi_terkini'],
            'status_sarana' => $validated['ruangan_status_sarana'],
            'foto_sarana' => null,
        ];

        try {
            DB::transaction(function () use ($basePayload, $ruangan, $jumlahUnit): void {
                for ($i = 0; $i < $jumlahUnit; $i++) {
                    $this->createSaranaRecord($basePayload, $ruangan);
                }
            });
        } catch (QueryException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Batch sarana per ruangan gagal disimpan. Silakan ulangi.');
        }

        return redirect()
            ->route('admin.sarana.index')
            ->with('success', "Berhasil menambahkan {$jumlahUnit} unit sarana untuk ruangan {$ruangan->nama_ruangan}.");
    }

    public function storePerKategori(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kategori_kategori_id' => ['required', 'exists:kategori_sarana,id'],
            'kategori_ruangan_id' => ['required', 'exists:ruangan,id'],
            'kategori_nama_sarana' => ['required', 'string', 'max:200'],
            'kategori_jumlah_unit' => ['required', 'integer', 'min:1', 'max:500'],
            'kategori_tahun_perolehan' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'kategori_harga_perolehan' => ['nullable', 'numeric', 'min:0'],
            'kategori_kondisi_terkini' => ['required', Rule::in(Sarana::KONDISI_LIST)],
            'kategori_status_sarana' => ['required', Rule::in(Sarana::STATUS_LIST)],
        ]);

        $ruangan = Ruangan::query()->with('gedung')->findOrFail((int) $validated['kategori_ruangan_id']);
        $kategori = KategoriSarana::query()->findOrFail((int) $validated['kategori_kategori_id']);
        $jumlahUnit = (int) $validated['kategori_jumlah_unit'];
        $basePayload = [
            'nama_sarana' => $validated['kategori_nama_sarana'],
            'kategori_id' => (int) $validated['kategori_kategori_id'],
            'ruangan_id' => (int) $validated['kategori_ruangan_id'],
            'tahun_perolehan' => (int) $validated['kategori_tahun_perolehan'],
            'harga_perolehan' => $validated['kategori_harga_perolehan'] ?? null,
            'kondisi_terkini' => $validated['kategori_kondisi_terkini'],
            'status_sarana' => $validated['kategori_status_sarana'],
            'foto_sarana' => null,
        ];

        try {
            DB::transaction(function () use ($basePayload, $ruangan, $jumlahUnit): void {
                for ($i = 0; $i < $jumlahUnit; $i++) {
                    $this->createSaranaRecord($basePayload, $ruangan);
                }
            });
        } catch (QueryException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Batch sarana per kategori gagal disimpan. Silakan ulangi.');
        }

        return redirect()
            ->route('admin.sarana.index')
            ->with('success', "Berhasil menambahkan {$jumlahUnit} unit sarana kategori {$kategori->nama_kategori}.");
    }

    public function edit(Sarana $sarana): View
    {
        return view('admin.sarana.edit', [
            'sarana' => $sarana,
            'kategoriList' => KategoriSarana::query()->orderBy('nama_kategori')->get(),
            'ruanganList' => Ruangan::query()->with('gedung')->orderBy('nama_ruangan')->get(),
            'kondisiList' => Sarana::KONDISI_LIST,
            'statusList' => Sarana::STATUS_LIST,
        ]);
    }

    public function update(Request $request, Sarana $sarana): RedirectResponse
    {
        $data = $this->validatedPayload($request);
        $data['nama_sarana'] = $this->generateNamaSarana($data['nama_sarana'], $sarana);

        if ($request->boolean('hapus_foto') && $sarana->foto_sarana) {
            Storage::disk('public')->delete($sarana->foto_sarana);
            $data['foto_sarana'] = null;
        }

        if ($request->hasFile('foto_sarana')) {
            if ($sarana->foto_sarana) {
                Storage::disk('public')->delete($sarana->foto_sarana);
            }
            $data['foto_sarana'] = $this->storeMediaFile($request->file('foto_sarana'), 'sarana', 'public');
        }

        $sarana->update($data);

        return redirect()
            ->route('admin.sarana.show', $sarana)
            ->with('success', 'Data sarana berhasil diperbarui.');
    }

    public function destroy(Sarana $sarana): RedirectResponse
    {
        try {
            $result = $this->deleteSaranaRecord($sarana);
        } catch (QueryException $e) {
            return redirect()
                ->route('admin.sarana.index')
                ->with('error', 'Sarana tidak bisa dihapus. Masih dipakai data lain.');
        }

        if ($result === 'archived') {
            return redirect()
                ->route('admin.sarana.index')
                ->with('success', 'Sarana diarsipkan karena masih terhubung dengan data proses.');
        }

        return redirect()
            ->route('admin.sarana.index')
            ->with('success', 'Data sarana berhasil dihapus.');
    }

    public function destroySelected(Request $request): RedirectResponse
    {
        $saranaIds = collect((array) $request->input('sarana_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($saranaIds === []) {
            return redirect()
                ->route('admin.sarana.index')
                ->with('error', 'Tidak ada sarana yang dipilih.');
        }

        $saranas = Sarana::query()
            ->whereIn('id', $saranaIds)
            ->get();

        if ($saranas->isEmpty()) {
            return redirect()
                ->route('admin.sarana.index')
                ->with('error', 'Tidak ada sarana yang dipilih.');
        }

        $deletedCount = 0;
        $archivedCount = 0;
        $failedCount = 0;

        foreach ($saranas as $sarana) {
            try {
                $result = $this->deleteSaranaRecord($sarana);
                if ($result === 'archived') {
                    $archivedCount++;
                } else {
                    $deletedCount++;
                }
            } catch (QueryException $e) {
                $failedCount++;
            }
        }

        $parts = [];
        if ($deletedCount > 0) {
            $parts[] = "{$deletedCount} sarana dihapus permanen";
        }
        if ($archivedCount > 0) {
            $parts[] = "{$archivedCount} sarana diarsipkan";
        }
        if ($failedCount > 0) {
            $parts[] = "{$failedCount} sarana gagal dihapus";
        }

        $message = $parts === [] ? 'Tidak ada perubahan data sarana.' : implode(', ', $parts) . '.';

        return redirect()
            ->route('admin.sarana.index')
            ->with($failedCount > 0 ? 'error' : 'success', $message);
    }

    public function destroyAll(): RedirectResponse
    {
        $saranas = Sarana::query()->get();
        if ($saranas->isEmpty()) {
            return redirect()
                ->route('admin.sarana.index')
                ->with('error', 'Tidak ada sarana untuk dihapus.');
        }

        $deletedCount = 0;
        $archivedCount = 0;
        $failedCount = 0;

        foreach ($saranas as $sarana) {
            try {
                $result = $this->deleteSaranaRecord($sarana);
                if ($result === 'archived') {
                    $archivedCount++;
                } else {
                    $deletedCount++;
                }
            } catch (QueryException $e) {
                $failedCount++;
            }
        }

        $parts = [];
        if ($deletedCount > 0) {
            $parts[] = "{$deletedCount} sarana dihapus permanen";
        }
        if ($archivedCount > 0) {
            $parts[] = "{$archivedCount} sarana diarsipkan";
        }
        if ($failedCount > 0) {
            $parts[] = "{$failedCount} sarana gagal dihapus";
        }

        $message = $parts === [] ? 'Tidak ada perubahan data sarana.' : implode(', ', $parts) . '.';

        return redirect()
            ->route('admin.sarana.index')
            ->with($failedCount > 0 ? 'error' : 'success', $message);
    }

    protected function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'nama_sarana' => ['required', 'string', 'max:200'],
            'kategori_id' => ['required', 'exists:kategori_sarana,id'],
            'ruangan_id' => ['required', 'exists:ruangan,id'],
            'tahun_perolehan' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'harga_perolehan' => ['nullable', 'numeric', 'min:0'],
            'kondisi_terkini' => ['required', Rule::in(Sarana::KONDISI_LIST)],
            'status_sarana' => ['required', Rule::in(Sarana::STATUS_LIST)],
            'foto_sarana' => ['nullable', 'image', 'max:3072'],
        ]);

        return [
            'nama_sarana' => $validated['nama_sarana'],
            'kategori_id' => $validated['kategori_id'],
            'ruangan_id' => $validated['ruangan_id'],
            'tahun_perolehan' => (int) $validated['tahun_perolehan'],
            'harga_perolehan' => $validated['harga_perolehan'] ?? null,
            'kondisi_terkini' => $validated['kondisi_terkini'],
            'status_sarana' => $validated['status_sarana'],
        ];
    }

    protected function deleteSaranaRecord(Sarana $sarana): string
    {
        $hasRelations = $sarana->pengajuan()->exists()
            || $sarana->riwayatKondisiSarana()->exists()
            || $sarana->mutasiSarana()->exists()
            || $sarana->penggantianSaranaLama()->exists()
            || $sarana->penggantianSaranaBaru()->exists();

        if ($hasRelations) {
            $sarana->delete();
            return 'archived';
        }

        if ($sarana->foto_sarana) {
            Storage::disk('public')->delete($sarana->foto_sarana);
        }

        $sarana->forceDelete();
        return 'deleted';
    }

    protected function generateKodeSarana(Ruangan $ruangan): string
    {
        $gedungCode = $this->makeSegmentCode($ruangan->gedung?->kode_gedung ?: $ruangan->gedung?->nama_gedung, 'GDG');
        $ruanganCode = $this->extractRuanganCodeSegment($ruangan);
        $lantai = max(0, (int) ($ruangan->lantai ?? 0));
        $prefix = sprintf('SRN-%s-%s-L%02d-%s-', $gedungCode, $ruanganCode, $lantai, date('Y'));
        $counter = 1;

        $lastCode = Sarana::withTrashed()
            ->where('kode_sarana', 'like', "{$prefix}%")
            ->orderByDesc('kode_sarana')
            ->value('kode_sarana');

        if ($lastCode) {
            $lastCounter = (int) substr($lastCode, strlen($prefix));
            $counter = $lastCounter + 1;
        }

        do {
            $candidate = $prefix . str_pad((string) $counter, 4, '0', STR_PAD_LEFT);
            $exists = Sarana::withTrashed()->where('kode_sarana', $candidate)->exists();
            $counter++;
        } while ($exists);

        return $candidate;
    }

    protected function makeSegmentCode(?string $name, string $fallback): string
    {
        if (!$name) {
            return strtoupper($fallback);
        }

        $sanitized = preg_replace('/[^A-Za-z0-9\\s]/', '', $name) ?? '';
        $words = preg_split('/\\s+/', trim($sanitized), -1, PREG_SPLIT_NO_EMPTY);

        $initials = collect($words)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');

        $base = strtoupper($initials . preg_replace('/[^A-Za-z0-9]/', '', $sanitized));
        $base = Str::substr($base, 0, 3);

        if ($base === '') {
            $base = strtoupper($fallback);
        }

        return str_pad($base, 3, 'X');
    }

    protected function extractRuanganCodeSegment(Ruangan $ruangan): string
    {
        $kodeRuangan = strtoupper(trim((string) ($ruangan->kode_ruangan ?? '')));
        if ($kodeRuangan !== '') {
            if (preg_match('/^[A-Z0-9]{3}-L\d{2}-([A-Z0-9]{3})$/', $kodeRuangan, $match)) {
                return $match[1];
            }

            $segments = array_values(array_filter(explode('-', $kodeRuangan)));
            $lastSegment = end($segments);
            if (is_string($lastSegment) && $lastSegment !== '') {
                return $this->makeSegmentCode($lastSegment, 'RGN');
            }
        }

        return $this->makeSegmentCode($ruangan->nama_ruangan, 'RGN');
    }

    protected function generateNamaSarana(string $requestedName, ?Sarana $currentSarana = null): string
    {
        $baseName = $this->normalizeNamaSaranaBase($requestedName);
        $usedNumbers = $this->collectUsedSaranaNumbers($baseName, $currentSarana?->id);
        $preferredNumber = $currentSarana ? $this->extractNamaSaranaNumber($currentSarana->nama_sarana) : null;

        if ($preferredNumber !== null && !in_array($preferredNumber, $usedNumbers, true)) {
            return $this->formatNamaSaranaWithNumber($baseName, $preferredNumber);
        }

        $nextNumber = 1;
        while (in_array($nextNumber, $usedNumbers, true)) {
            $nextNumber++;
        }

        return $this->formatNamaSaranaWithNumber($baseName, $nextNumber);
    }

    protected function normalizeNamaSaranaBase(string $name): string
    {
        $trimmed = trim($name);
        $withoutNumberSuffix = preg_replace('/[\s\-_]*\d+\s*$/', '', $trimmed) ?? '';
        $withoutNoise = preg_replace('/\s+/', ' ', trim($withoutNumberSuffix)) ?? '';

        return $withoutNoise !== '' ? $withoutNoise : 'sarana';
    }

    protected function normalizeNamaSaranaKey(string $name): string
    {
        return Str::lower(preg_replace('/[^A-Za-z0-9]/', '', $this->normalizeNamaSaranaBase($name)) ?? '');
    }

    protected function extractNamaSaranaNumber(string $name): ?int
    {
        if (!preg_match('/(\d+)\s*$/', trim($name), $matches)) {
            return null;
        }

        $number = (int) $matches[1];
        return $number > 0 ? $number : null;
    }

    protected function formatNamaSaranaWithNumber(string $baseName, int $number): string
    {
        $width = max(2, strlen((string) $number));
        return $baseName . str_pad((string) $number, $width, '0', STR_PAD_LEFT);
    }

    protected function collectUsedSaranaNumbers(string $baseName, ?int $exceptSaranaId = null): array
    {
        $baseKey = $this->normalizeNamaSaranaKey($baseName);
        if ($baseKey === '') {
            return [];
        }

        $numbers = Sarana::query()
            ->select(['id', 'nama_sarana', 'status_sarana'])
            ->when($exceptSaranaId, fn ($query, $id) => $query->where('id', '!=', $id))
            ->whereNull('deleted_at')
            ->get()
            ->filter(function (Sarana $sarana) use ($baseKey) {
                if ($sarana->status_sarana !== 'AKTIF') {
                    return false;
                }

                return $this->normalizeNamaSaranaKey($sarana->nama_sarana) === $baseKey;
            })
            ->map(fn (Sarana $sarana) => $this->extractNamaSaranaNumber($sarana->nama_sarana))
            ->filter(fn (?int $number) => $number !== null)
            ->values()
            ->all();

        sort($numbers);
        return array_values(array_unique($numbers));
    }

    protected function createSaranaRecord(array $payload, Ruangan $ruangan): Sarana
    {
        $payload['kode_sarana'] = $this->generateKodeSarana($ruangan);
        $payload['nama_sarana'] = $this->generateNamaSarana($payload['nama_sarana']);

        return Sarana::query()->create($payload);
    }

    protected function readImportRows(string $path, string $extension): array
    {
        if ($extension === 'xlsx') {
            if (!class_exists(ZipArchive::class)) {
                throw ValidationException::withMessages([
                    'file_import' => 'Server belum mendukung impor XLSX. Gunakan template CSV.',
                ]);
            }
            return $this->readXlsxRows($path);
        }

        return $this->readCsvRows($path);
    }

    protected function readCsvRows(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw ValidationException::withMessages([
                'file_import' => 'File tidak dapat dibaca.',
            ]);
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if ($this->isEmptyRow($row)) {
                continue;
            }
            $rows[] = $row;
        }

        fclose($handle);
        return $rows;
    }

    protected function readXlsxRows(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw ValidationException::withMessages([
                'file_import' => 'File XLSX tidak valid.',
            ]);
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            $zip->close();
            throw ValidationException::withMessages([
                'file_import' => 'Template XLSX tidak memiliki sheet1.',
            ]);
        }

        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        $sharedStrings = $this->readSharedStrings($sharedXml === false ? null : $sharedXml);

        $sheet = simplexml_load_string($sheetXml);
        if ($sheet === false) {
            $zip->close();
            throw ValidationException::withMessages([
                'file_import' => 'Gagal membaca isi sheet XLSX.',
            ]);
        }

        $sheet->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rowNodes = $sheet->xpath('//x:sheetData/x:row') ?: [];
        $rows = [];

        foreach ($rowNodes as $rowNode) {
            $cells = $rowNode->xpath('x:c') ?: [];
            $indexedCells = [];

            foreach ($cells as $cell) {
                $ref = (string) $cell['r'];
                $columnLetters = preg_replace('/\d+/', '', $ref) ?? '';
                $columnIndex = $this->excelColumnToIndex($columnLetters);
                $indexedCells[$columnIndex] = $this->readXlsxCellValue($cell, $sharedStrings);
            }

            if ($indexedCells === []) {
                continue;
            }

            ksort($indexedCells);
            $row = [];
            $maxIndex = max(array_keys($indexedCells));
            for ($i = 0; $i <= $maxIndex; $i++) {
                $row[] = isset($indexedCells[$i]) ? trim((string) $indexedCells[$i]) : '';
            }

            if ($this->isEmptyRow($row)) {
                continue;
            }

            $rows[] = $row;
        }

        $zip->close();
        return $rows;
    }

    protected function readSharedStrings(?string $sharedXml): array
    {
        if ($sharedXml === null) {
            return [];
        }

        $xml = simplexml_load_string($sharedXml);
        if ($xml === false) {
            return [];
        }

        $strings = [];
        foreach ($xml->si as $si) {
            if (isset($si->t)) {
                $strings[] = (string) $si->t;
                continue;
            }

            $value = '';
            if (isset($si->r)) {
                foreach ($si->r as $run) {
                    $value .= (string) ($run->t ?? '');
                }
            }
            $strings[] = $value;
        }

        return $strings;
    }

    protected function readXlsxCellValue(\SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) ($cell['t'] ?? '');

        if ($type === 's') {
            $index = (int) ($cell->v ?? 0);
            return (string) ($sharedStrings[$index] ?? '');
        }

        if ($type === 'inlineStr') {
            return (string) ($cell->is->t ?? '');
        }

        return (string) ($cell->v ?? '');
    }

    protected function excelColumnToIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $length = strlen($letters);
        $index = 0;

        for ($i = 0; $i < $length; $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }

        return max(0, $index - 1);
    }

    protected function parseImportRows(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        $header = array_map(function ($value, $index) {
            $clean = trim((string) $value);
            if ($index === 0) {
                $clean = preg_replace('/^\x{FEFF}/u', '', $clean) ?? $clean;
            }
            return Str::lower($clean);
        }, $rows[0], array_keys($rows[0]));
        $requiredHeader = ['nama_sarana', 'kategori_id', 'ruangan_id', 'tahun_perolehan', 'kondisi_terkini', 'status_sarana'];
        $missing = array_values(array_diff($requiredHeader, $header));

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'file_import' => 'Header template kurang: ' . implode(', ', $missing),
            ]);
        }

        $dataRows = [];
        foreach (array_slice($rows, 1) as $rowIndex => $row) {
            $assoc = [];
            foreach ($header as $index => $columnName) {
                $assoc[$columnName] = trim((string) ($row[$index] ?? ''));
            }

            if ($this->isEmptyRow($assoc)) {
                continue;
            }

            $assoc['kondisi_terkini'] = Str::upper($assoc['kondisi_terkini']);
            $assoc['status_sarana'] = Str::upper($assoc['status_sarana']);

            $validator = validator($assoc, [
                'nama_sarana' => ['required', 'string', 'max:200'],
                'kategori_id' => ['required', 'integer', 'exists:kategori_sarana,id'],
                'ruangan_id' => ['required', 'integer', 'exists:ruangan,id'],
                'tahun_perolehan' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
                'harga_perolehan' => ['nullable', 'numeric', 'min:0'],
                'kondisi_terkini' => ['required', Rule::in(Sarana::KONDISI_LIST)],
                'status_sarana' => ['required', Rule::in(Sarana::STATUS_LIST)],
                'jumlah_unit' => ['nullable', 'integer', 'min:1', 'max:500'],
            ]);

            if ($validator->fails()) {
                $message = $validator->errors()->first();
                throw ValidationException::withMessages([
                    'file_import' => 'Baris ' . ($rowIndex + 2) . ': ' . $message,
                ]);
            }

            $dataRows[] = $validator->validated();
        }

        return $dataRows;
    }

    protected function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
