<x-layouts.sbadmin>
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="page-title">Lapor Kerusakan</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Laporkan kondisi aset yang rusak untuk validasi kepala sarana.</p>
        </div>
        <a href="{{ $scanRoute ?? route('guru.scan') }}" class="btn-secondary">Kembali</a>
    </div>

    @if ($aset)
        <div class="panel mb-5 border border-cyan-200 bg-cyan-50/70 dark:border-cyan-400/30 dark:bg-cyan-500/10">
            <h2 class="text-sm font-semibold text-cyan-800 dark:text-cyan-200">Aset dari hasil scan</h2>
            <div class="mt-2 grid gap-2 text-sm text-slate-700 dark:text-slate-200 sm:grid-cols-2">
                <div><span class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Kode</span><br>{{ $aset->kode_aset }}</div>
                <div><span class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Nama</span><br>{{ $aset->nama_aset }}</div>
                <div><span class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Lokasi</span><br>{{ $aset->ruangan?->nama_ruangan }} - {{ $aset->ruangan?->gedung?->nama_gedung }}</div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ $storeRoute ?? route('guru.kerusakan.store') }}" enctype="multipart/form-data" class="panel space-y-4">
        @csrf

        <div class="grid gap-3 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Filter Gedung</label>
                <select
                    id="filter-gedung"
                    class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                >
                    <option value="">Semua gedung</option>
                    @foreach (($gedungList ?? collect()) as $gedung)
                        <option value="{{ $gedung->id }}">{{ $gedung->nama_gedung }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Filter Ruangan</label>
                <select
                    id="filter-ruangan"
                    class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                >
                    <option value="">Semua ruangan</option>
                    @foreach (($ruanganList ?? collect()) as $ruangan)
                        <option value="{{ $ruangan->id }}" data-gedung="{{ $ruangan->gedung_id }}">
                            {{ $ruangan->nama_ruangan }} - {{ $ruangan->gedung?->nama_gedung }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Filter Kategori</label>
                <select
                    id="filter-kategori"
                    class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                >
                    <option value="">Semua kategori</option>
                    @foreach (($kategoriList ?? collect()) as $kategori)
                        <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Pilih Aset yang Dilaporkan</label>
            <select
                id="aset-select"
                name="aset_id"
                class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                required
            >
                <option value="">-- Pilih aset --</option>
                @foreach (($asetList ?? collect()) as $asetItem)
                    <option
                        value="{{ $asetItem->id }}"
                        @selected((string) old('aset_id', $aset?->id) === (string) $asetItem->id)
                        data-kode="{{ $asetItem->kode_aset }}"
                        data-nama="{{ $asetItem->nama_aset }}"
                        data-gedung="{{ $asetItem->ruangan?->gedung?->id }}"
                        data-ruangan="{{ $asetItem->ruangan?->id }}"
                        data-kategori="{{ $asetItem->kategori?->id }}"
                        data-lokasi="{{ $asetItem->ruangan?->nama_ruangan }} - {{ $asetItem->ruangan?->gedung?->nama_gedung }}"
                    >
                        {{ $asetItem->kode_aset }} - {{ $asetItem->nama_aset }} ({{ $asetItem->ruangan?->nama_ruangan }} - {{ $asetItem->ruangan?->gedung?->nama_gedung }}) - {{ $asetItem->kategori?->nama_kategori }}
                    </option>
                @endforeach
            </select>
            @error('aset_id')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
            <div id="aset-preview" class="mt-3 rounded-xl border border-slate-200 bg-slate-50/80 p-3 text-xs text-slate-600 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-300">
                <p><span class="font-semibold text-slate-500 dark:text-slate-400">Kode:</span> <span id="preview-kode">{{ $aset?->kode_aset ?? '-' }}</span></p>
                <p class="mt-1"><span class="font-semibold text-slate-500 dark:text-slate-400">Nama:</span> <span id="preview-nama">{{ $aset?->nama_aset ?? '-' }}</span></p>
                <p class="mt-1"><span class="font-semibold text-slate-500 dark:text-slate-400">Lokasi:</span> <span id="preview-lokasi">{{ $aset?->ruangan?->nama_ruangan }} - {{ $aset?->ruangan?->gedung?->nama_gedung }}</span></p>
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Tingkat Kerusakan (Awal)</label>
            <select
                name="tingkat_kerusakan"
                class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                required
            >
                <option value="">Pilih tingkat</option>
                @foreach ($kondisiList as $kondisi)
                    <option value="{{ $kondisi }}" @selected(old('tingkat_kerusakan') === $kondisi)>{{ $kondisi }}</option>
                @endforeach
            </select>
            @error('tingkat_kerusakan')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Deskripsi Kerusakan</label>
            <textarea
                name="deskripsi"
                rows="4"
                placeholder="Jelaskan kondisi kerusakan aset."
                class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                required
            >{{ old('deskripsi') }}</textarea>
            @error('deskripsi')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Foto Kerusakan</label>
            <input
                type="file"
                name="foto_kerusakan"
                accept="image/*"
                class="w-full rounded-xl border border-dashed border-slate-300 bg-white px-3 py-2 text-sm text-slate-600 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-200 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                required
            >
            @error('foto_kerusakan')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-200 pt-3 dark:border-white/10">
            <button type="submit" class="btn-primary">Kirim Laporan</button>
        </div>
    </form>

    <script>
        (function () {
            const select = document.getElementById('aset-select');
            const gedungSelect = document.getElementById('filter-gedung');
            const ruanganSelect = document.getElementById('filter-ruangan');
            const kategoriSelect = document.getElementById('filter-kategori');
            const kodeEl = document.getElementById('preview-kode');
            const namaEl = document.getElementById('preview-nama');
            const lokasiEl = document.getElementById('preview-lokasi');
            const ruanganOptions = ruanganSelect ? Array.from(ruanganSelect.options).map((opt) => ({
                value: opt.value,
                text: opt.textContent,
                gedung: opt.getAttribute('data-gedung') || '',
            })) : [];

            function applyRuanganFilter() {
                if (!ruanganSelect) return;
                const selectedGedung = gedungSelect?.value || '';
                const current = ruanganSelect.value;
                ruanganSelect.innerHTML = '';

                ruanganOptions.forEach((optData) => {
                    if (optData.value === '' || !selectedGedung || optData.gedung === selectedGedung) {
                        const opt = document.createElement('option');
                        opt.value = optData.value;
                        opt.textContent = optData.text;
                        if (optData.gedung) {
                            opt.setAttribute('data-gedung', optData.gedung);
                        }
                        ruanganSelect.appendChild(opt);
                    }
                });

                if ([...ruanganSelect.options].some((opt) => opt.value === current)) {
                    ruanganSelect.value = current;
                } else {
                    ruanganSelect.value = '';
                }
            }

            function applyAsetFilter() {
                if (!select) return;
                const selectedGedung = gedungSelect?.value || '';
                const selectedRuangan = ruanganSelect?.value || '';
                const selectedKategori = kategoriSelect?.value || '';
                const current = select.value;
                let stillValid = false;

                Array.from(select.options).forEach((opt, index) => {
                    if (index === 0) {
                        opt.hidden = false;
                        return;
                    }

                    const matchGedung = !selectedGedung || opt.getAttribute('data-gedung') === selectedGedung;
                    const matchRuangan = !selectedRuangan || opt.getAttribute('data-ruangan') === selectedRuangan;
                    const matchKategori = !selectedKategori || opt.getAttribute('data-kategori') === selectedKategori;
                    const visible = matchGedung && matchRuangan && matchKategori;

                    opt.hidden = !visible;
                    if (visible && opt.value === current) {
                        stillValid = true;
                    }
                });

                if (!stillValid && current) {
                    select.value = '';
                }
            }

            function updatePreview() {
                if (!select) return;
                const selected = select.options[select.selectedIndex];
                if (!selected || !selected.value) {
                    if (kodeEl) kodeEl.textContent = '-';
                    if (namaEl) namaEl.textContent = '-';
                    if (lokasiEl) lokasiEl.textContent = '-';
                    return;
                }

                if (kodeEl) kodeEl.textContent = selected.getAttribute('data-kode') || '-';
                if (namaEl) namaEl.textContent = selected.getAttribute('data-nama') || '-';
                if (lokasiEl) lokasiEl.textContent = selected.getAttribute('data-lokasi') || '-';
            }

            function syncFilterFromSelectedAset() {
                if (!select) return;
                const selected = select.options[select.selectedIndex];
                if (!selected || !selected.value) {
                    return;
                }

                if (gedungSelect) {
                    gedungSelect.value = selected.getAttribute('data-gedung') || '';
                    applyRuanganFilter();
                }
                if (ruanganSelect) {
                    ruanganSelect.value = selected.getAttribute('data-ruangan') || '';
                }
                if (kategoriSelect) {
                    kategoriSelect.value = selected.getAttribute('data-kategori') || '';
                }
                applyAsetFilter();
                select.value = selected.value;
            }

            gedungSelect?.addEventListener('change', function () {
                applyRuanganFilter();
                applyAsetFilter();
                updatePreview();
            });
            ruanganSelect?.addEventListener('change', function () {
                applyAsetFilter();
                updatePreview();
            });
            kategoriSelect?.addEventListener('change', function () {
                applyAsetFilter();
                updatePreview();
            });
            select?.addEventListener('change', function () {
                syncFilterFromSelectedAset();
                updatePreview();
            });

            syncFilterFromSelectedAset();
            applyRuanganFilter();
            applyAsetFilter();
            updatePreview();
        })();
    </script>
</x-layouts.sbadmin>
