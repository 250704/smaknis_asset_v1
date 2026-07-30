<?php

namespace App\Http\Controllers;

use App\Models\ApprovalPengajuan;
use App\Models\Sarana;
use App\Models\Gedung;
use App\Models\KategoriSarana;
use App\Models\Notifikasi;
use App\Models\Pengajuan;
use App\Models\RiwayatKondisiSarana;
use App\Models\Ruangan;
use App\Models\User;
use App\Services\WhatsAppNotificationService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KerusakanController extends Controller
{
    private const ALLOWED_REPORTER_ROLES = ['guru', 'admin', 'kepala_sarana', 'bendahara', 'kepala_sekolah'];
    private const KONDISI_LIST = ['RINGAN', 'BERAT', 'TIDAK_LAYAK'];
    private const STATUS_LIST = ['DILAPORKAN', 'DIVALIDASI', 'DITINDAKLANJUTI', 'SELESAI', 'DITOLAK'];
    private const JENIS_PENGAJUAN = ['PERAWATAN', 'PENGGANTIAN'];
    private const ACTIVE_STATUS = ['DILAPORKAN', 'DIVALIDASI', 'DITINDAKLANJUTI'];

    public function create(Request $request, string $role): View
    {
        abort_unless(in_array($role, self::ALLOWED_REPORTER_ROLES, true), 404);

        $kodeSarana = trim((string) $request->query('kode_sarana', ''));
        $sarana = null;
        if ($kodeSarana !== '') {
            $sarana = Sarana::query()
                ->with(['ruangan.gedung'])
                ->where('kode_sarana', $kodeSarana)
                ->first();
        }

        $saranaList = Sarana::query()
            ->with(['kategori', 'ruangan.gedung'])
            ->where('status_sarana', 'AKTIF')
            ->orderBy('kode_sarana')
            ->get(['*']);

        $gedungList = Gedung::query()->orderBy('nama_gedung')->get(['*']);
        $ruanganList = Ruangan::query()->with('gedung')->orderBy('nama_ruangan')->get(['*']);
        $kategoriList = KategoriSarana::query()->orderBy('nama_kategori')->get(['*']);

        return view('guru.kerusakan.create', [
            'kodeSarana' => $kodeSarana,
            'sarana' => $sarana,
            'saranaList' => $saranaList,
            'gedungList' => $gedungList,
            'ruanganList' => $ruanganList,
            'kategoriList' => $kategoriList,
            'kondisiList' => self::KONDISI_LIST,
            'storeRoute' => route("{$role}.kerusakan.store"),
            'scanRoute' => route("{$role}.scan"),
        ]);
    }

    public function store(Request $request, string $role): RedirectResponse
    {
        abort_unless(in_array($role, self::ALLOWED_REPORTER_ROLES, true), 404);

        $validated = $request->validate([
            'sarana_id' => ['required', 'integer', 'exists:sarana,id'],
            'tingkat_kerusakan' => ['required', Rule::in(self::KONDISI_LIST)],
            'deskripsi' => ['required', 'string', 'max:1000'],
            'foto_kerusakan' => ['required', 'image', 'max:4096'],
        ]);

        $sarana = Sarana::query()->find($validated['sarana_id']);
        if (!$sarana) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['sarana_id' => 'Sarana tidak ditemukan.']);
        }

        $alreadyReported = RiwayatKondisiSarana::query()
            ->where('sarana_id', $sarana->id)
            ->whereIn('status', self::ACTIVE_STATUS)
            ->exists();
        if ($alreadyReported) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['sarana_id' => 'Sarana ini sudah memiliki laporan aktif yang sedang diproses.']);
        }

        $path = $this->storeMediaFile($request->file('foto_kerusakan'), 'kerusakan', 'public');

        $riwayat = RiwayatKondisiSarana::query()->create([
            'sarana_id' => $sarana->id,
            'user_id' => $request->user()->id,
            'tingkat_kerusakan' => $validated['tingkat_kerusakan'],
            'deskripsi' => $validated['deskripsi'],
            'foto_kerusakan' => $path,
            'status' => 'DILAPORKAN',
        ]);
        $this->broadcastKerusakanTracking(
            $riwayat,
            "Laporan dibuat oleh {$request->user()->display_name}.",
            'Laporan kerusakan baru menunggu validasi.'
        );

        return redirect()
            ->route("{$role}.scan")
            ->with('success', 'Laporan kerusakan berhasil dikirim.');
    }

    public function guruCreate(Request $request): View
    {
        return $this->create($request, 'guru');
    }

    public function guruStore(Request $request): RedirectResponse
    {
        return $this->store($request, 'guru');
    }

    public function kepalaSaranaIndex(Request $request): View
    {
        $currentUser = $request->user();
        abort_unless($currentUser && $currentUser->hasRole('kepala_sarana'), 403);

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => (string) $request->query('status', ''),
        ];

        $riwayat = RiwayatKondisiSarana::query()
            ->with(['sarana.ruangan.gedung', 'user'])
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->whereHas('sarana', function ($saranaQuery) use ($filters) {
                    $saranaQuery->where('kode_sarana', 'like', "%{$filters['q']}%")
                        ->orWhere('nama_sarana', 'like', "%{$filters['q']}%");
                });
            })
            ->when(in_array($filters['status'], self::STATUS_LIST, true), fn($query) => $query->where('status', $filters['status']))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('kepala_sarana.kerusakan.index', [
            'riwayat' => $riwayat,
            'filters' => $filters,
            'kondisiList' => self::KONDISI_LIST,
            'statusList' => self::STATUS_LIST,
        ]);
    }

    public function validateKerusakan(Request $request, RiwayatKondisiSarana $riwayat): RedirectResponse
    {
        $validatorUser = $request->user();
        if (!$validatorUser || !$validatorUser->hasRole('kepala_sarana')) {
            abort(403);
        }

        if ($riwayat->status !== 'DILAPORKAN') {
            return redirect()
                ->back()
                ->with('error', 'Laporan ini sudah diproses.');
        }

        $action = strtoupper((string) $request->input('action', 'VALIDASI'));

        if ($action === 'TOLAK') {
            $validated = $request->validate([
                'catatan' => ['required', 'string', 'max:500'],
            ]);

            $riwayat->update([
                'status' => 'DITOLAK',
                'validated_by' => $request->user()->id,
                'validated_at' => now(),
                'catatan_validasi' => $validated['catatan'],
            ]);

            $this->broadcastKerusakanTracking(
                $riwayat,
                "Laporan ditolak oleh {$request->user()->display_name}.",
                'Catatan: ' . $validated['catatan']
            );

            return redirect()
                ->back()
                ->with('success', 'Laporan kerusakan ditolak.');
        }

        $validated = $request->validate([
            'tingkat_kerusakan' => ['required', Rule::in(self::KONDISI_LIST)],
            'rekomendasi_tindakan' => ['required', Rule::in(self::JENIS_PENGAJUAN)],
            'estimasi_biaya' => ['required', 'numeric', 'min:0'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $sarana = $riwayat->sarana;
        if (!$sarana) {
            return redirect()
                ->back()
                ->with('error', 'Sarana tidak ditemukan.');
        }

        $existing = Pengajuan::query()
            ->where('sarana_id', $sarana->id)
            ->whereIn('status_pengajuan', [
                Pengajuan::STATUS_DIAJUKAN,
                Pengajuan::STATUS_DISETUJUI_KASARANA,
                Pengajuan::STATUS_DISETUJUI_BENDAHARA,
                Pengajuan::STATUS_DISETUJUI_KEPSEK,
                Pengajuan::STATUS_DIPROSES,
            ])
            ->exists();
        if ($existing) {
            return redirect()
                ->back()
                ->with('error', 'Sudah ada pengajuan aktif untuk sarana ini.');
        }

        $createdPengajuan = null;
        DB::transaction(function () use ($request, $riwayat, $sarana, $validated, &$createdPengajuan) {
            $riwayat->update([
                'tingkat_kerusakan' => $validated['tingkat_kerusakan'],
                'status' => 'DIVALIDASI',
                'validated_by' => $request->user()->id,
                'validated_at' => now(),
                'rekomendasi_tindakan' => $validated['rekomendasi_tindakan'],
                'catatan_validasi' => $validated['catatan'] ?? null,
            ]);

            $sarana->update([
                'kondisi_terkini' => $validated['tingkat_kerusakan'],
            ]);

            $jenisPengajuan = $validated['rekomendasi_tindakan'];
            $initialPengajuanStatus = Pengajuan::STATUS_DISETUJUI_KASARANA;

            // Buat pengajuan otomatis dari hasil validasi kerusakan oleh Kepala Sarana.
            $pengajuan = Pengajuan::query()->create([
                'sarana_id' => $sarana->id,
                'user_id' => $request->user()->id,
                'judul_pengajuan' => ($jenisPengajuan === 'PENGGANTIAN' ? 'Penggantian' : 'Perawatan') . " Sarana {$sarana->kode_sarana}",
                'jenis_pengajuan' => $jenisPengajuan,
                'deskripsi' => $riwayat->deskripsi,
                'estimasi_biaya' => $validated['estimasi_biaya'],
                'target_realisasi' => null,
                'status_pengajuan' => $initialPengajuanStatus,
            ]);

            ApprovalPengajuan::query()->create([
                'pengajuan_id' => $pengajuan->id,
                'approver_id' => $request->user()->id,
                'role_approval' => ApprovalPengajuan::ROLE_KASARANA,
                'status' => ApprovalPengajuan::STATUS_DISETUJUI,
                'catatan' => $validated['catatan'] ?? null,
                'approved_at' => now(),
            ]);

            $createdPengajuan = $pengajuan;
        });
        $this->cleanupKerusakanTrackingNotifications((string) $sarana->kode_sarana);
        if ($createdPengajuan) {
            $this->broadcastPengajuanTrackingFromKerusakan(
                $createdPengajuan,
                (string) $request->user()->display_name,
                (string) ($validated['catatan'] ?? '')
            );
        }

        return redirect()
            ->back()
            ->with('success', 'Kerusakan berhasil divalidasi dan pengajuan otomatis dibuat.');
    }

    public function kepalaSaranaRealisasiIndex(Request $request): View
    {
        $user = $request->user();
        if (!$user || !$user->hasRole('kepala_sarana')) {
            abort(403);
        }

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => (string) $request->query('status', 'DITINDAKLANJUTI'),
        ];

        $riwayat = RiwayatKondisiSarana::query()
            ->with(['sarana.ruangan.gedung', 'user'])
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->whereHas('sarana', function ($saranaQuery) use ($filters) {
                    $saranaQuery->where('kode_sarana', 'like', "%{$filters['q']}%")
                        ->orWhere('nama_sarana', 'like', "%{$filters['q']}%");
                });
            })
            ->when(in_array($filters['status'], self::STATUS_LIST, true), fn($query) => $query->where('status', $filters['status']))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $saranaIds = $riwayat->pluck('sarana_id')->filter()->unique()->values()->all();
        $pengajuanMap = Pengajuan::query()
            ->whereIn('sarana_id', $saranaIds)
            ->whereIn('status_pengajuan', [
                Pengajuan::STATUS_DIAJUKAN,
                Pengajuan::STATUS_DISETUJUI_KASARANA,
                Pengajuan::STATUS_DISETUJUI_BENDAHARA,
                Pengajuan::STATUS_DISETUJUI_KEPSEK,
                Pengajuan::STATUS_DIPROSES,
            ])
            ->get(['*'])
            ->groupBy('sarana_id');

        return view('kepala_sarana.kerusakan.realisasi', [
            'riwayat' => $riwayat,
            'filters' => $filters,
            'statusList' => self::STATUS_LIST,
            'pengajuanMap' => $pengajuanMap,
        ]);
    }

    private function humanizePengajuanStatus(string $status): string
    {
        return match ($status) {
            Pengajuan::STATUS_DIAJUKAN => 'Menunggu Approval Kepala Sarana',
            Pengajuan::STATUS_DISETUJUI_KASARANA => 'Menunggu Approval Bendahara',
            Pengajuan::STATUS_DISETUJUI_BENDAHARA => 'Menunggu Approval Kepala Sekolah',
            Pengajuan::STATUS_DISETUJUI_KEPSEK => 'Disetujui Final',
            Pengajuan::STATUS_DIPROSES => 'Realisasi Diproses',
            Pengajuan::STATUS_SELESAI => 'Selesai',
            Pengajuan::STATUS_DITOLAK => 'Ditolak',
            default => $status,
        };
    }

    private function humanizeKerusakanStatus(string $status): string
    {
        return match ($status) {
            'DILAPORKAN' => 'Dilaporkan',
            'DIVALIDASI' => 'Divalidasi',
            'DITINDAKLANJUTI' => 'Ditindaklanjuti',
            'SELESAI' => 'Selesai',
            'DITOLAK' => 'Ditolak',
            default => $status,
        };
    }

    private function kerusakanStatusLabel(string $status): string
    {
        return $this->humanizeKerusakanStatus($status);
    }

    private function pengajuanStatusLabel(string $status): string
    {
        return $this->humanizePengajuanStatus($status);
    }

    private function jenisLabel(string $jenis): string
    {
        return match ($jenis) {
            'PERAWATAN' => 'Perawatan',
            'PENGGANTIAN' => 'Penggantian',
            'PENGADAAN' => 'Pengadaan',
            default => $jenis,
        };
    }

    private function broadcastKerusakanTracking(
        RiwayatKondisiSarana $riwayat,
        string $aktivitas,
        ?string $catatan = null,
        ?int $actorUserId = null
    ): void {
        $riwayat->loadMissing(['sarana', 'user']);

        $judul = 'Tracking Kerusakan';
        $isi = "{$aktivitas}\nSarana: {$riwayat->sarana?->kode_sarana}\nStatus: {$this->kerusakanStatusLabel((string)$riwayat->status)}";
        if ($catatan !== null && trim($catatan) !== '') {
            $isi .= "\n{$catatan}";
        }

        $actorId = $actorUserId ?? (auth()->id() ? (int) auth()->id() : null);
        $excludeUserIds = $actorId ? [$actorId] : [];

        $targetRoles = $this->resolveNextKerusakanRoles($riwayat);
        foreach ($targetRoles as $role) {
            $this->notifyRole(
                $role,
                $judul,
                $isi,
                $this->resolveKerusakanUrlByRole($role, $riwayat),
                $excludeUserIds
            );
        }

        if ($targetRoles === []) {
            $this->notifyUser(
                $riwayat->user,
                $judul,
                $isi,
                $this->resolveKerusakanCreateUrlForUser($riwayat->user),
                $excludeUserIds
            );
        }
    }

    private function resolveNextKerusakanRoles(RiwayatKondisiSarana $riwayat): array
    {
        if ($riwayat->status === 'DILAPORKAN') {
            return $riwayat->user && $riwayat->user->hasRole('kepala_sarana')
                ? ['bendahara']
                : ['kepala_sarana'];
        }

        return [];
    }

    private function resolveKerusakanUrlByRole(string $role, RiwayatKondisiSarana $riwayat): ?string
    {
        $kodeSarana = $riwayat->sarana?->kode_sarana;

        return match ($role) {
            'admin' => route('admin.kerusakan.create', $kodeSarana ? ['kode_sarana' => $kodeSarana] : []),
            'kepala_sarana' => route('kepala_sarana.pengajuan.approval', $kodeSarana ? ['q' => $kodeSarana] : []),
            // Untuk bendahara, notifikasi kerusakan harus mengarah ke antrean kerja anggaran.
            'bendahara' => route('bendahara.pengajuan.approval'),
            'kepala_sekolah' => route('kepala_sekolah.kerusakan.index', $kodeSarana ? ['q' => $kodeSarana] : []),
            default => null,
        };
    }

    private function broadcastPengajuanTrackingFromKerusakan(
        Pengajuan $pengajuan,
        string $validatorName,
        string $catatan = '',
        ?int $actorUserId = null
    ): void {
        $judul = 'Tracking Pengajuan';
        $status = $this->pengajuanStatusLabel((string) $pengajuan->status_pengajuan);
        $isi = "Pengajuan dibuat dari validasi kerusakan oleh {$validatorName}.\n" .
            "Judul: {$pengajuan->judul_pengajuan}\n" .
            "Jenis: {$this->jenisLabel((string)$pengajuan->jenis_pengajuan)}\n" .
            "Status: {$status}";

        if (trim($catatan) !== '') {
            $isi .= "\nCatatan: {$catatan}";
        }

        $recipients = $this->resolvePengajuanAudienceUsers($pengajuan);
        foreach ($recipients as $recipient) {
            $userId = (int) $recipient->id;
            $this->notifyUsers(
                [$userId],
                $judul,
                $isi,
                $this->resolvePengajuanUrlForUser($recipient, $pengajuan),
                []
            );
        }
    }

    private function resolvePengajuanAudienceUsers(Pengajuan $pengajuan)
    {
        $roleNames = ['guru', 'admin', 'kepala_sarana', 'bendahara', 'kepala_sekolah'];

        return User::query()
            ->where(function ($query) use ($roleNames) {
                $query->whereIn('role', $roleNames)
                    ->orWhereHas('roleRelation', fn($roleQuery) => $roleQuery->whereIn('nama_role', $roleNames));
            })
            ->where(function ($query) {
                $query->whereNull('status_akun')->orWhere('status_akun', '!=', 'NONAKTIF');
            })
            ->get(['*'])
            ->push($pengajuan->user)
            ->filter(fn($user) => $user instanceof User)
            ->unique(function ($user) {
                return (int) $user->id;
            })
            ->values();
    }

    private function resolvePengajuanUrlForUser(?User $user, Pengajuan $pengajuan): ?string
    {
        if (!$user) {
            return null;
        }

        if ($user->hasRole('guru')) {
            return route('guru.pengajuan.show', $pengajuan);
        }

        if ($user->hasRole('admin')) {
            return route('admin.pengajuan.show', $pengajuan);
        }

        if ($user->hasRole('kepala_sarana')) {
            return route('kepala_sarana.pengajuan.show', $pengajuan);
        }

        if ($user->hasRole('bendahara')) {
            return route('bendahara.pengajuan.approval');
        }

        if ($user->hasRole('kepala_sekolah')) {
            return route('kepala_sekolah.pengajuan.show', $pengajuan);
        }

        return null;
    }

    private function cleanupKerusakanTrackingNotifications(string $kodeSarana): void
    {
        if (trim($kodeSarana) === '') {
            return;
        }

        Notifikasi::query()
            ->where('judul', 'like', '%Tracking Kerusakan%')
            ->where('isi', 'like', "%{$kodeSarana}%")
            ->delete();
    }

    private function notifyRole(string $role, string $judul, string $isi, ?string $url = null, array $excludeUserIds = []): void
    {
        $userIds = User::query()
            ->where(function ($query) use ($role) {
                $query->whereHas('roleRelation', fn($roleQuery) => $roleQuery->where('nama_role', $role))
                    ->orWhere('role', $role);
            })
            ->where(function ($query) {
                $query->whereNull('status_akun')->orWhere('status_akun', '!=', 'NONAKTIF');
            })
            ->pluck('id')
            ->all();

        $this->notifyUsers($userIds, $judul, $isi, $url, $excludeUserIds);
    }

    private function notifyUser(?User $user, string $judul, string $isi, ?string $url = null, array $excludeUserIds = []): void
    {
        if (!$user) {
            return;
        }

        $this->notifyUsers([$user->id], $judul, $isi, $url, $excludeUserIds);
    }

    private function notifyUsers(array $userIds, string $judul, string $isi, ?string $url = null, array $excludeUserIds = []): void
    {
        $judul = $this->normalizeNotificationTitle($judul);
        $thread = $this->extractNotificationThreadKey($judul, $isi);

        $excludedMap = [];
        foreach ($excludeUserIds as $excludedId) {
            $excludedMap[(int) $excludedId] = true;
        }

        $unique = array_values(array_unique(array_filter($userIds)));
        foreach ($unique as $userId) {
            if (isset($excludedMap[(int) $userId])) {
                continue;
            }

            $existingQuery = Notifikasi::query()->where('user_id', $userId);
            if ($thread !== null) {
                if ($thread['type'] === 'pengajuan') {
                    $existingQuery
                        ->where('judul', 'like', '%Tracking Pengajuan%')
                        ->where('isi', 'like', "%{$thread['key']}%");
                } else {
                    $existingQuery
                        ->where('judul', 'like', '%Tracking Kerusakan%')
                        ->where('isi', 'like', "%{$thread['key']}%");
                }
            } else {
                $existingQuery->where('is_read', false);
                if ($url) {
                    $existingQuery->where('url', $url);
                } else {
                    $existingQuery
                        ->whereNull('url')
                        ->where('judul', $judul);
                }
            }

            $existing = $existingQuery->latest('id')->first();
            if ($existing) {
                $existing->update([
                    'judul' => $judul,
                    'isi' => $isi,
                    'url' => $url,
                    'is_read' => false,
                ]);
                app(WhatsAppNotificationService::class)->sendToUserId((int) $userId, $judul, $isi, $url);
                continue;
            }

            Notifikasi::query()->create([
                'user_id' => $userId,
                'judul' => $judul,
                'isi' => $isi,
                'url' => $url,
                'is_read' => false,
            ]);
            app(WhatsAppNotificationService::class)->sendToUserId((int) $userId, $judul, $isi, $url);
        }
    }

    private function extractNotificationThreadKey(string $judul, string $isi): ?array
    {
        if (str_contains($judul, 'Tracking Pengajuan')) {
            if (preg_match('/^Judul:\\s*(.+)$/m', $isi, $matches) === 1) {
                $value = trim((string) $matches[1]);
                if ($value !== '') {
                    return ['type' => 'pengajuan', 'key' => $value];
                }
            }

            if (preg_match('/(?:AST|SRN)-[A-Z0-9-]+/i', $isi, $matches) === 1) {
                return ['type' => 'pengajuan', 'key' => strtoupper((string) $matches[0])];
            }

            return null;
        }

        if (str_contains($judul, 'Tracking Kerusakan')) {
            if (preg_match('/Sarana:\\s*([A-Z0-9-]+)/i', $isi, $matches) === 1) {
                return ['type' => 'kerusakan', 'key' => strtoupper((string) $matches[1])];
            }

            if (preg_match('/(?:AST|SRN)-[A-Z0-9-]+/i', $isi, $matches) === 1) {
                return ['type' => 'kerusakan', 'key' => strtoupper((string) $matches[0])];
            }
        }

        return null;
    }

    private function normalizeNotificationTitle(string $title): string
    {
        $cleanTitle = trim($title);
        if ($cleanTitle === '') {
            return 'Kerusakan | Update';
        }

        if (str_contains($cleanTitle, '|')) {
            return $cleanTitle;
        }

        $lower = mb_strtolower($cleanTitle);
        $prefix = 'Kerusakan';

        if (str_contains($lower, 'pengajuan')) {
            $prefix = 'Pengajuan';
        } elseif (str_contains($lower, 'realisasi')) {
            $prefix = 'Realisasi';
        } elseif (str_contains($lower, 'verifikasi') || str_contains($lower, 'validasi')) {
            $prefix = 'Verifikasi';
        }

        return "{$prefix} | {$cleanTitle}";
    }

    private function resolveKerusakanCreateUrlForUser(?User $user): ?string
    {
        if (!$user) {
            return null;
        }

        if ($user->hasRole('guru')) {
            return route('guru.kerusakan.create');
        }

        if ($user->hasRole('admin')) {
            return route('admin.kerusakan.create');
        }

        if ($user->hasRole('kepala_sarana')) {
            return route('kepala_sarana.kerusakan.create');
        }

        if ($user->hasRole('bendahara')) {
            return route('bendahara.kerusakan.create');
        }

        if ($user->hasRole('kepala_sekolah')) {
            return route('kepala_sekolah.kerusakan.create');
        }

        return null;
    }
}
