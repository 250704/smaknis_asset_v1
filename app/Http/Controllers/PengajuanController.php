<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\ApprovalPengajuan;
use App\Models\DetailPengadaan;
use App\Models\KategoriAset;
use App\Models\Notifikasi;
use App\Models\Pengajuan;
use App\Models\Penggantian;
use App\Models\Perawatan;
use App\Models\RiwayatKondisiAset;
use App\Models\Ruangan;
use App\Models\User;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PengajuanController extends Controller
{
    private const JENIS_LIST = ['PERAWATAN', 'PENGGANTIAN', 'PENGADAAN'];

    private const STATUS_LIST = [
        Pengajuan::STATUS_DIAJUKAN,
        Pengajuan::STATUS_DISETUJUI_KASARANA,
        Pengajuan::STATUS_DISETUJUI_BENDAHARA,
        Pengajuan::STATUS_DISETUJUI_KEPSEK,
        Pengajuan::STATUS_DITOLAK,
        Pengajuan::STATUS_DIPROSES,
        Pengajuan::STATUS_MENUNGGU_VERIFIKASI_TEKNIS,
        Pengajuan::STATUS_MENUNGGU_VERIFIKASI_KEUANGAN,
        Pengajuan::STATUS_SELESAI,
    ];

    private const APPROVAL_FLOW = [
        'kepala_sarana' => [
            'from' => Pengajuan::STATUS_DIAJUKAN,
            'to' => Pengajuan::STATUS_DISETUJUI_KASARANA,
            'role' => ApprovalPengajuan::ROLE_KASARANA,
        ],
        'bendahara' => [
            'from' => Pengajuan::STATUS_DISETUJUI_KASARANA,
            'to' => Pengajuan::STATUS_DISETUJUI_BENDAHARA,
            'role' => ApprovalPengajuan::ROLE_BENDAHARA,
        ],
        'kepala_sekolah' => [
            'from' => Pengajuan::STATUS_DISETUJUI_BENDAHARA,
            'to' => Pengajuan::STATUS_DIPROSES,
            'role' => ApprovalPengajuan::ROLE_KEPSEK,
        ],
    ];

    public function guruCreate(Request $request): View
    {
        return $this->createByRole($request, 'guru');
    }

    public function kepalaSaranaCreate(Request $request): View
    {
        return $this->createByRole($request, 'kepala_sarana');
    }

    public function bendaharaCreate(Request $request): View
    {
        return $this->createByRole($request, 'bendahara');
    }

    public function kepalaSekolahCreate(Request $request): View
    {
        return $this->createByRole($request, 'kepala_sekolah');
    }

    private function createByRole(Request $request, string $role): View
    {
        $kodeAset = trim((string) $request->query('kode_aset', ''));

        $storeRouteMap = [
            'guru' => 'guru.pengajuan.store',
            'admin' => 'admin.pengajuan.store',
            'kepala_sarana' => 'kepala_sarana.pengajuan.store',
            'bendahara' => 'bendahara.pengajuan.store',
            'kepala_sekolah' => 'kepala_sekolah.pengajuan.store',
        ];
        $indexRouteMap = [
            'guru' => 'guru.pengajuan.index',
            'admin' => 'admin.pengajuan.index',
            'kepala_sarana' => 'kepala_sarana.pengajuan.mine',
            'bendahara' => 'bendahara.pengajuan.mine',
            'kepala_sekolah' => 'kepala_sekolah.pengajuan.mine',
        ];
        $scanRouteMap = [
            'guru' => 'guru.scan',
            'admin' => 'admin.scan',
            'kepala_sarana' => 'kepala_sarana.scan',
            'bendahara' => 'bendahara.scan',
            'kepala_sekolah' => 'kepala_sekolah.scan',
        ];

        return view('guru.pengajuan.create', [
            'jenisList' => ['PENGADAAN'],
            'selectedJenis' => 'PENGADAAN',
            'kodeAset' => $kodeAset,
            'kategoriList' => KategoriAset::query()->orderBy('nama_kategori')->get(),
            'ruanganList' => Ruangan::query()->with('gedung')->orderBy('nama_ruangan')->get(),
            'storeRoute' => route($storeRouteMap[$role] ?? 'guru.pengajuan.store'),
            'indexRoute' => route($indexRouteMap[$role] ?? 'guru.pengajuan.index'),
            'scanRoute' => route($scanRouteMap[$role] ?? 'guru.scan'),
        ]);
    }

    public function guruStore(Request $request): RedirectResponse
    {
        return $this->storeManualPengajuan($request, 'guru.pengajuan.index');
    }

    public function kepalaSaranaStore(Request $request): RedirectResponse
    {
        return $this->storeManualPengajuan($request, 'kepala_sarana.pengajuan.mine');
    }

    public function bendaharaStore(Request $request): RedirectResponse
    {
        return $this->storeManualPengajuan($request, 'bendahara.pengajuan.mine');
    }

    public function kepalaSekolahStore(Request $request): RedirectResponse
    {
        return $this->storeManualPengajuan($request, 'kepala_sekolah.pengajuan.mine');
    }

    public function adminCreate(Request $request): View
    {
        return $this->createByRole($request, 'admin');
    }

    public function adminStore(Request $request): RedirectResponse
    {
        return $this->storeManualPengajuan($request, 'admin.pengajuan.index');
    }

    private function storeManualPengajuan(Request $request, string $redirectRoute): RedirectResponse
    {
        $base = $request->validate([
            'judul_pengajuan' => ['required', 'string', 'max:200'],
            'jenis_pengajuan' => ['required', Rule::in(['PENGADAAN'])],
            'deskripsi' => ['required', 'string'],
            'estimasi_biaya' => ['nullable', 'numeric', 'min:0'],
            'target_realisasi' => ['nullable', 'date'],
            'lampiran' => ['nullable', 'array', 'max:5'],
            'lampiran.*' => ['file', 'max:4096', 'mimes:jpg,jpeg,png,pdf,doc,docx'],
        ]);

        $jenis = 'PENGADAAN';
        $items = [];
        $validatedItems = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.nama_aset_rencana' => ['required', 'string', 'max:200'],
            'items.*.kategori_id' => ['required', 'integer', 'exists:kategori_aset,id'],
            'items.*.ruangan_id' => ['required', 'integer', 'exists:ruangan,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
            'items.*.spesifikasi' => ['nullable', 'string', 'max:500'],
            'items.*.estimasi_harga_satuan' => ['nullable', 'numeric', 'min:0'],
        ]);
        $items = $this->sanitizePengadaanItems($validatedItems['items']);
        if ($items === []) {
            return redirect()->back()->withInput()->withErrors(['items' => 'Minimal 1 item pengadaan valid wajib diisi.']);
        }

        $estimasi = $base['estimasi_biaya'] ?? null;
        if ($jenis === 'PENGADAAN' && $estimasi === null) {
            $estimasi = $this->calculateEstimasiPengadaan($items);
        }

        $lampiranPaths = [];
        if ($request->hasFile('lampiran')) {
            foreach ($request->file('lampiran') as $file) {
                if ($file) {
                    $lampiranPaths[] = [
                        'path' => $this->storeMediaFile($file, 'pengajuan', 'public'),
                        'name' => $file->getClientOriginalName(),
                    ];
                }
            }
        }

        $pengajuan = DB::transaction(function () use ($request, $base, $jenis, $items, $estimasi, $lampiranPaths) {
            $pengajuan = Pengajuan::query()->create([
                'aset_id' => null,
                'user_id' => $request->user()->id,
                'judul_pengajuan' => $base['judul_pengajuan'],
                'jenis_pengajuan' => $jenis,
                'deskripsi' => $base['deskripsi'],
                'estimasi_biaya' => $estimasi,
                'target_realisasi' => $base['target_realisasi'] ?? null,
                'status_pengajuan' => Pengajuan::STATUS_DIAJUKAN,
                'lampiran' => $lampiranPaths !== [] ? $lampiranPaths : null,
            ]);

            if ($jenis === 'PENGADAAN') {
                foreach ($items as $item) {
                    DetailPengadaan::query()->create([
                        'pengajuan_id' => $pengajuan->id,
                        'nama_aset_rencana' => $item['nama_aset_rencana'],
                        'kategori_id' => $item['kategori_id'],
                        'ruangan_id' => $item['ruangan_id'],
                        'jumlah' => $item['jumlah'],
                        'spesifikasi' => $item['spesifikasi'] ?? null,
                        'estimasi_harga_satuan' => $item['estimasi_harga_satuan'] ?? null,
                    ]);
                }
            }

            return $pengajuan;
        });

        $this->broadcastPengajuanTracking(
            $pengajuan,
            "Pengajuan dibuat oleh {$request->user()->display_name}.",
            'Status saat ini: Menunggu approval Kepala Sarana.'
        );

        return redirect()
            ->route($redirectRoute)
            ->with('success', 'Pengajuan berhasil dikirim. Menunggu verifikasi Kepala Sarana.');
    }

    public function guruIndex(Request $request): View
    {
        $filters = $this->buildFilters($request);

        $pengajuan = Pengajuan::query()
            ->with(['aset', 'user'])
            ->where('user_id', $request->user()->id)
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where('judul_pengajuan', 'like', "%{$filters['q']}%")
                    ->orWhereHas('aset', function ($asetQuery) use ($filters) {
                        $asetQuery->where('kode_aset', 'like', "%{$filters['q']}%")
                            ->orWhere('nama_aset', 'like', "%{$filters['q']}%");
                    });
            })
            ->when($filters['status'], fn($query, $status) => $query->where('status_pengajuan', $status))
            ->when($filters['jenis'], fn($query, $jenis) => $query->where('jenis_pengajuan', $jenis))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('shared.pengajuan.index', [
            'title' => 'Pengajuan Saya',
            'subtitle' => 'Pantau status pengajuan yang pernah kamu buat.',
            'pengajuan' => $pengajuan,
            'filters' => $filters,
            'statusList' => self::STATUS_LIST,
            'jenisList' => self::JENIS_LIST,
            'canApprove' => false,
            'approveRoute' => null,
            'rejectRoute' => null,
            'showUser' => false,
            'detailRoute' => 'guru.pengajuan.show',
        ]);
    }

    public function kepalaSaranaMineIndex(Request $request): View
    {
        return $this->mineIndexByRole($request, 'kepala_sarana.pengajuan.show');
    }

    public function bendaharaMineIndex(Request $request): View
    {
        return $this->mineIndexByRole($request, 'bendahara.pengajuan.show');
    }

    public function kepalaSekolahMineIndex(Request $request): View
    {
        return $this->mineIndexByRole($request, 'kepala_sekolah.pengajuan.show');
    }

    private function mineIndexByRole(Request $request, string $detailRoute): View
    {
        $filters = $this->buildFilters($request);

        $pengajuan = Pengajuan::query()
            ->with(['aset', 'user'])
            ->where('user_id', $request->user()->id)
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where('judul_pengajuan', 'like', "%{$filters['q']}%")
                    ->orWhereHas('aset', function ($asetQuery) use ($filters) {
                        $asetQuery->where('kode_aset', 'like', "%{$filters['q']}%")
                            ->orWhere('nama_aset', 'like', "%{$filters['q']}%");
                    });
            })
            ->when($filters['status'], fn($query, $status) => $query->where('status_pengajuan', $status))
            ->when($filters['jenis'], fn($query, $jenis) => $query->where('jenis_pengajuan', $jenis))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('shared.pengajuan.index', [
            'title' => 'Pengajuan Saya',
            'subtitle' => 'Daftar pengajuan yang kamu buat sendiri.',
            'pengajuan' => $pengajuan,
            'filters' => $filters,
            'statusList' => self::STATUS_LIST,
            'jenisList' => self::JENIS_LIST,
            'canApprove' => false,
            'approveRoute' => null,
            'rejectRoute' => null,
            'showUser' => false,
            'detailRoute' => $detailRoute,
        ]);
    }

    public function adminIndex(Request $request): View
    {
        $filters = $this->buildFilters($request);

        $pengajuan = Pengajuan::query()
            ->with(['aset', 'user'])
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where('judul_pengajuan', 'like', "%{$filters['q']}%")
                    ->orWhereHas('aset', function ($asetQuery) use ($filters) {
                        $asetQuery->where('kode_aset', 'like', "%{$filters['q']}%")
                            ->orWhere('nama_aset', 'like', "%{$filters['q']}%");
                    })
                    ->orWhereHas('user', fn($userQuery) => $userQuery->where('nama', 'like', "%{$filters['q']}%"));
            })
            ->when($filters['status'], fn($query, $status) => $query->where('status_pengajuan', $status))
            ->when($filters['jenis'], fn($query, $jenis) => $query->where('jenis_pengajuan', $jenis))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('shared.pengajuan.index', [
            'title' => 'Semua Pengajuan',
            'subtitle' => 'Monitoring seluruh pengajuan lintas role.',
            'pengajuan' => $pengajuan,
            'filters' => $filters,
            'statusList' => self::STATUS_LIST,
            'jenisList' => self::JENIS_LIST,
            'canApprove' => false,
            'approveRoute' => null,
            'rejectRoute' => null,
            'showUser' => true,
            'detailRoute' => 'admin.pengajuan.show',
        ]);
    }

    public function adminRealisasiIndex(Request $request): View
    {
        $filters = $this->buildFilters($request);
        $allowedStatus = [
            Pengajuan::STATUS_DISETUJUI_KEPSEK,
            Pengajuan::STATUS_DIPROSES,
            Pengajuan::STATUS_MENUNGGU_VERIFIKASI_TEKNIS,
            Pengajuan::STATUS_MENUNGGU_VERIFIKASI_KEUANGAN,
        ];

        $pengajuan = Pengajuan::query()
            ->with(['aset', 'user'])
            ->whereIn('status_pengajuan', $allowedStatus)
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where('judul_pengajuan', 'like', "%{$filters['q']}%")
                    ->orWhereHas('aset', function ($asetQuery) use ($filters) {
                        $asetQuery->where('kode_aset', 'like', "%{$filters['q']}%")
                            ->orWhere('nama_aset', 'like', "%{$filters['q']}%");
                    })
                    ->orWhereHas('user', fn($userQuery) => $userQuery->where('nama', 'like', "%{$filters['q']}%"));
            })
            ->when($filters['status'], fn($query, $status) => $query->where('status_pengajuan', $status))
            ->when($filters['jenis'], fn($query, $jenis) => $query->where('jenis_pengajuan', $jenis))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('shared.pengajuan.index', [
            'title' => 'Realisasi',
            'subtitle' => 'Daftar pengajuan yang siap atau sedang direalisasikan admin.',
            'pengajuan' => $pengajuan,
            'filters' => $filters,
            'statusList' => self::STATUS_LIST,
            'jenisList' => self::JENIS_LIST,
            'canApprove' => false,
            'approveRoute' => null,
            'rejectRoute' => null,
            'showUser' => true,
            'detailRoute' => 'admin.pengajuan.show',
            'showDualAction' => true,
            'viewRoute' => 'admin.pengajuan.show',
            'realisasiRoute' => 'admin.realisasi.show',
            'showFilters' => true,
        ]);
    }

    public function adminMineIndex(Request $request): View
    {
        return $this->mineIndexByRole($request, 'admin.pengajuan.show');
    }

    public function reviewIndex(Request $request, string $role): View
    {
        $role = trim($role);
        abort_unless(isset(self::APPROVAL_FLOW[$role]), 404);

        $mode = (string) $request->query('mode', $request->route('mode', 'default'));
        $defaultFilters = $this->defaultReviewFilters($role, $mode);
        $isBendaharaApproval = $role === 'bendahara' && $mode === 'approval';
        $isKepalaSaranaApproval = $role === 'kepala_sarana' && $mode === 'approval';
        $isLockedApprovalQueue = $isBendaharaApproval || $isKepalaSaranaApproval;
        $filters = $isLockedApprovalQueue
            ? $defaultFilters
            : $this->buildFilters($request, $defaultFilters);

        $pengajuan = Pengajuan::query()
            ->with(['aset', 'user'])
            ->when($isKepalaSaranaApproval, function ($query) {
                $query->where('status_pengajuan', Pengajuan::STATUS_DIAJUKAN)
                    ->whereDoesntHave('approvalPengajuan', function ($approvalQuery) {
                        $approvalQuery->where('role_approval', ApprovalPengajuan::ROLE_KASARANA);
                    });
            })
            ->when($isBendaharaApproval, function ($query) {
                $query->where('status_pengajuan', Pengajuan::STATUS_DISETUJUI_KASARANA)
                    ->whereDoesntHave('approvalPengajuan', function ($approvalQuery) {
                        $approvalQuery->where('role_approval', ApprovalPengajuan::ROLE_BENDAHARA);
                    });
            })
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where('judul_pengajuan', 'like', "%{$filters['q']}%")
                    ->orWhereHas('aset', function ($asetQuery) use ($filters) {
                        $asetQuery->where('kode_aset', 'like', "%{$filters['q']}%")
                            ->orWhere('nama_aset', 'like', "%{$filters['q']}%");
                    })
                    ->orWhereHas('user', fn($userQuery) => $userQuery->where('nama', 'like', "%{$filters['q']}%"));
            })
            ->when($filters['status'], fn($query, $status) => $query->where('status_pengajuan', $status))
            ->when($filters['jenis'], fn($query, $jenis) => $query->where('jenis_pengajuan', $jenis))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $titles = [
            'kepala_sarana' => $mode === 'validasi' ? 'Validasi Kerusakan' : 'Approval',
            'bendahara' => $mode === 'all' ? 'Semua Review Pengajuan' : 'Approval Anggaran',
            'kepala_sekolah' => 'Approval Final',
        ];

        $subtitles = [
            'kepala_sarana' => $mode === 'validasi'
                ? 'Validasi awal laporan kerusakan sebelum masuk pengajuan.'
                : 'Daftar pengajuan yang menunggu approval kepala sarana.',
            'bendahara' => $mode === 'all'
                ? 'Daftar baca seluruh pengajuan lintas status.'
                : 'Review kelayakan biaya dan approval anggaran.',
            'kepala_sekolah' => 'Persetujuan akhir sebelum realisasi.',
        ];

        $approveRoute = match ($role) {
            'kepala_sarana' => 'kepala_sarana.pengajuan.approve',
            'bendahara' => 'bendahara.pengajuan.approve',
            'kepala_sekolah' => 'kepala_sekolah.pengajuan.approve',
        };

        $rejectRoute = match ($role) {
            'kepala_sarana' => 'kepala_sarana.pengajuan.reject',
            'bendahara' => 'bendahara.pengajuan.reject',
            'kepala_sekolah' => 'kepala_sekolah.pengajuan.reject',
        };

        $detailRoute = match ($role) {
            'kepala_sarana' => 'kepala_sarana.pengajuan.show',
            'bendahara' => 'bendahara.pengajuan.show',
            'kepala_sekolah' => 'kepala_sekolah.pengajuan.show',
        };

        return view('shared.pengajuan.index', [
            'title' => $titles[$role] ?? 'Review Pengajuan',
            'subtitle' => $subtitles[$role] ?? 'Review pengajuan sesuai peran.',
            'pengajuan' => $pengajuan,
            'filters' => $filters,
            'statusList' => self::STATUS_LIST,
            'jenisList' => self::JENIS_LIST,
            'canApprove' => $mode === 'approval',
            'approveRoute' => $approveRoute,
            'rejectRoute' => $rejectRoute,
            'showUser' => true,
            'detailRoute' => $detailRoute,
            'showFilters' => !$isLockedApprovalQueue,
        ]);
    }

    public function approve(Request $request, Pengajuan $pengajuan, string $role): RedirectResponse
    {
        $flow = self::APPROVAL_FLOW[$role] ?? null;
        abort_unless($flow, 404);

        if ((int) $pengajuan->user_id === (int) $request->user()->id) {
            return redirect()
                ->back()
                ->with('error', 'Tidak bisa approval pengajuan milik sendiri.');
        }

        if ($pengajuan->status_pengajuan !== $flow['from']) {
            return redirect()
                ->back()
                ->with('error', 'Status pengajuan tidak sesuai untuk proses approval ini.');
        }

        $validated = $request->validate([
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $pengajuan, $flow, $validated) {
            $pengajuan->update(['status_pengajuan' => $flow['to']]);
            ApprovalPengajuan::query()->create([
                'pengajuan_id' => $pengajuan->id,
                'approver_id' => $request->user()->id,
                'role_approval' => $flow['role'],
                'status' => ApprovalPengajuan::STATUS_DISETUJUI,
                'catatan' => $validated['catatan'] ?? null,
                'approved_at' => now(),
            ]);
        });

        $this->broadcastPengajuanTracking(
            $pengajuan,
            "Disetujui oleh {$request->user()->display_name} ({$this->approvalRoleLabel($flow['role'])})."
        );

        return redirect()
            ->back()
            ->with('success', 'Pengajuan berhasil disetujui.');
    }

    public function reject(Request $request, Pengajuan $pengajuan, string $role): RedirectResponse
    {
        $flow = self::APPROVAL_FLOW[$role] ?? null;
        abort_unless($flow, 404);

        if ((int) $pengajuan->user_id === (int) $request->user()->id) {
            return redirect()
                ->back()
                ->with('error', 'Tidak bisa menolak pengajuan milik sendiri.');
        }

        if ($pengajuan->status_pengajuan !== $flow['from']) {
            return redirect()
                ->back()
                ->with('error', 'Status pengajuan tidak sesuai untuk proses penolakan ini.');
        }

        $validated = $request->validate([
            'catatan' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $pengajuan, $flow, $validated) {
            $pengajuan->update(['status_pengajuan' => Pengajuan::STATUS_DITOLAK]);
            ApprovalPengajuan::query()->create([
                'pengajuan_id' => $pengajuan->id,
                'approver_id' => $request->user()->id,
                'role_approval' => $flow['role'],
                'status' => ApprovalPengajuan::STATUS_DITOLAK,
                'catatan' => $validated['catatan'] ?? null,
                'approved_at' => now(),
            ]);
        });

        $this->broadcastPengajuanTracking(
            $pengajuan,
            "Ditolak oleh {$request->user()->display_name} ({$this->approvalRoleLabel($flow['role'])}).",
            'Catatan: ' . ($validated['catatan'] ?? '-')
        );

        return redirect()
            ->back()
            ->with('warning', 'Pengajuan ditolak.');
    }

    public function show(Request $request, Pengajuan $pengajuan): View
    {
        $user = $request->user();
        if ($user?->hasRole('guru') && $pengajuan->user_id !== $user->id) {
            abort(403);
        }

        $pengajuan->load([
            'aset.ruangan.gedung',
            'user',
            'detailPengadaan.kategori',
            'detailPengadaan.ruangan.gedung',
            'approvalPengajuan.approver',
            'perawatan',
            'penggantian.asetLama',
            'penggantian.asetBaru',
        ]);

        return view('shared.pengajuan.show', [
            'pengajuan' => $pengajuan,
            'isRealisasiPage' => false,
            'backRoute' => null,
        ]);
    }

    public function adminRealisasiShow(Request $request, Pengajuan $pengajuan): View
    {
        $user = $request->user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403);
        }

        $pengajuan->load([
            'aset.ruangan.gedung',
            'user',
            'detailPengadaan.kategori',
            'detailPengadaan.ruangan.gedung',
            'approvalPengajuan.approver',
            'perawatan',
            'penggantian.asetLama',
            'penggantian.asetBaru',
        ]);

        return view('shared.pengajuan.show', [
            'pengajuan' => $pengajuan,
            'isRealisasiPage' => true,
            'backRoute' => route('admin.realisasi.index'),
        ]);
    }

    public function realisasiPerawatan(Request $request, Pengajuan $pengajuan): RedirectResponse
    {
        $user = $request->user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403);
        }

        $existing = $pengajuan->perawatan;

        // Validasi lengkap & ketat
        $rules = [
            'tanggal_perawatan' => ['required', 'date'],
            'biaya_realisasi' => ['required', 'numeric', 'min:0'],
            'keterangan' => ['required', 'string', 'max:1000'],
            'nama_teknisi' => ['required', 'string', 'max:200'],
            'kontak_teknisi' => ['nullable', 'string', 'max:50'],
            'nama_vendor' => ['required', 'string', 'max:200'],
            'kontak_vendor' => ['nullable', 'string', 'max:50'],
            'foto_sesudah' => ['required', 'image', 'max:4096'],
            'foto_bukti' => ['nullable', 'image', 'max:4096'],
        ];

        $validated = $request->validate($rules);

        $payload = [
            'pengajuan_id' => $pengajuan->id,
            'tanggal_perawatan' => $validated['tanggal_perawatan'],
            'biaya_realisasi' => $validated['biaya_realisasi'],
            'keterangan' => $validated['keterangan'],
            'nama_teknisi' => $validated['nama_teknisi'] ?? null,
            'kontak_teknisi' => $validated['kontak_teknisi'] ?? null,
            'nama_vendor' => $validated['nama_vendor'],
            'kontak_vendor' => $validated['kontak_vendor'] ?? null,
        ];

        if ($request->hasFile('foto_sesudah')) {
            if ($existing?->foto_sesudah) {
                Storage::disk('public')->delete($existing->foto_sesudah);
            }
            $payload['foto_sesudah'] = $this->storeMediaFile($request->file('foto_sesudah'), 'perawatan', 'public');
        }
        if ($request->hasFile('foto_bukti')) {
            if ($existing?->foto_bukti) {
                Storage::disk('public')->delete($existing->foto_bukti);
            }
            $payload['foto_bukti'] = $this->storeMediaFile($request->file('foto_bukti'), 'perawatan/bukti', 'public');
        }

        DB::transaction(function () use ($pengajuan, $payload, $request) {
            // Simpan realisasi perawatan
            $pengajuan->perawatan()->updateOrCreate(
                ['pengajuan_id' => $pengajuan->id],
                $payload
            );

            // === PERUBAHAN: Status langsung SELESAI ===
            $pengajuan->update(['status_pengajuan' => Pengajuan::STATUS_SELESAI]);

            // Update kondisi aset kembali ke BAIK
            if ($pengajuan->aset_id) {
                $pengajuan->aset->update([
                    'kondisi_terkini' => 'BAIK',
                ]);
            }

            // Sync riwayat kerusakan
            $this->syncRiwayatKerusakanStatus($pengajuan, 'SELESAI');
        });

        $this->cleanupPerawatanMedia($pengajuan->perawatan()->first());
        $this->cleanupPengajuanLampiran($pengajuan);

        $this->broadcastPengajuanTracking(
            $pengajuan,
            "Realisasi perawatan diselesaikan oleh {$request->user()->display_name}.",
            "Biaya realisasi: Rp " . number_format((float) $validated['biaya_realisasi'], 0, ',','.') . '.'
        );

        return redirect()
            ->back()
            ->with('success', 'Realisasi perawatan berhasil disimpan. Status pengajuan: SELESAI.');
    }

    public function realisasiPenggantian(Request $request, Pengajuan $pengajuan): RedirectResponse
    {
        $user = $request->user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403);
        }

        $existing = $pengajuan->penggantian;

        // Validasi lengkap & ketat
        $rules = [
            'tanggal_penggantian' => ['required', 'date'],
            'biaya_realisasi' => ['required', 'numeric', 'min:0'],
            'keterangan' => ['required', 'string', 'max:1000'],
            'nama_teknisi' => ['required', 'string', 'max:200'],
            'kontak_teknisi' => ['nullable', 'string', 'max:50'],
            'nama_vendor' => ['required', 'string', 'max:200'],
            'kontak_vendor' => ['nullable', 'string', 'max:50'],
            'kode_aset_baru' => ['nullable', 'string', 'max:50'],
            'foto_aset_baru' => ['nullable', 'image', 'max:4096'],
            'foto_bukti' => ['nullable', 'image', 'max:4096'],
        ];

        $validated = $request->validate($rules);

        $asetLamaId = $pengajuan->aset_id;
        if (!$asetLamaId) {
            return redirect()
                ->back()
                ->with('error', 'Penggantian membutuhkan aset lama yang terkait.');
        }

        $asetBaruId = null;
        if (!empty($validated['kode_aset_baru'])) {
            $asetBaru = Aset::query()->where('kode_aset', $validated['kode_aset_baru'])->first();
            if (!$asetBaru) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['kode_aset_baru' => 'Kode aset baru tidak ditemukan.']);
            }
            $asetBaruId = $asetBaru->id;
        }

        $payload = [
            'pengajuan_id' => $pengajuan->id,
            'aset_lama_id' => $asetLamaId,
            'aset_baru_id' => $asetBaruId,
            'tanggal_penggantian' => $validated['tanggal_penggantian'],
            'biaya_realisasi' => $validated['biaya_realisasi'],
            'keterangan' => $validated['keterangan'],
            'nama_teknisi' => $validated['nama_teknisi'] ?? null,
            'kontak_teknisi' => $validated['kontak_teknisi'] ?? null,
            'nama_vendor' => $validated['nama_vendor'],
            'kontak_vendor' => $validated['kontak_vendor'] ?? null,
        ];

        if ($request->hasFile('foto_aset_baru')) {
            if ($existing?->foto_aset_baru) {
                Storage::disk('public')->delete($existing->foto_aset_baru);
            }
            $payload['foto_aset_baru'] = $this->storeMediaFile($request->file('foto_aset_baru'), 'penggantian', 'public');
        }
        if ($request->hasFile('foto_bukti')) {
            if ($existing?->foto_bukti) {
                Storage::disk('public')->delete($existing->foto_bukti);
            }
            $payload['foto_bukti'] = $this->storeMediaFile($request->file('foto_bukti'), 'penggantian/bukti', 'public');
        }

        DB::transaction(function () use ($pengajuan, $payload, $asetBaruId) {
            // Simpan realisasi penggantian
            $pengajuan->penggantian()->updateOrCreate(
                ['pengajuan_id' => $pengajuan->id],
                $payload
            );

            // === PERUBAHAN: Status langsung SELESAI ===
            $pengajuan->update(['status_pengajuan' => Pengajuan::STATUS_SELESAI]);

            // Update kondisi aset lama
            if ($pengajuan->aset_id) {
                $pengajuan->aset->update([
                    'kondisi_terkini' => 'BAIK',
                ]);
            }

            // Jika ada aset baru, update kondisinya
            if ($asetBaruId) {
                Aset::query()->where('id', $asetBaruId)->update([
                    'kondisi_terkini' => 'BAIK',
                ]);
            }

            // Sync riwayat kerusakan
            $this->syncRiwayatKerusakanStatus($pengajuan, 'SELESAI');
        });

        $this->cleanupPenggantianMedia($pengajuan->penggantian()->first());
        $this->cleanupPengajuanLampiran($pengajuan);

        $extra = "Biaya realisasi: Rp " . number_format((float) $validated['biaya_realisasi'], 0, ',','.') . '.';
        if ($asetBaruId && !empty($validated['kode_aset_baru'])) {
            $extra .= ' Aset baru: ' . $validated['kode_aset_baru'] . '.';
        }
        $this->broadcastPengajuanTracking(
            $pengajuan,
            "Realisasi penggantian diselesaikan oleh {$request->user()->display_name}.",
            $extra
        );

        return redirect()
            ->back()
            ->with('success', 'Realisasi penggantian berhasil disimpan. Status pengajuan: SELESAI.');
    }

    public function verifikasiTeknis(Request $request, Pengajuan $pengajuan): RedirectResponse
    {
        $user = $request->user();
        if (!$user || !$user->hasRole('kepala_sarana')) {
            abort(403);
        }

        if ($pengajuan->status_pengajuan !== Pengajuan::STATUS_MENUNGGU_VERIFIKASI_TEKNIS) {
            return redirect()
                ->back()
                ->with('error', 'Pengajuan belum pada tahap verifikasi teknis.');
        }

        $validated = $request->validate([
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $butuhVerifikasiKeuangan = $this->requiresFinanceVerification($pengajuan);
        $nextStatus = $butuhVerifikasiKeuangan
            ? Pengajuan::STATUS_MENUNGGU_VERIFIKASI_KEUANGAN
            : Pengajuan::STATUS_SELESAI;

        DB::transaction(function () use ($request, $pengajuan, $validated, $nextStatus) {
            $pengajuan->update(['status_pengajuan' => $nextStatus]);

            ApprovalPengajuan::query()->create([
                'pengajuan_id' => $pengajuan->id,
                'approver_id' => $request->user()->id,
                'role_approval' => ApprovalPengajuan::ROLE_KASARANA_VERIFIKASI,
                'status' => ApprovalPengajuan::STATUS_DISETUJUI,
                'catatan' => $validated['catatan'] ?? null,
                'approved_at' => now(),
            ]);
        });

        if ($nextStatus === Pengajuan::STATUS_MENUNGGU_VERIFIKASI_KEUANGAN) {
            $this->syncRiwayatKerusakanStatus($pengajuan, 'DITINDAKLANJUTI');
            $this->broadcastPengajuanTracking(
                $pengajuan,
                "Verifikasi teknis oleh {$request->user()->display_name}.",
                'Menunggu verifikasi keuangan.'
            );
        } else {
            // Update kondisi aset kembali ke BAIK jika langsung selesai
            if ($pengajuan->aset_id) {
                $pengajuan->aset->update([
                    'kondisi_terkini' => 'BAIK',
                ]);
            }

            $this->syncRiwayatKerusakanStatus($pengajuan, 'SELESAI');
            $this->cleanupPengajuanLampiran($pengajuan);
            $this->broadcastPengajuanTracking(
                $pengajuan,
                "Verifikasi teknis akhir oleh {$request->user()->display_name}.",
                'Pengajuan selesai.'
            );
        }

        return redirect()
            ->back()
            ->with('success', $nextStatus === Pengajuan::STATUS_MENUNGGU_VERIFIKASI_KEUANGAN
                ? 'Verifikasi teknis berhasil. Menunggu verifikasi keuangan.'
                : 'Verifikasi teknis berhasil. Pengajuan selesai.');
    }

    public function verifikasiKeuangan(Request $request, Pengajuan $pengajuan): RedirectResponse
    {
        $user = $request->user();
        if (!$user || !$user->hasRole('bendahara')) {
            abort(403);
        }

        if ($pengajuan->status_pengajuan !== Pengajuan::STATUS_MENUNGGU_VERIFIKASI_KEUANGAN) {
            return redirect()
                ->back()
                ->with('error', 'Pengajuan belum pada tahap verifikasi keuangan.');
        }

        $validated = $request->validate([
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $pengajuan, $validated) {
            $pengajuan->update(['status_pengajuan' => Pengajuan::STATUS_SELESAI]);
            ApprovalPengajuan::query()->create([
                'pengajuan_id' => $pengajuan->id,
                'approver_id' => $request->user()->id,
                'role_approval' => ApprovalPengajuan::ROLE_BENDAHARA_VERIFIKASI,
                'status' => ApprovalPengajuan::STATUS_DISETUJUI,
                'catatan' => $validated['catatan'] ?? null,
                'approved_at' => now(),
            ]);

            // Update kondisi aset kembali ke BAIK setelah pengajuan selesai
            if ($pengajuan->aset_id) {
                $pengajuan->aset->update([
                    'kondisi_terkini' => 'BAIK',
                ]);
            }
        });

        $this->syncRiwayatKerusakanStatus($pengajuan, 'SELESAI');
        $this->cleanupPengajuanLampiran($pengajuan);

        $this->broadcastPengajuanTracking(
            $pengajuan,
            "Verifikasi keuangan oleh {$request->user()->display_name}.",
            'Pengajuan selesai.'
        );

        return redirect()
            ->back()
            ->with('success', 'Verifikasi keuangan berhasil. Pengajuan selesai.');
    }

    private function buildFilters(Request $request, array $defaults = []): array
    {
        return [
            'q' => trim((string) $request->query('q', $defaults['q'] ?? '')),
            'status' => $request->query('status', $defaults['status'] ?? ''),
            'jenis' => $request->query('jenis', $defaults['jenis'] ?? ''),
        ];
    }

    private function defaultReviewFilters(string $role, string $mode): array
    {
        if ($role === 'bendahara' && $mode === 'all') {
            return ['status' => '', 'jenis' => '', 'q' => ''];
        }

        if ($role === 'kepala_sarana' && $mode === 'approval') {
            return ['status' => Pengajuan::STATUS_DIAJUKAN, 'jenis' => '', 'q' => ''];
        }

        if ($role === 'kepala_sarana' && $mode === 'validasi') {
            return ['status' => Pengajuan::STATUS_DIAJUKAN, 'jenis' => 'PERAWATAN', 'q' => ''];
        }

        if ($role === 'bendahara') {
            return ['status' => Pengajuan::STATUS_DISETUJUI_KASARANA, 'jenis' => '', 'q' => ''];
        }

        if ($role === 'kepala_sekolah') {
            return ['status' => Pengajuan::STATUS_DISETUJUI_BENDAHARA, 'jenis' => '', 'q' => ''];
        }

        return ['status' => '', 'jenis' => '', 'q' => ''];
    }

    private function sanitizePengadaanItems(array $items): array
    {
        $clean = [];
        foreach ($items as $item) {
            $nama = trim((string) ($item['nama_aset_rencana'] ?? ''));
            $kategori = (int) ($item['kategori_id'] ?? 0);
            $ruangan = (int) ($item['ruangan_id'] ?? 0);
            $jumlah = (int) ($item['jumlah'] ?? 0);

            if ($nama === '' || $kategori === 0 || $ruangan === 0 || $jumlah <= 0) {
                continue;
            }

            $clean[] = [
                'nama_aset_rencana' => $nama,
                'kategori_id' => $kategori,
                'ruangan_id' => $ruangan,
                'jumlah' => $jumlah,
                'spesifikasi' => trim((string) ($item['spesifikasi'] ?? '')),
                'estimasi_harga_satuan' => isset($item['estimasi_harga_satuan']) && $item['estimasi_harga_satuan'] !== ''
                    ? (float) $item['estimasi_harga_satuan']
                    : null,
            ];
        }

        return $clean;
    }

    private function calculateEstimasiPengadaan(array $items): ?float
    {
        $total = 0.0;
        foreach ($items as $item) {
            if ($item['estimasi_harga_satuan'] !== null) {
                $total += ((float) $item['estimasi_harga_satuan']) * (int) $item['jumlah'];
            }
        }

        return $total > 0 ? $total : null;
    }

    private function requiresFinanceVerification(Pengajuan $pengajuan): bool
    {
        $pengajuan->loadMissing(['perawatan', 'penggantian']);

        $biaya = 0.0;
        if ($pengajuan->jenis_pengajuan === 'PERAWATAN') {
            $biaya = (float) ($pengajuan->perawatan?->biaya_realisasi ?? 0);
        } elseif ($pengajuan->jenis_pengajuan === 'PENGGANTIAN') {
            $biaya = (float) ($pengajuan->penggantian?->biaya_realisasi ?? 0);
        } elseif ($pengajuan->jenis_pengajuan === 'PENGADAAN') {
            $biaya = (float) ($pengajuan->estimasi_biaya ?? 0);
        }

        return $biaya > 0;
    }

    private function syncRiwayatKerusakanStatus(Pengajuan $pengajuan, string $targetStatus): void
    {
        if (!$pengajuan->aset_id) {
            return;
        }

        $riwayat = RiwayatKondisiAset::query()
            ->where('aset_id', $pengajuan->aset_id)
            ->whereIn('status', ['DILAPORKAN', 'DIVALIDASI', 'DITINDAKLANJUTI', 'SELESAI'])
            ->where(function ($query) use ($pengajuan) {
                if (in_array($pengajuan->jenis_pengajuan, ['PERAWATAN', 'PENGGANTIAN'], true)) {
                    $query->whereNull('rekomendasi_tindakan')
                        ->orWhere('rekomendasi_tindakan', $pengajuan->jenis_pengajuan);
                    return;
                }

                $query->whereNotNull('id');
            })
            ->latest('id')
            ->first();

        if (!$riwayat || $riwayat->status === $targetStatus) {
            return;
        }

        $riwayat->update([
            'status' => $targetStatus,
        ]);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            Pengajuan::STATUS_DIAJUKAN => 'Menunggu Approval Kepala Sarana',
            Pengajuan::STATUS_DISETUJUI_KASARANA => 'Menunggu Approval Bendahara',
            Pengajuan::STATUS_DISETUJUI_BENDAHARA => 'Menunggu Approval Kepala Sekolah',
            Pengajuan::STATUS_DISETUJUI_KEPSEK => 'Disetujui Final',
            Pengajuan::STATUS_DIPROSES => 'Realisasi Diproses',
            Pengajuan::STATUS_MENUNGGU_VERIFIKASI_TEKNIS => 'Menunggu Verifikasi Teknis',
            Pengajuan::STATUS_MENUNGGU_VERIFIKASI_KEUANGAN => 'Menunggu Verifikasi Keuangan',
            Pengajuan::STATUS_DITOLAK => 'Ditolak',
            Pengajuan::STATUS_SELESAI => 'Selesai',
            default => $status,
        };
    }

    private function approvalRoleLabel(string $role): string
    {
        return match ($role) {
            ApprovalPengajuan::ROLE_KASARANA => 'Kepala Sarana',
            ApprovalPengajuan::ROLE_BENDAHARA => 'Bendahara',
            ApprovalPengajuan::ROLE_KEPSEK => 'Kepala Sekolah',
            ApprovalPengajuan::ROLE_KASARANA_VERIFIKASI => 'Verifikasi Teknis Kepala Sarana',
            ApprovalPengajuan::ROLE_BENDAHARA_VERIFIKASI => 'Verifikasi Keuangan Bendahara',
            default => $role,
        };
    }


    private function broadcastPengajuanTracking(
        Pengajuan $pengajuan,
        string $aktivitas,
        ?string $catatan = null,
        ?int $actorUserId = null
    ): void
    {
        $status = $this->statusLabel((string) $pengajuan->status_pengajuan);
        $judul = 'Tracking Pengajuan';
        $isi = "{$aktivitas}\nJudul: {$pengajuan->judul_pengajuan}\nJenis: {$this->jenisLabel((string) $pengajuan->jenis_pengajuan)}\nStatus: {$status}";

        if ($catatan !== null && trim($catatan) !== '') {
            $isi .= "\n{$catatan}";
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
        if ($role === 'bendahara' && $pengajuan->status_pengajuan === Pengajuan::STATUS_DISETUJUI_KASARANA) {
            return route('bendahara.pengajuan.approval');
        }

        if ($role === 'kepala_sekolah' && $pengajuan->status_pengajuan === Pengajuan::STATUS_DISETUJUI_BENDAHARA) {
            return route('kepala_sekolah.pengajuan.index');
        }

        return match ($role) {
            'admin' => $this->resolveAdminPengajuanMenuUrl($pengajuan),
            'kepala_sarana' => route('kepala_sarana.pengajuan.show', $pengajuan),
            'bendahara' => route('bendahara.pengajuan.show', $pengajuan),
            'kepala_sekolah' => route('kepala_sekolah.pengajuan.show', $pengajuan),
            default => null,
        };
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
        $trackingKey = $this->extractTrackingPengajuanKey($judul, $isi);

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
            if ($trackingKey !== null) {
                $existingQuery
                    ->where('judul', 'like', '%Tracking Pengajuan%')
                    ->where('isi', 'like', "%{$trackingKey}%");
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

    private function extractTrackingPengajuanKey(string $judul, string $isi): ?string
    {
        if (!str_contains($judul, 'Tracking Pengajuan')) {
            return null;
        }

        if (preg_match('/^Judul:\\s*(.+)$/m', $isi, $matches) === 1) {
            $value = trim((string) $matches[1]);
            if ($value !== '') {
                return $value;
            }
        }

        if (preg_match('/AST-[A-Z0-9-]+/i', $isi, $matches) === 1) {
            return strtoupper((string) $matches[0]);
        }

        return null;
    }

    private function normalizeNotificationTitle(string $title): string
    {
        $cleanTitle = trim($title);
        if ($cleanTitle === '') {
            return 'Pengajuan | Update';
        }

        // Jika sudah ada format "X | Y", kembalikan apa adanya
        if (str_contains($cleanTitle, '|')) {
            return $cleanTitle;
        }

        // Cek apakah ada emoji di awal - jika ada, kembalikan apa adanya
        $hasEmoji = preg_match('/^[\p{Emoji}]/u', $cleanTitle);
        if ($hasEmoji) {
            return $cleanTitle;
        }

        // Tambahkan prefix berdasarkan tipe
        $lower = mb_strtolower($cleanTitle);

        if (str_contains($lower, 'realisasi')) {
            return "Realisasi | {$cleanTitle}";
        } elseif (str_contains($lower, 'verifikasi')) {
            return "Verifikasi | {$cleanTitle}";
        } elseif (str_contains($lower, 'approval') || str_contains($lower, 'approve')) {
            return "Approval | {$cleanTitle}";
        } elseif (str_contains($lower, 'ditolak') || str_contains($lower, 'tolak')) {
            return "Penolakan | {$cleanTitle}";
        } elseif (str_contains($lower, 'selesai')) {
            return "Selesai | {$cleanTitle}";
        }

        return "Pengajuan | {$cleanTitle}";
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
            return $this->resolveAdminPengajuanMenuUrl($pengajuan);
        }

        if ($user->hasRole('kepala_sarana')) {
            return route('kepala_sarana.pengajuan.show', $pengajuan);
        }

        if ($user->hasRole('bendahara')) {
            if ($pengajuan->status_pengajuan === Pengajuan::STATUS_DISETUJUI_KASARANA) {
                return route('bendahara.pengajuan.approval');
            }
            return route('bendahara.pengajuan.show', $pengajuan);
        }

        if ($user->hasRole('kepala_sekolah')) {
            if ($pengajuan->status_pengajuan === Pengajuan::STATUS_DISETUJUI_BENDAHARA) {
                return route('kepala_sekolah.pengajuan.index');
            }
            return route('kepala_sekolah.pengajuan.show', $pengajuan);
        }

        return null;
    }

    private function resolveAdminPengajuanMenuUrl(Pengajuan $pengajuan): string
    {
        if (in_array($pengajuan->status_pengajuan, [
            Pengajuan::STATUS_DISETUJUI_KEPSEK,
            Pengajuan::STATUS_DIPROSES,
            Pengajuan::STATUS_MENUNGGU_VERIFIKASI_TEKNIS,
            Pengajuan::STATUS_MENUNGGU_VERIFIKASI_KEUANGAN,
        ], true)) {
            return route('admin.realisasi.index');
        }

        return route('admin.pengajuan.index');
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

    private function cleanupPerawatanMedia(?Perawatan $perawatan): void
    {
        if (!$perawatan) {
            return;
        }

        $paths = array_values(array_filter([
            $perawatan->foto_sesudah,
            $perawatan->foto_bukti,
        ]));

        if ($paths !== []) {
            Storage::disk('public')->delete($paths);
        }

        $perawatan->update([
            'foto_sesudah' => '',
            'foto_bukti' => '',
        ]);
    }

    private function cleanupPenggantianMedia(?Penggantian $penggantian): void
    {
        if (!$penggantian) {
            return;
        }

        $paths = array_values(array_filter([
            $penggantian->foto_aset_baru,
            $penggantian->foto_bukti,
        ]));

        if ($paths !== []) {
            Storage::disk('public')->delete($paths);
        }

        $penggantian->update([
            'foto_aset_baru' => '',
            'foto_bukti' => '',
        ]);
    }

    private function cleanupPengajuanLampiran(Pengajuan $pengajuan): void
    {
        $lampiran = $pengajuan->lampiran ?? [];
        if (!is_array($lampiran) || $lampiran === []) {
            return;
        }

        $paths = [];
        foreach ($lampiran as $item) {
            if (is_array($item) && !empty($item['path']) && is_string($item['path'])) {
                $paths[] = $item['path'];
            }
        }

        $this->deleteStoredFiles($paths);

        $pengajuan->update([
            'lampiran' => null,
        ]);
    }
}
