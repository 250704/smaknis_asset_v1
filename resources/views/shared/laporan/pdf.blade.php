<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Sistem Sarpras</title>
    <style>
        body { font-family: Arial, sans-serif; color: #0f172a; margin: 20px; }
        h1, h2, h3 { margin: 0 0 8px; }
        .meta { margin-bottom: 14px; font-size: 12px; color: #475569; }
        .section { margin-top: 16px; }
        .small { font-size: 11px; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; font-size: 11px; text-align: left; vertical-align: top; }
        th { background: #0f4c81; color: #ffffff; font-weight: 700; }
        .kpi-grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 8px; }
        .card { border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px; }
        .label { font-size: 10px; text-transform: uppercase; color: #64748b; }
        .value { font-size: 16px; font-weight: 700; margin-top: 3px; }
        .page-break { page-break-before: always; }
        .no-print { margin-bottom: 12px; }
        @media print {
            .no-print { display: none; }
            body { margin: 10mm; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding:8px 12px;border-radius:6px;border:1px solid #94a3b8;background:#0ea5e9;color:white;font-weight:700;cursor:pointer;">Print / Save PDF</button>
    </div>

    <h1>Laporan Sistem Sarpras</h1>
    <div class="meta">
        Role: {{ strtoupper(str_replace('_', ' ', $role)) }}<br>
        Periode: {{ $filters['date_from'] }} s/d {{ $filters['date_to'] }}<br>
        Dicetak: {{ now()->format('d M Y H:i') }}
    </div>

    <div class="section">
        <h2>01. Laporan Kerusakan</h2>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kode Sarana</th>
                    <th>Nama Sarana</th>
                    <th>Lokasi</th>
                    <th>Tingkat</th>
                    <th>Status</th>
                    <th>Pelapor</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($laporanKerusakan as $item)
                    <tr>
                        <td>{{ $item->created_at?->format('d M Y H:i') }}</td>
                        <td>{{ $item->aset?->kode_aset ?? '-' }}</td>
                        <td>{{ $item->aset?->nama_aset ?? '-' }}</td>
                        <td>{{ $item->aset?->ruangan?->nama_ruangan }} - {{ $item->aset?->ruangan?->gedung?->nama_gedung }}</td>
                        <td>{{ $item->tingkat_kerusakan }}</td>
                        <td>{{ $item->status }}</td>
                        <td>{{ $item->user?->display_name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7">Tidak ada data kerusakan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section page-break">
        <h2>02. Laporan Perawatan</h2>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kode Sarana</th>
                    <th>Nama Sarana</th>
                    <th>Pengaju</th>
                    <th>Biaya Realisasi</th>
                    <th>Vendor</th>
                    <th>Teknisi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($laporanPerawatan as $item)
                    <tr>
                        <td>{{ $item->tanggal_perawatan?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $item->pengajuan?->aset?->kode_aset ?? '-' }}</td>
                        <td>{{ $item->pengajuan?->aset?->nama_aset ?? '-' }}</td>
                        <td>{{ $item->pengajuan?->user?->display_name ?? '-' }}</td>
                        <td>Rp {{ number_format((float) ($item->biaya_realisasi ?? 0), 0, ',', '.') }}</td>
                        <td>{{ $item->nama_vendor ?? '-' }}</td>
                        <td>{{ $item->nama_teknisi ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7">Tidak ada data perawatan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section page-break">
        <h2>03. Laporan Penggantian</h2>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kode Sarana Lama</th>
                    <th>Kode Sarana Baru</th>
                    <th>Pengaju</th>
                    <th>Biaya Realisasi</th>
                    <th>Status</th>
                    <th>Vendor</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($laporanPenggantian as $item)
                    <tr>
                        <td>{{ $item->tanggal_penggantian?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $item->asetLama?->kode_aset ?? ($item->pengajuan?->aset?->kode_aset ?? '-') }}</td>
                        <td>{{ $item->asetBaru?->kode_aset ?? '-' }}</td>
                        <td>{{ $item->pengajuan?->user?->display_name ?? '-' }}</td>
                        <td>Rp {{ number_format((float) ($item->biaya_realisasi ?? 0), 0, ',', '.') }}</td>
                        <td>{{ $item->status_realisasi ?? '-' }}</td>
                        <td>{{ $item->nama_vendor ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7">Tidak ada data penggantian.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section page-break">
        <h2>04. Laporan Pengajuan</h2>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kode Sarana</th>
                    <th>Judul</th>
                    <th>Jenis</th>
                    <th>Estimasi</th>
                    <th>Status</th>
                    <th>Pengaju</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($laporanPengajuan as $item)
                    <tr>
                        <td>{{ $item->created_at?->format('d M Y H:i') }}</td>
                        <td>{{ $item->aset?->kode_aset ?? '-' }}</td>
                        <td>{{ $item->judul_pengajuan }}</td>
                        <td>{{ $item->jenis_pengajuan }}</td>
                        <td>Rp {{ number_format((float) ($item->estimasi_biaya ?? 0), 0, ',', '.') }}</td>
                        <td>{{ str_replace('_', ' ', $item->status_pengajuan) }}</td>
                        <td>{{ $item->user?->display_name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7">Tidak ada data pengajuan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section page-break">
        <h2>05. Ringkasan Keseluruhan</h2>

        <h3>Ringkasan KPI</h3>
        <div class="kpi-grid">
            <div class="card"><div class="label">Total Sarana</div><div class="value">{{ number_format($kpi['total_aset']) }}</div></div>
            <div class="card"><div class="label">Sarana Perlu Perhatian</div><div class="value">{{ number_format($kpi['aset_rusak']) }}</div></div>
            <div class="card"><div class="label">Total Pengajuan</div><div class="value">{{ number_format($kpi['total_pengajuan']) }}</div></div>
            <div class="card"><div class="label">Pengajuan Menunggu</div><div class="value">{{ number_format($kpi['pengajuan_menunggu']) }}</div></div>
            <div class="card"><div class="label">Total Kerusakan</div><div class="value">{{ number_format($kpi['total_kerusakan']) }}</div></div>
            <div class="card"><div class="label">Kerusakan Aktif</div><div class="value">{{ number_format($kpi['kerusakan_aktif']) }}</div></div>
        </div>

        <h3 style="margin-top:14px;">Ringkasan Keuangan</h3>
        <table>
            <thead>
                <tr><th>Item</th><th>Nilai</th></tr>
            </thead>
            <tbody>
                <tr><td>Estimasi Pengajuan</td><td>Rp {{ number_format($finance['estimasi_total'], 0, ',', '.') }}</td></tr>
                <tr><td>Realisasi Perawatan</td><td>Rp {{ number_format($finance['realisasi_perawatan'], 0, ',', '.') }}</td></tr>
                <tr><td>Realisasi Penggantian</td><td>Rp {{ number_format($finance['realisasi_penggantian'], 0, ',', '.') }}</td></tr>
                <tr><td>Total Realisasi</td><td>Rp {{ number_format($finance['total_realisasi'], 0, ',', '.') }}</td></tr>
                <tr><td>Selisih Anggaran</td><td>Rp {{ number_format($finance['selisih_anggaran'], 0, ',', '.') }}</td></tr>
            </tbody>
        </table>
    </div>

    <p class="small" style="margin-top:16px;">Dokumen ini dibuat otomatis dari sistem sesuai filter laporan aktif.</p>
</body>
</html>
