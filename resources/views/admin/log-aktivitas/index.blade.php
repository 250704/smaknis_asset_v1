<x-layouts.sbadmin>
    <div class="space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="page-title">Log Aktivitas</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Histori audit trail dari seluruh tindakan pengguna di dalam sistem.
                </p>
            </div>
            <div>
                <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-200">
                    Total: {{ number_format($logs->total()) }} log
                </span>
            </div>
        </div>

        {{-- Panel Filter --}}
        <section class="panel">
            <form method="GET" action="{{ route('admin.log-aktivitas.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <label class="filter-label" for="q">Pencarian</label>
                    <input
                        type="text"
                        id="q"
                        name="q"
                        value="{{ $filters['q'] ?? '' }}"
                        placeholder="Nama, email, deskripsi..."
                        class="filter-control"
                    >
                </div>
                <div class="lg:col-span-3">
                    <label class="filter-label" for="modul">Modul</label>
                    <select id="modul" name="modul" class="filter-control">
                        <option value="">Semua Modul</option>
                        @foreach ($modules as $mod)
                            <option value="{{ $mod }}" @selected(($filters['modul'] ?? '') === $mod)>
                                {{ str_replace('_', ' ', $mod) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="filter-label" for="tanggal_mulai">Tanggal Mulai</label>
                    <input
                        type="date"
                        id="tanggal_mulai"
                        name="tanggal_mulai"
                        value="{{ $filters['tanggal_mulai'] ?? '' }}"
                        class="filter-control"
                    >
                </div>
                <div class="lg:col-span-2">
                    <label class="filter-label" for="tanggal_selesai">Tanggal Selesai</label>
                    <input
                        type="date"
                        id="tanggal_selesai"
                        name="tanggal_selesai"
                        value="{{ $filters['tanggal_selesai'] ?? '' }}"
                        class="filter-control"
                    >
                </div>
                <div class="lg:col-span-1 flex items-end justify-end gap-2 mt-2 sm:mt-0">
                    <button type="submit" class="filter-submit w-full" title="Terapkan Filter">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('admin.log-aktivitas.index') }}" class="filter-reset flex items-center justify-center p-2 rounded-xl border border-slate-200 dark:border-white/10 text-slate-500 hover:text-slate-800 dark:hover:text-slate-200" title="Reset Filter">
                        <i class="fas fa-undo-alt"></i>
                    </a>
                </div>
            </form>
        </section>

        {{-- Table Log --}}
        <section class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-white/10 text-sm">
                    <thead class="bg-slate-50 dark:bg-white/[0.02]">
                        <tr>
                            <th class="px-4 py-3 font-semibold text-left text-slate-600 dark:text-slate-300 w-[160px]">Waktu</th>
                            <th class="px-4 py-3 font-semibold text-left text-slate-600 dark:text-slate-300 w-[200px]">Pengguna</th>
                            <th class="px-4 py-3 font-semibold text-left text-slate-600 dark:text-slate-300 w-[150px]">Modul</th>
                            <th class="px-4 py-3 font-semibold text-left text-slate-600 dark:text-slate-300 w-[140px]">Aktivitas</th>
                            <th class="px-4 py-3 font-semibold text-left text-slate-600 dark:text-slate-300">Deskripsi</th>
                            <th class="px-4 py-3 font-semibold text-left text-slate-600 dark:text-slate-300 w-[120px]">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/60 dark:divide-white/5 bg-white dark:bg-slate-900/40">
                        @forelse ($logs as $log)
                            @php
                                $roleCode = $log->user?->role_code;
                                $roleBadge = match ($roleCode) {
                                    'admin' => 'bg-red-50 text-red-700 border-red-200/60 dark:bg-red-500/10 dark:text-red-300 dark:border-red-500/20',
                                    'kepala_sarana' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60 dark:bg-emerald-500/10 dark:text-emerald-300 dark:border-emerald-500/20',
                                    'bendahara' => 'bg-amber-50 text-amber-700 border-amber-200/60 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-500/20',
                                    'kepala_sekolah' => 'bg-cyan-50 text-cyan-700 border-cyan-200/60 dark:bg-cyan-500/10 dark:text-cyan-300 dark:border-cyan-500/20',
                                    default => 'bg-slate-50 text-slate-700 border-slate-200/60 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                                };
                                $roleLabel = match ($roleCode) {
                                    'admin' => 'ADMIN',
                                    'guru' => 'GURU',
                                    'kepala_sarana' => 'KEPSAR',
                                    'bendahara' => 'BENDAHARA',
                                    'kepala_sekolah' => 'KEPSEK',
                                    default => strtoupper($roleCode ?? 'GUEST'),
                                };

                                // Modul colors
                                $modulBadge = match ($log->modul) {
                                    'SCAN_QR', 'SCAN_QR_ACTION_HUB' => 'bg-cyan-500/10 text-cyan-700 border-cyan-500/20 dark:text-cyan-300',
                                    'INVENTARIS', 'DATA_SARANA' => 'bg-blue-500/10 text-blue-700 border-blue-500/20 dark:text-blue-300',
                                    'USER_MANAGEMENT' => 'bg-purple-500/10 text-purple-700 border-purple-500/20 dark:text-purple-300',
                                    'PENGAJUAN' => 'bg-orange-500/10 text-orange-700 border-orange-500/20 dark:text-orange-300',
                                    'REALISASI' => 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20 dark:text-emerald-300',
                                    'LAPORAN' => 'bg-rose-500/10 text-rose-700 border-rose-500/20 dark:text-rose-300',
                                    default => 'bg-slate-500/10 text-slate-700 border-slate-500/20 dark:text-slate-300',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.01] transition-colors">
                                <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                    <div class="font-semibold text-slate-700 dark:text-slate-300">
                                        {{ $log->created_at?->format('d M Y') }}
                                    </div>
                                    <div class="text-[10px] mt-0.5">
                                        {{ $log->created_at?->format('H:i:s') }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($log->user)
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                                {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate" title="{{ $log->user->name }}">
                                                    {{ $log->user->name }}
                                                </p>
                                                <p class="text-[10px] text-slate-400 truncate" title="{{ $log->user->email }}">
                                                    {{ $log->user->email }}
                                                </p>
                                                <span class="inline-block mt-0.5 rounded px-1.5 py-0.5 text-[8px] font-extrabold border {{ $roleBadge }}">
                                                    {{ $roleLabel }}
                                                </span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs italic text-slate-400">Guest / Sistem</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex rounded-lg px-2 py-1 text-[10px] font-extrabold uppercase border {{ $modulBadge }}">
                                        {{ str_replace('_', ' ', $log->modul) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase">
                                        {{ str_replace('_', ' ', $log->aktivitas) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-300 break-words max-w-[360px]">
                                    {{ $log->deskripsi }}
                                </td>
                                <td class="px-4 py-3 text-xs font-mono text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="fas fa-network-wired text-[10px] text-slate-400"></i>
                                        {{ $log->ip_address ?? '127.0.0.1' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center">
                                    <div class="flex flex-col items-center justify-center p-4">
                                        <i class="fas fa-history text-3xl text-slate-300 dark:text-slate-600 mb-2"></i>
                                        <p class="text-xs text-slate-400">Tidak ada log aktivitas yang cocok dengan kriteria filter.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="px-4 py-3 border-t border-slate-200 dark:border-white/10 bg-slate-50/50 dark:bg-white/[0.02]">
                    {{ $logs->links() }}
                </div>
            @endif
        </section>
    </div>
</x-layouts.sbadmin>
