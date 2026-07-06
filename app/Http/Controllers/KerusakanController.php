<?php

namespace App\Http\Controllers;

use App\Models\ApprovalPengajuan;
use App\Models\Aset;
use App\Models\Gedung;
use App\Models\KategoriAset;
use App\Models\Notifikasi;
use App\Models\Pengajuan;
use App\Models\RiwayatKondisiAset;
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

        $kodeAset = trim((string) $request->query('kode_aset', ''));
        $aset = null;
        if ($kodeAset !== '') {
            $aset = Aset::query()
                ->with(['ruangan.gedung'])
                ->where('kode_aset', $kodeAset)
                ->first();
        }

        $asetList = Aset::query()
            ->with(['kategori', 'ruangan.gedung'])
            ->where('status_aset', 'AKTIF')
            ->orderBy('kode_aset')
            ->get();

        $gedungList = Gedung::query()->orderBy('nama_gedung')->get();
        $ruanganList = Ruangan::query()->with('gedung')->orderBy('nama_ruangan')->get();
        $kategoriList = KategoriAset::query()->orderBy('nama_kategori')->get();

        return view('guru.kerusakan.create', [
            'kodeAset' => $kodeAset,
            'aset' => $aset,
            'asetList' => $asetList,
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
            'aset_id' => ['required', 'integer', 'exists:aset,id'],
            'tingkat_kerusakan' => ['required', Rule::in(self::KONDISI_LIST)],
            'deskripsi' => ['required', 'string', 'max:1000'],
            'foto_kerusakan' => ['required', 'image', 'max:4096'],
        ]);

        $aset = Aset::query()->find($validated['aset_id']);
        if (!$aset) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['aset_id' => 'Aset tidak ditemukan.']);
        }

        $alreadyReported = RiwayatKondisiAset::query()
            ->where('aset_id', $aset->id)
            ->whereIn('status', self::ACTIVE_STATUS)
            ->exists();
        if ($alreadyReported) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['aset_id' => 'Aset ini sudah memiliki laporan aktif yang sedang diproses.']);
        }

        $path = $this->storeMediaFile($request->file('foto_kerusakan'), 'kerusakan', 'public');

        $riwayat = RiwayatKondisiAset::query()->create([
            'aset_id' => $aset->id,
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
        $forKepalaSekolah = $currentUser && $currentUser->hasRole('kepala_sekolah');

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => (string) $request->query('status', ''),
        ];

        $riwayat = RiwayatKondisiAset::query()
            ->with(['aset.ruangan.gedung', 'user'])
            ->when($forKepalaSekolah, function ($query) {
                $query->where('status', 'DILAPORKAN')
                    ->whereHas('user', function ($userQuery) {
                        $userQuery->where('role', 'kepala_sarana')
                            ->orWhereHas('roleRelation', fn ($roleQuery) => $roleQuery->where('nama_role', 'kepala_sarana'));
                    });
            })
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->whereHas('aset', function ($asetQuery) use ($filters) {
                    $asetQuery->where('kode_aset', 'like', "%{$filters['q']}%")
                        ->orWhere('nama_aset', 'like', "%{$filters['q']}%");
                });
            })
            ->when(in_array($filters['status'], self::STATUS_LIST, true), fn ($query) => $query->where('status', $filters['status']))
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

    public function validateKerusakan(Request $request, RiwayatKondisiAset $riwayat): RedirectResponse
    {
        $validatorUser = $request->user();
        $reportedByKepalaSarana = (bool) ($riwayat->user?->hasRole('kepala_sarana'));
        $requiredValidatorRole = $reportedByKepalaSarana ? 'kepala_sekolah' : 'kepala_sarana';
        if (!$validatorUser || !$validatorUser->hasRole($requiredValidatorRole)) {
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

        $aset = $riwayat->aset;
        if (!$aset) {
            return redirect()
                ->back()
                ->with('error', 'Aset tidak ditemukan.');
        }

        $existing = Pengajuan::query()
            ->where('aset_id', $aset->id)
            ->whereIn('status_pengajuan', [
                Pengajuan::STATUS_DIAJUKAN,
                Pengajuan::STATUS_DISETUJUI_KASARANA,
                Pengajuan::STATUS_DISETUJUI_BENDAHARA,
                Pengajuan::STATUS_DISETUJUI_KEPSEK,
                Pengajuan::STATUS_DIPROSES,
                Pengajuan::STATUS_MENUNGGU_VERIFIKASI_TEKNIS,
                Pengajuan::STATUS_MENUNGGU_VERIFIKASI_KEUANGAN,
            ])
            ->exists();
        if ($existing) {
            return redirect()
                ->back()
                ->with('error', 'Sudah ada pengajuan aktif untuk aset ini.');
        }

        $createdPengajuan = null;
        DB::transaction(function () use ($request, $riwayat, $aset, $validated, $requiredValidatorRole, &$createdPengajuan) {
            $riwayat->update([
                'tingkat_kerusakan' => $validated['tingkat_kerusakan'],
                'status' => 'DIVALIDASI',
                'validated_by' => $request->user()->id,
                'validated_at' => now(),
                'rekomendasi_tindakan' => $validated['rekomendasi_tindakan'],
                'catatan_validasi' => $validated['catatan'] ?? null,
            ]);

            $aset->update([
                'kondisi_terkini' => $validated['tingkat_kerusakan'],
            ]);

            $jenisPengajuan = $validated['rekomendasi_tindakan'];
            // Buat pengajuan otomatis dari hasil validasi kerusakan.
            // Status langsung ke antrean bendahara (DISETUJUI_KASARANA) sesuai flow final.
            $pengajuan = Pengajuan::query()->create([
                'aset_id' => $aset->id,
                'user_id' => $request->user()->id,
                'judul_pengajuan' => ($jenisPengajuan === 'PENGGANTIAN' ? 'Penggantian' : 'Perawatan') . " Aset {$aset->kode_aset}",
                'jenis_pengajuan' => $jenisPengajuan,
                'deskripsi' => $riwayat->deskripsi,
                'estimasi_biaya' => $validated['estimasi_biaya'],
                'target_realisasi' => null,
                'status_pengajuan' => Pengajuan::STATUS_DISETUJUI_KASARANA,
            ]);

            // Simpan approval pengajuan hanya jika validator adalah Kepala Sarana.
            // Jika validator adalah Kepala Sekolah (kasus pelapor Kepala Sarana),
            // audit validasi tetap tersimpan di riwayat_kondisi_aset.
            if ($requiredValidatorRole === 'kepala_sarana') {
                ApprovalPengajuan::query()->create([
                    'pengajuan_id' => $pengajuan->id,
                    'approver_id' => $request->user()->id,
                    'role_approval' => ApprovalPengajuan::ROLE_KASARANA,
                    'status' => ApprovalPengajuan::STATUS_DISETUJUI,
                    'catatan' => $validated['catatan'] ?? null,
                    'approved_at' => now(),
                ]);
            }

            $createdPengajuan = $pengajuan;
        });
        $this->cleanupKerusakanTrackingNotifications((string) $aset->kode_aset);
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

        $riwayat = RiwayatKondisiAset::query()
            ->with(['aset.ruangan.gedung', 'user'])
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->whereHas('aset', function ($asetQuery) use ($filters) {
                    $asetQuery->where('kode_aset', 'like', "%{$filters['q']}%")
                        ->orWhere('nama_aset', 'like', "%{$filters['q']}%");
                });
            })
            ->when(in_array($filters['status'], self::STATUS_LIST, true), fn ($query) => $query->where('status', $filters['status']))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $asetIds = $riwayat->pluck('aset_id')->filter()->unique()->values()->all();
        $pengajuanMap = Pengajuan::query()
            ->whereIn('aset_id', $asetIds)
            ->whereIn('status_pengajuan', [
                Pengajuan::STATUS_DIAJUKAN,
                Pengajuan::STATUS_DISETUJUI_KASARANA,
                Pengajuan::STATUS_DISETUJUI_BENDAHARA,
                Pengajuan::STATUS_DISETUJUI_KEPSEK,
                Pengajuan::STATUS_DIPROSES,
                Pengajuan::STATUS_MENUNGGU_VERIFIKASI_TEKNIS,
                Pengajuan::STATUS_MENUNGGU_VERIFIKASI_KEUANGAN,
            ])
            ->get()
            ->groupBy('aset_id');

        return view('kepala_sarana.kerusakan.realisasi', [
            'riwayat' => $riwayat,
            'filters' => $filters,
            'statusList' => self::STATUS_LIST,
            'pengajuanMap' => $pengajuanMap,
        ]);
    }

    public function kepalaSaranaSemuaProses(Request $request): View
    {
        $user = $request->user();
        if (!$user || !$user->hasRole('kepala_sarana')) {
            abort(403);
        }

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => strtoupper(trim((string) $request->query('status', ''))),
            'jenis' => strtoupper(trim((string) $request->query('jenis', ''))),
        ];

        $latestPengajuanIds = Pengajuan::query()
            ->selectRaw('MAX(id)')
            ->whereNotNull('aset_id')
            ->groupBy('aset_id');

        $latestKerusakanIds = RiwayatKondisiAset::query()
            ->selectRaw('MAX(id)')
            ->whereNotNull('aset_id')
            ->groupBy('aset_id');

        $latestPengajuan = Pengajuan::query()
            ->with(['aset.ruangan.gedung', 'approvalPengajuan.approver'])
            ->whereIn('id', $latestPengajuanIds)
            ->get()
            ->keyBy('aset_id');

        $latestKerusakan = RiwayatKondisiAset::query()
            ->with(['aset.ruangan.gedung', 'validator'])
            ->whereIn('id', $latestKerusakanIds)
            ->get()
            ->keyBy('aset_id');

        $asetIds = $latestPengajuan->keys()
            ->merge($latestKerusakan->keys())
            ->unique()
            ->filter()
            ->values()
            ->all();

        $asetCollection = Aset::query()
            ->with(['ruangan.gedung'])
            ->whereIn('id', $asetIds)
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($inner) use ($filters) {
                    $inner->where('kode_aset', 'like', "%{$filters['q']}%")
                        ->orWhere('nama_aset', 'like', "%{$filters['q']}%");
                });
            })
            ->get();

        $rows = $asetCollection->map(function (Aset $aset) use ($latestPengajuan, $latestKerusakan) {
            $pengajuan = $latestPengajuan->get($aset->id);
            $kerusakan = $latestKerusakan->get($aset->id);

            $pengajuanAt = $pengajuan?->updated_at ?? $pengajuan?->created_at;
            $kerusakanAt = $kerusakan?->updated_at ?? $kerusakan?->created_at;

            $source = null;
            if ($pengajuanAt && $kerusakanAt) {
                $source = $pengajuanAt->greaterThanOrEqualTo($kerusakanAt) ? 'PENGAJUAN' : 'KERUSAKAN';
            } elseif ($pengajuanAt) {
                $source = 'PENGAJUAN';
            } elseif ($kerusakanAt) {
                $source = 'KERUSAKAN';
            }

            $statusGroup = $this->mapStatusGroup($source, $pengajuan, $kerusakan);
            $tahap = $this->mapTahapTerakhir($source, $pengajuan, $kerusakan);
            $jenis = $source === 'PENGAJUAN'
                ? (string) ($pengajuan?->jenis_pengajuan ?? '-')
                : 'KERUSAKAN';
            $latestAt = $source === 'PENGAJUAN' ? $pengajuanAt : $kerusakanAt;
            $latestApproval = $pengajuan?->approvalPengajuan
                ?->sortByDesc(fn ($approval) => $approval->approved_at ?? $approval->created_at)
                ->first();

            $approvalBy = '-';
            if ($latestApproval) {
                $approvalBy = ($latestApproval->approver?->display_name ?? '-') . " ({$latestApproval->role_approval})";
            } elseif ($source === 'KERUSAKAN' && $kerusakan?->validator) {
                $approvalBy = ($kerusakan->validator->display_name ?? '-') . ' (VALIDASI)';
            }

            return [
                'aset_id' => $aset->id,
                'kode_aset' => $aset->kode_aset,
                'nama_aset' => $aset->nama_aset,
                'lokasi' => $aset->ruangan?->nama_ruangan . ' - ' . $aset->ruangan?->gedung?->nama_gedung,
                'jenis' => $jenis,
                'status_group' => $statusGroup,
                'status_label' => $source === 'PENGAJUAN'
                    ? $this->humanizePengajuanStatus((string) ($pengajuan?->status_pengajuan ?? '-'))
                    : $this->humanizeKerusakanStatus((string) ($kerusakan?->status ?? '-')),
                'tahap_terakhir' => $tahap,
                'approval_terakhir' => $approvalBy,
                'updated_at' => $latestAt,
                'detail_url' => $pengajuan
                    ? route('kepala_sarana.pengajuan.show', $pengajuan)
                    : route('kepala_sarana.kerusakan.index', ['q' => $aset->kode_aset]),
            ];
        });

        $stats = [
            'menunggu' => $rows->where('status_group', 'MENUNGGU')->count(),
            'proses' => $rows->where('status_group', 'PROSES')->count(),
            'selesai' => $rows->where('status_group', 'SELESAI')->count(),
            'ditolak' => $rows->where('status_group', 'DITOLAK')->count(),
            'total' => $rows->count(),
        ];

        $filteredRows = $rows
            ->when($filters['status'] !== '', fn (Collection $c) => $c->where('status_group', $filters['status']))
            ->when($filters['jenis'] !== '', fn (Collection $c) => $c->where('jenis', $filters['jenis']))
            ->sortByDesc(fn (array $item) => $item['updated_at']?->timestamp ?? 0)
            ->values();

        $perPage = 10;
        $page = (int) $request->query('page', 1);
        $total = $filteredRows->count();
        $items = $filteredRows->forPage($page, $perPage)->values();
        $monitoring = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('kepala_sarana.validasi.semua', [
            'monitoring' => $monitoring,
            'filters' => $filters,
            'stats' => $stats,
        ]);
    }

    private function mapStatusGroup(?string $source, ?Pengajuan $pengajuan, ?RiwayatKondisiAset $kerusakan): string
    {
        if ($source === 'PENGAJUAN' && $pengajuan) {
            return match ($pengajuan->status_pengajuan) {
                Pengajuan::STATUS_DITOLAK => 'DITOLAK',
                Pengajuan::STATUS_SELESAI => 'SELESAI',
                Pengajuan::STATUS_DIPROSES => 'PROSES',
                Pengajuan::STATUS_MENUNGGU_VERIFIKASI_TEKNIS, Pengajuan::STATUS_MENUNGGU_VERIFIKASI_KEUANGAN => 'MENUNGGU',
                default => 'MENUNGGU',
            };
        }

        if ($kerusakan) {
            return match ($kerusakan->status) {
                'DITOLAK' => 'DITOLAK',
                'SELESAI' => 'SELESAI',
                'DITINDAKLANJUTI' => 'PROSES',
                default => 'MENUNGGU',
            };
        }

        return 'MENUNGGU';
    }

    private function mapTahapTerakhir(?string $source, ?Pengajuan $pengajuan, ?RiwayatKondisiAset $kerusakan): string
    {
        if ($source === 'PENGAJUAN' && $pengajuan) {
            return match ($pengajuan->status_pengajuan) {
                Pengajuan::STATUS_DIAJUKAN => 'Menunggu Approval Kepala Sarana',
                Pengajuan::STATUS_DISETUJUI_KASARANA => 'Menunggu Approval Bendahara',
                Pengajuan::STATUS_DISETUJUI_BENDAHARA => 'Menunggu Approval Kepala Sekolah',
                Pengajuan::STATUS_DISETUJUI_KEPSEK => 'Menunggu Realisasi',
                Pengajuan::STATUS_DIPROSES => "{$pengajuan->jenis_pengajuan} sedang diproses",
                Pengajuan::STATUS_MENUNGGU_VERIFIKASI_TEKNIS => 'Menunggu verifikasi teknis kepala sarana',
                Pengajuan::STATUS_MENUNGGU_VERIFIKASI_KEUANGAN => 'Menunggu verifikasi keuangan bendahara',
                Pengajuan::STATUS_SELESAI => "{$pengajuan->jenis_pengajuan} selesai",
                Pengajuan::STATUS_DITOLAK => "{$pengajuan->jenis_pengajuan} ditolak",
                default => (string) $pengajuan->status_pengajuan,
            };
        }

        if ($kerusakan) {
            return match ($kerusakan->status) {
                'DILAPORKAN' => 'Laporan kerusakan masuk',
                'DIVALIDASI' => 'Sedang validasi kerusakan',
                'DITINDAKLANJUTI' => 'Kerusakan ditindaklanjuti',
                'SELESAI' => 'Kerusakan selesai ditangani',
                'DITOLAK' => 'Laporan kerusakan ditolak',
                default => (string) $kerusakan->status,
            };
        }

        return '-';
    }

    private function humanizePengajuanStatus(string $status): string
    {
        return match ($status) {
            Pengajuan::STATUS_DIAJUKAN => 'Menunggu Approval Kepala Sarana',
            Pengajuan::STATUS_DISETUJUI_KASARANA => 'Menunggu Approval Bendahara',
            Pengajuan::STATUS_DISETUJUI_BENDAHARA => 'Menunggu Approval Kepala Sekolah',
            Pengajuan::STATUS_DISETUJUI_KEPSEK => 'Disetujui Final',
            Pengajuan::STATUS_DIPROSES => 'Realisasi Diproses',
            Pengajuan::STATUS_MENUNGGU_VERIFIKASI_TEKNIS => 'Menunggu Verifikasi Teknis',
            Pengajuan::STATUS_MENUNGGU_VERIFIKASI_KEUANGAN => 'Menunggu Verifikasi Keuangan',
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
        RiwayatKondisiAset $riwayat,
        string $aktivitas,
        ?string $catatan = null,
        ?int $actorUserId = null
    ): void {
        $riwayat->loadMissing(['aset', 'user']);

        $judul = 'Tracking Kerusakan';
        $isi = "{$aktivitas}\nAset: {$riwayat->aset?->kode_aset}\nStatus: {$this->kerusakanStatusLabel((string) $riwayat->status)}";
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

    private function resolveNextKerusakanRoles(RiwayatKondisiAset $riwayat): array
    {
        if ($riwayat->status === 'DILAPORKAN') {
            return $riwayat->user && $riwayat->user->hasRole('kepala_sarana')
                ? ['kepala_sekolah']
                : ['kepala_sarana'];
        }

        return [];
    }

    private function resolveKerusakanUrlByRole(string $role, RiwayatKondisiAset $riwayat): ?string
    {
        $kodeAset = $riwayat->aset?->kode_aset;

        return match ($role) {
            'admin' => route('admin.kerusakan.create', $kodeAset ? ['kode_aset' => $kodeAset] : []),
            'kepala_sarana' => route('kepala_sarana.kerusakan.index', $kodeAset ? ['q' => $kodeAset] : []),
            // Untuk bendahara, notifikasi kerusakan harus mengarah ke antrean kerja anggaran.
            'bendahara' => route('bendahara.pengajuan.approval'),
            'kepala_sekolah' => route('kepala_sekolah.kerusakan.index', $kodeAset ? ['q' => $kodeAset] : []),
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
            "Jenis: {$this->jenisLabel((string) $pengajuan->jenis_pengajuan)}\n" .
            "Status: {$status}";

        if (trim($catatan) !== '') {
            $isi .= "\nCatatan: {$catatan}";
        }

        $actorId = $actorUserId ?? (auth()->id() ? (int) auth()->id() : null);
        $excludeUserIds = $actorId ? [$actorId] : [];

        $targetRoles = $this->resolveNextPengajuanRoles($pengajuan);
        foreach ($targetRoles as $role) {
            $this->notifyRole(
                $role,
                $judul,
                $isi,
                $this->resolvePengajuanUrlByRole($role, $pengajuan),
                $excludeUserIds
            );
        }

        if ($targetRoles === []) {
            $this->notifyUser(
                $pengajuan->user,
                $judul,
                $isi,
                $this->resolvePengajuanUrlForUser($pengajuan->user, $pengajuan),
                $excludeUserIds
            );
        }
    }

    private function resolveNextPengajuanRoles(Pengajuan $pengajuan): array
    {
        return match ((string) $pengajuan->status_pengajuan) {
            Pengajuan::STATUS_DIAJUKAN => ['kepala_sarana'],
            Pengajuan::STATUS_DISETUJUI_KASARANA => ['bendahara'],
            Pengajuan::STATUS_DISETUJUI_BENDAHARA => ['kepala_sekolah'],
            Pengajuan::STATUS_DISETUJUI_KEPSEK, Pengajuan::STATUS_DIPROSES => ['admin'],
            Pengajuan::STATUS_MENUNGGU_VERIFIKASI_TEKNIS => ['kepala_sarana'],
            Pengajuan::STATUS_MENUNGGU_VERIFIKASI_KEUANGAN => ['bendahara'],
            default => [],
        };
    }

    private function resolvePengajuanUrlByRole(string $role, Pengajuan $pengajuan): ?string
    {
        if ($role === 'kepala_sekolah' && $pengajuan->status_pengajuan === Pengajuan::STATUS_DISETUJUI_BENDAHARA) {
            return route('kepala_sekolah.pengajuan.index');
        }

        return match ($role) {
            'admin' => route('admin.pengajuan.show', $pengajuan),
            'kepala_sarana' => route('kepala_sarana.pengajuan.show', $pengajuan),
            'bendahara' => route('bendahara.pengajuan.approval'),
            'kepala_sekolah' => route('kepala_sekolah.pengajuan.show', $pengajuan),
            default => null,
        };
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

    private function cleanupKerusakanTrackingNotifications(string $kodeAset): void
    {
        if (trim($kodeAset) === '') {
            return;
        }

        Notifikasi::query()
            ->where('judul', 'like', '%Tracking Kerusakan%')
            ->where('isi', 'like', "%{$kodeAset}%")
            ->delete();
    }

    private function notifyRole(string $role, string $judul, string $isi, ?string $url = null, array $excludeUserIds = []): void
    {
        $userIds = User::query()
            ->where(function ($query) use ($role) {
                $query->whereHas('roleRelation', fn ($roleQuery) => $roleQuery->where('nama_role', $role))
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

            if (preg_match('/AST-[A-Z0-9-]+/i', $isi, $matches) === 1) {
                return ['type' => 'pengajuan', 'key' => strtoupper((string) $matches[0])];
            }

            return null;
        }

        if (str_contains($judul, 'Tracking Kerusakan')) {
            if (preg_match('/Aset:\\s*([A-Z0-9-]+)/i', $isi, $matches) === 1) {
                return ['type' => 'kerusakan', 'key' => strtoupper((string) $matches[1])];
            }

            if (preg_match('/AST-[A-Z0-9-]+/i', $isi, $matches) === 1) {
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
