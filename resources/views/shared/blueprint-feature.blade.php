<x-layouts.sbadmin>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="page-title">{{ $title }}</h1>
        <span class="inline-flex rounded-full border border-cyan-400/30 bg-cyan-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-cyan-700 dark:text-cyan-200">
            {{ str_replace('_', ' ', $role) }}
        </span>
    </div>

    <div class="panel">
        <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ $description }}</p>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
            Halaman ini sudah disiapkan sesuai struktur blueprint.
            Implementasi detail proses sedang dilanjutkan bertahap.
        </p>

        @if (!empty($scanContext))
            <div class="mt-4 rounded-xl border border-cyan-300 bg-cyan-50 px-4 py-3 dark:border-cyan-400/30 dark:bg-cyan-500/10">
                <p class="text-xs font-semibold uppercase tracking-wide text-cyan-700 dark:text-cyan-200">Context dari Scan QR</p>
                <p class="mt-2 text-sm text-slate-700 dark:text-slate-200">
                    Aksi: <span class="font-semibold">{{ str_replace('-', ' ', strtoupper($scanContext['aksi'] ?: '-')) }}</span><br>
                    Sarana: <span class="font-semibold">{{ $scanContext['kode_aset'] ?: '-' }}</span> - {{ $scanContext['nama_aset'] ?: '-' }}<br>
                    ID Sarana: {{ $scanContext['aset_id'] ?: '-' }}
                </p>
            </div>
        @endif
    </div>
</x-layouts.sbadmin>


