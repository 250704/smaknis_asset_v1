<x-layouts.sbadmin>
    <div class="mb-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="page-title">Input Sarana Per Unit</h1>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.sarana.create') }}" class="btn-secondary">
                    <i class="mr-2 text-xs fas fa-arrow-left"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="px-4 py-3 mb-6 text-sm border rounded-xl border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="w-full">
        <div class="panel">
            <form method="POST" action="{{ route('admin.sarana.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                {{-- Informasi Dasar --}}
                <div>
                    <h3 class="flex items-center gap-2 mb-4 text-sm font-semibold text-slate-700 dark:text-slate-200">
                        <i class="text-blue-500 fas fa-info-circle"></i>
                        Informasi Dasar
                    </h3>
                    <div class="grid gap-4 md:grid-cols-2">
                        {{-- <div class="md:col-span-2">
                            <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200">Format Kode Sarana (Otomatis)</label>
                            <input type="text" value="{{ $kodeSaranaPattern }}" disabled class="w-full text-sm rounded-xl border-slate-300 bg-slate-100 text-slate-600 dark:border-white/15 dark:bg-slate-800 dark:text-slate-300">
                        </div> --}}
                        <div class="md:col-span-2">
                            <label for="nama_sarana" class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200">Nama Sarana <span class="text-rose-500">*</span></label>
                            <input id="nama_sarana" name="nama_sarana" type="text" value="{{ old('nama_sarana') }}" required placeholder="Contoh: PC All in One" class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                            @error('nama_sarana') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Klasifikasi --}}
                <div class="pt-5 border-t border-slate-200/50 dark:border-white/10">
                    <h3 class="flex items-center gap-2 mb-4 text-sm font-semibold text-slate-700 dark:text-slate-200">
                        <i class="text-blue-500 fas fa-tags"></i>
                        Klasifikasi
                    </h3>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="kategori_id" class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200">Kategori <span class="text-rose-500">*</span></label>
                            <select id="kategori_id" name="kategori_id" required class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                                <option value="">Pilih kategori</option>
                                @foreach ($kategoriList as $kategori)
                                    <option value="{{ $kategori->id }}" @selected(old('kategori_id') == $kategori->id)>{{ $kategori->nama_kategori }}</option>
                                @endforeach
                            </select>
                            @error('kategori_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="ruangan_id" class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200">Ruangan <span class="text-rose-500">*</span></label>
                            <select id="ruangan_id" name="ruangan_id" required class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                                <option value="">Pilih ruangan</option>
                                @foreach ($ruanganList as $ruangan)
                                    <option value="{{ $ruangan->id }}" @selected(old('ruangan_id') == $ruangan->id)>{{ $ruangan->nama_ruangan }} - {{ $ruangan->gedung?->nama_gedung }}</option>
                                @endforeach
                            </select>
                            @error('ruangan_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Detail Sarana --}}
                <div class="pt-5 border-t border-slate-200/50 dark:border-white/10">
                    <h3 class="flex items-center gap-2 mb-4 text-sm font-semibold text-slate-700 dark:text-slate-200">
                        <i class="text-blue-500 fas fa-clipboard-list"></i>
                        Detail Sarana
                    </h3>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label for="tahun_perolehan" class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200">Tahun Perolehan <span class="text-rose-500">*</span></label>
                            <input id="tahun_perolehan" name="tahun_perolehan" type="number" min="1900" max="{{ date('Y') + 1 }}" value="{{ old('tahun_perolehan', date('Y')) }}" required class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                            @error('tahun_perolehan') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="harga_perolehan" class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200">Harga Perolehan (Rp)</label>
                            <input id="harga_perolehan" name="harga_perolehan" type="number" min="0" step="0.01" value="{{ old('harga_perolehan') }}" placeholder="0" class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                            @error('harga_perolehan') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="kondisi_terkini" class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200">Kondisi <span class="text-rose-500">*</span></label>
                            <select id="kondisi_terkini" name="kondisi_terkini" required class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                                @foreach ($kondisiList as $kondisi)
                                    <option value="{{ $kondisi }}" @selected(old('kondisi_terkini', 'BAIK') === $kondisi)>{{ $kondisi }}</option>
                                @endforeach
                            </select>
                            @error('kondisi_terkini') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="status_sarana" class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200">Status <span class="text-rose-500">*</span></label>
                            <select id="status_sarana" name="status_sarana" required class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                                @foreach ($statusList as $status)
                                    <option value="{{ $status }}" @selected(old('status_sarana', 'AKTIF') === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                            @error('status_sarana') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="flex items-center justify-end gap-2 pt-5 border-t border-slate-200/50 dark:border-white/10">
                    <button type="reset" class="btn-secondary">
                        <i class="mr-2 text-xs fas fa-undo"></i>Reset
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class="mr-2 text-xs fas fa-save"></i>Simpan Sarana
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.sbadmin>
