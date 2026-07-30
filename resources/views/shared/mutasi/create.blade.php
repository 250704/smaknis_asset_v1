<x-layouts.sbadmin>
    <div class="mb-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="page-title">
                    {{ in_array($role, ['kepala_sarana', 'kepala_sekolah'], true) ? 'Mutasi Sarana Baru' : 'Ajukan Usulan Mutasi' }}
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pindahkan sarana/aset ke ruangan lain dengan pencatatan otomatis.</p>
            </div>
            <div>
                <a href="{{ route($role . '.mutasi.index') }}" class="btn-secondary flex items-center gap-2">
                    <i class="fas fa-arrow-left text-xs"></i>
                    <span>Kembali</span>
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

    <div class="panel w-full">
        <form method="POST" action="{{ route($role . '.mutasi.store') }}" class="space-y-6">
            @csrf

            {{-- 1. Pilihan Sarana --}}
            <div>
                <h3 class="flex items-center gap-2 mb-4 text-sm font-semibold text-slate-700 dark:text-slate-200">
                    <i class="text-blue-500 fas fa-box"></i>
                    Informasi Sarana
                </h3>

                @if($sarana)
                    {{-- Prefilled Sarana from QR Scan --}}
                    <input type="hidden" name="sarana_id" value="{{ $sarana->id }}">
                    <div class="grid gap-4 md:grid-cols-2 p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/60 dark:border-white/5">
                        <div>
                            <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Nama Sarana</span>
                            <span class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $sarana->nama_sarana }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Kode Sarana</span>
                            <span class="text-xs font-mono font-bold text-blue-600 dark:text-blue-400">{{ $sarana->kode_sarana }}</span>
                        </div>
                        <div class="md:col-span-2 pt-2 border-t border-slate-200/50 dark:border-white/5">
                            <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Ruangan Sekarang (Asal)</span>
                            <span class="text-sm text-slate-700 dark:text-slate-300 font-semibold">
                                Ruang {{ $sarana->ruangan?->nama_ruangan ?? '-' }} ({{ $sarana->ruangan?->gedung?->nama_gedung ?? '-' }})
                            </span>
                        </div>
                    </div>
                @else
                    {{-- Interactive Live Filter Bar + Quick Select --}}
                    <div x-data="{
                        search: '',
                        kategoriId: '0',
                        gedungId: '0',
                        ruanganId: '0',
                        selectedSaranaId: '{{ old('sarana_id', '') }}',
                        saranaList: @js($saranaList->map(fn($s) => [
                            'id' => (string) $s->id,
                            'nama' => $s->nama_sarana,
                            'kode' => $s->kode_sarana,
                            'kategori_id' => (string) $s->kategori_id,
                            'kategori_nama' => $s->kategori?->nama_kategori ?? '-',
                            'gedung_id' => (string) ($s->ruangan?->gedung_id ?? 0),
                            'gedung_nama' => $s->ruangan?->gedung?->nama_gedung ?? '-',
                            'ruangan_id' => (string) $s->ruangan_id,
                            'ruangan_nama' => $s->ruangan?->nama_ruangan ?? '-',
                            'kondisi' => $s->kondisi_terkini,
                        ])),
                        get filteredSarana() {
                            return this.saranaList.filter(item => {
                                const q = this.search.trim().toLowerCase();
                                const matchesSearch = !q || item.nama.toLowerCase().includes(q) || item.kode.toLowerCase().includes(q);
                                const matchesKat = this.kategoriId === '0' || item.kategori_id === this.kategoriId;
                                const matchesGed = this.gedungId === '0' || item.gedung_id === this.gedungId;
                                const matchesRua = this.ruanganId === '0' || item.ruangan_id === this.ruanganId;
                                return matchesSearch && matchesKat && matchesGed && matchesRua;
                            });
                        },
                        resetFilter() {
                            this.search = '';
                            this.kategoriId = '0';
                            this.gedungId = '0';
                            this.ruanganId = '0';
                        }
                    }">
                        {{-- Filter Grid Matching Admin Laporan Screenshot --}}
                        <div class="mb-5 p-4 rounded-2xl bg-slate-50/80 dark:bg-slate-900/40 border border-slate-200/80 dark:border-white/10 space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    <i class="fas fa-filter text-blue-500 mr-1.5"></i>Filter Pencarian Sarana
                                </span>
                                <span class="text-xs font-semibold text-blue-600 dark:text-blue-400">
                                    Ditemukan <span x-text="filteredSarana.length"></span> sarana
                                </span>
                            </div>

                            <div class="filter-grid">
                                <div>
                                    <label class="filter-label" for="filter_q">Pencarian</label>
                                    <input id="filter_q" type="text" x-model="search" placeholder="Kode sarana, nama..." class="filter-control">
                                </div>
                                <div>
                                    <label class="filter-label" for="filter_kat">Kategori</label>
                                    <select id="filter_kat" x-model="kategoriId" class="filter-control">
                                        <option value="0">Semua kategori</option>
                                        @foreach ($kategoriList as $k)
                                            <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="filter-label" for="filter_ged">Gedung</label>
                                    <select id="filter_ged" x-model="gedungId" class="filter-control">
                                        <option value="0">Semua gedung</option>
                                        @foreach ($gedungList as $g)
                                            <option value="{{ $g->id }}">{{ $g->nama_gedung }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="filter-label" for="filter_rua">Ruangan</label>
                                    <select id="filter_rua" x-model="ruanganId" class="filter-control">
                                        <option value="0">Semua ruangan</option>
                                        @foreach ($ruanganList as $r)
                                            <option value="{{ $r->id }}">Ruang {{ $r->nama_ruangan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="filter-actions md:col-span-2 lg:col-span-1">
                                    <button type="button" @click="resetFilter()" class="filter-reset w-full justify-center">
                                        <i class="fas fa-undo text-xs"></i>Reset
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Select Input Dynamically Filtered --}}
                        <div>
                            <label for="sarana_id" class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200">
                                Pilih Sarana Yang Akan Dimutasikan <span class="text-rose-500">*</span>
                            </label>
                            <select id="sarana_id" name="sarana_id" x-model="selectedSaranaId" required class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                                <option value="">-- Pilih dari hasil filter --</option>
                                <template x-for="item in filteredSarana" :key="item.id">
                                    <option :value="item.id" x-text="`${item.kode} - ${item.nama} (Ruang ${item.ruangan_nama} - ${item.gedung_nama})`"></option>
                                </template>
                            </select>
                            @error('sarana_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @endif
            </div>

            {{-- 2. Detail Perpindahan --}}
            <div class="pt-5 border-t border-slate-200/50 dark:border-white/10">
                <h3 class="flex items-center gap-2 mb-4 text-sm font-semibold text-slate-700 dark:text-slate-200">
                    <i class="text-blue-500 fas fa-exchange-alt"></i>
                    Detail Perpindahan
                </h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="ruangan_tujuan" class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200">Ruangan Tujuan <span class="text-rose-500">*</span></label>
                        <select id="ruangan_tujuan" name="ruangan_tujuan" required class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                            <option value="">Pilih ruangan tujuan...</option>
                            @foreach ($ruanganList as $ruangan)
                                <option value="{{ $ruangan->id }}" @selected(old('ruangan_tujuan') == $ruangan->id)>
                                    Ruang {{ $ruangan->nama_ruangan }} ({{ $ruangan->gedung?->nama_gedung ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        @error('ruangan_tujuan') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="tanggal_mutasi" class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200">Tanggal Rencana Mutasi <span class="text-rose-500">*</span></label>
                        <input id="tanggal_mutasi" name="tanggal_mutasi" type="date" value="{{ old('tanggal_mutasi', date('Y-m-d')) }}" required class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                        @error('tanggal_mutasi') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="keterangan" class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200">Keterangan / Alasan Mutasi</label>
                        <textarea id="keterangan" name="keterangan" rows="3" placeholder="Jelaskan alasan pemindahan sarana..." class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">{{ old('keterangan') }}</textarea>
                        @error('keterangan') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- 3. Opsi Eksekusi untuk Kepala Sarana & Kepala Sekolah --}}
            @if(in_array($role, ['kepala_sarana', 'kepala_sekolah'], true))
                <div class="pt-5 border-t border-slate-200/50 dark:border-white/10">
                    <h3 class="flex items-center gap-2 mb-4 text-sm font-semibold text-slate-700 dark:text-slate-200">
                        <i class="text-blue-500 fas fa-shield-alt"></i>
                        Opsi Otoritas
                    </h3>
                    <div class="p-4 rounded-2xl bg-blue-50/40 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/30">
                        <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200">Jenis Eksekusi</label>
                        <div class="mt-2 space-y-2">
                            <div class="flex items-center">
                                <input type="radio" id="opsi_propose" name="eksekusi_langsung" value="0" @checked(old('eksekusi_langsung', '0') === '0') class="h-4 w-4 text-blue-600 border-slate-300 focus:ring-blue-500">
                                <label for="opsi_propose" class="ml-2.5 text-sm text-slate-700 dark:text-slate-300 font-medium cursor-pointer">
                                    Simpan sebagai Usulan (Perlu Validasi Ulang)
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="opsi_direct" name="eksekusi_langsung" value="1" @checked(old('eksekusi_langsung') === '1') class="h-4 w-4 text-blue-600 border-slate-300 focus:ring-blue-500">
                                <label for="opsi_direct" class="ml-2.5 text-sm text-slate-700 dark:text-slate-300 font-medium cursor-pointer">
                                    Eksekusi Langsung (Perbarui Ruangan Sarana di Database Sekarang)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="pt-6 flex justify-end gap-3">
                <a href="{{ route($role . '.mutasi.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    {{ in_array($role, ['kepala_sarana', 'kepala_sekolah'], true) ? 'Simpan Mutasi' : 'Ajukan Usulan' }}
                </button>
            </div>
        </form>
    </div>
</x-layouts.sbadmin>
