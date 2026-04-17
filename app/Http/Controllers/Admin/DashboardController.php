<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\Pengajuan;
use App\Models\Perawatan;
use App\Models\Penggantian;
use App\Models\DetailPengadaan;
use App\Models\LogAktivitas;
use App\Models\Ruangan;
use App\Models\KategoriAset;
use App\Models\Gedung;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $summary = Cache::remember('dashboard_admin_summary_v1', now()->addSeconds(45), function () {
            $totalAset = Aset::count();
            $totalRuangan = Ruangan::count();
            $totalGedung = Gedung::count();
            $totalKategori = KategoriAset::count();

            $pengajuanStats = Pengajuan::query()
                ->selectRaw("
                    COUNT(*) as total_pengajuan,
                    SUM(CASE WHEN status_pengajuan = 'DIAJUKAN' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status_pengajuan IN ('DISETUJUI_KASARANA','DISETUJUI_BENDAHARA','DISETUJUI_KEPSEK','DIPROSES') THEN 1 ELSE 0 END) as disetujui,
                    SUM(CASE WHEN status_pengajuan = 'DITOLAK' THEN 1 ELSE 0 END) as ditolak,
                    SUM(CASE WHEN status_pengajuan = 'SELESAI' THEN 1 ELSE 0 END) as selesai
                ")
                ->first();

            $asetKondisiStats = Aset::query()
                ->selectRaw("
                    SUM(CASE WHEN kondisi_terkini = 'BAIK' THEN 1 ELSE 0 END) as baik,
                    SUM(CASE WHEN kondisi_terkini = 'RINGAN' THEN 1 ELSE 0 END) as rusak_ringan,
                    SUM(CASE WHEN kondisi_terkini = 'BERAT' THEN 1 ELSE 0 END) as rusak_berat,
                    SUM(CASE WHEN kondisi_terkini = 'TIDAK_LAYAK' THEN 1 ELSE 0 END) as tidak_layak
                ")
                ->first();

            $monthlyRows = Pengajuan::query()
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
                ->where('created_at', '>=', Carbon::now()->subMonths(5)->startOfMonth())
                ->groupBy('ym')
                ->orderBy('ym')
                ->pluck('total', 'ym');

            $pengajuanPerBulan = collect(range(5, 0))->map(function (int $month) use ($monthlyRows) {
                $date = Carbon::now()->subMonths($month);
                $ym = $date->format('Y-m');
                return [
                    'bulan' => $date->format('M'),
                    'jumlah' => (int) ($monthlyRows[$ym] ?? 0),
                ];
            });

            $totalPerawatan = Perawatan::count();
            $totalPenggantian = Penggantian::count();
            $totalPengadaan = DetailPengadaan::count();
            $nilaiTotalAset = (float) (Aset::sum('harga_perolehan') ?? 0);
            $totalEstimasi = (float) (Pengajuan::whereNotNull('estimasi_biaya')->sum('estimasi_biaya') ?? 0);
            $totalAnggaranDisetujui = (float) (Pengajuan::whereNotNull('anggaran_disetujui')->sum('anggaran_disetujui') ?? 0);
            $totalRealisasi = (float) (Pengajuan::whereNotNull('biaya_realisasi')->sum('biaya_realisasi') ?? 0);
            $pengajuanDenganBiaya = Pengajuan::whereNotNull('estimasi_biaya')->count();

            return [
                'totalAset' => $totalAset,
                'totalRuangan' => $totalRuangan,
                'totalGedung' => $totalGedung,
                'totalKategori' => $totalKategori,
                'totalPengajuan' => (int) ($pengajuanStats->total_pengajuan ?? 0),
                'pengajuanPending' => (int) ($pengajuanStats->pending ?? 0),
                'pengajuanDisetujui' => (int) ($pengajuanStats->disetujui ?? 0),
                'pengajuanDitolak' => (int) ($pengajuanStats->ditolak ?? 0),
                'pengajuanSelesai' => (int) ($pengajuanStats->selesai ?? 0),
                'asetBaik' => (int) ($asetKondisiStats->baik ?? 0),
                'asetRusakRingan' => (int) ($asetKondisiStats->rusak_ringan ?? 0),
                'asetRusakBerat' => (int) ($asetKondisiStats->rusak_berat ?? 0),
                'asetHilang' => (int) ($asetKondisiStats->tidak_layak ?? 0),
                'pengajuanPerBulan' => $pengajuanPerBulan,
                'totalPerawatan' => $totalPerawatan,
                'totalPenggantian' => $totalPenggantian,
                'totalPengadaan' => $totalPengadaan,
                'nilaiTotalAset' => $nilaiTotalAset,
                'totalEstimasi' => $totalEstimasi,
                'totalAnggaranDisetujui' => $totalAnggaranDisetujui,
                'totalRealisasi' => $totalRealisasi,
                'pengajuanDenganBiaya' => $pengajuanDenganBiaya,
            ];
        });

        $totalAset = $summary['totalAset'];
        $totalRuangan = $summary['totalRuangan'];
        $totalGedung = $summary['totalGedung'];
        $totalKategori = $summary['totalKategori'];
        $totalPengajuan = $summary['totalPengajuan'];
        $pengajuanPending = $summary['pengajuanPending'];
        $pengajuanDisetujui = $summary['pengajuanDisetujui'];
        $pengajuanDitolak = $summary['pengajuanDitolak'];
        $pengajuanSelesai = $summary['pengajuanSelesai'];
        $asetBaik = $summary['asetBaik'];
        $asetRusakRingan = $summary['asetRusakRingan'];
        $asetRusakBerat = $summary['asetRusakBerat'];
        $asetHilang = $summary['asetHilang'];
        $pengajuanPerBulan = $summary['pengajuanPerBulan'];
        $totalPerawatan = $summary['totalPerawatan'];
        $totalPenggantian = $summary['totalPenggantian'];
        $totalPengadaan = $summary['totalPengadaan'];
        $nilaiTotalAset = $summary['nilaiTotalAset'];
        $totalEstimasi = $summary['totalEstimasi'];
        $totalAnggaranDisetujui = $summary['totalAnggaranDisetujui'];
        $totalRealisasi = $summary['totalRealisasi'];
        $pengajuanDenganBiaya = $summary['pengajuanDenganBiaya'];

        // Data untuk Grafik - Aset per Kategori
        $asetPerKategori = Aset::select('kategori_id', DB::raw('count(*) as total'))
            ->with('kategori')
            ->groupBy('kategori_id')
            ->get();

        // Aktivitas Terbaru
        $aktivitasTerbaru = LogAktivitas::with('user')
            ->latest()
            ->limit(5)
            ->get();

        // Pengajuan Terbaru
        $pengajuanTerbaru = Pengajuan::with(['user', 'aset'])
            ->latest()
            ->limit(5)
            ->get();

        // Aset per Ruangan (untuk chart)
        $asetPerRuangan = Aset::select('ruangan_id', DB::raw('count(*) as total'))
            ->with('ruangan')
            ->groupBy('ruangan_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalAset',
            'totalRuangan',
            'totalGedung',
            'totalKategori',
            'totalPengajuan',
            'pengajuanPending',
            'pengajuanDisetujui',
            'pengajuanDitolak',
            'pengajuanSelesai',
            'asetBaik',
            'asetRusakRingan',
            'asetRusakBerat',
            'asetHilang',
            'asetPerKategori',
            'pengajuanPerBulan',
            'aktivitasTerbaru',
            'pengajuanTerbaru',
            'totalPerawatan',
            'totalPenggantian',
            'totalPengadaan',
            'nilaiTotalAset',
            'asetPerRuangan',
            'totalEstimasi',
            'totalAnggaranDisetujui',
            'totalRealisasi',
            'pengajuanDenganBiaya'
        ));
    }
}
