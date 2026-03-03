<x-layouts.sbadmin>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="page-title">Edit Gedung</h1>
        <a href="{{ route('admin.master.gedung.index') }}" class="btn-secondary">Kembali</a>
    </div>

    <section class="panel max-w-2xl">
        <form method="POST" action="{{ route('admin.master.gedung.update', $gedung) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="nama_gedung" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nama Gedung</label>
                <input
                    id="nama_gedung"
                    name="nama_gedung"
                    type="text"
                    value="{{ old('nama_gedung', $gedung->nama_gedung) }}"
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
                    value="{{ old('kode_gedung', $gedung->kode_gedung) }}"
                    class="w-full rounded-xl border-slate-300 bg-white text-sm uppercase text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                    required
                >
                @error('kode_gedung')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary">Update</button>
        </form>
    </section>
</x-layouts.sbadmin>



