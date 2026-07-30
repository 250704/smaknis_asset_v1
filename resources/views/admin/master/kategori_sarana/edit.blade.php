<x-layouts.sbadmin>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="page-title">Edit Kategori Sarana</h1>
        <a href="{{ route('admin.master.kategori-sarana.index') }}" class="btn-secondary">Kembali</a>
    </div>

    <section class="panel max-w-2xl">
        <form method="POST" action="{{ route('admin.master.kategori-sarana.update', $kategorisarana) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="nama_kategori" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nama Kategori</label>
                <input
                    id="nama_kategori"
                    name="nama_kategori"
                    type="text"
                    value="{{ old('nama_kategori', $kategorisarana->nama_kategori) }}"
                    class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                    required
                >
                @error('nama_kategori')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary">Update</button>
        </form>
    </section>
</x-layouts.sbadmin>



