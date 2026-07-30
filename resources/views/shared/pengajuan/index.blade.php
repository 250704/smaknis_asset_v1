<x-layouts.sbadmin>
    @php
        $role = $role ?? '';
        $showUser = $showUser ?? false;
        $canApprove = $canApprove ?? false;
        $countKerusakan = $laporanKerusakanCount ?? (isset($laporanKerusakan) && $laporanKerusakan !== null ? $laporanKerusakan->count() : 0);
        $totalPengajuan = $pengajuanCount ?? (method_exists($pengajuan, 'total') ? $pengajuan->total() : $pengajuan->count());
        $totalAntrean = $totalPengajuan + $countKerusakan;
        $showDualAction = (bool) ($showDualAction ?? false);
        $viewRoute = $viewRoute ?? ($detailRoute ?? null);
        $realisasiRoute = $realisasiRoute ?? ($detailRoute ?? null);
        $statusLabels = [
            'DIAJUKAN' => 'Menunggu Approval Kepala Sarana',
            'DISETUJUI_KASARANA' => 'Menunggu Approval Bendahara',
            'DISETUJUI_BENDAHARA' => 'Menunggu Approval Kepala Sekolah',
            'DISETUJUI_KEPSEK' => 'Disetujui Final',
            'DIPROSES' => 'Realisasi Diproses',
            'MENUNGGU_VERIFIKASI_TEKNIS' => 'Menunggu Verifikasi Teknis',
            'MENUNGGU_VERIFIKASI_KEUANGAN' => 'Menunggu Verifikasi Keuangan',
            'DITOLAK' => 'Ditolak',
            'SELESAI' => 'Selesai',
        ];
        $tingkatLabelMap = [
            'RINGAN' => 'Rusak Ringan',
            'BERAT' => 'Rusak Berat',
            'TIDAK_LAYAK' => 'Tidak Layak Pakai',
        ];
    @endphp

    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            <div class="flex flex-wrap items-center gap-2 mt-3">
                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold bg-white border rounded-full shadow-sm border-slate-200 text-slate-700 dark:border-white/10 dark:bg-slate-900/60 dark:text-slate-200">
                    {{ number_format($totalAntrean) }} Total Antrean
                </span>
                @if ($countKerusakan > 0)
                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300">
                        <i class="mr-1.5 fas fa-exclamation-triangle"></i> {{ $countKerusakan }} Menunggu Validasi Kerusakan
                    </span>
                @endif
                @if ($totalPengajuan > 0)
                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-300">
                        <i class="mr-1.5 fas fa-file-invoice"></i> {{ $totalPengajuan }} Review Pengajuan
                    </span>
                @endif
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="flex items-center justify-between px-4 py-3 mb-5 text-sm border shadow-sm rounded-xl border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
            <div class="flex items-center gap-2.5">
                <i class="fas fa-check-circle text-emerald-600 dark:text-emerald-400"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session('warning'))
        <div class="flex items-center justify-between px-4 py-3 mb-5 text-sm border shadow-sm rounded-xl border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-200">
            <div class="flex items-center gap-2.5">
                <i class="fas fa-exclamation-circle text-amber-600 dark:text-amber-400"></i>
                <span>{{ session('warning') }}</span>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-center justify-between px-4 py-3 mb-5 text-sm border shadow-sm rounded-xl border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
            <div class="flex items-center gap-2.5">
                <i class="fas fa-times-circle text-rose-600 dark:text-rose-400"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 mb-5 border shadow-sm rounded-xl border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
            <p class="text-sm font-semibold">Terdapat kesalahan pada input Anda:</p>
            <ul class="mt-1.5 list-disc list-inside text-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- @if (!empty($filters['q']) || !empty($filters['status']) || !empty($filters['jenis']))
        <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 mb-5 text-blue-900 border border-blue-200 shadow-sm rounded-xl bg-blue-50/70 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-200">
            <div class="flex items-center gap-2 text-xs font-medium">
                <i class="text-blue-600 fas fa-filter dark:text-blue-400"></i>
                <span>Filter aktif:</span>
                @if (!empty($filters['q']))
                    <span class="px-2 py-0.5 bg-white dark:bg-slate-800 rounded border border-blue-200 dark:border-blue-500/30 font-semibold">Pencarian: "{{ $filters['q'] }}"</span>
                @endif
                @if (!empty($filters['status']))
                    <span class="px-2 py-0.5 bg-white dark:bg-slate-800 rounded border border-blue-200 dark:border-blue-500/30 font-semibold">Status: {{ $statusLabels[$filters['status']] ?? $filters['status'] }}</span>
                @endif
                @if (!empty($filters['jenis']))
                    <span class="px-2 py-0.5 bg-white dark:bg-slate-800 rounded border border-blue-200 dark:border-blue-500/30 font-semibold">Jenis: {{ $filters['jenis'] }}</span>
                @endif
            </div>
            <a href="{{ url()->current() }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                <i class="fas fa-undo"></i> Reset Filter / Tampilkan Semua
            </a>
        </div>
    @endif --}}

    @if (($showFilters ?? true) === true)
        <section class="mb-6 panel">
            <form method="GET" class="filter-grid">
                <div class="xl:col-span-3">
                    <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500" for="q">Pencarian</label>
                    <input
                        type="text"
                        id="q"
                        name="q"
                        value="{{ $filters['q'] ?? '' }}"
                        placeholder="Cari judul / kode sarana / nama sarana..."
                        class="filter-control"
                    >
                </div>
                <div class="xl:col-span-2">
                    <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500" for="status">Status</label>
                    <select id="status" name="status" class="filter-control">
                        <option value="">Semua status</option>
                        @foreach ($statusList as $status)
                            <option value="{{ $status }}" @selected((string) ($filters['status'] ?? '') === (string) $status)>{{ $statusLabels[$status] ?? $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500" for="jenis">Jenis</label>
                    <select id="jenis" name="jenis" class="filter-control">
                        <option value="">Semua jenis</option>
                        @foreach ($jenisList as $jenis)
                            <option value="{{ $jenis }}" @selected((string) ($filters['jenis'] ?? '') === (string) $jenis)>{{ $jenis }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="filter-submit">
                        <i class="text-xs fas fa-filter"></i>Filter
                    </button>
                    <a href="{{ url()->current() }}" class="filter-reset">
                        <i class="text-xs fas fa-undo"></i>Reset
                    </a>
                </div>
            </form>
        </section>
    @endif

    {{-- DAFTAR ANTREAN PERSETUJUAN & VALIDASI TERPADU --}}
    @php
        $currentRole = !empty($role) ? $role : (auth()->user()?->role ?? '');
        $tabelTitle = 'Daftar Antrean Persetujuan & Validasi';
        $tabelEmptyMsg = 'Belum ada antrean persetujuan atau validasi yang ditemukan.';
        if ($currentRole === 'admin') {
            $tabelTitle = 'Daftar Realisasi';
            $tabelEmptyMsg = 'Belum ada antrean realisasi yang ditemukan.';
        } elseif (in_array($currentRole, ['bendahara', 'kepala_sekolah'])) {
            $tabelTitle = 'Daftar Persetujuan';
            $tabelEmptyMsg = 'Belum ada antrean persetujuan yang ditemukan.';
        }
    @endphp
    <section>
        <div class="flex flex-wrap items-center justify-end gap-3 mb-3.5">
            @if ($pengajuan->isNotEmpty())
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Halaman {{ $pengajuan->currentPage() }} dari {{ $pengajuan->lastPage() }}</span>
            @endif
        </div>

        @if ($pengajuan->isEmpty())
            <div class="p-8 text-center bg-white border shadow-sm panel rounded-2xl text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                <i class="mb-3 text-3xl fas fa-inbox text-slate-300 dark:text-slate-600"></i>
                <p class="font-medium">{{ $tabelEmptyMsg }}</p>
            </div>
        @else
            <div class="overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200/80 dark:border-white/10 dark:bg-slate-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs divide-y divide-slate-200/80 dark:divide-white/10 table-auto">
                        <thead class="border-b bg-slate-50/70 dark:bg-slate-800/40 border-slate-200/80 dark:border-white/5">
                            <tr>
                                <th class="px-2 py-2.5 text-[10px] font-bold tracking-wider text-left uppercase text-slate-400 dark:text-slate-500">Tipe</th>
                                <th class="px-2 py-2.5 text-[10px] font-bold tracking-wider text-left uppercase text-slate-400 dark:text-slate-500">sarana & Lokasi</th>
                                <th class="px-2 py-2.5 text-[10px] font-bold tracking-wider text-left uppercase text-slate-400 dark:text-slate-500">Informasi / Judul</th>
                                <th class="px-2 py-2.5 text-[10px] font-bold tracking-wider text-left uppercase text-slate-400 dark:text-slate-500">Estimasi Biaya</th>
                                <th class="px-2 py-2.5 text-[10px] font-bold tracking-wider text-left uppercase text-slate-400 dark:text-slate-500">Status</th>
                                <th class="px-2 py-2.5 text-[10px] font-bold tracking-wider text-left uppercase text-slate-400 dark:text-slate-500">Tanggal</th>
                                @if ($showUser)
                                    <th class="px-2 py-2.5 text-[10px] font-bold tracking-wider text-left uppercase text-slate-400 dark:text-slate-500">Pengaju</th>
                                @endif
                                <th class="px-2 py-2.5 text-[10px] font-bold tracking-wider text-center uppercase text-slate-400 dark:text-slate-500">Detail</th>
                                @if ($canApprove)
                                    <th class="px-2 py-2.5 text-[10px] font-bold tracking-wider text-center uppercase text-slate-400 dark:text-slate-500">Approval</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach ($pengajuan as $item)
                                @php
                                    $tipeItem = $item->tipe_item ?? 'pengajuan';
                                @endphp
                                @if ($tipeItem === 'laporan_kerusakan')
                                    <tr x-data="{ showValidate: false, actionMode: 'VALIDASI', showPhotoModal: false, showDetailModal: false }" class="transition hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                                        <td class="px-2 py-2 align-middle whitespace-nowrap">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300">
                                                Laporan
                                            </span>
                                        </td>

                                        <td class="px-2 py-2 align-middle max-w-[180px]">
                                            <div class="flex items-center gap-2">
                                                @if (!empty($item->foto_kerusakan))
                                                    <div 
                                                        @click="showPhotoModal = true"
                                                        class="relative flex-shrink-0 w-9 h-9 overflow-hidden border rounded-lg cursor-pointer border-slate-200 dark:border-white/10 group shadow-sm bg-slate-50 dark:bg-slate-800"
                                                        title="Klik untuk memperbesar foto"
                                                    >
                                                        <img 
                                                            src="{{ asset('storage/' . $item->foto_kerusakan) }}" 
                                                            alt="{{ $item->sarana?->nama_sarana }}" 
                                                            class="object-cover w-full h-full transition duration-200 group-hover:scale-108"
                                                        />
                                                    </div>
                                                @else
                                                    <div class="flex items-center justify-center flex-shrink-0 w-9 h-9 border rounded-lg bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-white/10 text-slate-400">
                                                        <i class="fas fa-image text-xs"></i>
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                                    <div class="font-semibold text-slate-800 dark:text-slate-100 truncate text-[11px]" title="{{ $item->sarana?->nama_sarana }}">{{ $item->sarana?->nama_sarana ?? '-' }}</div>
                                                    <div class="mt-0.5 flex flex-col gap-0.5">
                                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-mono font-medium bg-slate-100 text-slate-650 dark:bg-slate-800 dark:text-slate-350 w-fit whitespace-nowrap">
                                                            {{ $item->sarana?->kode_sarana ?? '-' }}
                                                        </span>
                                                        <span class="text-[9px] text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                                            <i class="mr-0.5 fas fa-map-marker-alt text-slate-400"></i>{{ $item->sarana?->ruangan?->nama_ruangan ?? '-' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-2 py-2 align-middle">
                                            <div class="flex flex-col">
                                                <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Laporan Kerusakan</span>
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-semibold w-fit {{
                                                    $item->tingkat_kerusakan === 'BERAT' || $item->tingkat_kerusakan === 'TIDAK_LAYAK'
                                                        ? 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-450'
                                                        : 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-450'
                                                }} mt-0.5">
                                                    {{ $tingkatLabelMap[$item->tingkat_kerusakan] ?? $item->tingkat_kerusakan }}
                                                </span>
                                            </div>
                                        </td>

                                        <td class="px-2 py-2 align-middle text-[11px] text-slate-400 whitespace-nowrap">
                                            -
                                        </td>

                                        <td class="px-2 py-2 align-middle whitespace-nowrap">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-455">
                                                Menunggu Validasi
                                            </span>
                                        </td>

                                        <td class="px-2 py-2 text-[10px] align-middle whitespace-nowrap text-slate-500 dark:text-slate-400">
                                            <div class="font-medium">{{ $item->created_at?->format('d M Y') }}</div>
                                            <div class="text-[9px] text-slate-400 mt-0.5">{{ $item->created_at?->format('H:i') }} WIB</div>
                                        </td>

                                        @if ($showUser)
                                            <td class="px-2 py-2 align-middle whitespace-nowrap">
                                                <div class="font-medium text-slate-700 dark:text-slate-200 text-[11px]">{{ $item->user?->display_name ?? '-' }}</div>
                                                <span class="inline-flex items-center px-1 py-0.2 mt-0.5 rounded text-[9px] font-semibold bg-slate-50 text-slate-550 dark:bg-slate-800 dark:text-slate-450 uppercase tracking-wider">
                                                    {{ strtoupper($item->user?->role ?? '') }}
                                                </span>
                                            </td>
                                        @endif

                                        <td class="px-2 py-2 text-center align-middle whitespace-nowrap">
                                            <button
                                                type="button"
                                                @click="showDetailModal = true"
                                                class="inline-flex items-center gap-1 px-1.5 py-1 rounded-lg text-[10px] font-semibold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-white/15 hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm"
                                            >
                                                <i class="fas fa-eye text-slate-500"></i>
                                                <span>Detail</span>
                                            </button>
                                        </td>

                                        @if ($canApprove)
                                            <td class="px-2 py-2 text-center align-middle whitespace-nowrap">
                                                <div class="flex items-center justify-center gap-1">
                                                    <button
                                                        type="button"
                                                        @click="showValidate = true; actionMode = 'VALIDASI'"
                                                        class="inline-flex items-center gap-1 px-1.5 py-1 rounded-lg text-[10px] font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition"
                                                    >
                                                        <i class="fas fa-check-circle"></i>
                                                        <span>Validasi</span>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        @click="showValidate = true; actionMode = 'TOLAK'"
                                                        class="inline-flex items-center gap-1 px-1.5 py-1 rounded-lg text-[10px] font-semibold bg-rose-600 hover:bg-rose-700 text-white shadow-sm transition"
                                                    >
                                                        <i class="fas fa-times-circle"></i>
                                                        <span>Tolak</span>
                                                    </button>
                                                </div>

                                                @if (!empty($item->foto_kerusakan))
                                                    <template x-teleport="body">
                                                        <div
                                                            x-show="showPhotoModal"
                                                            x-cloak
                                                            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                                                            @click.self="showPhotoModal = false"
                                                        >
                                                            <div class="w-full max-w-lg overflow-hidden text-left bg-white border shadow-2xl rounded-2xl dark:bg-slate-900 border-slate-200 dark:border-white/10">
                                                                <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-200 dark:border-white/10">
                                                                    <div class="flex items-center gap-2">
                                                                        <i class="text-blue-600 fas fa-camera dark:text-blue-400"></i>
                                                                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Foto Bukti Kerusakan</h3>
                                                                    </div>
                                                                    <button type="button" @click="showPhotoModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="p-4 text-center bg-slate-50 dark:bg-slate-950">
                                                                    <img
                                                                        src="{{ asset('storage/' . $item->foto_kerusakan) }}"
                                                                        alt="Foto Kerusakan"
                                                                        class="max-h-[65vh] w-auto mx-auto rounded-xl object-contain border border-slate-200 dark:border-white/10 shadow"
                                                                    >
                                                                </div>
                                                                <div class="flex items-center justify-between px-5 py-3 bg-white border-t dark:bg-slate-900 border-slate-200 dark:border-white/10">
                                                                    <a
                                                                        href="{{ asset('storage/' . $item->foto_kerusakan) }}"
                                                                        target="_blank"
                                                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400"
                                                                    >
                                                                        <i class="fas fa-external-link-alt"></i>
                                                                        <span>Buka di Tab Baru</span>
                                                                    </a>
                                                                    <button type="button" @click="showPhotoModal = false" class="px-3.5 py-1.5 text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-xl dark:border-white/15 dark:bg-slate-800 dark:text-slate-200">Tutup</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                @endif

                                                <template x-teleport="body">
                                                    <div
                                                        x-show="showDetailModal"
                                                        x-cloak
                                                        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                                                        @click.self="showDetailModal = false"
                                                    >
                                                        <div class="w-full max-w-lg overflow-hidden text-left bg-white border shadow-2xl rounded-2xl dark:bg-slate-900 border-slate-200 dark:border-white/10">
                                                            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-200 dark:border-white/10">
                                                                <div class="flex items-center gap-2">
                                                                    <i class="text-blue-600 fas fa-info-circle dark:text-blue-400"></i>
                                                                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Detail Laporan Kerusakan</h3>
                                                                </div>
                                                                <button type="button" @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </div>
                                                            <div class="p-5 space-y-4">
                                                                <div class="grid grid-cols-2 gap-4">
                                                                    <div>
                                                                        <span class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Nama sarana</span>
                                                                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $item->sarana?->nama_sarana ?? '-' }}</span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kode sarana</span>
                                                                        <span class="inline-flex px-1.5 py-0.5 text-xs font-mono font-medium rounded bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $item->sarana?->kode_sarana ?? '-' }}</span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Lokasi</span>
                                                                        <span class="text-xs text-slate-700 dark:text-slate-300">{{ $item->sarana?->ruangan?->nama_ruangan ?? '-' }} ({{ $item->sarana?->ruangan?->gedung?->nama_gedung ?? '-' }})</span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Dilaporkan Oleh</span>
                                                                        <span class="text-xs text-slate-700 dark:text-slate-300">{{ $item->user?->display_name ?? '-' }} <span class="text-[10px] font-semibold bg-slate-100 px-1 py-0.5 rounded text-slate-500 dark:bg-slate-800">({{ strtoupper($item->user?->role ?? '') }})</span></span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Waktu Laporan</span>
                                                                        <span class="text-xs text-slate-700 dark:text-slate-300">{{ $item->created_at?->format('d M Y, H:i') }} WIB</span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tingkat Kerusakan</span>
                                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{
                                                                            $item->tingkat_kerusakan === 'BERAT' || $item->tingkat_kerusakan === 'TIDAK_LAYAK'
                                                                                ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300'
                                                                                : 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300'
                                                                        }}">
                                                                            {{ $tingkatLabelMap[$item->tingkat_kerusakan] ?? $item->tingkat_kerusakan }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                @if(!empty($item->deskripsi))
                                                                    <div>
                                                                        <span class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Deskripsi Kerusakan</span>
                                                                        <div class="p-3 mt-1 text-xs leading-relaxed border rounded-xl bg-slate-50/50 text-slate-700 border-slate-200 dark:bg-slate-950 dark:border-white/5 dark:text-slate-300">
                                                                            {{ $item->deskripsi }}
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                                @if(!empty($item->foto_kerusakan))
                                                                    <div>
                                                                        <span class="block mb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Foto Bukti Kerusakan</span>
                                                                        <div class="relative overflow-hidden border rounded-xl border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-950 p-2">
                                                                            <img
                                                                                src="{{ asset('storage/' . $item->foto_kerusakan) }}"
                                                                                alt="Foto Kerusakan"
                                                                                class="max-h-[200px] w-auto mx-auto rounded-lg object-contain"
                                                                            >
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="flex items-center justify-between px-5 py-3 bg-white border-t dark:bg-slate-900 border-slate-200 dark:border-white/10">
                                                                @if(!empty($item->foto_kerusakan))
                                                                    <a
                                                                        href="{{ asset('storage/' . $item->foto_kerusakan) }}"
                                                                        target="_blank"
                                                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400"
                                                                    >
                                                                        <i class="fas fa-external-link-alt"></i>
                                                                        <span>Buka Foto Ukuran Penuh</span>
                                                                    </a>
                                                                @else
                                                                    <div></div>
                                                                @endif
                                                                <button type="button" @click="showDetailModal = false" class="px-3.5 py-1.5 text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-xl dark:border-white/15 dark:bg-slate-800 dark:text-slate-200">Tutup</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>

                                                <template x-teleport="body">
                                                    <div
                                                        x-show="showValidate"
                                                        x-cloak
                                                        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                                                        @click.self="showValidate = false"
                                                    >
                                                        <div class="w-full max-w-lg p-6 overflow-hidden text-left bg-white border shadow-2xl rounded-2xl dark:bg-slate-900 border-slate-200 dark:border-white/10">
                                                            <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-slate-200 dark:border-white/10">
                                                                <div class="flex items-center gap-2.5">
                                                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl" :class="actionMode === 'VALIDASI' ? 'bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400' : 'bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400'">
                                                                        <i class="text-sm fas" :class="actionMode === 'VALIDASI' ? 'fa-clipboard-check' : 'fa-exclamation-circle'"></i>
                                                                    </span>
                                                                    <h3 class="text-sm font-bold tracking-tight text-slate-800 dark:text-slate-100" x-text="actionMode === 'VALIDASI' ? 'Validasi & Rekomendasi Penanganan' : 'Tolak Laporan Kerusakan'"></h3>
                                                                </div>
                                                                <button type="button" @click="showValidate = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </div>

                                                            <form method="POST" action="{{ route('kepala_sarana.kerusakan.validate', $item) }}" class="space-y-4">
                                                                @csrf
                                                                <input type="hidden" name="action" :value="actionMode">

                                                                <template x-if="actionMode === 'VALIDASI'">
                                                                    <div class="space-y-3.5">
                                                                        <div class="grid gap-3 sm:grid-cols-2">
                                                                            <div>
                                                                                <label class="block mb-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200">Tingkat Kerusakan Terverifikasi</label>
                                                                                <select name="tingkat_kerusakan" class="w-full px-3 py-2 text-xs bg-white shadow-sm rounded-xl border-slate-300 text-slate-700 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-800 dark:text-slate-100">
                                                                                    <option value="RINGAN" @selected($item->tingkat_kerusakan === 'RINGAN')>Rusak Ringan</option>
                                                                                    <option value="BERAT" @selected($item->tingkat_kerusakan === 'BERAT')>Rusak Berat</option>
                                                                                    <option value="TIDAK_LAYAK" @selected($item->tingkat_kerusakan === 'TIDAK_LAYAK')>Tidak Layak Pakai</option>
                                                                                </select>
                                                                            </div>
                                                                            <div>
                                                                                <label class="block mb-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200">Rekomendasi Penanganan</label>
                                                                                <select name="rekomendasi_tindakan" class="w-full px-3 py-2 text-xs bg-white shadow-sm rounded-xl border-slate-300 text-slate-700 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-800 dark:text-slate-100">
                                                                                    <option value="PERAWATAN">Ajukan Perawatan / Perbaikan</option>
                                                                                    <option value="PENGGANTIAN">Ajukan Penggantian sarana</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div>
                                                                            <label class="block mb-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200">Estimasi Biaya Penanganan (Rp) <span class="text-rose-500">*</span></label>
                                                                            <input
                                                                                type="number"
                                                                                name="estimasi_biaya"
                                                                                step="1000"
                                                                                min="0"
                                                                                placeholder="Contoh: 350000"
                                                                                class="w-full px-3 py-2 text-xs bg-white shadow-sm rounded-xl border-slate-300 text-slate-700 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-800 dark:text-slate-100"
                                                                                required
                                                                            >
                                                                        </div>
                                                                    </div>
                                                                </template>

                                                                <div>
                                                                    <label class="block mb-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200">Catatan Validasi / Alasan <span x-show="actionMode === 'TOLAK'" class="text-rose-500">*</span></label>
                                                                    <textarea
                                                                        name="catatan"
                                                                        rows="3"
                                                                        placeholder="Tambahkan catatan hasil survei / alasan keputusan..."
                                                                        class="w-full px-3 py-2 text-xs bg-white shadow-sm rounded-xl border-slate-300 text-slate-700 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-800 dark:text-slate-100"
                                                                    ></textarea>
                                                                </div>

                                                                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-200 dark:border-white/10">
                                                                    <button type="button" @click="showValidate = false" class="px-4 py-2 text-xs font-semibold bg-white border rounded-xl border-slate-300 text-slate-700 hover:bg-slate-50 dark:border-white/15 dark:bg-slate-800 dark:text-slate-200">Batal</button>
                                                                    <button
                                                                        type="submit"
                                                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white shadow-sm transition"
                                                                        :class="actionMode === 'TOLAK' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-blue-600 hover:bg-blue-700'"
                                                                    >
                                                                        <i class="fas fa-check"></i>
                                                                        <span x-text="actionMode === 'VALIDASI' ? 'Konfirmasi Validasi' : 'Konfirmasi Tolak Laporan'"></span>
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </template>
                                            </td>
                                        @endif
                                    </tr>
                                @elseif ($tipeItem === 'mutasi_sarana')
                                    <tr class="transition hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                                        <td class="px-2 py-2 align-middle whitespace-nowrap">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-purple-100 text-purple-800 dark:bg-purple-500/20 dark:text-purple-300">
                                                Mutasi
                                            </span>
                                        </td>

                                        <td class="px-2 py-2 align-middle max-w-[180px]">
                                            <div class="flex items-center gap-2">
                                                <div class="flex items-center justify-center flex-shrink-0 w-9 h-9 border rounded-lg bg-purple-50 dark:bg-purple-950/40 border-purple-200 dark:border-purple-800/30 text-purple-600 dark:text-purple-400">
                                                    <i class="fas fa-exchange-alt text-xs"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="font-semibold text-slate-800 dark:text-slate-100 truncate text-[11px]" title="{{ $item->sarana?->nama_sarana }}">{{ $item->sarana?->nama_sarana ?? '-' }}</div>
                                                    <div class="mt-0.5 flex flex-col gap-0.5">
                                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-mono font-medium bg-slate-100 text-slate-650 dark:bg-slate-800 dark:text-slate-350 w-fit whitespace-nowrap">
                                                            {{ $item->sarana?->kode_sarana ?? '-' }}
                                                        </span>
                                                        <span class="text-[9px] text-purple-600 dark:text-purple-400 whitespace-nowrap font-medium">
                                                            <i class="mr-0.5 fas fa-arrow-right"></i>Ke Ruang {{ $item->ruanganTujuan?->nama_ruangan ?? '-' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-2 py-2 align-middle">
                                            <div class="flex flex-col">
                                                <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Mutasi Sarana</span>
                                                <span class="text-[9px] text-slate-400 truncate max-w-[130px]">{{ $item->keterangan ?: 'Pemindahan sarana' }}</span>
                                            </div>
                                        </td>

                                        <td class="px-2 py-2 align-middle text-[11px] text-slate-400 whitespace-nowrap">
                                            -
                                        </td>

                                        <td class="px-2 py-2 align-middle whitespace-nowrap">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold {{
                                                $item->status_mutasi === 'DITOLAK'
                                                    ? 'bg-rose-50 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300'
                                                    : ($item->status_mutasi === 'DISETUJUI'
                                                        ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300'
                                                        : 'bg-amber-50 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300')
                                            }}">
                                                {{ $item->status_mutasi }}
                                            </span>
                                        </td>

                                        <td class="px-2 py-2 text-[10px] align-middle whitespace-nowrap text-slate-500 dark:text-slate-400">
                                            <div class="font-medium">{{ $item->created_at?->format('d M Y') }}</div>
                                            <div class="text-[9px] text-slate-400 mt-0.5">{{ $item->created_at?->format('H:i') }} WIB</div>
                                        </td>

                                        @if ($showUser)
                                            <td class="px-2 py-2 align-middle whitespace-nowrap">
                                                <div class="font-medium text-slate-700 dark:text-slate-200 text-[11px]">{{ $item->user?->display_name ?? '-' }}</div>
                                                <span class="inline-flex items-center px-1 py-0.2 mt-0.5 rounded text-[9px] font-semibold bg-slate-50 text-slate-550 dark:bg-slate-800 dark:text-slate-450 uppercase tracking-wider">
                                                    {{ strtoupper($item->user?->role ?? '') }}
                                                </span>
                                            </td>
                                        @endif

                                        <td class="px-2 py-2 text-center align-middle whitespace-nowrap">
                                            @php
                                                $mutasiShowRoute = match($role) {
                                                    'admin' => route('admin.mutasi.show', $item->id),
                                                    'kepala_sarana' => route('kepala_sarana.mutasi.show', $item->id),
                                                    'bendahara' => route('bendahara.mutasi.show', $item->id),
                                                    'kepala_sekolah' => route('kepala_sekolah.mutasi.show', $item->id),
                                                    default => route('guru.mutasi.show', $item->id),
                                                };
                                            @endphp
                                            <a href="{{ $mutasiShowRoute }}" class="inline-flex items-center gap-1 px-1.5 py-1 rounded-lg text-[10px] font-semibold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-white/15 hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm">
                                                <i class="fas fa-eye text-slate-500"></i>
                                                <span>Detail</span>
                                            </a>
                                        </td>

                                        @if ($canApprove)
                                            <td class="px-2 py-2 text-center align-middle whitespace-nowrap">
                                                @if($role === 'kepala_sarana' && $item->status_mutasi === 'DIAJUKAN')
                                                    <div class="flex items-center justify-center gap-1">
                                                        <form method="POST" action="{{ route('kepala_sarana.mutasi.approve', $item->id) }}" class="inline">
                                                            @csrf
                                                            <button type="submit" onclick="return confirm('Setujui mutasi sarana ini?')" class="inline-flex items-center gap-1 px-1.5 py-1 rounded-lg text-[10px] font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition">
                                                                <i class="fas fa-check-circle"></i>
                                                                <span>Setujui</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @else
                                                    <span class="text-[10px] text-slate-400">-</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @else
                                    <tr x-data="{ showApproveModal: false, approveAction: 'APPROVE' }" class="transition hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                                        <td class="px-2 py-2 align-middle whitespace-nowrap">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-300">
                                                Pengajuan
                                            </span>
                                        </td>

                                        <td class="px-2 py-2 align-middle max-w-[180px]">
                                            <div class="flex items-center gap-2">
                                                <div class="flex items-center justify-center flex-shrink-0 w-9 h-9 border rounded-lg bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-white/10 text-slate-400">
                                                    <i class="fas fa-file-invoice text-xs"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="font-semibold text-slate-800 dark:text-slate-100 truncate text-[11px]" title="{{ $item->sarana?->nama_sarana }}">{{ $item->sarana?->nama_sarana ?? '-' }}</div>
                                                    <div class="mt-0.5 flex flex-col gap-0.5">
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-mono font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 w-fit whitespace-nowrap">
                                                            {{ $item->sarana?->kode_sarana ?? '-' }}
                                                        </span>
                                                        <span class="text-[9px] text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                                            <i class="mr-0.5 fas fa-map-marker-alt text-slate-400"></i>{{ $item->sarana?->ruangan?->nama_ruangan ?? '-' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-2 py-2 align-middle">
                                            <div class="flex flex-col">
                                                <div class="font-semibold text-slate-800 dark:text-slate-100 truncate max-w-[120px] text-[11px]" title="{{ $item->judul_pengajuan }}">{{ $item->judul_pengajuan }}</div>
                                                <div class="text-[9px] text-slate-400 mt-0.5 uppercase tracking-wider font-medium">Jenis: {{ $item->jenis_pengajuan }}</div>
                                            </div>
                                        </td>

                                        <td class="px-2 py-2 align-middle whitespace-nowrap text-[11px] text-slate-800 dark:text-slate-200">
                                            Rp {{ number_format($item->estimasi_biaya, 0, ',', '.') }}
                                        </td>

                                        <td class="px-2 py-2 align-middle whitespace-nowrap">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold {{
                                                $item->status_pengajuan === 'DITOLAK'
                                                    ? 'bg-rose-50 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300'
                                                    : ($item->status_pengajuan === 'SELESAI' || $item->status_pengajuan === 'DISETUJUI_KEPSEK'
                                                        ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300'
                                                        : 'bg-blue-50 text-blue-800 dark:bg-blue-500/20 dark:text-blue-300')
                                            }}">
                                                {{ $statusLabels[$item->status_pengajuan] ?? $item->status_pengajuan }}
                                            </span>
                                        </td>

                                        <td class="px-2 py-2 text-[10px] align-middle whitespace-nowrap text-slate-500 dark:text-slate-400">
                                            <div class="font-medium">{{ $item->created_at?->format('d M Y') }}</div>
                                            <div class="text-[9px] text-slate-400 mt-0.5">{{ $item->created_at?->format('H:i') }} WIB</div>
                                        </td>

                                        @if ($showUser)
                                            <td class="px-2 py-2 align-middle whitespace-nowrap">
                                                <div class="font-medium text-slate-700 dark:text-slate-200 text-[11px]">{{ $item->user?->display_name ?? '-' }}</div>
                                                <span class="inline-flex items-center px-1.5 py-0.2 mt-0.5 rounded text-[9px] font-semibold bg-slate-50 text-slate-550 dark:bg-slate-800 dark:text-slate-450 uppercase tracking-wider">
                                                    {{ strtoupper($item->user?->role ?? '') }}
                                                </span>
                                            </td>
                                        @endif

                                        <td class="px-2 py-2 text-center align-middle whitespace-nowrap">
                                            @if ($showDualAction)
                                                <div class="flex items-center justify-center gap-1">
                                                    <a href="{{ route($viewRoute, $item) }}" class="inline-flex items-center gap-1 px-1.5 py-1 rounded-lg text-[10px] font-semibold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-white/15 hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm">
                                                        <i class="fas fa-eye text-slate-500"></i>
                                                        <span>Detail</span>
                                                    </a>
                                                    <a href="{{ route($realisasiRoute, $item) }}" class="inline-flex items-center gap-1 px-1.5 py-1 rounded-lg text-[10px] font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition">
                                                        <i class="fas fa-tools"></i>
                                                        <span>Realisasi</span>
                                                    </a>
                                                </div>
                                            @else
                                                <a href="{{ route($detailRoute, $item) }}" class="inline-flex items-center gap-1 px-1.5 py-1 rounded-lg text-[10px] font-semibold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-white/15 hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm">
                                                    <i class="fas fa-eye text-slate-500"></i>
                                                    <span>Detail</span>
                                                </a>
                                            @endif
                                        </td>

                                        @if ($canApprove)
                                            <td class="px-2 py-2 text-center align-middle whitespace-nowrap">
                                                <div class="flex items-center justify-center gap-1">
                                                    <button
                                                        type="button"
                                                        @click="showApproveModal = true; approveAction = 'APPROVE'"
                                                        class="inline-flex items-center gap-1 px-1.5 py-1 rounded-lg text-[10px] font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition"
                                                    >
                                                        <i class="fas fa-check-circle"></i>
                                                        <span>Setujui</span>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        @click="showApproveModal = true; approveAction = 'REJECT'"
                                                        class="inline-flex items-center gap-1 px-1.5 py-1 rounded-lg text-[10px] font-semibold bg-rose-600 hover:bg-rose-700 text-white shadow-sm transition"
                                                    >
                                                        <i class="fas fa-times-circle"></i>
                                                        <span>Tolak</span>
                                                    </button>
                                                </div>

                                                <template x-teleport="body">
                                                    <div
                                                        x-show="showApproveModal"
                                                        x-cloak
                                                        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                                                        @click.self="showApproveModal = false"
                                                    >
                                                        <div class="w-full max-w-lg p-6 overflow-hidden text-left bg-white border shadow-2xl rounded-2xl dark:bg-slate-900 border-slate-200 dark:border-white/10">
                                                            <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-slate-200 dark:border-white/10">
                                                                <div class="flex items-center gap-2.5">
                                                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl" :class="approveAction === 'APPROVE' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400' : 'bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400'">
                                                                        <i class="text-sm fas" :class="approveAction === 'APPROVE' ? 'fa-clipboard-check' : 'fa-exclamation-circle'"></i>
                                                                    </span>
                                                                    <h3 class="text-sm font-bold tracking-tight text-slate-800 dark:text-slate-100" x-text="approveAction === 'APPROVE' ? 'Konfirmasi Persetujuan Pengajuan' : 'Konfirmasi Penolakan Pengajuan'"></h3>
                                                                </div>
                                                                <button type="button" @click="showApproveModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </div>

                                                            <form
                                                                method="POST"
                                                                :action="approveAction === 'APPROVE' ? '{{ route($approveRoute, $item) }}' : '{{ route($rejectRoute, $item) }}'"
                                                                class="space-y-4"
                                                            >
                                                                @csrf
                                                                <div>
                                                                    <label class="block mb-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200">
                                                                        Catatan Approval / Alasan <span x-show="approveAction === 'REJECT'" class="text-rose-500">*</span>
                                                                    </label>
                                                                    <textarea
                                                                        name="catatan"
                                                                        rows="3"
                                                                        :required="approveAction === 'REJECT'"
                                                                        placeholder="Tambahkan catatan (opsional untuk persetujuan, wajib jika ditolak)..."
                                                                        class="w-full px-3 py-2 text-xs bg-white shadow-sm rounded-xl border-slate-300 text-slate-700 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-800 dark:text-slate-100"
                                                                    ></textarea>
                                                                </div>

                                                                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-200 dark:border-white/10">
                                                                    <button type="button" @click="showApproveModal = false" class="px-4 py-2 text-xs font-semibold bg-white border rounded-xl border-slate-300 text-slate-700 hover:bg-slate-50 dark:border-white/15 dark:bg-slate-800 dark:text-slate-200">Batal</button>
                                                                    <button
                                                                        type="submit"
                                                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white shadow-sm transition"
                                                                        :class="approveAction === 'APPROVE' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'"
                                                                    >
                                                                        <i class="fas fa-check"></i>
                                                                        <span x-text="approveAction === 'APPROVE' ? 'Ya, Setujui Pengajuan' : 'Ya, Tolak Pengajuan'"></span>
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </template>
                                            </td>
                                        @endif
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (method_exists($pengajuan, 'links'))
                    <div class="p-4 border-t border-slate-200 dark:border-white/10">
                        {{ $pengajuan->links() }}
                    </div>
                @endif
            </div>
        @endif
    </section>
</x-layouts.sbadmin>
