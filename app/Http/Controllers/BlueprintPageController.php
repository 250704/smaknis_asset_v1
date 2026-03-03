<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;

class BlueprintPageController extends Controller
{
    protected array $features = [
        'admin' => [
            'scan-qr' => ['Scan QR', 'Pusat aksi aset melalui QR code.'],
            'data-aset' => ['Data Aset', 'Kelola dan monitoring data aset per unit.'],
            'tambah-aset' => ['Tambah Aset', 'Input aset satuan, ruangan, atau massal.'],
            'mutasi-aset' => ['Mutasi Aset', 'Eksekusi dan histori perpindahan aset.'],
            'cetak-qr' => ['Cetak QR', 'Generate dan cetak QR code aset.'],
            'semua-pengajuan' => ['Semua Pengajuan', 'Monitoring seluruh pengajuan lintas role.'],
            'realisasi' => ['Realisasi', 'Realisasi perawatan, penggantian, dan pengadaan.'],
            'pelaporan' => ['Pelaporan', 'Laporan inventaris, kerusakan, pengajuan, dan realisasi.'],
            'manajemen-user' => ['Manajemen User', 'Kelola akun pengguna sistem.'],
            'log-aktivitas' => ['Log Aktivitas', 'Audit trail aktivitas user (admin only).'],
        ],
        'guru' => [
            'scan-qr' => ['Scan QR', 'Scan aset dan buat pengajuan cepat.'],
            'buat-pengajuan' => ['Buat Pengajuan Manual', 'Form pengajuan perawatan/penggantian/pengadaan.'],
            'riwayat-pengajuan' => ['Riwayat Pengajuan', 'Pantau status pengajuan yang pernah dibuat.'],
            'notifikasi' => ['Notifikasi', 'Informasi approval dan status realisasi.'],
        ],
        'kepala_sarana' => [
            'scan-qr' => ['Scan QR', 'Validasi kondisi aset di lapangan.'],
            'data-aset' => ['Data Aset', 'Lihat data aset aktif dan histori ringkas.'],
            'histori-aset' => ['Histori Aset', 'Riwayat kondisi dan mutasi aset.'],
            'validasi-kerusakan' => ['Validasi Kerusakan KR1-KR3', 'Validasi laporan kerusakan teknis.'],
            'approval-teknis' => ['Approval Teknis', 'Approval tahap kepala sarana.'],
            'pelaporan' => ['Pelaporan', 'Akses laporan untuk monitoring teknis.'],
            'notifikasi' => ['Notifikasi', 'Informasi pengajuan baru dan tindak lanjut.'],
        ],
        'bendahara' => [
            'scan-qr' => ['Scan QR', 'Lihat aset terkait pengajuan anggaran.'],
            'semua-review' => ['Semua Review Pengajuan', 'Review pengajuan dari sisi biaya/anggaran.'],
            'approval-anggaran' => ['Approval Anggaran', 'Approval tahap bendahara.'],
            'pelaporan' => ['Pelaporan Rekap', 'Rekap biaya estimasi dan realisasi.'],
            'notifikasi' => ['Notifikasi', 'Informasi approval dan status terbaru.'],
        ],
        'kepala_sekolah' => [
            'scan-qr' => ['Scan QR', 'Lihat detail aset sebagai referensi keputusan.'],
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
                'aset_id' => (string) $request->query('aset_id', ''),
                'kode_aset' => (string) $request->query('kode_aset', ''),
                'nama_aset' => (string) $request->query('nama_aset', ''),
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
}
