<x-layouts.sbadmin>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="page-title">Data Aset</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Daftar aset aktif untuk monitoring kepala sarana.</p>
        </div>
        <a href="{{ route('kepala_sarana.aset.histori') }}" class="btn-secondary">Lihat Histori Aset</a>
    </div>

    <section class="panel">
        <form method="GET" action="{{ route('kepala_sarana.aset.index') }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div class="xl:col-span-2">
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200" for="q">Cari Aset</label>
                <input
                    type="text"
                    id="q"
                    name="q"
                    value="{{ $filters['q'] }}"
                    placeholder="Kode aset atau nama aset..."
                    class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                >
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200" for="kategori_id">Kategori</label>
                <select id="kategori_id" name="kategori_id" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                    <option value="">Semua kategori</option>
                    @foreach ($kategoriList as $kategori)
                        <option value="{{ $kategori->id }}" @selected((string) $filters['kategori_id'] === (string) $kategori->id)>{{ $kategori->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200" for="gedung_id">Gedung</label>
                <select id="gedung_id" name="gedung_id" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                    <option value="">Semua gedung</option>
                    @foreach ($gedungList as $gedung)
                        <option value="{{ $gedung->id }}" @selected((string) $filters['gedung_id'] === (string) $gedung->id)>{{ $gedung->nama_gedung }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200" for="ruangan_id">Ruangan</label>
                <select id="ruangan_id" name="ruangan_id" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                    <option value="">Semua ruangan</option>
                    @foreach ($ruanganList as $ruangan)
                        <option value="{{ $ruangan->id }}" @selected((string) $filters['ruangan_id'] === (string) $ruangan->id)>{{ $ruangan->nama_ruangan }} - {{ $ruangan->gedung?->nama_gedung }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200" for="kondisi_terkini">Kondisi</label>
                <select id="kondisi_terkini" name="kondisi_terkini" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                    <option value="">Semua kondisi</option>
                    @foreach ($kondisiList as $kondisi)
                        <option value="{{ $kondisi }}" @selected((string) $filters['kondisi_terkini'] === (string) $kondisi)>{{ $kondisi }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200" for="status_aset">Status</label>
                <select id="status_aset" name="status_aset" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                    <option value="">Semua status</option>
                    @foreach ($statusList as $status)
                        <option value="{{ $status }}" @selected((string) $filters['status_aset'] === (string) $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2 xl:col-span-4">
                <button type="submit" class="btn-primary">Terapkan Filter</button>
                <a href="{{ route('kepala_sarana.aset.index') }}" class="btn-secondary">Reset</a>
            </div>
        </form>
    </section>

    <section class="panel mt-5">
        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-white/10">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-white/10">
                <thead class="bg-slate-50 dark:bg-white/[0.04]">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Kode</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Aset</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Kategori</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Lokasi</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Kondisi</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse ($aset as $item)
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-300">{{ $item->kode_aset }}</td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-700 dark:text-slate-200">{{ $item->nama_aset }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Tahun {{ $item->tahun_perolehan }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $item->kategori?->nama_kategori }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                {{ $item->ruangan?->nama_ruangan }}<br>
                                <span class="text-xs text-slate-500 dark:text-slate-400">{{ $item->ruangan?->gedung?->nama_gedung }} - Lt. {{ $item->ruangan?->lantai ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $item->kondisi_terkini === 'BAIK' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200' }}">
                                    {{ $item->kondisi_terkini }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $item->status_aset === 'AKTIF' ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-200' : 'bg-slate-200 text-slate-700 dark:bg-slate-600/30 dark:text-slate-200' }}">
                                    {{ $item->status_aset }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('kepala_sarana.scan', ['kode_aset' => $item->kode_aset]) }}" class="btn-secondary">Lihat</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">Belum ada data aset.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $aset->links() }}
        </div>
    </section>
</x-layouts.sbadmin>
