<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\LogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ScanQrController extends Controller
{
    private const ALLOWED_ROLES = [
        'admin',
        'guru',
        'kepala_sarana',
        'bendahara',
        'kepala_sekolah',
    ];

    private const ACTIONS = [
        'lapor-kerusakan' => 'Lapor Kerusakan',
        'ajukan-perawatan' => 'Ajukan Perawatan',
        'ajukan-penggantian' => 'Ajukan Penggantian',
        'usulan-mutasi' => 'Usulan Mutasi',
    ];

    private const KODE_ASET_PATTERN = '/^AST-[A-Z0-9]{3}-[A-Z0-9]{3}-L\\d{2}-\\d{4}-\\d{4}$/';
    private const KODE_ASET_SEARCH_PATTERN = '/^[A-Z0-9\\-]{3,50}$/';

    public function index(Request $request): View
    {
        $role = (string) $request->route('role');
        abort_unless(in_array($role, self::ALLOWED_ROLES, true), 404);

        $kodeAset = Str::upper(trim((string) $request->query('kode_aset', '')));
        $aset = null;
        $searchResults = collect();
        $scanError = null;
        $isExactFormat = false;

        if ($kodeAset !== '') {
            $normalizedCode = preg_replace('/\\s+/', '', $kodeAset) ?? '';
            $kodeAset = $normalizedCode;

            if (!preg_match(self::KODE_ASET_SEARCH_PATTERN, $kodeAset)) {
                $scanError = 'Format kode tidak valid. Gunakan huruf, angka, dan tanda "-" (min 3 karakter).';
            } else {
                $isExactFormat = (bool) preg_match(self::KODE_ASET_PATTERN, $kodeAset);

                $query = Aset::query()
                    ->with(['kategori', 'ruangan.gedung']);

                if ($isExactFormat) {
                    $query->where('kode_aset', $kodeAset);
                } else {
                    $query->where('kode_aset', 'like', "%{$kodeAset}%")
                        ->orderByRaw('CASE WHEN kode_aset = ? THEN 0 ELSE 1 END', [$kodeAset]);
                }

                $searchResults = $query
                    ->latest()
                    ->limit(10)
                    ->get();

                $aset = $searchResults->first();

                if ($aset) {
                    $aset->load([
                        'riwayatKondisiAset' => fn ($query) => $query->latest()->limit(5),
                        'mutasiAset' => fn ($query) => $query->with(['ruanganAsal', 'ruanganTujuan'])->latest()->limit(5),
                    ]);

                    $this->logScanActivity(
                        request: $request,
                        role: $role,
                        kodeAset: $kodeAset,
                        aset: $aset,
                        note: $isExactFormat ? 'Exact QR match' : 'Search match',
                    );
                } else {
                    $this->logScanActivity(
                        request: $request,
                        role: $role,
                        kodeAset: $kodeAset,
                        aset: null,
                        note: 'No asset found',
                    );
                }
            }
        }

        $recentAset = Aset::query()
            ->with(['kategori', 'ruangan.gedung'])
            ->latest()
            ->limit(8)
            ->get();

        return view('shared.scan-qr-action-hub', [
            'role' => $role,
            'kodeAset' => $kodeAset,
            'aset' => $aset,
            'searchResults' => $searchResults,
            'recentAset' => $recentAset,
            'actions' => self::ACTIONS,
            'scanError' => $scanError,
            'isExactFormat' => $isExactFormat,
        ]);
    }

    public function quickAction(Request $request, Aset $aset, string $action): RedirectResponse
    {
        $role = (string) $request->route('role');
        abort_unless(in_array($role, self::ALLOWED_ROLES, true), 404);
        abort_unless(array_key_exists($action, self::ACTIONS), 404);

        $this->logScanActivity(
            request: $request,
            role: $role,
            kodeAset: $aset->kode_aset,
            aset: $aset,
            note: 'Quick action: ' . $this->actionLabel($action),
        );

        return match ($role) {
            'admin' => $this->redirectForAdmin($aset, $action),
            'guru' => $this->redirectForGuru($aset, $action),
            'kepala_sarana' => $this->redirectForKepalaSarana($aset, $action),
            'bendahara' => $this->redirectForBendahara($aset, $action),
            'kepala_sekolah' => $this->redirectForKepalaSekolah($aset, $action),
            default => abort(404),
        };
    }

    private function redirectForAdmin(Aset $aset, string $action): RedirectResponse
    {
        $targetFeature = match ($action) {
            'usulan-mutasi' => 'mutasi-aset',
            default => 'semua-pengajuan',
        };

        return redirect()
            ->route('admin.feature', $this->scanContext($targetFeature, $aset, $action))
            ->with('success', "Aksi {$this->actionLabel($action)} dipilih untuk aset {$aset->kode_aset}.");
    }

    private function redirectForGuru(Aset $aset, string $action): RedirectResponse
    {
        return redirect()
            ->route('guru.feature', $this->scanContext('buat-pengajuan', $aset, $action))
            ->with('success', "Aksi {$this->actionLabel($action)} dipilih untuk aset {$aset->kode_aset}.");
    }

    private function redirectForKepalaSarana(Aset $aset, string $action): RedirectResponse
    {
        $targetFeature = match ($action) {
            'lapor-kerusakan' => 'validasi-kerusakan',
            default => 'approval-teknis',
        };

        return redirect()
            ->route('kepala_sarana.feature', $this->scanContext($targetFeature, $aset, $action))
            ->with('success', "Aksi {$this->actionLabel($action)} dipilih untuk aset {$aset->kode_aset}.");
    }

    private function redirectForBendahara(Aset $aset, string $action): RedirectResponse
    {
        $targetFeature = match ($action) {
            'lapor-kerusakan' => 'semua-review',
            default => 'approval-anggaran',
        };

        return redirect()
            ->route('bendahara.feature', $this->scanContext($targetFeature, $aset, $action))
            ->with('success', "Aksi {$this->actionLabel($action)} dipilih untuk aset {$aset->kode_aset}.");
    }

    private function redirectForKepalaSekolah(Aset $aset, string $action): RedirectResponse
    {
        return redirect()
            ->route('kepala_sekolah.feature', $this->scanContext('approval-final', $aset, $action))
            ->with('success', "Aksi {$this->actionLabel($action)} dipilih untuk aset {$aset->kode_aset}.");
    }

    private function actionLabel(string $action): string
    {
        return self::ACTIONS[$action] ?? $action;
    }

    private function scanContext(string $feature, Aset $aset, string $action): array
    {
        return [
            'feature' => $feature,
            'source' => 'scan-qr',
            'aset_id' => $aset->id,
            'kode_aset' => $aset->kode_aset,
            'nama_aset' => $aset->nama_aset,
            'aksi' => $action,
        ];
    }

    private function logScanActivity(Request $request, string $role, string $kodeAset, ?Aset $aset, string $note): void
    {
        $user = $request->user();
        if (!$user) {
            return;
        }

        LogAktivitas::query()->create([
            'user_id' => $user->id,
            'aktivitas' => 'SCAN_QR',
            'modul' => 'SCAN_QR_ACTION_HUB',
            'deskripsi' => sprintf(
                '[%s] kode=%s; aset_id=%s; %s',
                $role,
                $kodeAset,
                $aset?->id ?? '-',
                $note,
            ),
            'ip_address' => $request->ip(),
        ]);
    }
}
