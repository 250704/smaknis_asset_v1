<?php

namespace App\Http\Controllers;

use App\Models\Sarana;
use App\Models\ApprovalPengajuan;
use App\Models\DetailPengadaan;
use App\Models\KategoriSarana;
use App\Models\Notifikasi;
use App\Models\Pengajuan;
use App\Models\Penggantian;
use App\Models\Perawatan;
use App\Models\RiwayatKondisiSarana;
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
    private const JENIS_LIST = ['PERAWATAN', 'PENGGANTIAN', 'PENGADAAN', 'MUTASI', 'KERUSAKAN'];

    private const STATUS_LIST = [
        Pengajuan::STATUS_DIAJUKAN,
        Pengajuan::STATUS_DISETUJUI_KASARANA,
        Pengajuan::STATUS_DISETUJUI_BENDAHARA,
        Pengajuan::STATUS_DISETUJUI_KEPSEK,
        Pengajuan::STATUS_DITOLAK,
        Pengajuan::STATUS_DIPROSES,
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

    public function guruCreate(Request $request): RedirectResponse
    {
        return redirect()->route('guru.kerusakan.create')
            ->with('info', 'Pengajuan pengadaan barang baru mandiri hanya dapat dilakukan oleh Kepala Sarana, Admin, Bendahara, dan Kepala Sekolah. Silakan laporkan kerusakan atau usulkan mutasi sarana.');
    }

    public function guruStore(Request $request): RedirectResponse
    {
        return redirect()->route('guru.kerusakan.create')
            ->with('info', 'Pengajuan pengadaan barang baru mandiri hanya dapat dilakukan oleh Kepala Sarana, Admin, Bendahara, dan Kepala Sekolah. Silakan laporkan kerusakan atau usulkan mutasi sarana.');
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
        $kodeSarana = trim((string) $request->query('kode_sarana', ''));

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
            'kodeSarana' => $kodeSarana,
            'kategoriList' => KategoriSarana::query()->orderBy('nama_kategori')->get(['*']),
            'ruanganList' => Ruangan::query()->with('gedung')->orderBy('nama_ruangan')->get(),
            'storeRoute' => route($storeRouteMap[$role] ?? 'guru.pengajuan.store'),
            'indexRoute' => route($indexRouteMap[$role] ?? 'guru.pengajuan.index'),
            'scanRoute' => route($scanRouteMap[$role] ?? 'guru.scan'),
        ]);
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
        $request->merge([
            'jenis_pengajuan' => $request->input('jenis_pengajuan', 'PENGADAAN'),
        ]);

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
            'items.*.nama_sarana_rencana' => ['required', 'string', 'max:200'],
            'items.*.kategori_id' => ['required', 'integer', 'exists:kategori_sarana,id'],
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

        $isKasarana = $request->user()->hasRole('kepala_sarana');
        $initialStatus = $isKasarana ? Pengajuan::STATUS_DISETUJUI_KASARANA : Pengajuan::STATUS_DIAJUKAN;

        $pengajuan = DB::transaction(function () use ($request, $base, $jenis, $items, $estimasi, $lampiranPaths, $isKasarana, $initialStatus) {
            $pengajuan = Pengajuan::query()->create([
                'sarana_id' => null,
                'user_id' => $request->user()->id,
                'judul_pengajuan' => $base['judul_pengajuan'],
                'jenis_pengajuan' => $jenis,
                'deskripsi' => $base['deskripsi'],
                'estimasi_biaya' => $estimasi,
                'target_realisasi' => $base['target_realisasi'] ?? null,
                'status_pengajuan' => $initialStatus,
                'lampiran' => $lampiranPaths !== [] ? $lampiranPaths : null,
            ]);

            if ($isKasarana) {
                ApprovalPengajuan::query()->create([
                    'pengajuan_id' => $pengajuan->id,
                    'approver_id' => $request->user()->id,
                    'role_approval' => ApprovalPengajuan::ROLE_KASARANA,
                    'status' => ApprovalPengajuan::STATUS_DISETUJUI,
                    'catatan' => 'Auto-approved (Dibuat oleh Kepala Sarana)',
                    'approved_at' => now(),
                ]);
            }

            if ($jenis === 'PENGADAAN') {
                foreach ($items as $item) {
                    DetailPengadaan::query()->create([
                        'pengajuan_id' => $pengajuan->id,
                        'nama_sarana_rencana' => $item['nama_sarana_rencana'],
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

        $trackingMsg = $isKasarana
            ? 'Status saat ini: Menunggu approval Bendahara.'
            : 'Status saat ini: Menunggu approval Kepala Sarana.';

        $this->broadcastPengajuanTracking(
            $pengajuan,
            "Pengajuan dibuat oleh {$request->user()->display_name}.",
            $trackingMsg
        );

        return redirect()
            ->route($redirectRoute)
            ->with('success', 'Pengajuan berhasil dikirim. Menunggu verifikasi Kepala Sarana.');
    }

    public function guruIndex(Request $request): View
    {
        $filters = $this->buildFilters($request);
        $userId = $request->user()->id;

        $merged = $this->fetchMergedList($filters, 'guru', null, $userId);
        $pengajuan = $merged['paginated'];

        return view('shared.pengajuan.index', [
            'title' => 'Pengajuan Saya',
            'subtitle' => 'Pantau status pengajuan dan laporan kerusakan kamu.',
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
        $userId = $request->user()->id;
        $role = (string) ($request->user()->role_code ?? 'guru');

        $merged = $this->fetchMergedList($filters, $role, null, $userId);
        $pengajuan = $merged['paginated'];

        return view('shared.pengajuan.index', [
            'title' => 'Pengajuan Saya',
            'subtitle' => 'Daftar pengajuan dan laporan kerusakan kamu.',
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
        $user = $request->user();
        $roleName = $user?->role ?? 'admin';
        $detailRoute = match ($roleName) {
            'kepala_sekolah' => 'kepala_sekolah.pengajuan.show',
            'kepala_sarana' => 'kepala_sarana.pengajuan.show',
            'bendahara' => 'bendahara.pengajuan.show',
            default => 'admin.pengajuan.show',
        };

        $merged = $this->fetchMergedList($filters, $roleName, 'all');
        $pengajuan = $merged['paginated'];

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
            'detailRoute' => $detailRoute,
            'role' => $roleName,
            'laporanKerusakanCount' => 0,
            'pengajuanCount' => $merged['total'],
        ]);
    }

    public function adminRealisasiIndex(Request $request): View
    {
        $filters = $this->buildFilters($request);
        $allowedStatus = [
            Pengajuan::STATUS_DISETUJUI_KEPSEK,
            Pengajuan::STATUS_DIPROSES,
        ];

        $pengajuan = Pengajuan::query()
            ->with(['sarana', 'user'])
            ->whereIn('status_pengajuan', $allowedStatus)
            ->when($filters['status'], fn($query, $status) => $query->where('status_pengajuan', $status))
            ->when($filters['jenis'], fn($query, $jenis) => $query->where('jenis_pengajuan', $jenis))
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($subQuery) use ($filters) {
                    $subQuery->where('judul_pengajuan', 'like', "%{$filters['q']}%")
                        ->orWhereHas('sarana', function ($saranaQuery) use ($filters) {
                            $saranaQuery->where('kode_sarana', 'like', "%{$filters['q']}%")
                                ->orWhere('nama_sarana', 'like', "%{$filters['q']}%");
                        })
                        ->orWhereHas('user', fn($userQuery) => $userQuery->where('nama', 'like', "%{$filters['q']}%"));
                });
            })
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
        $filters = $this->buildFilters($request, $defaultFilters);

        $titles = [
            'kepala_sarana' => $mode === 'all' ? 'Semua Pengajuan' : 'Approval & Validasi',
            'bendahara' => $mode === 'all' ? 'Semua Pengajuan' : 'Approval Anggaran',
            'kepala_sekolah' => 'Approval Final',
        ];

        $subtitles = [
            'kepala_sarana' => $mode === 'all'
                ? 'Monitoring seluruh pengajuan dan laporan kerusakan.'
                : 'Pusat approval pengajuan dan validasi laporan kerusakan sarana.',
            'bendahara' => $mode === 'all'
                ? 'Daftar seluruh pengajuan dan laporan kerusakan lintas status.'
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

        // Fetch merged list
        $merged = $this->fetchMergedList($filters, $role, $mode);
        $pengajuan = $merged['paginated'];

        return view('shared.pengajuan.index', [
            'title' => $titles[$role] ?? 'Review Pengajuan',
            'subtitle' => $subtitles[$role] ?? 'Review pengajuan sesuai peran.',
            'role' => $role,
            'pengajuan' => $pengajuan,
            'laporanKerusakan' => null,
            'laporanKerusakanCount' => 0,
            'pengajuanCount' => $merged['total'],
            'filters' => $filters,
            'statusList' => self::STATUS_LIST,
            'jenisList' => self::JENIS_LIST,
            'canApprove' => $mode === 'approval',
            'approveRoute' => $approveRoute,
            'rejectRoute' => $rejectRoute,
            'showUser' => true,
            'detailRoute' => $detailRoute,
            'showFilters' => true,
        ]);
    }

    private function fetchMergedList(array $filters, ?string $role = null, ?string $mode = null, ?int $onlyUserId = null): array
    {
        $isApprovalMode = $mode === 'approval';

        // 1. Fetch Laporan Kerusakan
        $laporanQuery = RiwayatKondisiSarana::query()
            ->with(['sarana.ruangan.gedung', 'user']);

        if ($onlyUserId !== null) {
            $laporanQuery->where('user_id', $onlyUserId);
        } elseif ($isApprovalMode) {
            if ($role === 'kepala_sarana') {
                $laporanQuery->where('status', 'DILAPORKAN');
            } else {
                $laporanQuery->whereRaw('1 = 0');
            }
        } else {
            $laporanQuery->whereNotIn('status', ['DIVALIDASI', 'DITINDAKLANJUTI', 'SELESAI']);
        }

        if ($filters['status']) {
            if ($filters['status'] === Pengajuan::STATUS_DIAJUKAN) {
                $laporanQuery->where('status', 'DILAPORKAN');
            } elseif ($filters['status'] === 'SELESAI') {
                $laporanQuery->where('status', 'SELESAI');
            } elseif ($filters['status'] === 'DITOLAK') {
                $laporanQuery->where('status', 'DITOLAK');
            } else {
                $laporanQuery->whereRaw('1 = 0');
            }
        }

        if ($filters['q'] !== '') {
            $q = $filters['q'];
            $laporanQuery->where(function ($sub) use ($q) {
                $sub->whereHas('sarana', function ($saranaQuery) use ($q) {
                    $saranaQuery->where('kode_sarana', 'like', "%{$q}%")
                        ->orWhere('nama_sarana', 'like', "%{$q}%");
                })->orWhere('deskripsi', 'like', "%{$q}%");
            });
        }

        if (empty($filters['jenis']) || $filters['jenis'] === 'KERUSAKAN') {
            $laporanList = $laporanQuery->get()->map(function ($item) {
                $item->tipe_item = 'laporan_kerusakan';
                return $item;
            });
        } else {
            $laporanList = collect();
        }

        // 2. Fetch Pengajuan
        $pengajuanQuery = Pengajuan::query()
            ->with(['sarana.ruangan.gedung', 'user']);

        if ($onlyUserId !== null) {
            $pengajuanQuery->where(function ($query) use ($onlyUserId) {
                $query->where('user_id', $onlyUserId)
                    ->orWhere(function ($sub) use ($onlyUserId) {
                        $sub->whereIn('jenis_pengajuan', ['PERAWATAN', 'PENGGANTIAN'])
                            ->whereIn('sarana_id', function ($saranaSub) use ($onlyUserId) {
                                $saranaSub->select('sarana_id')
                                    ->from('riwayat_kondisi_sarana')
                                    ->where('user_id', $onlyUserId)
                                    ->whereNotNull('sarana_id');
                            });
                    });
            });
        } elseif ($isApprovalMode) {
            if ($role === 'kepala_sarana') {
                $pengajuanQuery->where('status_pengajuan', Pengajuan::STATUS_DIAJUKAN)
                    ->whereDoesntHave('approvalPengajuan', function ($approvalQuery) {
                        $approvalQuery->where('role_approval', ApprovalPengajuan::ROLE_KASARANA);
                    });
            } elseif ($role === 'bendahara') {
                $pengajuanQuery->where('status_pengajuan', Pengajuan::STATUS_DISETUJUI_KASARANA)
                    ->whereDoesntHave('approvalPengajuan', function ($approvalQuery) {
                        $approvalQuery->where('role_approval', ApprovalPengajuan::ROLE_BENDAHARA);
                    });
            } elseif ($role === 'kepala_sekolah') {
                $pengajuanQuery->where('status_pengajuan', Pengajuan::STATUS_DISETUJUI_BENDAHARA)
                    ->whereDoesntHave('approvalPengajuan', function ($approvalQuery) {
                        $approvalQuery->where('role_approval', ApprovalPengajuan::ROLE_KEPSEK);
                    });
            }
        }

        if ($filters['status']) {
            $pengajuanQuery->where('status_pengajuan', $filters['status']);
        }

        if ($filters['jenis'] && !in_array($filters['jenis'], ['KERUSAKAN', 'MUTASI'], true)) {
            $pengajuanQuery->where('jenis_pengajuan', $filters['jenis']);
        } elseif (in_array($filters['jenis'], ['KERUSAKAN', 'MUTASI'], true)) {
            $pengajuanQuery->whereRaw('1 = 0');
        }

        if ($filters['q'] !== '') {
            $pengajuanQuery->where(function ($subQuery) use ($filters) {
                $subQuery->where('judul_pengajuan', 'like', "%{$filters['q']}%")
                    ->orWhereHas('sarana', function ($saranaQuery) use ($filters) {
                        $saranaQuery->where('kode_sarana', 'like', "%{$filters['q']}%")
                            ->orWhere('nama_sarana', 'like', "%{$filters['q']}%");
                    })
                    ->orWhereHas('user', fn($userQuery) => $userQuery->where('nama', 'like', "%{$filters['q']}%"));
            });
        }

        $pengajuanList = $pengajuanQuery->get()->map(function ($item) {
            $item->tipe_item = 'pengajuan';
            return $item;
        });

        // 3. Fetch Mutasi Sarana
        $mutasiQuery = \App\Models\MutasiSarana::query()
            ->with(['sarana.ruangan.gedung', 'userPengaju', 'ruanganAsal', 'ruanganTujuan']);

        if ($onlyUserId !== null) {
            $mutasiQuery->where('user_pengaju_id', $onlyUserId);
        } elseif ($isApprovalMode) {
            if ($role === 'kepala_sarana') {
                $mutasiQuery->where('status_mutasi', 'DIAJUKAN');
            } else {
                $mutasiQuery->whereRaw('1 = 0');
            }
        }

        if ($filters['status']) {
            if ($filters['status'] === Pengajuan::STATUS_DIAJUKAN) {
                $mutasiQuery->where('status_mutasi', 'DIAJUKAN');
            } elseif ($filters['status'] === 'SELESAI') {
                $mutasiQuery->where('status_mutasi', 'DISETUJUI');
            } elseif ($filters['status'] === 'DITOLAK') {
                $mutasiQuery->where('status_mutasi', 'DITOLAK');
            } else {
                $mutasiQuery->whereRaw('1 = 0');
            }
        }

        if (empty($filters['jenis']) || $filters['jenis'] === 'MUTASI') {
            if ($filters['q'] !== '') {
                $q = $filters['q'];
                $mutasiQuery->where(function ($sub) use ($q) {
                    $sub->whereHas('sarana', function ($saranaQuery) use ($q) {
                        $saranaQuery->where('kode_sarana', 'like', "%{$q}%")
                            ->orWhere('nama_sarana', 'like', "%{$q}%");
                    })->orWhere('keterangan', 'like', "%{$q}%");
                });
            }

            $mutasiList = $mutasiQuery->get()->map(function ($item) {
                $item->tipe_item = 'mutasi_sarana';
                $item->jenis_pengajuan = 'MUTASI';
                $item->judul_pengajuan = 'Mutasi: ' . ($item->sarana?->nama_sarana ?? 'Sarana') . ' ke Ruang ' . ($item->ruanganTujuan?->nama_ruangan ?? '-');
                $item->status_pengajuan = $item->status_mutasi;
                $item->estimasi_biaya = 0;
                $item->user = $item->userPengaju;
                return $item;
            });
        } else {
            $mutasiList = collect();
        }

        // 4. Merge and Sort (Latest first)
        $mergedCollection = $laporanList->concat($pengajuanList)->concat($mutasiList);
        $sortedCollection = $mergedCollection->sortByDesc('created_at');

        // 5. Manual Pagination
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = 12;
        $currentItems = $sortedCollection->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedItems = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $sortedCollection->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
        );
        $paginatedItems->withQueryString();

        return [
            'paginated' => $paginatedItems,
            'total' => $sortedCollection->count(),
        ];
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
            // Allow guru to view pengajuan linked to their kerusakan report
            $isKerusakanReporter = $pengajuan->sarana_id
                ? RiwayatKondisiSarana::query()
                ->where('sarana_id', $pengajuan->sarana_id)
                ->where('user_id', $user->id)
                ->exists()
                : false;

            if (!$isKerusakanReporter) {
                abort(403);
            }
        }

        $pengajuan->load([
            'sarana.ruangan.gedung',
            'user',
            'detailPengadaan.kategori',
            'detailPengadaan.ruangan.gedung',
            'approvalPengajuan.approver',
            'perawatan',
            'penggantian.saranaLama',
            'penggantian.saranaBaru',
        ]);

        $latestKerusakan = null;
        if ($pengajuan->sarana_id) {
            $latestKerusakan = RiwayatKondisiSarana::query()
                ->where('sarana_id', $pengajuan->sarana_id)
                ->latest('id')
                ->first();
        }

        return view('shared.pengajuan.show', [
            'pengajuan' => $pengajuan,
            'isRealisasiPage' => false,
            'backRoute' => null,
            'latestKerusakan' => $latestKerusakan,
        ]);
    }

    public function adminRealisasiShow(Request $request, Pengajuan $pengajuan): View
    {
        $user = $request->user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403);
        }

        $pengajuan->load([
            'sarana.ruangan.gedung',
            'user',
            'detailPengadaan.kategori',
            'detailPengadaan.ruangan.gedung',
            'approvalPengajuan.approver',
            'perawatan',
            'penggantian.saranaLama',
            'penggantian.saranaBaru',
        ]);

        $latestKerusakan = null;
        if ($pengajuan->sarana_id) {
            $latestKerusakan = RiwayatKondisiSarana::query()
                ->where('sarana_id', $pengajuan->sarana_id)
                ->latest('id')
                ->first();
        }

        return view('shared.pengajuan.show', [
            'pengajuan' => $pengajuan,
            'isRealisasiPage' => true,
            'backRoute' => route('admin.realisasi.index'),
            'latestKerusakan' => $latestKerusakan,
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

            // Update kondisi sarana kembali ke BAIK
            if ($pengajuan->sarana_id) {
                $pengajuan->sarana->update([
                    'kondisi_terkini' => 'BAIK',
                ]);
            }

            // Sync riwayat kerusakan
            $this->syncRiwayatKerusakanStatus($pengajuan, 'SELESAI');
        });

        // $this->cleanupPerawatanMedia($pengajuan->perawatan()->first());
        // $this->cleanupPengajuanLampiran($pengajuan);

        $this->broadcastPengajuanTracking(
            $pengajuan,
            "Realisasi perawatan diselesaikan oleh {$request->user()->display_name}.",
            "Biaya realisasi: Rp " . number_format((float) $validated['biaya_realisasi'], 0, ',', '.') . '.'
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
            'kode_sarana_baru' => ['nullable', 'string', 'max:50'],
            'foto_sarana_baru' => ['nullable', 'image', 'max:4096'],
            'foto_bukti' => ['nullable', 'image', 'max:4096'],
        ];

        $validated = $request->validate($rules);

        $saranaLamaId = $pengajuan->sarana_id;
        if (!$saranaLamaId) {
            return redirect()
                ->back()
                ->with('error', 'Penggantian membutuhkan sarana lama yang terkait.');
        }

        $saranaBaruId = null;
        if (!empty($validated['kode_sarana_baru'])) {
            $saranaBaru = Sarana::query()->where('kode_sarana', $validated['kode_sarana_baru'])->first();
            if (!$saranaBaru) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['kode_sarana_baru' => 'Kode sarana baru tidak ditemukan.']);
            }
            $saranaBaruId = $saranaBaru->id;
        }

        $payload = [
            'pengajuan_id' => $pengajuan->id,
            'sarana_lama_id' => $saranaLamaId,
            'sarana_baru_id' => $saranaBaruId,
            'tanggal_penggantian' => $validated['tanggal_penggantian'],
            'biaya_realisasi' => $validated['biaya_realisasi'],
            'keterangan' => $validated['keterangan'],
            'nama_teknisi' => $validated['nama_teknisi'] ?? null,
            'kontak_teknisi' => $validated['kontak_teknisi'] ?? null,
            'nama_vendor' => $validated['nama_vendor'],
            'kontak_vendor' => $validated['kontak_vendor'] ?? null,
        ];

        if ($request->hasFile('foto_sarana_baru')) {
            if ($existing?->foto_sarana_baru) {
                Storage::disk('public')->delete($existing->foto_sarana_baru);
            }
            $payload['foto_sarana_baru'] = $this->storeMediaFile($request->file('foto_sarana_baru'), 'penggantian', 'public');
        }
        if ($request->hasFile('foto_bukti')) {
            if ($existing?->foto_bukti) {
                Storage::disk('public')->delete($existing->foto_bukti);
            }
            $payload['foto_bukti'] = $this->storeMediaFile($request->file('foto_bukti'), 'penggantian/bukti', 'public');
        }

        DB::transaction(function () use ($pengajuan, $payload, $saranaBaruId) {
            // Simpan realisasi penggantian
            $pengajuan->penggantian()->updateOrCreate(
                ['pengajuan_id' => $pengajuan->id],
                $payload
            );

            // === PERUBAHAN: Status langsung SELESAI ===
            $pengajuan->update(['status_pengajuan' => Pengajuan::STATUS_SELESAI]);

            // Update kondisi sarana lama
            if ($pengajuan->sarana_id) {
                $pengajuan->sarana->update([
                    'kondisi_terkini' => 'BAIK',
                ]);
            }

            // Jika ada sarana baru, update kondisinya
            if ($saranaBaruId) {
                Sarana::query()->where('id', $saranaBaruId)->update([
                    'kondisi_terkini' => 'BAIK',
                ]);
            }

            // Sync riwayat kerusakan
            $this->syncRiwayatKerusakanStatus($pengajuan, 'SELESAI');
        });

        // $this->cleanupPenggantianMedia($pengajuan->penggantian()->first());
        // $this->cleanupPengajuanLampiran($pengajuan);

        $extra = "Biaya realisasi: Rp " . number_format((float) $validated['biaya_realisasi'], 0, ',', '.') . '.';
        if ($saranaBaruId && !empty($validated['kode_sarana_baru'])) {
            $extra .= ' Sarana baru: ' . $validated['kode_sarana_baru'] . '.';
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
            $nama = trim((string) ($item['nama_sarana_rencana'] ?? ''));
            $kategori = (int) ($item['kategori_id'] ?? 0);
            $ruangan = (int) ($item['ruangan_id'] ?? 0);
            $jumlah = (int) ($item['jumlah'] ?? 0);

            if ($nama === '' || $kategori === 0 || $ruangan === 0 || $jumlah <= 0) {
                continue;
            }

            $clean[] = [
                'nama_sarana_rencana' => $nama,
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


    private function syncRiwayatKerusakanStatus(Pengajuan $pengajuan, string $targetStatus): void
    {
        if (!$pengajuan->sarana_id) {
            return;
        }

        $riwayat = RiwayatKondisiSarana::query()
            ->where('sarana_id', $pengajuan->sarana_id)
            ->whereIn('status', ['DILAPORKAN', 'DIVALIDASI', 'DITINDAKLANJUTI', 'SELESAI'], 'and', false)
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
    ): void {
        $status = $this->statusLabel((string) $pengajuan->status_pengajuan);
        $judul = 'Tracking Pengajuan';
        $isi = "{$aktivitas}\nJudul: {$pengajuan->judul_pengajuan}\nJenis: {$this->jenisLabel((string)$pengajuan->jenis_pengajuan)}\nStatus: {$status}";

        if ($catatan !== null && trim($catatan) !== '') {
            $isi .= "\n{$catatan}";
        }

        $actorId = $actorUserId ?? (request()->user() ? (int) request()->user()->id : null);

        $this->notifyPengajuanAudience(
            $pengajuan,
            $judul,
            $isi,
            $actorId
        );
    }

    private function notifyPengajuanAudience(
        Pengajuan $pengajuan,
        string $judul,
        string $isi,
        ?int $actorUserId = null
    ): void {
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
            ->get()
            ->push($pengajuan->user)
            ->filter()
            ->unique(function ($user) {
                return (int) $user->id;
            })
            ->values();
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
                        ->whereNull('url', 'and', false)
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

        if (preg_match('/SAR-[A-Z0-9-]+/i', $isi, $matches) === 1) {
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
            $penggantian->foto_sarana_baru,
            $penggantian->foto_bukti,
        ]));

        if ($paths !== []) {
            Storage::disk('public')->delete($paths);
        }

        $penggantian->update([
            'foto_sarana_baru' => '',
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
