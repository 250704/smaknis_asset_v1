<x-layouts.sbadmin>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="page-title">Edit Ruangan</h1>
        <a href="{{ route('admin.master.ruangan.index') }}" class="btn-secondary">Kembali</a>
    </div>

    <section class="panel max-w-2xl">
        <form method="POST" action="{{ route('admin.master.ruangan.update', $ruangan) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="gedung_id" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Gedung</label>
                <select
                    id="gedung_id"
                    name="gedung_id"
                    class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                    required
                >
                    <option value="">Pilih gedung</option>
                    @foreach ($gedungList as $gedung)
                        <option value="{{ $gedung->id }}" @selected(old('gedung_id', $ruangan->gedung_id) == $gedung->id)>
                            {{ $gedung->nama_gedung }}
                        </option>
                    @endforeach
                </select>
                @error('gedung_id')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="nama_ruangan" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nama Ruangan</label>
                <input
                    id="nama_ruangan"
                    name="nama_ruangan"
                    type="text"
                    value="{{ old('nama_ruangan', $ruangan->nama_ruangan) }}"
                    class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                    required
                >
                @error('nama_ruangan')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="kode_ruangan" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Kode Ruangan (3 karakter)</label>
                <input
                    id="kode_ruangan"
                    name="kode_ruangan"
                    type="text"
                    maxlength="3"
                    value="{{ old('kode_ruangan', $ruangan->kode_ruangan) }}"
                    class="w-full rounded-xl border-slate-300 bg-white text-sm uppercase text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                    required
                >
                @error('kode_ruangan')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="lantai" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Lantai</label>
                <input
                    id="lantai"
                    name="lantai"
                    type="number"
                    min="1"
                    max="99"
                    value="{{ old('lantai', $ruangan->lantai) }}"
                    class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                    required
                >
                @error('lantai')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary">Update</button>
        </form>
    </section>
</x-layouts.sbadmin>



