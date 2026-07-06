<?php

namespace App\Http\Controllers\Admin\Inventaris;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\Gedung;
use App\Models\KategoriAset;
use App\Models\Ruangan;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AsetController extends Controller
{
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
            ->paginate(10)
            ->withQueryString();

        $kategoriList = KategoriAset::query()->orderBy('nama_kategori')->get();
        $gedungList = Gedung::query()->orderBy('nama_gedung')->get();
        $ruanganList = Ruangan::query()
            ->with('gedung')
            ->when($filters['gedung_id'], fn ($query, $gedungId) => $query->where('gedung_id', $gedungId))
            ->orderBy('nama_ruangan')
            ->get();

        return view('admin.aset.index', [
            'aset' => $aset,
            'filters' => $filters,
            'kategoriList' => $kategoriList,
            'gedungList' => $gedungList,
            'ruanganList' => $ruanganList,
            'kondisiList' => Aset::KONDISI_LIST,
            'statusList' => Aset::STATUS_LIST,
        ]);
    }

    public function create(): View
    {
        return view('admin.aset.create', [
            'kategoriList' => KategoriAset::query()->orderBy('nama_kategori')->get(),
            'ruanganList' => Ruangan::query()->with('gedung')->orderBy('nama_ruangan')->get(),
            'kondisiList' => Aset::KONDISI_LIST,
            'statusList' => Aset::STATUS_LIST,
        ]);
    }

    public function createUnit(): View
    {
        return view('admin.aset.create-unit', [
            'kategoriList' => KategoriAset::query()->orderBy('nama_kategori')->get(),
            'ruanganList' => Ruangan::query()->with('gedung')->orderBy('nama_ruangan')->get(),
            'kondisiList' => Aset::KONDISI_LIST,
            'statusList' => Aset::STATUS_LIST,
            'kodeAsetPattern' => 'AST-{GDG}-{RGN}-L{LT}-{YYYY}-{NNNN}',
        ]);
    }

    public function createImportMassal(): View
    {
        return view('admin.aset.import-massal', [
            'kategoriList' => KategoriAset::orderBy('nama_kategori')->get(),
            'ruanganList' => Ruangan::with('gedung')->orderBy('nama_ruangan')->get(),
            'kondisiList' => Aset::KONDISI_LIST,
            'statusList' => Aset::STATUS_LIST,
        ]);
    }

    public function storeImportMassal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rows' => ['required', 'array', 'min:1', 'max:50'],
            'rows.*.nama_aset' => ['required', 'string', 'max:200'],
            'rows.*.kategori_id' => ['required', 'exists:kategori_aset,id'],
            'rows.*.ruangan_id' => ['required', 'exists:ruangan,id'],
            'rows.*.tahun_perolehan' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'rows.*.kondisi_terkini' => ['required', Rule::in(Aset::KONDISI_LIST)],
            'rows.*.status_aset' => ['required', Rule::in(Aset::STATUS_LIST)],
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
                        'nama_aset' => $row['nama_aset'],
                        'kategori_id' => (int) $row['kategori_id'],
                        'ruangan_id' => (int) $row['ruangan_id'],
                        'tahun_perolehan' => (int) $row['tahun_perolehan'],
                        'harga_perolehan' => $row['harga_perolehan'] ?? null,
                        'kondisi_terkini' => $row['kondisi_terkini'],
                        'status_aset' => $row['status_aset'],
                        'foto_aset' => null,
                    ];

                    for ($i = 0; $i < $jumlahUnit; $i++) {
                        $this->createAssetRecord($payload, $ruangan);
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
            ->route('admin.aset.index')
            ->with('success', "Berhasil import {$totalRows} baris data ({$totalUnits} unit aset).");
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedPayload($request);
        $ruangan = Ruangan::query()->with('gedung')->findOrFail((int) $data['ruangan_id']);
        $data['kode_aset'] = $this->generateKodeAset($ruangan);
        $data['nama_aset'] = $this->generateNamaAset($data['nama_aset']);

        if ($request->hasFile('foto_aset')) {
            $data['foto_aset'] = $this->storeMediaFile($request->file('foto_aset'), 'aset', 'public');
        }

        try {
            $aset = Aset::query()->create($data);
        } catch (QueryException $e) {
            if ($request->hasFile('foto_aset') && !empty($data['foto_aset'])) {
                Storage::disk('public')->delete($data['foto_aset']);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan aset. Silakan ulangi proses.');
        }

        return redirect()
            ->route('admin.aset.show', $aset)
            ->with('success', "Aset berhasil ditambahkan dengan kode {$aset->kode_aset}.");
    }

    public function show(Aset $aset): View
    {
        $aset->load(['kategori', 'ruangan.gedung']);

        $riwayatKondisi = $aset->riwayatKondisiAset()
            ->with(['user', 'validator'])
            ->latest()
            ->limit(10)
            ->get();

        $riwayatMutasi = $aset->mutasiAset()
            ->with(['ruanganAsal.gedung', 'ruanganTujuan.gedung', 'userPengaju', 'validator'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.aset.show', compact('aset', 'riwayatKondisi', 'riwayatMutasi'));
    }

    public function storePerRuangan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ruangan_ruangan_id' => ['required', 'exists:ruangan,id'],
            'ruangan_kategori_id' => ['required', 'exists:kategori_aset,id'],
            'ruangan_nama_aset' => ['required', 'string', 'max:200'],
            'ruangan_jumlah_unit' => ['required', 'integer', 'min:1', 'max:500'],
            'ruangan_tahun_perolehan' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'ruangan_harga_perolehan' => ['nullable', 'numeric', 'min:0'],
            'ruangan_kondisi_terkini' => ['required', Rule::in(Aset::KONDISI_LIST)],
            'ruangan_status_aset' => ['required', Rule::in(Aset::STATUS_LIST)],
        ]);

        $ruangan = Ruangan::query()->with('gedung')->findOrFail((int) $validated['ruangan_ruangan_id']);
        $jumlahUnit = (int) $validated['ruangan_jumlah_unit'];
        $basePayload = [
            'nama_aset' => $validated['ruangan_nama_aset'],
            'kategori_id' => (int) $validated['ruangan_kategori_id'],
            'ruangan_id' => (int) $validated['ruangan_ruangan_id'],
            'tahun_perolehan' => (int) $validated['ruangan_tahun_perolehan'],
            'harga_perolehan' => $validated['ruangan_harga_perolehan'] ?? null,
            'kondisi_terkini' => $validated['ruangan_kondisi_terkini'],
            'status_aset' => $validated['ruangan_status_aset'],
            'foto_aset' => null,
        ];

        try {
            DB::transaction(function () use ($basePayload, $ruangan, $jumlahUnit): void {
                for ($i = 0; $i < $jumlahUnit; $i++) {
                    $this->createAssetRecord($basePayload, $ruangan);
                }
            });
        } catch (QueryException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Batch aset per ruangan gagal disimpan. Silakan ulangi.');
        }

        return redirect()
            ->route('admin.aset.index')
            ->with('success', "Berhasil menambahkan {$jumlahUnit} unit aset untuk ruangan {$ruangan->nama_ruangan}.");
    }

    public function storePerKategori(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kategori_kategori_id' => ['required', 'exists:kategori_aset,id'],
            'kategori_ruangan_id' => ['required', 'exists:ruangan,id'],
            'kategori_nama_aset' => ['required', 'string', 'max:200'],
            'kategori_jumlah_unit' => ['required', 'integer', 'min:1', 'max:500'],
            'kategori_tahun_perolehan' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'kategori_harga_perolehan' => ['nullable', 'numeric', 'min:0'],
            'kategori_kondisi_terkini' => ['required', Rule::in(Aset::KONDISI_LIST)],
            'kategori_status_aset' => ['required', Rule::in(Aset::STATUS_LIST)],
        ]);

        $ruangan = Ruangan::query()->with('gedung')->findOrFail((int) $validated['kategori_ruangan_id']);
        $kategori = KategoriAset::query()->findOrFail((int) $validated['kategori_kategori_id']);
        $jumlahUnit = (int) $validated['kategori_jumlah_unit'];
        $basePayload = [
            'nama_aset' => $validated['kategori_nama_aset'],
            'kategori_id' => (int) $validated['kategori_kategori_id'],
            'ruangan_id' => (int) $validated['kategori_ruangan_id'],
            'tahun_perolehan' => (int) $validated['kategori_tahun_perolehan'],
            'harga_perolehan' => $validated['kategori_harga_perolehan'] ?? null,
            'kondisi_terkini' => $validated['kategori_kondisi_terkini'],
            'status_aset' => $validated['kategori_status_aset'],
            'foto_aset' => null,
        ];

        try {
            DB::transaction(function () use ($basePayload, $ruangan, $jumlahUnit): void {
                for ($i = 0; $i < $jumlahUnit; $i++) {
                    $this->createAssetRecord($basePayload, $ruangan);
                }
            });
        } catch (QueryException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Batch aset per kategori gagal disimpan. Silakan ulangi.');
        }

        return redirect()
            ->route('admin.aset.index')
            ->with('success', "Berhasil menambahkan {$jumlahUnit} unit aset kategori {$kategori->nama_kategori}.");
    }

    public function edit(Aset $aset): View
    {
        return view('admin.aset.edit', [
            'aset' => $aset,
            'kategoriList' => KategoriAset::query()->orderBy('nama_kategori')->get(),
            'ruanganList' => Ruangan::query()->with('gedung')->orderBy('nama_ruangan')->get(),
            'kondisiList' => Aset::KONDISI_LIST,
            'statusList' => Aset::STATUS_LIST,
        ]);
    }

    public function update(Request $request, Aset $aset): RedirectResponse
    {
        $data = $this->validatedPayload($request);
        $data['nama_aset'] = $this->generateNamaAset($data['nama_aset'], $aset);

        if ($request->boolean('hapus_foto') && $aset->foto_aset) {
            Storage::disk('public')->delete($aset->foto_aset);
            $data['foto_aset'] = null;
        }

        if ($request->hasFile('foto_aset')) {
            if ($aset->foto_aset) {
                Storage::disk('public')->delete($aset->foto_aset);
            }
            $data['foto_aset'] = $this->storeMediaFile($request->file('foto_aset'), 'aset', 'public');
        }

        $aset->update($data);

        return redirect()
            ->route('admin.aset.show', $aset)
            ->with('success', 'Data aset berhasil diperbarui.');
    }

    public function destroy(Aset $aset): RedirectResponse
    {
        try {
            $result = $this->deleteAssetRecord($aset);
        } catch (QueryException $e) {
            return redirect()
                ->route('admin.aset.index')
                ->with('error', 'Aset tidak bisa dihapus. Masih dipakai data lain.');
        }

        if ($result === 'archived') {
            return redirect()
                ->route('admin.aset.index')
                ->with('success', 'Aset diarsipkan karena masih terhubung dengan data proses.');
        }

        return redirect()
            ->route('admin.aset.index')
            ->with('success', 'Data aset berhasil dihapus.');
    }

    public function destroySelected(Request $request): RedirectResponse
    {
        $assetIds = collect((array) $request->input('aset_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($assetIds === []) {
            return redirect()
                ->route('admin.aset.index')
                ->with('error', 'Tidak ada aset yang dipilih.');
        }

        $assets = Aset::query()
            ->whereIn('id', $assetIds)
            ->get();

        if ($assets->isEmpty()) {
            return redirect()
                ->route('admin.aset.index')
                ->with('error', 'Tidak ada aset yang dipilih.');
        }

        $deletedCount = 0;
        $archivedCount = 0;
        $failedCount = 0;

        foreach ($assets as $asset) {
            try {
                $result = $this->deleteAssetRecord($asset);
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
            $parts[] = "{$deletedCount} aset dihapus permanen";
        }
        if ($archivedCount > 0) {
            $parts[] = "{$archivedCount} aset diarsipkan";
        }
        if ($failedCount > 0) {
            $parts[] = "{$failedCount} aset gagal dihapus";
        }

        $message = $parts === [] ? 'Tidak ada perubahan data aset.' : implode(', ', $parts) . '.';

        return redirect()
            ->route('admin.aset.index')
            ->with($failedCount > 0 ? 'error' : 'success', $message);
    }

    public function destroyAll(): RedirectResponse
    {
        $assets = Aset::query()->get();
        if ($assets->isEmpty()) {
            return redirect()
                ->route('admin.aset.index')
                ->with('error', 'Tidak ada aset untuk dihapus.');
        }

        $deletedCount = 0;
        $archivedCount = 0;
        $failedCount = 0;

        foreach ($assets as $asset) {
            try {
                $result = $this->deleteAssetRecord($asset);
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
            $parts[] = "{$deletedCount} aset dihapus permanen";
        }
        if ($archivedCount > 0) {
            $parts[] = "{$archivedCount} aset diarsipkan";
        }
        if ($failedCount > 0) {
            $parts[] = "{$failedCount} aset gagal dihapus";
        }

        $message = $parts === [] ? 'Tidak ada perubahan data aset.' : implode(', ', $parts) . '.';

        return redirect()
            ->route('admin.aset.index')
            ->with($failedCount > 0 ? 'error' : 'success', $message);
    }

    protected function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'nama_aset' => ['required', 'string', 'max:200'],
            'kategori_id' => ['required', 'exists:kategori_aset,id'],
            'ruangan_id' => ['required', 'exists:ruangan,id'],
            'tahun_perolehan' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'harga_perolehan' => ['nullable', 'numeric', 'min:0'],
            'kondisi_terkini' => ['required', Rule::in(Aset::KONDISI_LIST)],
            'status_aset' => ['required', Rule::in(Aset::STATUS_LIST)],
            'foto_aset' => ['nullable', 'image', 'max:3072'],
        ]);

        return [
            'nama_aset' => $validated['nama_aset'],
            'kategori_id' => $validated['kategori_id'],
            'ruangan_id' => $validated['ruangan_id'],
            'tahun_perolehan' => (int) $validated['tahun_perolehan'],
            'harga_perolehan' => $validated['harga_perolehan'] ?? null,
            'kondisi_terkini' => $validated['kondisi_terkini'],
            'status_aset' => $validated['status_aset'],
        ];
    }

    protected function deleteAssetRecord(Aset $aset): string
    {
        $hasRelations = $aset->pengajuan()->exists()
            || $aset->riwayatKondisiAset()->exists()
            || $aset->mutasiAset()->exists()
            || $aset->penggantianAsetLama()->exists()
            || $aset->penggantianAsetBaru()->exists();

        if ($hasRelations) {
            $aset->delete();
            return 'archived';
        }

        if ($aset->foto_aset) {
            Storage::disk('public')->delete($aset->foto_aset);
        }

        $aset->forceDelete();
        return 'deleted';
    }

    protected function generateKodeAset(Ruangan $ruangan): string
    {
        $gedungCode = $this->makeSegmentCode($ruangan->gedung?->kode_gedung ?: $ruangan->gedung?->nama_gedung, 'GDG');
        $ruanganCode = $this->extractRuanganCodeSegment($ruangan);
        $lantai = max(0, (int) ($ruangan->lantai ?? 0));
        $prefix = sprintf('AST-%s-%s-L%02d-%s-', $gedungCode, $ruanganCode, $lantai, date('Y'));
        $counter = 1;

        $lastCode = Aset::withTrashed()
            ->where('kode_aset', 'like', "{$prefix}%")
            ->orderByDesc('kode_aset')
            ->value('kode_aset');

        if ($lastCode) {
            $lastCounter = (int) substr($lastCode, strlen($prefix));
            $counter = $lastCounter + 1;
        }

        do {
            $candidate = $prefix . str_pad((string) $counter, 4, '0', STR_PAD_LEFT);
            $exists = Aset::withTrashed()->where('kode_aset', $candidate)->exists();
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

    protected function generateNamaAset(string $requestedName, ?Aset $currentAset = null): string
    {
        $baseName = $this->normalizeNamaAsetBase($requestedName);
        $usedNumbers = $this->collectUsedAssetNumbers($baseName, $currentAset?->id);
        $preferredNumber = $currentAset ? $this->extractNamaAsetNumber($currentAset->nama_aset) : null;

        if ($preferredNumber !== null && !in_array($preferredNumber, $usedNumbers, true)) {
            return $this->formatNamaAsetWithNumber($baseName, $preferredNumber);
        }

        $nextNumber = 1;
        while (in_array($nextNumber, $usedNumbers, true)) {
            $nextNumber++;
        }

        return $this->formatNamaAsetWithNumber($baseName, $nextNumber);
    }

    protected function normalizeNamaAsetBase(string $name): string
    {
        $trimmed = trim($name);
        $withoutNumberSuffix = preg_replace('/[\s\-_]*\d+\s*$/', '', $trimmed) ?? '';
        $withoutNoise = preg_replace('/\s+/', ' ', trim($withoutNumberSuffix)) ?? '';

        return $withoutNoise !== '' ? $withoutNoise : 'aset';
    }

    protected function normalizeNamaAsetKey(string $name): string
    {
        return Str::lower(preg_replace('/[^A-Za-z0-9]/', '', $this->normalizeNamaAsetBase($name)) ?? '');
    }

    protected function extractNamaAsetNumber(string $name): ?int
    {
        if (!preg_match('/(\d+)\s*$/', trim($name), $matches)) {
            return null;
        }

        $number = (int) $matches[1];
        return $number > 0 ? $number : null;
    }

    protected function formatNamaAsetWithNumber(string $baseName, int $number): string
    {
        $width = max(2, strlen((string) $number));
        return $baseName . str_pad((string) $number, $width, '0', STR_PAD_LEFT);
    }

    protected function collectUsedAssetNumbers(string $baseName, ?int $exceptAssetId = null): array
    {
        $baseKey = $this->normalizeNamaAsetKey($baseName);
        if ($baseKey === '') {
            return [];
        }

        $numbers = Aset::query()
            ->select(['id', 'nama_aset', 'status_aset'])
            ->when($exceptAssetId, fn ($query, $id) => $query->where('id', '!=', $id))
            ->whereNull('deleted_at')
            ->get()
            ->filter(function (Aset $aset) use ($baseKey) {
                if ($aset->status_aset !== 'AKTIF') {
                    return false;
                }

                return $this->normalizeNamaAsetKey($aset->nama_aset) === $baseKey;
            })
            ->map(fn (Aset $aset) => $this->extractNamaAsetNumber($aset->nama_aset))
            ->filter(fn (?int $number) => $number !== null)
            ->values()
            ->all();

        sort($numbers);
        return array_values(array_unique($numbers));
    }

    protected function createAssetRecord(array $payload, Ruangan $ruangan): Aset
    {
        $payload['kode_aset'] = $this->generateKodeAset($ruangan);
        $payload['nama_aset'] = $this->generateNamaAset($payload['nama_aset']);

        return Aset::query()->create($payload);
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
        $requiredHeader = ['nama_aset', 'kategori_id', 'ruangan_id', 'tahun_perolehan', 'kondisi_terkini', 'status_aset'];
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
            $assoc['status_aset'] = Str::upper($assoc['status_aset']);

            $validator = validator($assoc, [
                'nama_aset' => ['required', 'string', 'max:200'],
                'kategori_id' => ['required', 'integer', 'exists:kategori_aset,id'],
                'ruangan_id' => ['required', 'integer', 'exists:ruangan,id'],
                'tahun_perolehan' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
                'harga_perolehan' => ['nullable', 'numeric', 'min:0'],
                'kondisi_terkini' => ['required', Rule::in(Aset::KONDISI_LIST)],
                'status_aset' => ['required', Rule::in(Aset::STATUS_LIST)],
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
