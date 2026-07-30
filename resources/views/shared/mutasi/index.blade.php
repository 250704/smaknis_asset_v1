<x-layouts.sbadmin>
    <div class="mb-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="page-title">Histori & Usulan Mutasi Sarana</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Daftar perpindahan sarana antar ruangan dan status validasinya.</p>
            </div>
            <div>
                <a href="{{ route($role . '.mutasi.create') }}" class="btn-primary flex items-center gap-2">
                    <i class="fas fa-plus text-xs"></i>
                    <span>Ajukan/Mutasikan Sarana</span>
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <section class="panel mb-6">
        <form method="GET" class="filter-grid">
            <div>
                <label class="filter-label" for="q">Pencarian</label>
                <input id="q" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Kode sarana, nama..." class="filter-control">
            </div>
            <div>
                <label class="filter-label" for="gedung_id">Gedung</label>
                <select id="gedung_id" name="gedung_id" class="filter-control">
                    <option value="">Semua gedung...</option>
                    @foreach ($gedungList as $gedung)
                        <option value="{{ $gedung->id }}" @selected((int) ($filters['gedung_id'] ?? 0) === (int) $gedung->id)>{{ $gedung->nama_gedung }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="filter-label" for="ruangan_id">Ruangan</label>
                <select id="ruangan_id" name="ruangan_id" class="filter-control">
                    <option value="">Semua ruangan...</option>
                    @foreach ($ruanganList as $ruangan)
                        <option value="{{ $ruangan->id }}" @selected((int) ($filters['ruangan_id'] ?? 0) === (int) $ruangan->id)>{{ $ruangan->nama_ruangan }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="filter-label" for="status_mutasi">Status</label>
                <select id="status_mutasi" name="status_mutasi" class="filter-control">
                    <option value="">Semua status...</option>
                    <option value="DIAJUKAN" @selected(($filters['status_mutasi'] ?? '') === 'DIAJUKAN')>DIAJUKAN</option>
                    <option value="DISETUJUI" @selected(($filters['status_mutasi'] ?? '') === 'DISETUJUI')>DISETUJUI</option>
                    <option value="DITOLAK" @selected(($filters['status_mutasi'] ?? '') === 'DITOLAK')>DITOLAK</option>
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="filter-submit">
                    <i class="fas fa-filter text-xs"></i>Filter
                </button>
                <a href="{{ url()->current() }}" class="filter-reset">
                    <i class="fas fa-undo text-xs"></i>Reset
                </a>
            </div>
        </form>
    </section>

    <div class="panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-white/10 text-slate-500 dark:text-slate-400 font-semibold bg-slate-50/50 dark:bg-slate-900/50">
                        <th class="px-6 py-4">Sarana</th>
                        <th class="px-6 py-4">Ruangan Asal</th>
                        <th class="px-6 py-4">Ruangan Tujuan</th>
                        <th class="px-6 py-4">Pengaju</th>
                        <th class="px-6 py-4">Tanggal Rencana</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse($mutasiList as $mutasi)
                        <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800 dark:text-slate-200">{{ $mutasi->sarana?->nama_sarana ?? '-' }}</div>
                                <div class="font-mono text-xs text-blue-600 dark:text-blue-400">{{ $mutasi->sarana?->kode_sarana ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-slate-700 dark:text-slate-300 font-medium">Ruang {{ $mutasi->ruanganAsal?->nama_ruangan ?? '-' }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $mutasi->ruanganAsal?->gedung?->nama_gedung ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-slate-700 dark:text-slate-300 font-medium text-blue-600 dark:text-blue-400">Ruang {{ $mutasi->ruanganTujuan?->nama_ruangan ?? '-' }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $mutasi->ruanganTujuan?->gedung?->nama_gedung ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-slate-700 dark:text-slate-300">{{ $mutasi->userPengaju?->display_name ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-slate-600 dark:text-slate-400">{{ $mutasi->tanggal_mutasi?->format('d M Y') ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($mutasi->status_mutasi === 'DISETUJUI')
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                        DISETUJUI
                                    </span>
                                @elseif($mutasi->status_mutasi === 'DITOLAK')
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                        DITOLAK
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                        DIAJUKAN
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route($role . '.mutasi.show', $mutasi->id) }}" class="inline-flex items-center justify-center p-2 text-blue-600 hover:bg-blue-50 hover:text-blue-800 rounded-lg dark:text-blue-400 dark:hover:bg-blue-900/30 transition-colors">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-slate-400 dark:text-slate-500">
                                <i class="fas fa-exchange-alt text-3xl mb-3 block opacity-40"></i>
                                Belum ada riwayat mutasi sarana.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mutasiList->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-white/10">
                {{ $mutasiList->links() }}
            </div>
        @endif
    </div>
</x-layouts.sbadmin>
