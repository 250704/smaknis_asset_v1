<x-layouts.sbadmin>
    <div class="mb-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="page-title">Tambah Sarana</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pilih metode penambahan sarana</p>
            </div>
            <a href="{{ route('admin.sarana.index') }}" class="btn-secondary">
                <i class="fas fa-arrow-left mr-2 text-xs"></i>Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 md:grid-cols-2">
        {{-- Card 1: Input Per Unit --}}
        <a href="{{ route('admin.sarana.create-unit') }}" class="group panel overflow-hidden p-0 transition-all duration-200 hover:shadow-lg hover:-translate-y-1">
            <div class="border-b border-slate-200 bg-gradient-to-r from-blue-50 to-cyan-50 px-6 py-4 dark:border-white/10 dark:from-slate-800 dark:to-slate-800/50">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/20 text-blue-600 transition-transform duration-200 group-hover:scale-110 dark:text-blue-400">
                        <i class="fas fa-box text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Input Per Unit</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Tambah satu sarana individual</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <ul class="mb-6 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-xs text-emerald-500 mt-1"></i>
                        <span>Untuk penambahan 1 sarana</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-xs text-emerald-500 mt-1"></i>
                        <span>Form lengkap dengan semua detail</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-xs text-emerald-500 mt-1"></i>
                        <span>Cocok untuk input manual</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-xs text-emerald-500 mt-1"></i>
                        <span>Upload foto sarana tersedia</span>
                    </li>
                </ul>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">Mulai Input →</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-600 transition-transform duration-200 group-hover:translate-x-2 dark:bg-blue-500/20 dark:text-blue-400">
                        <i class="fas fa-arrow-right text-xs"></i>
                    </div>
                </div>
            </div>
        </a>

        {{-- Card 2: Input Massal --}}
        <a href="{{ route('admin.sarana.import-massal.create') }}" class="group panel overflow-hidden p-0 transition-all duration-200 hover:shadow-lg hover:-translate-y-1">
            <div class="border-b border-slate-200 bg-gradient-to-r from-emerald-50 to-teal-50 px-6 py-4 dark:border-white/10 dark:from-slate-800 dark:to-slate-800/50">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-600 transition-transform duration-200 group-hover:scale-110 dark:text-emerald-400">
                        <i class="fas fa-layer-group text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Input Massal</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Tambah banyak sarana sekaligus</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <ul class="mb-6 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-xs text-emerald-500 mt-1"></i>
                        <span>Untuk penambahan banyak sarana</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-xs text-emerald-500 mt-1"></i>
                        <span>Input langsung dari web (tanpa file)</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-xs text-emerald-500 mt-1"></i>
                        <span>Bedakan gedung, kategori, ruangan</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-xs text-emerald-500 mt-1"></i>
                        <span>Max 50 baris per import</span>
                    </li>
                </ul>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">Mulai Input →</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 transition-transform duration-200 group-hover:translate-x-2 dark:bg-emerald-500/20 dark:text-emerald-400">
                        <i class="fas fa-arrow-right text-xs"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- Quick Info --}}
   
</x-layouts.sbadmin>
