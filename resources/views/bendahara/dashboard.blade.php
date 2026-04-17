<x-layouts.sbadmin>
    <div class="mb-6">
        <h1 class="page-title">Dashboard Bendahara</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Fokus approval anggaran dan verifikasi keuangan realisasi.</p>
    </div>

    <div class="panel">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Fokus Proses</h2>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('bendahara.scan') }}" class="btn-secondary">Scan QR</a>
            <a href="{{ route('bendahara.pengajuan.approval') }}" class="btn-primary">Approval Anggaran</a>
            <a href="{{ route('bendahara.pengajuan.index') }}" class="btn-secondary">Semua Pengajuan</a>
            <a href="{{ route('bendahara.kerusakan.create') }}" class="btn-secondary">Lapor Kerusakan</a>
            <a href="{{ route('bendahara.feature', ['feature' => 'pelaporan']) }}" class="btn-secondary">Pelaporan Rekap</a>
            <a href="{{ route('bendahara.notifikasi.index') }}" class="btn-secondary">Notifikasi</a>
        </div>
        <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Antrian utama bendahara berasal dari status Menunggu Approval Bendahara dan Menunggu Verifikasi Keuangan.</p>
    </div>
</x-layouts.sbadmin>


