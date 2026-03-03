<x-layouts.sbadmin>
    <div class="mb-6">
        <h1 class="page-title">Dashboard Kepala Sarana</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Fokus validasi teknis dan approval level 1.</p>
    </div>

    <div class="panel">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Fokus Proses</h2>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('kepala_sarana.scan') }}" class="btn-secondary">Scan QR</a>
            <a href="{{ route('kepala_sarana.feature', ['feature' => 'validasi-kerusakan']) }}" class="btn-danger">Validasi KR1-KR3</a>
            <a href="{{ route('kepala_sarana.feature', ['feature' => 'approval-teknis']) }}" class="btn-primary">Approval Teknis</a>
            <a href="{{ route('kepala_sarana.feature', ['feature' => 'data-aset']) }}" class="btn-secondary">Data Aset</a>
            <a href="{{ route('kepala_sarana.feature', ['feature' => 'pelaporan']) }}" class="btn-secondary">Pelaporan</a>
        </div>
        <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Tahap ini memvalidasi aspek teknis sebelum pengajuan naik ke bendahara.</p>
    </div>
</x-layouts.sbadmin>


