<x-layouts.sbadmin>
    <div class="mb-6">
        <h1 class="page-title">Dashboard Kepala Sekolah</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Fokus pengambilan keputusan akhir pengajuan.</p>
    </div>

    <div class="panel">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Fokus Proses</h2>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('kepala_sekolah.feature', ['feature' => 'approval-final']) }}" class="btn-primary">Approval Final</a>
            <a href="{{ route('kepala_sekolah.feature', ['feature' => 'pelaporan']) }}" class="btn-secondary">Pelaporan</a>
            <a href="{{ route('kepala_sekolah.feature', ['feature' => 'notifikasi']) }}" class="btn-secondary">Notifikasi</a>
            <a href="{{ route('kepala_sekolah.scan') }}" class="btn-secondary">Scan QR</a>
        </div>
        <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Keputusan akhir pengajuan berada pada tahap kepala sekolah.</p>
    </div>
</x-layouts.sbadmin>


