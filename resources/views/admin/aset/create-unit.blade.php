<x-layouts.sbadmin>
    <div class="mb-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="page-title">Input Aset Per Unit</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Form penambahan aset individual</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.aset.create') }}" class="btn-secondary">
                    <i class="fas fa-arrow-left mr-2 text-xs"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-12">
        {{-- Form --}}
        <div class="xl:col-span-8">
            <div class="panel">
                <form method="POST" action="{{ route('admin.aset.store') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    
                    {{-- Informasi Dasar --}}
                    <div>
                        <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200">
                            <i class="fas fa-info-circle text-blue-500"></i>
                            Informasi Dasar
                        </h3>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Format Kode Aset (Otomatis)</label>
                                <input type="text" value="{{ $kodeAsetPattern }}" disabled class="w-full rounded-xl border-slate-300 bg-slate-100 text-sm text-slate-600 dark:border-white/15 dark:bg-slate-800 dark:text-slate-300">
                            </div>
                            <div class="md:col-span-2">
                                <label for="nama_aset" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Nama Aset <span class="text-rose-500">*</span></label>
                                <input id="nama_aset" name="nama_aset" type="text" value="{{ old('nama_aset') }}" required placeholder="Contoh: PC All in One" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                                @error('nama_aset') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Klasifikasi --}}
                    <div>
                        <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200">
                            <i class="fas fa-tags text-blue-500"></i>
                            Klasifikasi
                        </h3>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="kategori_id" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Kategori <span class="text-rose-500">*</span></label>
                                <select id="kategori_id" name="kategori_id" required class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                                    <option value="">Pilih kategori</option>
                                    @foreach ($kategoriList as $kategori)
                                        <option value="{{ $kategori->id }}" @selected(old('kategori_id') == $kategori->id)>{{ $kategori->nama_kategori }}</option>
                                    @endforeach
                                </select>
                                @error('kategori_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="ruangan_id" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Ruangan <span class="text-rose-500">*</span></label>
                                <select id="ruangan_id" name="ruangan_id" required class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                                    <option value="">Pilih ruangan</option>
                                    @foreach ($ruanganList as $ruangan)
                                        <option value="{{ $ruangan->id }}" @selected(old('ruangan_id') == $ruangan->id)>{{ $ruangan->nama_ruangan }} - {{ $ruangan->gedung?->nama_gedung }}</option>
                                    @endforeach
                                </select>
                                @error('ruangan_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Detail Aset --}}
                    <div>
                        <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200">
                            <i class="fas fa-clipboard-list text-blue-500"></i>
                            Detail Aset
                        </h3>
                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label for="tahun_perolehan" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Tahun Perolehan <span class="text-rose-500">*</span></label>
                                <input id="tahun_perolehan" name="tahun_perolehan" type="number" min="1900" max="{{ date('Y') + 1 }}" value="{{ old('tahun_perolehan', date('Y')) }}" required class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                                @error('tahun_perolehan') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="harga_perolehan" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Harga Perolehan (Rp)</label>
                                <input id="harga_perolehan" name="harga_perolehan" type="number" min="0" step="0.01" value="{{ old('harga_perolehan') }}" placeholder="0" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                                @error('harga_perolehan') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="kondisi_terkini" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Kondisi <span class="text-rose-500">*</span></label>
                                <select id="kondisi_terkini" name="kondisi_terkini" required class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                                    @foreach ($kondisiList as $kondisi)
                                        <option value="{{ $kondisi }}" @selected(old('kondisi_terkini', 'BAIK') === $kondisi)>{{ $kondisi }}</option>
                                    @endforeach
                                </select>
                                @error('kondisi_terkini') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="status_aset" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Status <span class="text-rose-500">*</span></label>
                                <select id="status_aset" name="status_aset" required class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                                    @foreach ($statusList as $status)
                                        <option value="{{ $status }}" @selected(old('status_aset', 'AKTIF') === $status)>{{ $status }}</option>
                                    @endforeach
                                </select>
                                @error('status_aset') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Foto Aset --}}
                    <div>
                        <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200">
                            <i class="fas fa-camera text-blue-500"></i>
                            Foto Aset
                        </h3>
                        <div class="rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-6 text-center dark:border-white/10 dark:bg-slate-900/50">
                            <label for="foto_aset" class="cursor-pointer">
                                <i class="fas fa-cloud-upload-alt text-3xl text-slate-400"></i>
                                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                                    <span class="font-semibold text-blue-600 dark:text-blue-400">Klik untuk upload</span> atau drag & drop
                                </p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-500">PNG, JPG, GIF (Max. 5MB)</p>
                                <input id="foto_aset" name="foto_aset" type="file" accept="image/*" class="sr-only">
                            </label>
                            @error('foto_aset') <p class="mt-3 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-5 dark:border-white/10">
                        <button type="reset" class="btn-secondary">
                            <i class="fas fa-undo mr-2 text-xs"></i>Reset
                        </button>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save mr-2 text-xs"></i>Simpan Aset
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Preview --}}
        <aside class="xl:col-span-4">
            <div class="panel sticky top-6">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Preview Kode Aset</h3>
                <div class="mt-3 space-y-3">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-slate-900/60">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Format Kode</p>
                        <p id="preview-kode" class="mt-1 font-mono text-lg font-bold text-slate-800 dark:text-slate-100">{{ $kodeAsetPattern }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-slate-900/60">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Nama Aset</p>
                        <p id="preview-nama" class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-100">-</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-slate-900/60">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Lokasi</p>
                        <p id="preview-lokasi" class="mt-1 text-sm text-slate-800 dark:text-slate-100">-</p>
                    </div>
                </div>

                <div class="mt-4 rounded-xl bg-blue-500/10 p-4">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-lightbulb mt-0.5 text-yellow-500"></i>
                        <div class="text-sm text-slate-700 dark:text-slate-300">
                            <p class="font-semibold">Tips</p>
                            <p class="mt-1 text-xs">Ingin input banyak aset sekaligus? Gunakan fitur <strong>Import Massal Excel</strong> untuk efisiensi waktu.</p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <script>
        (function() {
            const namaInput = document.getElementById('nama_aset');
            const kategoriSelect = document.getElementById('kategori_id');
            const ruanganSelect = document.getElementById('ruangan_id');
            const previewKode = document.getElementById('preview-kode');
            const previewNama = document.getElementById('preview-nama');
            const previewLokasi = document.getElementById('preview-lokasi');

            function updatePreview() {
                const nama = namaInput?.value || '-';
                const kategori = kategoriSelect?.options[kategoriSelect?.selectedIndex]?.text || '-';
                const ruangan = ruanganSelect?.options[ruanganSelect?.selectedIndex]?.text || '-';

                if (previewNama) previewNama.textContent = nama;
                if (previewLokasi) previewLokasi.textContent = ruangan !== '-' ? ruangan : '-';
                
                // Update kode preview dengan nama aset
                if (previewKode && nama !== '-') {
                    const base = nama.trim().replace(/\d+$/, '').trim() || 'aset';
                    previewKode.textContent = `${base.toLowerCase().replace(/\s+/g, '-')}01`;
                }
            }

            [namaInput, kategoriSelect, ruanganSelect].forEach(el => {
                if (el) el.addEventListener('input', updatePreview);
                if (el) el.addEventListener('change', updatePreview);
            });
        })();
    </script>
</x-layouts.sbadmin>
