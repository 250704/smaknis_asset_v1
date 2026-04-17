<x-layouts.sbadmin>
    @php
        $statusPengajuanLabels = [
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
    @endphp

    {{-- Header --}}
    <div class="mb-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="page-title">Dashboard Admin</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Selamat datang! Berikut ringkasan sistem inventaris sarana prasarana.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                    <i class="fas fa-circle text-[6px] mr-1.5"></i>
                    Online
                </span>
                <span class="text-xs text-slate-500 dark:text-slate-400">
                    {{ now()->isoFormat('dddd, D MMMM Y') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Kartu Statistik Utama --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {{-- Total Aset --}}
        <div class="stat-card group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Total Aset</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($totalAset) }}</p>
                    <div class="mt-2 flex items-center gap-2 text-xs">
                        <span class="text-slate-500 dark:text-slate-400">{{ $totalRuangan }} Ruangan</span>
                        <span class="text-slate-300 dark:text-slate-600">•</span>
                        <span class="text-slate-500 dark:text-slate-400">{{ $totalGedung }} Gedung</span>
                    </div>
                </div>
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-400 text-white shadow-lg shadow-blue-500/25 transition-transform group-hover:scale-110">
                    <i class="fas fa-boxes-stacked text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Total Pengajuan --}}
        <div class="stat-card group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Total Pengajuan</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($totalPengajuan) }}</p>
                    <div class="mt-2 flex items-center gap-2 text-xs">
                        <span class="text-amber-600 dark:text-amber-400">{{ $pengajuanPending }} Pending</span>
                        <span class="text-slate-300 dark:text-slate-600">•</span>
                        <span class="text-emerald-600 dark:text-emerald-400">{{ $pengajuanSelesai }} Selesai</span>
                    </div>
                </div>
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-400 text-white shadow-lg shadow-emerald-500/25 transition-transform group-hover:scale-110">
                    <i class="fas fa-file-signature text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Realisasi --}}
        <div class="stat-card group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Realisasi</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($totalPerawatan + $totalPenggantian) }}</p>
                    <div class="mt-2 flex items-center gap-2 text-xs">
                        <span class="text-slate-500 dark:text-slate-400">{{ $totalPerawatan }} Perawatan</span>
                        <span class="text-slate-300 dark:text-slate-600">•</span>
                        <span class="text-slate-500 dark:text-slate-400">{{ $totalPenggantian }} Penggantian</span>
                    </div>
                </div>
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-400 text-white shadow-lg shadow-amber-500/25 transition-transform group-hover:scale-110">
                    <i class="fas fa-tools text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Nilai Inventaris --}}
        <div class="stat-card group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Nilai Inventaris</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">Rp {{ number_format($nilaiTotalAset, 0, ',', '.') }}</p>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ $totalKategori }} Kategori Aset</p>
                </div>
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-purple-400 text-white shadow-lg shadow-violet-500/25 transition-transform group-hover:scale-110">
                    <i class="fas fa-coins text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Tracking Keuangan Pengajuan --}}
    <div class="mt-8">
        <h3 class="mb-4 text-sm font-semibold text-slate-800 dark:text-white">
            <i class="fas fa-chart-line text-emerald-500 mr-2"></i>Tracking Keuangan Pengajuan
        </h3>
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="dashboard-card border-l-4 border-l-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Total Estimasi</p>
                        <p class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">
                            Rp {{ number_format($totalEstimasi ?? 0, 0, ',', '.') }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $pengajuanDenganBiaya }} pengajuan</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/15 text-blue-600 dark:text-blue-400">
                        <i class="fas fa-calculator text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="dashboard-card border-l-4 border-l-amber-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Anggaran Disetujui</p>
                        <p class="mt-2 text-2xl font-bold text-amber-600 dark:text-amber-400">
                            Rp {{ number_format($totalAnggaranDisetujui ?? 0, 0, ',', '.') }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Disetujui bendahara</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400">
                        <i class="fas fa-check-circle text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="dashboard-card border-l-4 border-l-emerald-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Realisasi</p>
                        <p class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                            Rp {{ number_format($totalRealisasi ?? 0, 0, ',', '.') }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            @if($totalEstimasi > 0 && $totalRealisasi > 0)
                                @php $persentase = ($totalRealisasi / $totalEstimasi) * 100 @endphp
                                @if($persentase <= 100)
                                    <span class="text-emerald-600 dark:text-emerald-400">✅</span>
                                @else
                                    <span class="text-rose-600 dark:text-rose-400">⚠️</span>
                                @endif
                                {{ number_format($persentase, 1) }}% dari estimasi
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                        <i class="fas fa-wallet text-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Progress Bar Estimasi vs Realisasi --}}
        @if($totalEstimasi > 0)
            <div class="mt-4 dashboard-card">
                <div class="mb-2 flex items-center justify-between text-xs">
                    <span class="font-medium text-slate-600 dark:text-slate-300">Progress Realisasi vs Estimasi</span>
                    <span class="font-medium text-slate-600 dark:text-slate-300">
                        @php
                            $persentase = $totalEstimasi > 0 ? ($totalRealisasi / $totalEstimasi) * 100 : 0;
                            $selisih = $totalEstimasi - $totalRealisasi;
                        @endphp
                        {{ number_format($persentase, 1) }}% 
                        (@if($selisih >= 0)
                            <span class="text-emerald-600">Hemat: Rp {{ number_format($selisih, 0, ',', '.') }}</span>
                        @else
                            <span class="text-rose-600">Over: Rp {{ number_format(abs($selisih), 0, ',', '.') }}</span>
                        @endif)
                    </span>
                </div>
                <div class="h-4 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                    <div 
                        class="h-full rounded-full transition-all duration-500 
                            @if($persentase <= 100) bg-gradient-to-r from-emerald-500 to-teal-400 @else bg-gradient-to-r from-rose-500 to-orange-400 @endif"
                        style="width: {{ min($persentase, 100) }}%"
                    ></div>
                </div>
            </div>
        @endif
    </div>

    {{-- Grafik dan Chart --}}
    <div class="mt-8 grid gap-4 lg:grid-cols-2">
        {{-- Grafik Kondisi Aset --}}
        <div class="dashboard-card">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-white">
                        <i class="fas fa-chart-pie text-cyan-500 mr-2"></i>Kondisi Aset
                    </h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Distribusi kondisi seluruh aset</p>
                </div>
                <a href="{{ route('admin.aset.index') }}" class="text-xs font-medium text-cyan-600 hover:text-cyan-700 dark:text-cyan-400 dark:hover:text-cyan-300">
                    Lihat Detail <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="relative h-64">
                <canvas id="kondisiAsetChart"></canvas>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-3">
                <div class="flex items-center gap-2 rounded-lg bg-emerald-50 p-2 dark:bg-emerald-500/10">
                    <div class="h-3 w-3 rounded-full bg-emerald-500"></div>
                    <div>
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300">Baik</p>
                        <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ $asetBaik }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 rounded-lg bg-amber-50 p-2 dark:bg-amber-500/10">
                    <div class="h-3 w-3 rounded-full bg-amber-500"></div>
                    <div>
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300">Rusak Ringan</p>
                        <p class="text-sm font-bold text-amber-600 dark:text-amber-400">{{ $asetRusakRingan }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 rounded-lg bg-rose-50 p-2 dark:bg-rose-500/10">
                    <div class="h-3 w-3 rounded-full bg-rose-500"></div>
                    <div>
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300">Rusak Berat</p>
                        <p class="text-sm font-bold text-rose-600 dark:text-rose-400">{{ $asetRusakBerat }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 rounded-lg bg-slate-100 p-2 dark:bg-slate-700/50">
                    <div class="h-3 w-3 rounded-full bg-slate-500"></div>
                    <div>
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300">Tidak Layak</p>
                        <p class="text-sm font-bold text-slate-600 dark:text-slate-400">{{ $asetHilang }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Grafik Pengajuan per Bulan --}}
        <div class="dashboard-card">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-white">
                        <i class="fas fa-chart-line text-emerald-500 mr-2"></i>Tren Pengajuan
                    </h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">6 bulan terakhir</p>
                </div>
                <a href="{{ route('admin.pengajuan.index') }}" class="text-xs font-medium text-cyan-600 hover:text-cyan-700 dark:text-cyan-400 dark:hover:text-cyan-300">
                    Lihat Detail <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="relative h-64">
                <canvas id="pengajuanBulanChart"></canvas>
            </div>
            <div class="mt-4 flex items-center justify-center gap-6 text-xs">
                <div class="flex items-center gap-2">
                    <div class="h-3 w-3 rounded bg-blue-500"></div>
                    <span class="text-slate-600 dark:text-slate-300">Total: <strong>{{ $totalPengajuan }}</strong></span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-3 w-3 rounded bg-emerald-500"></div>
                    <span class="text-slate-600 dark:text-slate-300">Rata-rata: <strong>{{ $totalPengajuan > 0 ? round($totalPengajuan / 6, 1) : 0 }}/bulan</strong></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Pengajuan Cards --}}
    <div class="mt-8">
        <h3 class="mb-4 text-sm font-semibold text-slate-800 dark:text-white">
            <i class="fas fa-clipboard-list text-blue-500 mr-2"></i>Status Pengajuan
        </h3>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('admin.pengajuan.index') }}" class="stat-card-hover group block rounded-2xl border-l-4 border-amber-500 p-4 transition-all hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Pending Review</p>
                        <p class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400 group-hover:scale-110 group-hover:text-amber-700 dark:group-hover:text-amber-300">{{ $pengajuanPending }}</p>
                    </div>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/15 text-amber-600 transition-transform group-hover:scale-110 dark:text-amber-400">
                        <i class="fas fa-clock text-sm"></i>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.pengajuan.index') }}" class="stat-card-hover group block rounded-2xl border-l-4 border-blue-500 p-4 transition-all hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Disetujui / Proses</p>
                        <p class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400 group-hover:scale-110 group-hover:text-blue-700 dark:group-hover:text-blue-300">{{ $pengajuanDisetujui }}</p>
                    </div>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-500/15 text-blue-600 transition-transform group-hover:scale-110 dark:text-blue-400">
                        <i class="fas fa-check-circle text-sm"></i>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.pengajuan.index') }}" class="stat-card-hover group block rounded-2xl border-l-4 border-rose-500 p-4 transition-all hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Ditolak</p>
                        <p class="mt-1 text-2xl font-bold text-rose-600 dark:text-rose-400 group-hover:scale-110 group-hover:text-rose-700 dark:group-hover:text-rose-300">{{ $pengajuanDitolak }}</p>
                    </div>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-500/15 text-rose-600 transition-transform group-hover:scale-110 dark:text-rose-400">
                        <i class="fas fa-times-circle text-sm"></i>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.pengajuan.index') }}" class="stat-card-hover group block rounded-2xl border-l-4 border-emerald-500 p-4 transition-all hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Selesai</p>
                        <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400 group-hover:scale-110 group-hover:text-emerald-700 dark:group-hover:text-emerald-300">{{ $pengajuanSelesai }}</p>
                    </div>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-600 transition-transform group-hover:scale-110 dark:text-emerald-400">
                        <i class="fas fa-flag-checkered text-sm"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Tabel Aktivitas dan Pengajuan Terbaru --}}
    <div class="mt-8 grid gap-4 lg:grid-cols-2">
        {{-- Aktivitas Terbaru --}}
        <div class="dashboard-card">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-white">
                        <i class="fas fa-history text-amber-500 mr-2"></i>Aktivitas Terbaru
                    </h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">5 aktivitas terakhir</p>
                </div>
                <a href="{{ route('admin.feature', ['feature' => 'log-aktivitas']) }}" class="text-xs font-medium text-cyan-600 hover:text-cyan-700 dark:text-cyan-400 dark:hover:text-cyan-300">
                    Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="space-y-2">
                @forelse($aktivitasTerbaru as $aktivitas)
                    <div class="group flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3 transition hover:border-cyan-300 hover:bg-cyan-50/50 dark:border-white/10 dark:bg-white/5 dark:hover:border-cyan-600 dark:hover:bg-cyan-900/10">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600 transition group-hover:bg-cyan-100 group-hover:text-cyan-600 dark:bg-white/10 dark:text-slate-300 dark:group-hover:bg-cyan-900/30 dark:group-hover:text-cyan-400">
                            @if(str_contains(strtolower($aktivitas->aktivitas), 'login'))
                                <i class="fas fa-sign-in-alt text-xs"></i>
                            @elseif(str_contains(strtolower($aktivitas->aktivitas), 'tambah') || str_contains(strtolower($aktivitas->aktivitas), 'create'))
                                <i class="fas fa-plus text-xs"></i>
                            @elseif(str_contains(strtolower($aktivitas->aktivitas), 'edit') || str_contains(strtolower($aktivitas->aktivitas), 'update'))
                                <i class="fas fa-edit text-xs"></i>
                            @elseif(str_contains(strtolower($aktivitas->aktivitas), 'hapus') || str_contains(strtolower($aktivitas->aktivitas), 'delete'))
                                <i class="fas fa-trash text-xs"></i>
                            @else
                                <i class="fas fa-clipboard-list text-xs"></i>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 dark:text-white truncate">{{ $aktivitas->aktivitas }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                <span class="font-medium text-slate-600 dark:text-slate-300">{{ $aktivitas->user?->display_name ?? 'System' }}</span>
                                • {{ $aktivitas->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-8 text-center">
                        <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800">
                            <i class="fas fa-inbox text-lg text-slate-400"></i>
                        </div>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Belum ada aktivitas</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Aktivitas akan muncul setelah ada tindakan</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Pengajuan Terbaru --}}
        <div class="dashboard-card">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-white">
                        <i class="fas fa-file-alt text-emerald-500 mr-2"></i>Pengajuan Terbaru
                    </h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">5 pengajuan terakhir</p>
                </div>
                <a href="{{ route('admin.pengajuan.index') }}" class="text-xs font-medium text-cyan-600 hover:text-cyan-700 dark:text-cyan-400 dark:hover:text-cyan-300">
                    Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="space-y-2">
                @forelse($pengajuanTerbaru as $pengajuan)
                    <div class="group flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3 transition hover:border-cyan-300 hover:bg-cyan-50/50 dark:border-white/10 dark:bg-white/5 dark:hover:border-cyan-600 dark:hover:bg-cyan-900/10">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg transition group-hover:scale-110
                            @if(in_array($pengajuan->status_pengajuan, ['DIAJUKAN', 'MENUNGGU_VERIFIKASI_TEKNIS', 'MENUNGGU_VERIFIKASI_KEUANGAN'])) bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400
                            @elseif(in_array($pengajuan->status_pengajuan, ['DISETUJUI_KASARANA', 'DISETUJUI_BENDAHARA', 'DISETUJUI_KEPSEK', 'DIPROSES'])) bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400
                            @elseif($pengajuan->status_pengajuan === 'DITOLAK') bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400
                            @else bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400
                            @endif">
                            <i class="fas fa-file-signature text-xs"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 dark:text-white truncate">{{ $pengajuan->judul_pengajuan }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                <span class="font-medium text-slate-600 dark:text-slate-300">{{ $pengajuan->user?->display_name ?? 'Unknown' }}</span>
                                • 
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium
                                    @if(in_array($pengajuan->status_pengajuan, ['DIAJUKAN', 'MENUNGGU_VERIFIKASI_TEKNIS', 'MENUNGGU_VERIFIKASI_KEUANGAN'])) bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300
                                    @elseif(in_array($pengajuan->status_pengajuan, ['DISETUJUI_KASARANA', 'DISETUJUI_BENDAHARA', 'DISETUJUI_KEPSEK', 'DIPROSES'])) bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300
                                    @elseif($pengajuan->status_pengajuan === 'DITOLAK') bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300
                                    @else bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300
                                    @endif">
                                    {{ $statusPengajuanLabels[$pengajuan->status_pengajuan] ?? $pengajuan->status_pengajuan }}
                                </span>
                            </p>
                        </div>
                        <div class="text-xs text-slate-400 dark:text-slate-500">
                            {{ $pengajuan->created_at->diffForHumans() }}
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-8 text-center">
                        <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800">
                            <i class="fas fa-inbox text-lg text-slate-400"></i>
                        </div>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Belum ada pengajuan</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Pengajuan akan muncul setelah ada yang mengajukan</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="mt-8">
        <h3 class="mb-4 text-sm font-semibold text-slate-800 dark:text-white">
            <i class="fas fa-bolt text-amber-500 mr-2"></i>Aksi Cepat
        </h3>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <a href="{{ route('admin.scan') }}" class="kpi-card group block">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Scan</p>
                        <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-cyan-600 dark:group-hover:text-cyan-400">QR Action Hub</p>
                    </div>
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-500/20 text-cyan-700 transition-transform group-hover:scale-110 group-hover:bg-cyan-500/30 dark:text-cyan-200">
                        <i class="fas fa-qrcode text-xs"></i>
                    </span>
                </div>
            </a>

            <a href="{{ route('admin.aset.index') }}" class="kpi-card group block">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Inventaris</p>
                        <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-blue-600 dark:group-hover:text-blue-400">Kelola Data Aset</p>
                    </div>
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-blue-500/20 text-blue-200 transition-transform group-hover:scale-110 group-hover:bg-blue-500/30">
                        <i class="fas fa-boxes-stacked text-xs"></i>
                    </span>
                </div>
            </a>

            <a href="{{ route('admin.aset.create') }}" class="kpi-card group block">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Tambah</p>
                        <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-emerald-600 dark:group-hover:text-emerald-400">Aset Baru</p>
                    </div>
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-200 transition-transform group-hover:scale-110 group-hover:bg-emerald-500/30">
                        <i class="fas fa-plus text-xs"></i>
                    </span>
                </div>
            </a>

            <a href="{{ route('admin.cetak-qr.index') }}" class="kpi-card group block">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Cetak</p>
                        <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-violet-600 dark:group-hover:text-violet-400">QR Code Label</p>
                    </div>
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-violet-500/20 text-violet-200 transition-transform group-hover:scale-110 group-hover:bg-violet-500/30">
                        <i class="fas fa-print text-xs"></i>
                    </span>
                </div>
            </a>

            <a href="{{ route('admin.feature', ['feature' => 'log-aktivitas']) }}" class="kpi-card group block">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Audit</p>
                        <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-amber-600 dark:group-hover:text-amber-400">Log Aktivitas</p>
                    </div>
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/20 text-amber-200 transition-transform group-hover:scale-110 group-hover:bg-amber-500/30">
                        <i class="fas fa-clock-rotate-left text-xs"></i>
                    </span>
                </div>
            </a>
        </div>
    </div>

    {{-- Chart.js Scripts --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#94a3b8' : '#64748b';
            const gridColor = isDark ? 'rgba(148, 163, 184, 0.1)' : 'rgba(148, 163, 184, 0.2)';

            // Chart Kondisi Aset (Doughnut)
            const kondisiCtx = document.getElementById('kondisiAsetChart');
            if (kondisiCtx) {
                new Chart(kondisiCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Baik', 'Rusak Ringan', 'Rusak Berat', 'Tidak Layak'],
                        datasets: [{
                            data: [{{ $asetBaik }}, {{ $asetRusakRingan }}, {{ $asetRusakBerat }}, {{ $asetHilang }}],
                            backgroundColor: [
                                'rgb(16, 185, 129)',
                                'rgb(245, 158, 11)',
                                'rgb(239, 68, 68)',
                                'rgb(100, 116, 139)'
                            ],
                            borderWidth: 0,
                            spacing: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: textColor,
                                    padding: 15,
                                    font: { size: 11, family: "'Plus Jakarta Sans', sans-serif" }
                                }
                            }
                        },
                        cutout: '65%'
                    }
                });
            }

            // Chart Pengajuan per Bulan (Bar)
            const pengajuanCtx = document.getElementById('pengajuanBulanChart');
            if (pengajuanCtx) {
                new Chart(pengajuanCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($pengajuanPerBulan->pluck('bulan')->toArray()) !!},
                        datasets: [{
                            label: 'Jumlah Pengajuan',
                            data: {!! json_encode($pengajuanPerBulan->pluck('jumlah')->toArray()) !!},
                            backgroundColor: 'rgb(59, 130, 246)',
                            borderRadius: 6,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    color: textColor,
                                    precision: 0
                                },
                                grid: { color: gridColor }
                            },
                            x: {
                                ticks: { color: textColor },
                                grid: { display: false }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</x-layouts.sbadmin>
