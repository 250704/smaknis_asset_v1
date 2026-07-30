<?php

namespace App\Http\Controllers;

use App\Models\Sarana;
use App\Models\Gedung;
use App\Models\KategoriSarana;
use App\Models\Pengajuan;
use App\Models\Penggantian;
use App\Models\Perawatan;
use App\Models\RiwayatKondisiSarana;
use App\Models\Ruangan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BlueprintPageController extends Controller
{
    protected array $features = [
        'admin' => [
            'scan-qr' => ['Scan QR', 'Pusat aksi sarana melalui QR code.'],
            'data-sarana' => ['Data Sarana', 'Kelola dan monitoring data sarana per unit.'],
            'tambah-sarana' => ['Tambah Sarana', 'Input sarana satuan, ruangan, atau massal.'],
            'mutasi-sarana' => ['Mutasi Sarana', 'Eksekusi dan histori perpindahan sarana.'],
            'cetak-qr' => ['Cetak QR', 'Generate dan cetak QR code sarana.'],
            'semua-pengajuan' => ['Semua Pengajuan', 'Monitoring seluruh pengajuan lintas role.'],
            'realisasi' => ['Realisasi', 'Realisasi perawatan, penggantian, dan pengadaan.'],
            'pelaporan' => ['Pelaporan', 'Laporan inventaris, kerusakan, pengajuan, dan realisasi.'],
            'manajemen-user' => ['Manajemen User', 'Kelola akun pengguna sistem.'],
            'log-aktivitas' => ['Log Aktivitas', 'Audit trail aktivitas user (admin only).'],
        ],
        'guru' => [
            'scan-qr' => ['Scan QR', 'Scan sarana dan buat pengajuan cepat.'],
            'buat-pengajuan' => ['Buat Pengajuan Manual', 'Form pengajuan perawatan/penggantian/pengadaan.'],
            'riwayat-pengajuan' => ['Riwayat Pengajuan', 'Pantau status pengajuan yang pernah dibuat.'],
            'notifikasi' => ['Notifikasi', 'Informasi approval dan status realisasi.'],
        ],
        'kepala_sarana' => [
            'scan-qr' => ['Scan QR', 'Validasi kondisi sarana di lapangan.'],
            'data-sarana' => ['Data Sarana', 'Lihat data sarana aktif dan histori ringkas.'],
            'histori-sarana' => ['Histori Sarana', 'Riwayat kondisi dan mutasi sarana.'],
            'validasi-kerusakan' => ['Validasi Kerusakan', 'Validasi laporan kerusakan teknis (ringan/berat/tidak layak).'],
            'approval-teknis' => ['Approval', 'Approval tahap kepala sarana.'],
            'semua-proses' => ['Semua Proses', 'Ringkasan pengajuan, kerusakan, perawatan, dan penggantian.'],
            'pelaporan' => ['Pelaporan', 'Akses laporan untuk monitoring teknis.'],
            'notifikasi' => ['Notifikasi', 'Informasi pengajuan baru dan tindak lanjut.'],
        ],
        'bendahara' => [
            'scan-qr' => ['Scan QR', 'Lihat sarana terkait pengajuan anggaran.'],
            'semua-review' => ['Semua Review Pengajuan', 'Review pengajuan dari sisi biaya/anggaran.'],
            'approval-anggaran' => ['Approval Anggaran', 'Approval tahap bendahara.'],
            'pelaporan' => ['Pelaporan Rekap', 'Rekap biaya estimasi dan realisasi.'],
            'notifikasi' => ['Notifikasi', 'Informasi approval dan status terbaru.'],
        ],
        'kepala_sekolah' => [
            'scan-qr' => ['Scan QR', 'Lihat detail sarana sebagai referensi keputusan.'],
            'approval-final' => ['Approval Final', 'Persetujuan akhir pengajuan.'],
            'pelaporan' => ['Pelaporan', 'Ringkasan manajerial inventaris dan pengajuan.'],
            'notifikasi' => ['Notifikasi', 'Informasi keputusan dan aktivitas penting.'],
        ],
    ];

    public function show(Request $request, string $role, string $feature): View
    {
        if (!isset($this->features[$role][$feature])) {
            abort(404);
        }

        [$title, $description] = $this->features[$role][$feature];

        $scanContext = null;
        if ($request->query('source') === 'scan-qr') {
            $scanContext = [
                'sarana_id' => (string) $request->query('sarana_id', ''),
                'kode_sarana' => (string) $request->query('kode_sarana', ''),
                'nama_sarana' => (string) $request->query('nama_sarana', ''),
                'aksi' => (string) $request->query('aksi', ''),
            ];
        }

        return view('shared.blueprint-feature', [
            'title' => $title,
            'description' => $description,
            'role' => $role,
            'featureKey' => $feature,
            'scanContext' => $scanContext,
        ]);
    }

    public function laporan(Request $request, string $role): View
    {
        $allowedRoles = ['admin', 'kepala_sarana', 'bendahara', 'kepala_sekolah'];
        abort_unless(in_array($role, $allowedRoles, true), 403);

        if (!isset($this->features[$role]['pelaporan'])) {
            abort(404);
        }

        $payload = $this->buildLaporanPayload($request, $role, false);

        return view('shared.laporan.index', $payload);
    }

    public function laporanExportExcel(Request $request, string $role): BinaryFileResponse
    {
        $allowedRoles = ['admin', 'kepala_sarana', 'bendahara', 'kepala_sekolah'];
        abort_unless(in_array($role, $allowedRoles, true), 403);
        $payload = $this->buildLaporanPayload($request, $role, true);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $this->buildKerusakanSheet($spreadsheet, $payload);
        $this->buildPerawatanSheet($spreadsheet, $payload);
        $this->buildPenggantianSheet($spreadsheet, $payload);
        $this->buildPengajuanSheet($spreadsheet, $payload);
        $this->buildSummarySheet($spreadsheet, $payload);

        $spreadsheet->setActiveSheetIndex(0);

        $fileName = 'laporan-sarpras-' . $role . '-' . now()->format('Ymd-His') . '.xlsx';
        $tempPath = tempnam(sys_get_temp_dir(), 'laporan_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    public function laporanExportPdf(Request $request, string $role): View
    {
        $allowedRoles = ['admin', 'kepala_sarana', 'bendahara', 'kepala_sekolah'];
        abort_unless(in_array($role, $allowedRoles, true), 403);
        $payload = $this->buildLaporanPayload($request, $role, true);

        return view('shared.laporan.pdf', $payload);
    }

    private function buildLaporanPayload(Request $request, string $role, bool $withDetails = false): array
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'gedung_id' => (int) $request->query('gedung_id', 0),
            'ruangan_id' => (int) $request->query('ruangan_id', 0),
            'kategori_id' => (int) $request->query('kategori_id', 0),
            'kondisi_terkini' => strtoupper(trim((string) $request->query('kondisi_terkini', ''))),
            'status_sarana' => strtoupper(trim((string) $request->query('status_sarana', ''))),
            'date_from' => (string) $request->query('date_from', now()->copy()->startOfMonth()->toDateString()),
            'date_to' => (string) $request->query('date_to', now()->copy()->endOfMonth()->toDateString()),
        ];

        $saranaQuery = Sarana::query()
            ->with(['kategori', 'ruangan.gedung'])
            ->when($filters['status_sarana'], fn(Builder $q, $status) => $q->where('status_sarana', $status))
            ->when($filters['kategori_id'], fn(Builder $q, $id) => $q->where('kategori_id', $id))
            ->when($filters['ruangan_id'], fn(Builder $q, $id) => $q->where('ruangan_id', $id))
            ->when($filters['kondisi_terkini'], fn(Builder $q, $kondisi) => $q->where('kondisi_terkini', $kondisi))
            ->when($filters['gedung_id'], function (Builder $q, $id) {
                $q->whereHas('ruangan', fn(Builder $r) => $r->where('gedung_id', $id));
            })
            ->when($filters['q'], function (Builder $q, $search) {
                $q->where(function (Builder $inner) use ($search) {
                    $inner->where('kode_sarana', 'like', "%{$search}%")
                        ->orWhere('nama_sarana', 'like', "%{$search}%");
                });
            });

        if (!$withDetails) {
            return [
                'role' => $role,
                'filters' => $filters,
                'gedungList' => Gedung::query()->orderBy('nama_gedung')->get(),
                'ruanganList' => Ruangan::query()
                    ->with('gedung')
                    ->when($filters['gedung_id'] > 0, fn(Builder $q) => $q->where('gedung_id', $filters['gedung_id']))
                    ->orderBy('nama_ruangan')
                    ->get(),
                'kategoriList' => KategoriSarana::query()->orderBy('nama_kategori')->get(),
                'kondisiList' => Sarana::KONDISI_LIST,
                'statusList' => Sarana::STATUS_LIST,
                'sarana' => (clone $saranaQuery)->orderBy('kode_sarana')->paginate(10)->withQueryString(),
            ];
        }

        $today = now();
        $fromDate = $this->parseDateOrDefault((string) $filters['date_from'], $today->copy()->startOfMonth());
        $toDate = $this->parseDateOrDefault((string) $filters['date_to'], $today->copy()->endOfDay());
        if ($toDate->lt($fromDate)) {
            [$fromDate, $toDate] = [$toDate->copy()->startOfDay(), $fromDate->copy()->endOfDay()];
        } else {
            $fromDate = $fromDate->copy()->startOfDay();
            $toDate = $toDate->copy()->endOfDay();
        }

        $pengajuanQuery = Pengajuan::query()
            ->with(['sarana.ruangan.gedung', 'user'])
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->when($filters['kategori_id'] > 0, function (Builder $q) use ($filters) {
                $q->whereHas('sarana', fn(Builder $sarana) => $sarana->where('kategori_id', $filters['kategori_id']));
            })
            ->when($filters['ruangan_id'] > 0, function (Builder $q) use ($filters) {
                $q->whereHas('sarana', fn(Builder $sarana) => $sarana->where('ruangan_id', $filters['ruangan_id']));
            })
            ->when($filters['gedung_id'] > 0, function (Builder $q) use ($filters) {
                $q->whereHas('sarana.ruangan', fn(Builder $ruangan) => $ruangan->where('gedung_id', $filters['gedung_id']));
            })
            ->when($filters['q'] !== '', function (Builder $q) use ($filters) {
                $q->where(function (Builder $inner) use ($filters) {
                    $inner->where('judul_pengajuan', 'like', "%{$filters['q']}%")
                        ->orWhereHas('sarana', function (Builder $sarana) use ($filters) {
                            $sarana->where('kode_sarana', 'like', "%{$filters['q']}%")
                                ->orWhere('nama_sarana', 'like', "%{$filters['q']}%");
                        });
                });
            });

        $kerusakanQuery = RiwayatKondisiSarana::query()
            ->with(['sarana.ruangan.gedung', 'user'])
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->when($filters['kategori_id'] > 0, function (Builder $q) use ($filters) {
                $q->whereHas('sarana', fn(Builder $sarana) => $sarana->where('kategori_id', $filters['kategori_id']));
            })
            ->when($filters['ruangan_id'] > 0, function (Builder $q) use ($filters) {
                $q->whereHas('sarana', fn(Builder $sarana) => $sarana->where('ruangan_id', $filters['ruangan_id']));
            })
            ->when($filters['gedung_id'] > 0, function (Builder $q) use ($filters) {
                $q->whereHas('sarana.ruangan', fn(Builder $ruangan) => $ruangan->where('gedung_id', $filters['gedung_id']));
            })
            ->when($filters['q'] !== '', function (Builder $q) use ($filters) {
                $q->whereHas('sarana', function (Builder $sarana) use ($filters) {
                    $sarana->where('kode_sarana', 'like', "%{$filters['q']}%")
                        ->orWhere('nama_sarana', 'like', "%{$filters['q']}%");
                });
            });

        $summaryCacheKey = 'laporan_summary_v1:' . $role . ':' . md5(json_encode($filters));
        $summary = Cache::remember($summaryCacheKey, now()->addSeconds(45), function () use ($saranaQuery, $pengajuanQuery, $kerusakanQuery, $fromDate, $toDate, $filters) {
            $totalSarana = (clone $saranaQuery)->count();
            $saranaAktif = (clone $saranaQuery)->where('status_sarana', 'AKTIF')->count();
            $saranaNonaktif = (clone $saranaQuery)->where('status_sarana', 'NONAKTIF')->count();
            $saranaRusak = (clone $saranaQuery)->whereIn('kondisi_terkini', ['RINGAN', 'BERAT', 'TIDAK_LAYAK'])->count();

            $statusMenunggu = [
                Pengajuan::STATUS_DIAJUKAN,
                Pengajuan::STATUS_DISETUJUI_KASARANA,
                Pengajuan::STATUS_DISETUJUI_BENDAHARA,
                Pengajuan::STATUS_DISETUJUI_KEPSEK,
                Pengajuan::STATUS_DIPROSES,
            ];

            $totalPengajuan = (clone $pengajuanQuery)->count();
            $pengajuanMenunggu = (clone $pengajuanQuery)->whereIn('status_pengajuan', $statusMenunggu)->count();
            $pengajuanSelesai = (clone $pengajuanQuery)->where('status_pengajuan', Pengajuan::STATUS_SELESAI)->count();
            $pengajuanDitolak = (clone $pengajuanQuery)->where('status_pengajuan', Pengajuan::STATUS_DITOLAK)->count();

            $totalKerusakan = (clone $kerusakanQuery)->count();
            $kerusakanAktif = (clone $kerusakanQuery)->whereIn('status', ['DILAPORKAN', 'DIVALIDASI', 'DITINDAKLANJUTI'])->count();
            $kerusakanSelesai = (clone $kerusakanQuery)->where('status', 'SELESAI')->count();

            $estimasiTotal = (float) ((clone $pengajuanQuery)->sum('estimasi_biaya') ?? 0);

            $realisasiPerawatan = (float) (Perawatan::query()
                ->whereHas('pengajuan', function (Builder $q) use ($fromDate, $toDate, $filters) {
                    $q->whereBetween('created_at', [$fromDate, $toDate]);
                    $this->applyPengajuanSaranaFilter($q, $filters);
                })
                ->sum('biaya_realisasi') ?? 0);

            $realisasiPenggantian = (float) (Penggantian::query()
                ->whereHas('pengajuan', function (Builder $q) use ($fromDate, $toDate, $filters) {
                    $q->whereBetween('created_at', [$fromDate, $toDate]);
                    $this->applyPengajuanSaranaFilter($q, $filters);
                })
                ->sum('biaya_realisasi') ?? 0);

            $totalRealisasi = $realisasiPerawatan + $realisasiPenggantian;
            $selisihAnggaran = $estimasiTotal - $totalRealisasi;

            $trenPengajuan = (clone $pengajuanQuery)
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bulan, COUNT(*) as total")
                ->groupBy('bulan')
                ->orderBy('bulan')
                ->get()
                ->map(fn($row) => ['bulan' => (string) $row->bulan, 'total' => (int) $row->total])
                ->values()
                ->all();

            $trenKerusakan = (clone $kerusakanQuery)
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bulan, COUNT(*) as total")
                ->groupBy('bulan')
                ->orderBy('bulan')
                ->get()
                ->map(fn($row) => ['bulan' => (string) $row->bulan, 'total' => (int) $row->total])
                ->values()
                ->all();

            return [
                'kpi' => [
                    'total_sarana' => $totalSarana,
                    'sarana_aktif' => $saranaAktif,
                    'sarana_nonaktif' => $saranaNonaktif,
                    'sarana_rusak' => $saranaRusak,
                    'total_pengajuan' => $totalPengajuan,
                    'pengajuan_menunggu' => $pengajuanMenunggu,
                    'pengajuan_selesai' => $pengajuanSelesai,
                    'pengajuan_ditolak' => $pengajuanDitolak,
                    'total_kerusakan' => $totalKerusakan,
                    'kerusakan_aktif' => $kerusakanAktif,
                    'kerusakan_selesai' => $kerusakanSelesai,
                ],
                'finance' => [
                    'estimasi_total' => $estimasiTotal,
                    'realisasi_perawatan' => $realisasiPerawatan,
                    'realisasi_penggantian' => $realisasiPenggantian,
                    'total_realisasi' => $totalRealisasi,
                    'selisih_anggaran' => $selisihAnggaran,
                ],
                'tren_pengajuan' => $trenPengajuan,
                'tren_kerusakan' => $trenKerusakan,
            ];
        });

        $trenPengajuan = collect($summary['tren_pengajuan'])->map(fn(array $row) => (object) $row);
        $trenKerusakan = collect($summary['tren_kerusakan'])->map(fn(array $row) => (object) $row);

        $latestPengajuan = (clone $pengajuanQuery)
            ->latest('id')
            ->limit(8)
            ->get();

        $saranaPerluPerhatian = (clone $saranaQuery)
            ->whereIn('kondisi_terkini', ['RINGAN', 'BERAT', 'TIDAK_LAYAK'])
            ->latest('updated_at')
            ->limit(8)
            ->get();

        $laporanKerusakan = collect();
        $laporanPerawatan = collect();
        $laporanPenggantian = collect();
        $laporanPengajuan = collect();

        if ($withDetails) {
            $laporanKerusakan = (clone $kerusakanQuery)
                ->latest('id')
                ->get();

            $laporanPengajuan = (clone $pengajuanQuery)
                ->latest('id')
                ->get();

            $laporanPerawatan = Perawatan::query()
                ->with(['pengajuan.sarana.ruangan.gedung', 'pengajuan.user'])
                ->whereHas('pengajuan', function (Builder $q) use ($fromDate, $toDate, $filters) {
                    $q->whereBetween('created_at', [$fromDate, $toDate]);
                    $this->applyPengajuanSaranaFilter($q, $filters);
                })
                ->latest('id')
                ->get();

            $laporanPenggantian = Penggantian::query()
                ->with(['pengajuan.sarana.ruangan.gedung', 'pengajuan.user', 'saranaLama', 'saranaBaru'])
                ->whereHas('pengajuan', function (Builder $q) use ($fromDate, $toDate, $filters) {
                    $q->whereBetween('created_at', [$fromDate, $toDate]);
                    $this->applyPengajuanSaranaFilter($q, $filters);
                })
                ->latest('id')
                ->get();
        }

        $gedungList = Gedung::query()->orderBy('nama_gedung')->get();
        $ruanganList = Ruangan::query()
            ->with('gedung')
            ->when($filters['gedung_id'] > 0, fn(Builder $q) => $q->where('gedung_id', $filters['gedung_id']))
            ->orderBy('nama_ruangan')
            ->get();
        $kategoriList = KategoriSarana::query()->orderBy('nama_kategori')->get();

        return [
            'role' => $role,
            'filters' => $filters,
            'gedungList' => $gedungList,
            'ruanganList' => $ruanganList,
            'kategoriList' => $kategoriList,
            'kpi' => $summary['kpi'],
            'finance' => $summary['finance'],
            'trenPengajuan' => $trenPengajuan,
            'trenKerusakan' => $trenKerusakan,
            'latestPengajuan' => $latestPengajuan,
            'saranaPerluPerhatian' => $saranaPerluPerhatian,
            'laporanKerusakan' => $laporanKerusakan,
            'laporanPerawatan' => $laporanPerawatan,
            'laporanPenggantian' => $laporanPenggantian,
            'laporanPengajuan' => $laporanPengajuan,
        ];
    }

    private function parseDateOrDefault(string $value, Carbon $fallback): Carbon
    {
        if (trim($value) === '') {
            return $fallback;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return $fallback;
        }
    }

    private function buildKerusakanSheet(Spreadsheet $spreadsheet, array $payload): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('01 Kerusakan');
        $row = $this->initSheetMeta($sheet, 'Laporan Kerusakan', $payload);
        $headers = ['Tanggal', 'Kode Sarana', 'Nama Sarana', 'Lokasi', 'Tingkat', 'Status', 'Pelapor', 'Validator', 'Catatan'];
        $this->writeTableHeader($sheet, $row, $headers);
        $row++;

        foreach ($payload['laporanKerusakan'] as $item) {
            $sheet->fromArray([
                $item->created_at?->format('Y-m-d H:i'),
                $item->sarana?->kode_sarana ?? '-',
                $item->sarana?->nama_sarana ?? '-',
                ($item->sarana?->ruangan?->nama_ruangan ?? '-') . ' - ' . ($item->sarana?->ruangan?->gedung?->nama_gedung ?? '-'),
                $item->tingkat_kerusakan ?? '-',
                $item->status ?? '-',
                $item->user?->display_name ?? '-',
                $item->validator?->display_name ?? '-',
                $item->catatan_validasi ?? '-',
            ], null, "A{$row}");
            $row++;
        }

        $this->finalizeSheet($sheet, 'A', 'I', $row - 1);
    }

    private function buildPerawatanSheet(Spreadsheet $spreadsheet, array $payload): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('02 Perawatan');
        $row = $this->initSheetMeta($sheet, 'Laporan Perawatan', $payload);
        $headers = ['Tanggal', 'Kode Sarana', 'Nama Sarana', 'Lokasi', 'Pengaju', 'Biaya Realisasi', 'Vendor', 'Teknisi', 'Keterangan'];
        $this->writeTableHeader($sheet, $row, $headers);
        $row++;

        foreach ($payload['laporanPerawatan'] as $item) {
            $sheet->fromArray([
                $item->tanggal_perawatan?->format('Y-m-d') ?? '-',
                $item->pengajuan?->sarana?->kode_sarana ?? '-',
                $item->pengajuan?->sarana?->nama_sarana ?? '-',
                ($item->pengajuan?->sarana?->ruangan?->nama_ruangan ?? '-') . ' - ' . ($item->pengajuan?->sarana?->ruangan?->gedung?->nama_gedung ?? '-'),
                $item->pengajuan?->user?->display_name ?? '-',
                (float) ($item->biaya_realisasi ?? 0),
                $item->nama_vendor ?? '-',
                $item->nama_teknisi ?? '-',
                $item->keterangan ?? '-',
            ], null, "A{$row}");
            $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        $this->finalizeSheet($sheet, 'A', 'I', $row - 1);
    }

    private function buildPenggantianSheet(Spreadsheet $spreadsheet, array $payload): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('03 Penggantian');
        $row = $this->initSheetMeta($sheet, 'Laporan Penggantian', $payload);
        $headers = ['Tanggal', 'Kode Sarana Lama', 'Nama Sarana Lama', 'Kode Sarana Baru', 'Nama Sarana Baru', 'Pengaju', 'Biaya Realisasi', 'Status Realisasi', 'Vendor'];
        $this->writeTableHeader($sheet, $row, $headers);
        $row++;

        foreach ($payload['laporanPenggantian'] as $item) {
            $sheet->fromArray([
                $item->tanggal_penggantian?->format('Y-m-d') ?? '-',
                $item->saranaLama?->kode_sarana ?? ($item->pengajuan?->sarana?->kode_sarana ?? '-'),
                $item->saranaLama?->nama_sarana ?? ($item->pengajuan?->sarana?->nama_sarana ?? '-'),
                $item->saranaBaru?->kode_sarana ?? '-',
                $item->saranaBaru?->nama_sarana ?? '-',
                $item->pengajuan?->user?->display_name ?? '-',
                (float) ($item->biaya_realisasi ?? 0),
                $item->status_realisasi ?? '-',
                $item->nama_vendor ?? '-',
            ], null, "A{$row}");
            $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        $this->finalizeSheet($sheet, 'A', 'I', $row - 1);
    }

    private function buildPengajuanSheet(Spreadsheet $spreadsheet, array $payload): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('04 Pengajuan');
        $row = $this->initSheetMeta($sheet, 'Laporan Pengajuan', $payload);
        $headers = ['Tanggal', 'Kode Sarana', 'Judul', 'Jenis', 'Estimasi', 'Status', 'Pengaju'];
        $this->writeTableHeader($sheet, $row, $headers);
        $row++;

        foreach ($payload['laporanPengajuan'] as $item) {
            $sheet->fromArray([
                $item->created_at?->format('Y-m-d H:i'),
                $item->sarana?->kode_sarana ?? '-',
                $item->judul_pengajuan ?? '-',
                $item->jenis_pengajuan ?? '-',
                (float) ($item->estimasi_biaya ?? 0),
                $item->status_pengajuan ?? '-',
                $item->user?->display_name ?? '-',
            ], null, "A{$row}");
            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        $this->finalizeSheet($sheet, 'A', 'G', $row - 1);
    }

    private function buildSummarySheet(Spreadsheet $spreadsheet, array $payload): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('05 Ringkasan');
        $row = $this->initSheetMeta($sheet, 'Ringkasan Keseluruhan', $payload);

        $sheet->setCellValue("A{$row}", 'Ringkasan KPI');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row += 1;

        foreach ($payload['kpi'] as $label => $value) {
            $sheet->setCellValue("A{$row}", strtoupper(str_replace('_', ' ', $label)));
            $sheet->setCellValue("B{$row}", (int) $value);
            $row++;
        }

        $row += 1;
        $sheet->setCellValue("A{$row}", 'Ringkasan Keuangan');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row += 1;

        foreach ($payload['finance'] as $label => $value) {
            $sheet->setCellValue("A{$row}", strtoupper(str_replace('_', ' ', $label)));
            $sheet->setCellValue("B{$row}", (float) $value);
            $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        $row += 1;
        $sheet->setCellValue("A{$row}", 'Tren Pengajuan');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row += 1;
        $this->writeTableHeader($sheet, $row, ['Bulan', 'Total']);
        $row++;
        foreach ($payload['trenPengajuan'] as $item) {
            $sheet->fromArray([(string) $item->bulan, (int) $item->total], null, "A{$row}");
            $row++;
        }

        $row += 1;
        $sheet->setCellValue("A{$row}", 'Tren Kerusakan');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row += 1;
        $this->writeTableHeader($sheet, $row, ['Bulan', 'Total']);
        $row++;
        foreach ($payload['trenKerusakan'] as $item) {
            $sheet->fromArray([(string) $item->bulan, (int) $item->total], null, "A{$row}");
            $row++;
        }

        $this->finalizeSheet($sheet, 'A', 'C', $row - 1);
    }

    private function initSheetMeta(Worksheet $sheet, string $title, array $payload): int
    {
        $sheet->setCellValue('A1', 'Laporan Sistem Sarpras');
        $sheet->setCellValue('A2', $title);
        $sheet->setCellValue('A3', 'Periode');
        $sheet->setCellValue('B3', $payload['filters']['date_from'] . ' s/d ' . $payload['filters']['date_to']);
        $sheet->setCellValue('A4', 'Dicetak');
        $sheet->setCellValue('B4', now()->format('Y-m-d H:i'));

        $sheet->getStyle('A1:A4')->getFont()->setBold(true);
        $sheet->getStyle('A1:B4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        return 6;
    }

    private function writeTableHeader(Worksheet $sheet, int $row, array $headers): void
    {
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue("{$col}{$row}", $header);
            $col++;
        }

        $lastCol = chr(ord('A') + max(count($headers) - 1, 0));
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F4C81']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    }

    private function finalizeSheet(Worksheet $sheet, string $startCol, string $endCol, int $lastRow): void
    {
        if ($lastRow >= 6) {
            $sheet->getStyle("{$startCol}6:{$endCol}{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->freezePane("A7");
        }

        foreach (range($startCol, $endCol) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    private function applyPengajuanSaranaFilter(Builder $query, array $filters): void
    {
        if (($filters['kategori_id'] ?? 0) > 0) {
            $query->whereHas('sarana', fn(Builder $sarana) => $sarana->where('kategori_id', (int) $filters['kategori_id']));
        }

        if (($filters['ruangan_id'] ?? 0) > 0) {
            $query->whereHas('sarana', fn(Builder $sarana) => $sarana->where('ruangan_id', (int) $filters['ruangan_id']));
        }

        if (($filters['gedung_id'] ?? 0) > 0) {
            $query->whereHas('sarana.ruangan', fn(Builder $ruangan) => $ruangan->where('gedung_id', (int) $filters['gedung_id']));
        }

        if (trim((string) ($filters['q'] ?? '')) !== '') {
            $q = trim((string) $filters['q']);
            $query->where(function (Builder $inner) use ($q) {
                $inner->where('judul_pengajuan', 'like', "%{$q}%")
                    ->orWhereHas('sarana', function (Builder $sarana) use ($q) {
                        $sarana->where('kode_sarana', 'like', "%{$q}%")
                            ->orWhere('nama_sarana', 'like', "%{$q}%");
                    });
            });
        }
    }
}
