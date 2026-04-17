<x-layouts.sbadmin>
    <div x-data="{ modalOpen: @js($errors->any()) }" class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="page-title">Master Gedung</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Daftar data gedung untuk struktur lokasi aset.</p>
            </div>
            <button type="button" @click="modalOpen = true" class="btn-primary">
                <i class="fas fa-plus mr-2 text-xs"></i>
                Tambah Gedung
            </button>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
                {{ session('error') }}
            </div>
        @endif

        <section class="panel">
            <form method="GET" action="{{ route('admin.master.gedung.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="relative flex-1">
                    <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input
                        type="text"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Cari nama atau kode gedung..."
                        class="w-full rounded-xl border-slate-300 bg-white pl-9 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                    >
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="btn-secondary">Cari</button>
                    @if ($search !== '')
                        <a href="{{ route('admin.master.gedung.index') }}" class="btn-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </section>

        <section class="panel overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] divide-y divide-slate-200 text-sm dark:divide-white/10">
                    <thead class="bg-slate-50 dark:bg-white/[0.04]">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Kode Gedung</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Nama Gedung</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Jumlah Ruangan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @forelse ($gedung as $index => $item)
                            <tr class="bg-white/70 transition hover:bg-blue-50/60 dark:bg-transparent dark:hover:bg-cyan-500/10">
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                    {{ $gedung->firstItem() + $index }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                        {{ $item->kode_gedung }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-200">{{ $item->nama_gedung }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $item->ruangan_count }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.master.gedung.edit', $item) }}" class="btn-secondary">Edit</a>
                                        <form action="{{ route('admin.master.gedung.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus data gedung ini? Gedung yang masih memiliki ruangan tidak dapat dihapus.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-secondary text-rose-600 dark:text-rose-300">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Belum ada data gedung.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($gedung->hasPages())
            <div>
                {{ $gedung->links() }}
            </div>
        @endif

        <div
            x-show="modalOpen"
            x-cloak
            @keydown.escape.window="modalOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
            style="display: none;"
        >
            <div
                x-show="modalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                @click.outside="modalOpen = false"
                class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-white/10 dark:bg-slate-900"
            >
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tambah Gedung</h3>
                    <button type="button" @click="modalOpen = false" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-white/10 dark:hover:text-slate-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.master.gedung.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="nama_gedung" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Nama Gedung</label>
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
                        <label for="kode_gedung" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Kode Gedung (3 karakter)</label>
                        <input
                            id="kode_gedung"
                            name="kode_gedung"
                            type="text"
                            maxlength="3"
                            value="{{ old('kode_gedung') }}"
                            class="w-full rounded-xl border-slate-300 bg-white text-sm uppercase text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                            required
                        >
                        @error('kode_gedung')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <button type="button" @click="modalOpen = false" class="btn-secondary w-full">Batal</button>
                        <button type="submit" class="btn-primary w-full justify-center">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.sbadmin>
