<x-layouts.sbadmin>
    @php
        $openTambahModal = $errors->has('gedung_id') || $errors->has('nama_ruangan') || $errors->has('lantai');
    @endphp

    <div class="mb-6">
        <h1 class="page-title">Master Ruangan</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kelola ruangan berdasarkan gedung sebagai lokasi aset aktif.</p>
    </div>

    @if (session('success'))
        <div class="px-4 py-3 mb-4 text-sm border rounded-xl border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="px-4 py-3 mb-4 text-sm border rounded-xl border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
            {{ session('error') }}
        </div>
    @endif

    <div x-data="{ openTambahModal: {{ $openTambahModal ? 'true' : 'false' }} }" class="space-y-5">
        <div class="flex justify-end">
            <button type="button" @click="openTambahModal = true" class="btn-primary whitespace-nowrap">
                <i class="mr-2 fas fa-plus"></i>Tambah Ruangan
            </button>
        </div>

        <section class="panel">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Daftar Ruangan</h2>
                <form method="GET" action="{{ route('admin.master.ruangan.index') }}" class="flex w-full gap-2 sm:w-auto">
                    <input
                        type="text"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Cari ruangan / kode / gedung..."
                        class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40 sm:w-72"
                    >
                    <button type="submit" class="btn-secondary whitespace-nowrap">Cari</button>
                </form>
            </div>

            <div class="mt-4 overflow-x-auto border rounded-xl border-slate-200 dark:border-white/10">
                <table class="min-w-full text-sm divide-y divide-slate-200 dark:divide-white/10">
                    <thead class="bg-slate-50 dark:bg-white/[0.04]">
                        <tr>
                            <th class="w-16 px-4 py-3 font-semibold text-left text-slate-600 dark:text-slate-300">No</th>
                            <th class="px-4 py-3 font-semibold text-left text-slate-600 dark:text-slate-300">Ruangan</th>
                            <th class="w-44 px-4 py-3 font-semibold text-left text-slate-600 dark:text-slate-300">Kode</th>
                            <th class="px-4 py-3 font-semibold text-left text-slate-600 dark:text-slate-300">Gedung</th>
                            <th class="w-28 px-4 py-3 font-semibold text-left text-slate-600 dark:text-slate-300">Lantai</th>
                            <th class="w-52 py-3 pl-4 pr-4 font-semibold text-right text-slate-600 dark:text-slate-300">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-transparent divide-y divide-slate-100 dark:divide-white/5">
                        @forelse ($ruangan as $item)
                            <tr x-data="{ openEditModal: false }">
                                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ $ruangan->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-200">{{ $item->nama_ruangan }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300"><span class="px-2 py-1 text-xs font-semibold rounded-lg bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $item->kode_ruangan }}</span></td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $item->gedung?->nama_gedung }} <span class="text-xs text-slate-400">({{ $item->gedung?->kode_gedung }})</span></td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">Lt. {{ $item->lantai ?? '-' }}</td>
                                <td class="py-3 pl-4 pr-4">
                                    <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                        <button type="button" class="btn-muted whitespace-nowrap" @click="openEditModal = true">Edit</button>
                                        <form
                                            action="{{ route('admin.master.ruangan.destroy', $item) }}"
                                            method="POST"
                                            class="shrink-0"
                                            data-confirm-title="Konfirmasi Hapus Ruangan"
                                            data-confirm-message="Apakah Anda yakin ingin menghapus data ruangan ini?"
                                            data-confirm-confirm-label="Ya, Hapus"
                                            data-confirm-variant="danger"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger whitespace-nowrap">Hapus</button>
                                        </form>
                                    </div>

                                    <div
                                        x-cloak
                                        x-show="openEditModal"
                                        x-transition.opacity
                                        class="fixed inset-0 z-[90] flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-sm"
                                        @keydown.escape.window="openEditModal = false"
                                    >
                                        <div
                                            x-show="openEditModal"
                                            x-transition
                                            class="w-full max-w-3xl panel"
                                            @click.outside="openEditModal = false"
                                        >
                                            <div class="flex items-start justify-between gap-4">
                                                <div>
                                                    <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Edit Ruangan</h2>
                                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Perbarui data ruangan, kode akan disesuaikan otomatis.</p>
                                                </div>
                                                <button type="button" class="btn-secondary btn-sm" @click="openEditModal = false">Tutup</button>
                                            </div>

                                            <form method="POST" action="{{ route('admin.master.ruangan.update', $item) }}" class="grid gap-4 mt-4 md:grid-cols-12">
                                                @csrf
                                                @method('PATCH')
                                                <div class="md:col-span-4">
                                                    <label for="edit_gedung_id_{{ $item->id }}" class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200">Gedung</label>
                                                    <select
                                                        id="edit_gedung_id_{{ $item->id }}"
                                                        name="gedung_id"
                                                        class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                                                        required
                                                    >
                                                        <option value="">Pilih gedung</option>
                                                        @foreach ($gedungList as $gedung)
                                                            <option value="{{ $gedung->id }}" @selected($item->gedung_id == $gedung->id)>
                                                                {{ $gedung->nama_gedung }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="md:col-span-5">
                                                    <label for="edit_nama_ruangan_{{ $item->id }}" class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200">Nama Ruangan</label>
                                                    <input
                                                        id="edit_nama_ruangan_{{ $item->id }}"
                                                        name="nama_ruangan"
                                                        type="text"
                                                        value="{{ $item->nama_ruangan }}"
                                                        class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                                                        required
                                                    >
                                                </div>

                                                <div class="md:col-span-3">
                                                    <label for="edit_lantai_{{ $item->id }}" class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200">Lantai</label>
                                                    <input
                                                        id="edit_lantai_{{ $item->id }}"
                                                        name="lantai"
                                                        type="number"
                                                        min="1"
                                                        max="99"
                                                        value="{{ $item->lantai }}"
                                                        class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                                                        required
                                                    >
                                                </div>

                                                <div class="px-4 py-3 text-xs border md:col-span-8 rounded-xl border-slate-200 bg-slate-50 text-slate-600 dark:border-white/10 dark:bg-slate-900/40 dark:text-slate-300">
                                                    Kode ruangan dibuat otomatis oleh sistem dengan format:
                                                    <span class="font-mono font-semibold">KDG-Lxx-RNG</span>
                                                    (contoh: <span class="font-mono">GDU-L02-LAB</span>).
                                                </div>

                                                <div class="md:col-span-4 md:flex md:items-end">
                                                    <button type="submit" class="w-full btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-sm text-center text-slate-500 dark:text-slate-400">Belum ada data ruangan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $ruangan->links() }}
            </div>
        </section>

        <div
            x-cloak
            x-show="openTambahModal"
            x-transition.opacity
            class="fixed inset-0 z-[80] flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-sm"
            @keydown.escape.window="openTambahModal = false"
        >
            <div
                x-show="openTambahModal"
                x-transition
                class="w-full max-w-3xl panel"
                @click.outside="openTambahModal = false"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Tambah Ruangan</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Isi data ruangan baru, kode akan dibuat otomatis.</p>
                    </div>
                    <button type="button" class="btn-secondary btn-sm" @click="openTambahModal = false">Tutup</button>
                </div>

                <form method="POST" action="{{ route('admin.master.ruangan.store') }}" class="grid gap-4 mt-4 md:grid-cols-12">
                    @csrf
                    <div class="md:col-span-4">
                        <label for="gedung_id" class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200">Gedung</label>
                        <select
                            id="gedung_id"
                            name="gedung_id"
                            class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                            required
                        >
                            <option value="">Pilih gedung</option>
                            @foreach ($gedungList as $gedung)
                                <option value="{{ $gedung->id }}" @selected(old('gedung_id') == $gedung->id)>
                                    {{ $gedung->nama_gedung }}
                                </option>
                            @endforeach
                        </select>
                        @error('gedung_id')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-5">
                        <label for="nama_ruangan" class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200">Nama Ruangan</label>
                        <input
                            id="nama_ruangan"
                            name="nama_ruangan"
                            type="text"
                            value="{{ old('nama_ruangan') }}"
                            class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                            required
                        >
                        @error('nama_ruangan')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-3">
                        <label for="lantai" class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200">Lantai</label>
                        <input
                            id="lantai"
                            name="lantai"
                            type="number"
                            min="1"
                            max="99"
                            value="{{ old('lantai', 1) }}"
                            class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                            required
                        >
                        @error('lantai')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="px-4 py-3 text-xs border md:col-span-8 rounded-xl border-slate-200 bg-slate-50 text-slate-600 dark:border-white/10 dark:bg-slate-900/40 dark:text-slate-300">
                        Kode ruangan dibuat otomatis oleh sistem dengan format:
                        <span class="font-mono font-semibold">KDG-Lxx-RNG</span>
                        (contoh: <span class="font-mono">GDU-L02-LAB</span>).
                    </div>

                    <div class="md:col-span-4 md:flex md:items-end">
                        <button type="submit" class="w-full btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.sbadmin>
