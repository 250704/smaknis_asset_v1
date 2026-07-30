<?php

namespace App\Http\Controllers;

use App\Models\Sarana;
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

    /** Aksi hasil scan yang seragam untuk semua peran. */
    private const SCAN_ACTIONS = [
        'lapor-kerusakan' => 'Lapor Kerusakan',
        'usulan-mutasi' => 'Usulan Mutasi',
        'histori-sarana' => 'Histori Sarana',
    ];

    private const KODE_SARANA_PATTERN = '/^SRN-[A-Z0-9]{3}-[A-Z0-9]{3}-L\\d{2}-\\d{4}-\\d{4}$/';
    private const INVALID_QR_MESSAGE = 'QR code tidak valid. Pastikan QR milik sarana yang terdaftar.';

    public function index(Request $request): View|RedirectResponse
    {
        $role = (string) $request->route('role');
        abort_unless(in_array($role, self::ALLOWED_ROLES, true), 404);

        $kodeSarana = $this->normalizeScanValue((string) $request->query('kode_sarana', ''));
        $sarana = null;
        $searchResults = collect();
        $scanError = null;
        $isExactFormat = false;

        if ($kodeSarana !== '') {
            $kodeSarana = preg_replace('/\\s+/', '', $kodeSarana) ?? '';
            $kodeSarana = Str::upper($kodeSarana);

            $matchedKodeSarana = $this->extractKodeSarana($kodeSarana);
            $isExactFormat = $matchedKodeSarana !== null;

            if ($isExactFormat) {
                $kodeSarana = $matchedKodeSarana;

                $query = Sarana::query()
                    ->with(['kategori', 'ruangan.gedung'])
                    ->where('kode_sarana', $kodeSarana);

                $searchResults = $query
                    ->orderBy('kode_sarana')
                    ->limit(10)
                    ->get();

                $sarana = $searchResults->first();

                if ($sarana) {
                    $sarana->load([
                        'riwayatKondisiSarana' => fn ($query) => $query->latest()->limit(5),
                        'mutasiSarana' => fn ($query) => $query->with(['ruanganAsal', 'ruanganTujuan'])->latest()->limit(5),
                    ]);

                    $this->logScanActivity(
                        request: $request,
                        role: $role,
                        kodeSarana: $kodeSarana,
                        sarana: $sarana,
                        note: 'Exact QR match',
                    );

                } else {
                    $this->logScanActivity(
                        request: $request,
                        role: $role,
                        kodeSarana: $kodeSarana,
                        sarana: null,
                        note: 'No sarana found',
                    );

                    $scanError = self::INVALID_QR_MESSAGE;
                }
            } else {
                $scanError = self::INVALID_QR_MESSAGE;
            }
        }

        $recentSarana = Sarana::query()
            ->with(['kategori', 'ruangan.gedung'])
            ->orderBy('kode_sarana')
            ->limit(8)
            ->get();

        $actions = self::SCAN_ACTIONS;

        return view('shared.scan-qr-action-hub', [
            'role' => $role,
            'kodeSarana' => $kodeSarana,
            'sarana' => $sarana,
            'searchResults' => $searchResults,
            'recentSarana' => $recentSarana,
            'actions' => $actions,
            'scanError' => $scanError,
            'isExactFormat' => $isExactFormat,
        ]);
    }

    public function publicScan(Request $request): View|RedirectResponse
    {
        $kodeSarana = $this->normalizeScanValue((string) $request->query('kode_sarana', ''));
        $sarana = null;
        $scanError = null;
        $isExactFormat = false;

        if ($kodeSarana !== '') {
            $kodeSarana = preg_replace('/\s+/', '', $kodeSarana) ?? '';
            $kodeSarana = Str::upper($kodeSarana);

            $matchedKodeSarana = $this->extractKodeSarana($kodeSarana);
            $isExactFormat = $matchedKodeSarana !== null;

            if ($isExactFormat) {
                $kodeSarana = $matchedKodeSarana;

                $sarana = Sarana::query()
                    ->with(['kategori', 'ruangan.gedung'])
                    ->where('kode_sarana', $kodeSarana)
                    ->first();

                if ($sarana) {
                    $sarana->load([
                        'riwayatKondisiSarana' => fn ($query) => $query->latest()->limit(5),
                        'mutasiSarana' => fn ($query) => $query->with(['ruanganAsal', 'ruanganTujuan'])->latest()->limit(5),
                    ]);
                } else {
                    $scanError = self::INVALID_QR_MESSAGE;
                }
            } else {
                $scanError = self::INVALID_QR_MESSAGE;
            }
        }

        return view('shared.public-scan-qr', [
            'kodeSarana' => $kodeSarana,
            'sarana' => $sarana,
            'scanError' => $scanError,
            'isExactFormat' => $isExactFormat,
        ]);
    }

    public function quickAction(Request $request, Sarana $sarana, string $action): RedirectResponse
    {
        $role = (string) $request->route('role');
        abort_unless(in_array($role, self::ALLOWED_ROLES, true), 404);
        
        abort_unless(array_key_exists($action, self::SCAN_ACTIONS), 404);

        $this->logScanActivity(
            request: $request,
            role: $role,
            kodeSarana: $sarana->kode_sarana,
            sarana: $sarana,
            note: 'Quick action: ' . $this->actionLabel($action),
        );

        if ($action === 'lapor-kerusakan') {
            return redirect()->route($role . '.kerusakan.create', ['kode_sarana' => $sarana->kode_sarana]);
        }

        if ($action === 'usulan-mutasi') {
            return redirect()->route($role . '.mutasi.create', ['sarana_id' => $sarana->id]);
        }

        return redirect()->route('scan.sarana.histori', ['q' => $sarana->kode_sarana]);
    }

    /**
     * Titik masuk aksi dari halaman scan QR publik.
     * Middleware auth menyimpan URL tujuan sehingga pengguna kembali ke aksi ini setelah login.
     */
    public function publicQuickAction(Request $request, Sarana $sarana, string $action): RedirectResponse
    {
        $role = (string) $request->user()?->role_code;
        abort_unless(in_array($role, self::ALLOWED_ROLES, true), 403);
        abort_unless(array_key_exists($action, self::SCAN_ACTIONS), 404);

        return redirect()->route($role . '.scan.action', [
            'sarana' => $sarana,
            'action' => $action,
        ]);
    }

    private function actionLabel(string $action): string
    {
        return self::SCAN_ACTIONS[$action] ?? $action;
    }

    private function normalizeScanValue(string $value): string
    {
        return trim($value);
    }

    private function extractKodeSarana(string $value): ?string
    {
        if (preg_match(self::KODE_SARANA_PATTERN, $value)) {
            return $value;
        }

        if (preg_match('/SRN-[A-Z0-9]{3}-[A-Z0-9]{3}-L\\d{2}-\\d{4}-\\d{4}/', $value, $matches)) {
            return $matches[0];
        }

        return null;
    }

    private function logScanActivity(Request $request, string $role, string $kodeSarana, ?Sarana $sarana, string $note): void
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
                '[%s] kode=%s; sarana_id=%s; %s',
                $role,
                $kodeSarana,
                $sarana?->id ?? '-',
                $note,
            ),
            'ip_address' => $request->ip(),
        ]);
    }
}
