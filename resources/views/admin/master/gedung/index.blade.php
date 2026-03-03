<x-layouts.sbadmin>
    <div class="mb-6">
        <h1 class="page-title">Master Gedung</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kelola referensi gedung untuk struktur lokasi aset.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-5 lg:grid-cols-12">
        <section class="panel lg:col-span-4">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Tambah Gedung</h2>
            <form method="POST" action="{{ route('admin.master.gedung.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label for="nama_gedung" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nama Gedung</label>
                    <input
                        id="nama_gedung"
                        name="nama_gedung"
                        type="text"
                        value="{{ old('nama_gedung') }}"
                        class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                        required
                    >
                    @error('nama_gedung')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="kode_gedung" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Kode Gedung (3 karakter)</label>
                    <input
                        id="kode_gedung"
                        name="kode_gedung"
                        type="text"
                        maxlength="3"
                        value="{{ old('kode_gedung') }}"
                        class="w-full rounded-xl border-slate-300 bg-white text-sm uppercase text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                        placeholder="GDA"
                        required
                    >
                    @error('kode_gedung')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="btn-primary w-full">Simpan</button>
            </form>
        </section>

        <section class="panel lg:col-span-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Daftar Gedung</h2>
                <form method="GET" action="{{ route('admin.master.gedung.index') }}" class="flex w-full gap-2 sm:w-auto">
                    <input
                        type="text"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Cari nama / kode gedung..."
                        class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40 sm:w-64"
                    >
                    <button type="submit" class="btn-secondary">Cari</button>
                </form>
            </div>

            <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 dark:border-white/10">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-white/10 text-sm">
                    <thead class="bg-slate-50 dark:bg-white/[0.04]">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">No</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Nama Gedung</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Kode</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5 bg-transparent">
                        @forelse ($gedung as $item)
                            <tr>
                                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ $gedung->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-200">{{ $item->nama_gedung }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300"><span class="rounded-lg bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $item->kode_gedung }}</span></td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('admin.master.gedung.edit', $item) }}" class="btn-secondary">Edit</a>
                                        <form action="{{ route('admin.master.gedung.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus data gedung ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">Belum ada data gedung.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $gedung->links() }}
            </div>
        </section>
    </div>
</x-layouts.sbadmin>



