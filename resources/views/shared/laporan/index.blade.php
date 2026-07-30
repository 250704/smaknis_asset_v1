<x-layouts.sbadmin>
    @php
        $roleLabel = match ($role) {
            'admin' => 'Admin',
            'kepala_sarana' => 'Kepala Sarana',
            'bendahara' => 'Bendahara',
            'kepala_sekolah' => 'Kepala Sekolah',
            default => strtoupper(str_replace('_', ' ', $role)),
        };
    @endphp

    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="page-title">Laporan Sarana</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Data sarana yang bisa difilter dan ditinjau lintas role.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @php
                $baseParams = request()->query();
                $excelRoute = route($role . '.laporan.export.excel', $baseParams);
                $pdfRoute = route($role . '.laporan.export.pdf', $baseParams);
            @endphp
            <a href="{{ $pdfRoute }}" target="_blank" class="btn-secondary">Export PDF</a>
            <a href="{{ $excelRoute }}" class="btn-primary">Download Excel</a>
            <span class="inline-flex rounded-full border border-cyan-400/30 bg-cyan-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-cyan-700 dark:text-cyan-200">
                {{ $roleLabel }}
            </span>
        </div>
    </div>

    <section class="panel">
        <form method="GET" class="filter-grid">
            <div>
                <label class="filter-label" for="q">Pencarian</label>
                <input id="q" type="text" name="q" value="{{ $filters['q'] }}" placeholder="Kode sarana, nama sarana..." class="filter-control">
            </div>
            <div>
                <label class="filter-label" for="kategori_id">Kategori</label>
                <select id="kategori_id" name="kategori_id" class="filter-control">
                    <option value="0">Semua kategori</option>
                    @foreach ($kategoriList as $kategori)
                        <option value="{{ $kategori->id }}" @selected((int) $filters['kategori_id'] === (int) $kategori->id)>{{ $kategori->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="filter-label" for="gedung_id">Gedung</label>
                <select id="gedung_id" name="gedung_id" class="filter-control">
                    <option value="0">Semua gedung</option>
                    @foreach ($gedungList as $gedung)
                        <option value="{{ $gedung->id }}" @selected((int) $filters['gedung_id'] === (int) $gedung->id)>{{ $gedung->nama_gedung }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="filter-label" for="ruangan_id">Ruangan</label>
                <select id="ruangan_id" name="ruangan_id" class="filter-control">
                    <option value="0">Semua ruangan</option>
                    @foreach ($ruanganList as $ruangan)
                        <option value="{{ $ruangan->id }}" @selected((int) $filters['ruangan_id'] === (int) $ruangan->id)>{{ $ruangan->nama_ruangan }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="filter-label" for="kondisi_terkini">Kondisi</label>
                <select id="kondisi_terkini" name="kondisi_terkini" class="filter-control">
                    <option value="">Semua kondisi</option>
                    @foreach ($kondisiList as $kondisi)
                        <option value="{{ $kondisi }}" @selected($filters['kondisi_terkini'] === $kondisi)>{{ $kondisi }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="filter-label" for="status_sarana">Status</label>
                <select id="status_sarana" name="status_sarana" class="filter-control">
                    <option value="">Semua status</option>
                    @foreach ($statusList as $status)
                        <option value="{{ $status }}" @selected($filters['status_sarana'] === $status)>{{ $status }}</option>
                    @endforeach
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

    <section class="mt-5">
        <div class="panel overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] divide-y divide-slate-200 text-sm dark:divide-white/10">
                    <thead class="bg-slate-50 dark:bg-white/[0.04]">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Kode</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Sarana</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Kategori</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Lokasi</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Kondisi</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @forelse ($sarana as $item)
                            <tr class="bg-white/70 transition hover:bg-blue-50/60 dark:bg-transparent dark:hover:bg-cyan-500/10">
                                <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $item->kode_sarana }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-700 dark:text-slate-200">{{ $item->nama_sarana }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Tahun {{ $item->tahun_perolehan }}</p>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-300">{{ $item->kategori?->nama_kategori }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-300">
                                    {{ $item->ruangan?->nama_ruangan }}<br>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ $item->ruangan?->gedung?->nama_gedung }} - Lt. {{ $item->ruangan?->lantai ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $item->kondisi_terkini === 'BAIK' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200' }}">
                                        {{ $item->kondisi_terkini }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $item->status_sarana === 'AKTIF' ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-200' : 'bg-slate-200 text-slate-700 dark:bg-slate-600/30 dark:text-slate-200' }}">
                                        {{ $item->status_sarana }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Belum ada data sarana.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $sarana->links() }}
        </div>
    </section>
</x-layouts.sbadmin>
