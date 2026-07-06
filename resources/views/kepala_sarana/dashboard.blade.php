<x-layouts.sbadmin>
    <div class="mb-6">
        <h1 class="page-title">Dashboard Kepala Sarana</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Fokus validasi kerusakan dan approval tahap awal pengajuan.</p>
    </div>

    <div class="panel">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Fokus Proses</h2>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('kepala_sarana.scan') }}" class="btn-secondary">Scan QR</a>
            <a href="{{ route('kepala_sarana.kerusakan.index') }}" class="btn-danger">Validasi Kerusakan</a>
            <a href="{{ route('kepala_sarana.pengajuan.approval') }}" class="btn-primary">Approval</a>
            <a href="{{ route('kepala_sarana.validasi.semua') }}" class="btn-secondary">Semua Proses</a>
            <a href="{{ route('kepala_sarana.aset.index') }}" class="btn-secondary">Data Sarana</a>
            <a href="{{ route('kepala_sarana.feature', ['feature' => 'pelaporan']) }}" class="btn-secondary">Pelaporan</a>
        </div>
        <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Urutan status: Menunggu Approval Kepala Sarana -> Menunggu Approval Bendahara -> Menunggu Approval Kepala Sekolah -> Realisasi Diproses.</p>
    </div>
</x-layouts.sbadmin>

