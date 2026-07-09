<x-layouts.sbadmin>
    <div class="mb-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="page-title">Buat Pengajuan</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Ajukan pengadaan sarana baru sesuai kebutuhan sekolah.</p>
            </div>
            <a href="{{ $indexRoute ?? route('guru.pengajuan.index') }}" class="btn-secondary">
                <i class="fas fa-history mr-2 text-xs"></i>
                Daftar Pengajuan
            </a>
        </div>
    </div>

    <form method="POST" action="{{ $storeRoute ?? route('guru.pengajuan.store') }}" enctype="multipart/form-data" class="panel space-y-4" id="form-pengajuan">
        @csrf

        {{-- Section 1: INFORMASI PENGAJUAN --}}
        <div class="rounded-2xl border border-blue-200 bg-blue-50/50 p-5 dark:border-blue-700 dark:bg-blue-900/20">
            <h3 class="mb-3 flex items-center gap-2 text-base font-bold text-blue-800 dark:text-blue-200">
                <i class="fas fa-info-circle text-blue-600"></i>
                1. Informasi Pengajuan
            </h3>
            
            <div class="grid gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                        Judul Pengajuan <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="judul_pengajuan"
                        value="{{ old('judul_pengajuan') }}"
                        placeholder="Contoh: Pengadaan PC Baru untuk Lab Komputer, Pengadaan Meja Kursi Ruang Kelas"
                        class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                        required
                    >
                    @error('judul_pengajuan')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                        Deskripsi Kebutuhan <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        name="deskripsi"
                        rows="4"
                        placeholder="Jelaskan kebutuhan pengadaan ini, alasan, manfaat, dan spesifikasi umum yang diinginkan."
                        class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                        required
                    >{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Section 2: RINCIAN PENGADAAN --}}
        <div class="rounded-2xl border border-amber-200 bg-amber-50/50 p-5 dark:border-amber-700 dark:bg-amber-900/20" id="section-pengadaan">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="flex items-center gap-2 text-base font-bold text-amber-800 dark:text-amber-200">
                        <i class="fas fa-list text-amber-600"></i>
                        2. Rincian Barang yang Diajukan
                    </h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400">Isi semua barang yang diperlukan (minimal 1 item).</p>
                </div>
                <button type="button" id="btn-add-item" class="btn-secondary">
                    <i class="fas fa-plus mr-1 text-xs"></i>
                    Tambah Item
                </button>
            </div>

            @error('items')
                <p class="mb-3 text-xs text-rose-600">{{ $message }}</p>
            @enderror

            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900/60">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-100 text-xs font-bold uppercase tracking-wide text-slate-600 dark:bg-white/[0.04] dark:text-slate-300">
                        <tr>
                            <th class="px-3 py-2 text-left">Nama Barang</th>
                            <th class="px-3 py-2 text-left">Kategori</th>
                            <th class="px-3 py-2 text-left">Ruangan</th>
                            <th class="px-3 py-2 text-center">Jml</th>
                            <th class="px-3 py-2 text-right">Harga Satuan</th>
                            <th class="px-3 py-2 text-left">Spesifikasi</th>
                            <th class="px-3 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="items-body" class="divide-y divide-slate-100 dark:divide-white/5"></tbody>
                </table>
            </div>
        </div>

        {{-- Section 3: ESTIMASI & LAMPIRAN --}}
        <div class="rounded-2xl border border-slate-200 p-5 dark:border-white/10">
            <h3 class="mb-3 flex items-center gap-2 text-base font-bold text-slate-700 dark:text-slate-200">
                <i class="fas fa-file-invoice text-cyan-500"></i>
                3. Estimasi & Lampiran
            </h3>

            <div class="space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                            Estimasi Total Biaya <span class="text-slate-400">(Opsional)</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">Rp</span>
                            <input
                                type="number"
                                name="estimasi_biaya"
                                value="{{ old('estimasi_biaya') }}"
                                min="0"
                                step="1000"
                                placeholder="Auto-fill dari rincian jika kosong"
                                class="w-full rounded-xl border-slate-300 bg-white pl-8 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                            >
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Akan dihitung otomatis dari rincian jika dikosongkan</p>
                        @error('estimasi_biaya')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                            Target Realisasi <span class="text-slate-400">(Opsional)</span>
                        </label>
                        <input
                            type="date"
                            name="target_realisasi"
                            value="{{ old('target_realisasi') }}"
                            class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                        >
                        @error('target_realisasi')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                        Lampiran Dokumen <span class="text-slate-400">(Opsional)</span>
                    </label>
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 dark:border-white/10 dark:bg-slate-900/40">
                        <input
                            type="file"
                            name="lampiran[]"
                            multiple
                            class="w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-500 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white file:hover:bg-blue-600 dark:text-slate-300 dark:file:bg-cyan-600 dark:file:hover:bg-cyan-700"
                            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                        >
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            <i class="fas fa-info-circle mr-1"></i>
                            Maksimal 5 file. Format: JPG, JPEG, PNG, PDF, DOC, DOCX
                        </p>
                        @error('lampiran')
                            <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                        @error('lampiran.*')
                            <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 pt-4 dark:border-white/10">
            <a href="{{ $indexRoute ?? route('guru.pengajuan.index') }}" class="btn-secondary">
                Batal
            </a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-paper-plane mr-2 text-xs"></i>
                Kirim Pengajuan
            </button>
        </div>
    </form>

    <script>
        (function () {
            const sectionPengadaan = document.getElementById('section-pengadaan');
            const itemsBody = document.getElementById('items-body');
            const btnAddItem = document.getElementById('btn-add-item');
            
            const kategoriOptions = `
                @foreach ($kategoriList as $kategori)
                    <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
                @endforeach
            `;
            
            const ruanganOptions = `
                @foreach ($ruanganList as $ruangan)
                    <option value="{{ $ruangan->id }}">{{ $ruangan->nama_ruangan }} - {{ $ruangan->gedung?->nama_gedung }}</option>
                @endforeach
            `;

            function createRow(index) {
                const row = document.createElement('tr');
                row.className = 'hover:bg-slate-50 dark:hover:bg-white/[0.02]';
                row.innerHTML = `
                    <td class="px-3 py-2">
                        <input type="text" name="items[${index}][nama_aset_rencana]" class="w-40 rounded-lg border-slate-300 bg-white px-2 py-1.5 text-xs text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40" placeholder="Nama barang" required>
                    </td>
                    <td class="px-3 py-2">
                        <select name="items[${index}][kategori_id]" class="w-40 rounded-lg border-slate-300 bg-white px-2 py-1.5 text-xs text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40" required>
                            <option value="">Pilih</option>
                            ${kategoriOptions}
                        </select>
                    </td>
                    <td class="px-3 py-2">
                        <select name="items[${index}][ruangan_id]" class="w-48 rounded-lg border-slate-300 bg-white px-2 py-1.5 text-xs text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40" required>
                            <option value="">Pilih</option>
                            ${ruanganOptions}
                        </select>
                    </td>
                    <td class="px-3 py-2 text-center">
                        <input type="number" name="items[${index}][jumlah]" min="1" class="w-20 rounded-lg border-slate-300 bg-white px-2 py-1.5 text-center text-xs text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40" placeholder="1" required>
                    </td>
                    <td class="px-3 py-2 text-right">
                        <input type="number" name="items[${index}][estimasi_harga_satuan]" min="0" step="1000" class="w-28 rounded-lg border-slate-300 bg-white px-2 py-1.5 text-right text-xs text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40" placeholder="Rp">
                    </td>
                    <td class="px-3 py-2">
                        <input type="text" name="items[${index}][spesifikasi]" class="w-40 rounded-lg border-slate-300 bg-white px-2 py-1.5 text-xs text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40" placeholder="Spek">
                    </td>
                    <td class="px-3 py-2 text-center">
                        <button type="button" class="rounded-lg bg-rose-100 px-2 py-1 text-xs font-semibold text-rose-600 transition hover:bg-rose-200 dark:bg-rose-500/20 dark:text-rose-300 dark:hover:bg-rose-500/30">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;

                const removeBtn = row.querySelector('button');
                removeBtn?.addEventListener('click', () => {
                    row.remove();
                });

                return row;
            }

            function addRow() {
                const index = itemsBody?.children?.length ?? 0;
                itemsBody?.appendChild(createRow(index));
            }

            function ensureInitialRow() {
                if (itemsBody && itemsBody.children.length === 0) {
                    addRow();
                }
            }

            btnAddItem?.addEventListener('click', addRow);
            ensureInitialRow();
        })();
    </script>
</x-layouts.sbadmin>
