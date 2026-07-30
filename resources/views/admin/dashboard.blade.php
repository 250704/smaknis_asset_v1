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

        $realisasiCount = $totalPerawatan + $totalPenggantian;
        $totalStatusPengajuan = max($totalPengajuan, 1);
        $persenPending = round(($pengajuanPending / $totalStatusPengajuan) * 100);
        $persenDiproses = round(($pengajuanDisetujui / $totalStatusPengajuan) * 100);
        $persenSelesai = round(($pengajuanSelesai / $totalStatusPengajuan) * 100);
    @endphp

    {{-- <div class="flex flex-wrap items-end justify-between gap-3 mb-6">
        <div>
            <h1 class="flex items-center gap-3 page-title">
                <span class="inline-flex items-center justify-center text-blue-600 h-9 w-9 rounded-xl bg-blue-500/10 dark:bg-blue-500/20 dark:text-blue-300">
                    <i class="text-sm fas fa-gauge-high"></i>
                </span>
                Dashboard Admin
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Ringkasan informasi inti inventaris dan pengajuan.</p>
        </div>
        <p class="inline-flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
            <i class="far fa-calendar text-slate-400"></i>
            {{ now()->isoFormat('dddd, D MMMM Y') }}
        </p>
    </div> --}}

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="panel">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Total Sarana</p>
                <span class="inline-flex items-center justify-center text-blue-600 rounded-lg h-9 w-9 bg-blue-500/10 dark:bg-blue-500/20 dark:text-blue-300">
                    <i class="text-sm fas fa-layer-group"></i>
                </span>
            </div>
            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($totalSarana) }}</p>
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ $totalRuangan }} ruangan - {{ $totalGedung }} gedung</p>
        </div>

        <div class="panel">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Total Pengajuan</p>
                <span class="inline-flex items-center justify-center rounded-lg h-9 w-9 bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-300">
                    <i class="text-sm fas fa-file-signature"></i>
                </span>
            </div>
            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($totalPengajuan) }}</p>
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ $pengajuanPending }} pending - {{ $pengajuanSelesai }} selesai</p>
        </div>

        <div class="panel">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Realisasi</p>
                <span class="inline-flex items-center justify-center rounded-lg h-9 w-9 bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-300">
                    <i class="text-sm fas fa-chart-line"></i>
                </span>
            </div>
            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($realisasiCount) }}</p>
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ $totalPerawatan }} perawatan - {{ $totalPenggantian }} penggantian</p>
        </div>

        <div class="panel">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Nilai Sarana</p>
                <span class="inline-flex items-center justify-center rounded-lg h-9 w-9 bg-violet-500/10 text-violet-600 dark:bg-violet-500/20 dark:text-violet-300">
                    <i class="text-sm fas fa-wallet"></i>
                </span>
            </div>
            <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">Rp {{ number_format($nilaiTotalSarana, 0, ',', '.') }}</p>
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ $totalKategori }} kategori sarana</p>
        </div>
    </div>

    <div class="grid gap-4 mt-6 lg:grid-cols-3">
        <div class="panel lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h2 class="inline-flex items-center gap-2 text-sm font-semibold text-slate-800 dark:text-white">
                    <i class="text-blue-500 fas fa-chart-simple"></i>
                    Status Pengajuan
                </h2>
                <a href="{{ route('admin.pengajuan.index') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">Lihat semua</a>
            </div>

            <div class="space-y-3">
                <div>
                    <div class="flex items-center justify-between mb-1 text-xs">
                        <span class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-300"><i class="fas fa-hourglass-half text-amber-500"></i>Pending</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $pengajuanPending }} ({{ $persenPending }}%)</span>
                    </div>
                    <div class="h-2 rounded-full bg-slate-200 dark:bg-slate-700">
                        <div class="h-2 rounded-full bg-amber-500" style="width: {{ min($persenPending, 100) }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1 text-xs">
                        <span class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-300"><i class="text-blue-500 fas fa-gears"></i>Diproses / Disetujui</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $pengajuanDisetujui }} ({{ $persenDiproses }}%)</span>
                    </div>
                    <div class="h-2 rounded-full bg-slate-200 dark:bg-slate-700">
                        <div class="h-2 bg-blue-500 rounded-full" style="width: {{ min($persenDiproses, 100) }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1 text-xs">
                        <span class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-300"><i class="fas fa-circle-check text-emerald-500"></i>Selesai</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $pengajuanSelesai }} ({{ $persenSelesai }}%)</span>
                    </div>
                    <div class="h-2 rounded-full bg-slate-200 dark:bg-slate-700">
                        <div class="h-2 rounded-full bg-emerald-500" style="width: {{ min($persenSelesai, 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <h2 class="inline-flex items-center gap-2 text-sm font-semibold text-slate-800 dark:text-white">
                <i class="fas fa-bolt text-amber-500"></i>
                Aksi Cepat
            </h2>
            <div class="grid gap-2 mt-4">
                <a href="{{ route('admin.scan') }}" class="justify-start btn-secondary"><i class="fas fa-qrcode text-slate-500"></i>Scan QR</a>
                <a href="{{ route('admin.sarana.index') }}" class="justify-start btn-secondary"><i class="fas fa-boxes-stacked text-slate-500"></i>Kelola Sarana</a>
                <a href="{{ route('admin.sarana.create') }}" class="justify-start btn-secondary"><i class="fas fa-plus text-slate-500"></i>Tambah Sarana</a>
                <a href="{{ route('admin.pengajuan.index') }}" class="justify-start btn-secondary"><i class="fas fa-file-circle-check text-slate-500"></i>Kelola Pengajuan</a>
                <a href="{{ route('admin.cetak-qr.index') }}" class="justify-start btn-secondary"><i class="fas fa-print text-slate-500"></i>Cetak QR</a>
            </div>
        </div>
    </div>

    <div class="grid gap-4 mt-6 lg:grid-cols-2">
        <div class="panel">
            <div class="flex items-center justify-between mb-4">
                <h2 class="inline-flex items-center gap-2 text-sm font-semibold text-slate-800 dark:text-white">
                    <i class="fas fa-file-lines text-emerald-500"></i>
                    Pengajuan Terbaru
                </h2>
                <a href="{{ route('admin.pengajuan.index') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">Lihat semua</a>
            </div>

            <div class="space-y-3">
                @forelse($pengajuanTerbaru as $pengajuan)
                    <div class="px-3 py-2 bg-white border rounded-xl border-slate-200 dark:border-white/10 dark:bg-white/5">
                        <p class="inline-flex items-center gap-2 text-sm font-medium truncate text-slate-800 dark:text-white">
                            <i class="fas fa-angle-right text-[10px] text-slate-400"></i>
                            {{ $pengajuan->judul_pengajuan }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ $pengajuan->user?->display_name ?? 'Unknown' }} - {{ $statusPengajuanLabels[$pengajuan->status_pengajuan] ?? $pengajuan->status_pengajuan }}
                        </p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400">Belum ada pengajuan terbaru.</p>
                @endforelse
            </div>
        </div>

        <div class="panel">
            <div class="flex items-center justify-between mb-4">
                <h2 class="inline-flex items-center gap-2 text-sm font-semibold text-slate-800 dark:text-white">
                    <i class="fas fa-clock-rotate-left text-violet-500"></i>
                    Aktivitas Terbaru
                </h2>
                <a href="{{ route('admin.feature', ['feature' => 'log-aktivitas']) }}" class="text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">Lihat semua</a>
            </div>

            <div class="space-y-3">
                @forelse($aktivitasTerbaru as $aktivitas)
                    <div class="px-3 py-2 bg-white border rounded-xl border-slate-200 dark:border-white/10 dark:bg-white/5">
                        <p class="inline-flex items-center gap-2 text-sm font-medium text-slate-800 dark:text-white">
                            <i class="far fa-circle-dot text-[10px] text-slate-400"></i>
                            {{ $aktivitas->aktivitas }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $aktivitas->user?->display_name ?? 'System' }} - {{ $aktivitas->created_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400">Belum ada aktivitas terbaru.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.sbadmin>
