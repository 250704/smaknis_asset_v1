<x-layouts.sbadmin>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="page-title">Edit Sarana</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Perbarui data sarana dan status operasional.</p>
        </div>
        <a href="{{ route('admin.aset.show', $aset) }}" class="btn-secondary">Kembali</a>
    </div>

    <section class="panel max-w-4xl">
        <form method="POST" action="{{ route('admin.aset.update', $aset) }}" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-2">
            @csrf
            @method('PUT')

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Kode Sarana</label>
                <input type="text" value="{{ $aset->kode_aset }}" disabled class="w-full rounded-xl border-slate-300 bg-slate-100 text-sm text-slate-600 dark:border-white/15 dark:bg-slate-800 dark:text-slate-300">
            </div>

            <div class="md:col-span-2">
                <label for="nama_aset" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nama Sarana</label>
                <input id="nama_aset" name="nama_aset" type="text" value="{{ old('nama_aset', $aset->nama_aset) }}" required placeholder="Isi nama dasar, nomor akan dikelola otomatis" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Saat penggantian sarana, sistem akan memprioritaskan nomor lama jika tersedia.</p>
                @error('nama_aset') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="kategori_id" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Kategori</label>
                <select id="kategori_id" name="kategori_id" required class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                    <option value="">Pilih kategori</option>
                    @foreach ($kategoriList as $kategori)
                        <option value="{{ $kategori->id }}" @selected(old('kategori_id', $aset->kategori_id) == $kategori->id)>{{ $kategori->nama_kategori }}</option>
                    @endforeach
                </select>
                @error('kategori_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="ruangan_id" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Ruangan</label>
                <select id="ruangan_id" name="ruangan_id" required class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                    <option value="">Pilih ruangan</option>
                    @foreach ($ruanganList as $ruangan)
                        <option value="{{ $ruangan->id }}" @selected(old('ruangan_id', $aset->ruangan_id) == $ruangan->id)>{{ $ruangan->nama_ruangan }} [{{ $ruangan->kode_ruangan ?? '---' }}] (Lt. {{ $ruangan->lantai ?? '-' }}) - {{ $ruangan->gedung?->nama_gedung }} [{{ $ruangan->gedung?->kode_gedung ?? '---' }}]</option>
                    @endforeach
                </select>
                @error('ruangan_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="tahun_perolehan" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Tahun Perolehan</label>
                <input id="tahun_perolehan" name="tahun_perolehan" type="number" min="1900" max="{{ date('Y') + 1 }}" value="{{ old('tahun_perolehan', $aset->tahun_perolehan) }}" required class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                @error('tahun_perolehan') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="harga_perolehan" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Harga Perolehan</label>
                <input id="harga_perolehan" name="harga_perolehan" type="number" min="0" step="0.01" value="{{ old('harga_perolehan', $aset->harga_perolehan) }}" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                @error('harga_perolehan') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="kondisi_terkini" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Kondisi</label>
                <select id="kondisi_terkini" name="kondisi_terkini" required class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                    @foreach ($kondisiList as $kondisi)
                        <option value="{{ $kondisi }}" @selected(old('kondisi_terkini', $aset->kondisi_terkini) === $kondisi)>{{ $kondisi }}</option>
                    @endforeach
                </select>
                @error('kondisi_terkini') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="status_aset" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Status</label>
                <select id="status_aset" name="status_aset" required class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                    @foreach ($statusList as $status)
                        <option value="{{ $status }}" @selected(old('status_aset', $aset->status_aset) === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                @error('status_aset') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="foto_aset" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Foto Sarana (Opsional)</label>
                <input id="foto_aset" name="foto_aset" type="file" accept="image/*" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                @error('foto_aset') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            @if ($aset->foto_aset)
                <div class="md:col-span-2 rounded-xl border border-slate-200 p-3 dark:border-white/10">
                    <p class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-200">Foto Saat Ini</p>
                    <img src="{{ asset('storage/' . $aset->foto_aset) }}" alt="Foto aset" class="h-48 rounded-lg object-cover">
                    <label class="mt-3 inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                        <input type="checkbox" name="hapus_foto" value="1" class="rounded border-slate-300 text-rose-600">
                        Hapus foto lama
                    </label>
                </div>
            @endif

            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
                <a href="{{ route('admin.aset.show', $aset) }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </section>
</x-layouts.sbadmin>
