<x-layouts.sbadmin>
    <div class="mb-6">
        <h1 class="page-title">Dashboard Admin / Petugas Sarana</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Ringkasan area kerja utama sesuai blueprint final.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <a href="{{ route('admin.scan') }}" class="kpi-card block">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Scan</p>
                    <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-100">QR Action Hub</p>
                </div>
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-500/20 text-cyan-700 dark:text-cyan-200">
                    <i class="fas fa-qrcode text-xs"></i>
                </span>
            </div>
        </a>

        <a href="{{ route('admin.aset.index') }}" class="kpi-card block">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Inventaris</p>
                    <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-100">Kelola Data Aset</p>
                </div>
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-blue-500/20 text-blue-200">
                    <i class="fas fa-boxes-stacked text-xs"></i>
                </span>
            </div>
        </a>

        <a href="{{ route('admin.feature', ['feature' => 'semua-pengajuan']) }}" class="kpi-card block">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Pengajuan</p>
                    <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-100">Monitoring Semua Pengajuan</p>
                </div>
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-200">
                    <i class="fas fa-file-signature text-xs"></i>
                </span>
            </div>
        </a>

        <a href="{{ route('admin.feature', ['feature' => 'realisasi']) }}" class="kpi-card block">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Realisasi</p>
                    <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-100">Perawatan, Penggantian, Pengadaan</p>
                </div>
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-500/20 text-cyan-700 dark:text-cyan-200">
                    <i class="fas fa-check-double text-xs"></i>
                </span>
            </div>
        </a>

        <a href="{{ route('admin.feature', ['feature' => 'log-aktivitas']) }}" class="kpi-card block">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Audit</p>
                    <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-100">Log Aktivitas Admin</p>
                </div>
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/20 text-amber-200">
                    <i class="fas fa-clock-rotate-left text-xs"></i>
                </span>
            </div>
        </a>
    </div>
</x-layouts.sbadmin>

