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

    // Actions untuk GURU - Hanya Lapor Kerusakan dan Mutasi
    private const ACTIONS_GURU = [
        'lapor-kerusakan' => 'Lapor Kerusakan',
        'usulan-mutasi' => 'Usulan Mutasi',
    ];

    // Actions untuk KEPALA SARANA - Validasi dan Approval
    private const ACTIONS_KEPALA_SARANA = [
        'lapor-kerusakan' => 'Lapor Kerusakan',
        'validasi-kerusakan' => 'Validasi Kerusakan',
        'approval-teknis' => 'Approval',
    ];

    // Actions untuk ADMIN - Management
    private const ACTIONS_ADMIN = [
        'lapor-kerusakan' => 'Lapor Kerusakan',
        'lihat-histori' => 'Lihat Histori',
        'usulan-mutasi' => 'Usulan Mutasi',
    ];

    // Actions untuk BENDAHARA - Review
    private const ACTIONS_BENDAHARA = [
        'lapor-kerusakan' => 'Lapor Kerusakan',
        'review-pengajuan' => 'Review Pengajuan',
    ];

    // Actions untuk KEPALA SEKOLAH - Approval
    private const ACTIONS_KEPALA_SEKOLAH = [
        'lapor-kerusakan' => 'Lapor Kerusakan',
        'approval-final' => 'Approval Final',
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
                    ->orderBy('kode_aset')
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
            ->orderBy('kode_aset')
            ->limit(8)
            ->get();

        // Get actions based on role
        $actions = match ($role) {
            'guru' => self::ACTIONS_GURU,
            'kepala_sarana' => self::ACTIONS_KEPALA_SARANA,
            'bendahara' => self::ACTIONS_BENDAHARA,
            'kepala_sekolah' => self::ACTIONS_KEPALA_SEKOLAH,
            'admin' => self::ACTIONS_ADMIN,
            default => [],
        };

        return view('shared.scan-qr-action-hub', [
            'role' => $role,
            'kodeAset' => $kodeAset,
            'aset' => $aset,
            'searchResults' => $searchResults,
            'recentAset' => $recentAset,
            'actions' => $actions,
            'scanError' => $scanError,
            'isExactFormat' => $isExactFormat,
        ]);
    }

    public function quickAction(Request $request, Aset $aset, string $action): RedirectResponse
    {
        $role = (string) $request->route('role');
        abort_unless(in_array($role, self::ALLOWED_ROLES, true), 404);
        
        // Get allowed actions for this role
        $allowedActions = match ($role) {
            'guru' => self::ACTIONS_GURU,
            'kepala_sarana' => self::ACTIONS_KEPALA_SARANA,
            'bendahara' => self::ACTIONS_BENDAHARA,
            'kepala_sekolah' => self::ACTIONS_KEPALA_SEKOLAH,
            'admin' => self::ACTIONS_ADMIN,
            default => [],
        };
        
        abort_unless(array_key_exists($action, $allowedActions), 404);

        $this->logScanActivity(
            request: $request,
            role: $role,
            kodeAset: $aset->kode_aset,
            aset: $aset,
            note: 'Quick action: ' . $this->actionLabel($action, $role),
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
        if ($action === 'lapor-kerusakan') {
            return redirect()
                ->route('admin.kerusakan.create', ['kode_aset' => $aset->kode_aset])
                ->with('success', "Silakan laporkan kerusakan aset {$aset->kode_aset}.");
        }

        if ($action === 'lihat-histori') {
            return redirect()
                ->route('kepala_sarana.aset.histori', ['q' => $aset->kode_aset])
                ->with('success', "Menampilkan histori aset {$aset->kode_aset}.");
        }

        if ($action === 'usulan-mutasi') {
            return redirect()
                ->route('admin.feature', ['feature' => 'mutasi-aset', 'aset_id' => $aset->id])
                ->with('success', "Usulan mutasi untuk aset {$aset->kode_aset}.");
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('error', 'Aksi tidak dikenali.');
    }

    private function redirectForGuru(Aset $aset, string $action): RedirectResponse
    {
        if ($action === 'lapor-kerusakan') {
            return redirect()
                ->route('guru.kerusakan.create', ['kode_aset' => $aset->kode_aset])
                ->with('success', "Silakan laporkan kerusakan aset {$aset->kode_aset}.");
        }

        if ($action === 'usulan-mutasi') {
            return redirect()
                ->route('admin.feature', ['feature' => 'mutasi-aset', 'aset_id' => $aset->id])
                ->with('success', "Usulan mutasi untuk aset {$aset->kode_aset}.");
        }

        return redirect()
            ->route('guru.pengajuan.index')
            ->with('error', 'Aksi tidak dikenali.');
    }

    private function redirectForKepalaSarana(Aset $aset, string $action): RedirectResponse
    {
        if ($action === 'lapor-kerusakan') {
            return redirect()
                ->route('kepala_sarana.kerusakan.create', ['kode_aset' => $aset->kode_aset])
                ->with('success', "Silakan laporkan kerusakan aset {$aset->kode_aset}.");
        }

        if ($action === 'validasi-kerusakan') {
            return redirect()
                ->route('kepala_sarana.kerusakan.index', ['q' => $aset->kode_aset])
                ->with('success', "Silakan validasi kerusakan aset {$aset->kode_aset}.");
        }

        if ($action === 'approval-teknis') {
            return redirect()
                ->route('kepala_sarana.pengajuan.approval', ['q' => $aset->kode_aset])
                ->with('success', "Silakan approval teknis untuk aset {$aset->kode_aset}.");
        }

        return redirect()
            ->route('kepala_sarana.dashboard')
            ->with('error', 'Aksi tidak dikenali.');
    }

    private function redirectForBendahara(Aset $aset, string $action): RedirectResponse
    {
        if ($action === 'lapor-kerusakan') {
            return redirect()
                ->route('bendahara.kerusakan.create', ['kode_aset' => $aset->kode_aset])
                ->with('success', "Silakan laporkan kerusakan aset {$aset->kode_aset}.");
        }

        if ($action === 'review-pengajuan') {
            return redirect()
                ->route('bendahara.pengajuan.index', ['q' => $aset->kode_aset])
                ->with('success', "Review pengajuan untuk aset {$aset->kode_aset}.");
        }

        return redirect()
            ->route('bendahara.dashboard')
            ->with('error', 'Aksi tidak dikenali.');
    }

    private function redirectForKepalaSekolah(Aset $aset, string $action): RedirectResponse
    {
        if ($action === 'lapor-kerusakan') {
            return redirect()
                ->route('kepala_sekolah.kerusakan.create', ['kode_aset' => $aset->kode_aset])
                ->with('success', "Silakan laporkan kerusakan aset {$aset->kode_aset}.");
        }

        if ($action === 'approval-final') {
            return redirect()
                ->route('kepala_sekolah.pengajuan.index', ['q' => $aset->kode_aset])
                ->with('success', "Approval final untuk aset {$aset->kode_aset}.");
        }

        return redirect()
            ->route('kepala_sekolah.dashboard')
            ->with('error', 'Aksi tidak dikenali.');
    }

    private function actionLabel(string $action, string $role): string
    {
        $actions = match ($role) {
            'guru' => self::ACTIONS_GURU,
            'kepala_sarana' => self::ACTIONS_KEPALA_SARANA,
            'bendahara' => self::ACTIONS_BENDAHARA,
            'kepala_sekolah' => self::ACTIONS_KEPALA_SEKOLAH,
            'admin' => self::ACTIONS_ADMIN,
            default => [],
        };

        return $actions[$action] ?? $action;
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
