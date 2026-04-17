<x-layouts.sbadmin>
    @php
        $roleLabel = match ($role) {
            'admin' => 'Admin',
            'kepala_sarana' => 'Kepala Sarana',
            'bendahara' => 'Bendahara',
            'kepala_sekolah' => 'Kepala Sekolah',
            default => strtoupper(str_replace('_', ' ', $role)),
        };
    @endphp

    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="page-title">Laporan Sistem Sarpras</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Data real-time inventaris, pengajuan, kerusakan, dan keuangan.
            </p>
        </div>
        <div class="flex items-center gap-2">
            @php
                $baseParams = request()->query();
                $excelRoute = route($role . '.laporan.export.excel', $baseParams);
                $pdfRoute = route($role . '.laporan.export.pdf', $baseParams);
            @endphp
            <a href="{{ $pdfRoute }}" target="_blank" class="btn-secondary">Export PDF</a>
            <a href="{{ $excelRoute }}" class="btn-primary">Download Excel</a>
            <span class="inline-flex rounded-full border border-cyan-400/30 bg-cyan-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-cyan-700 dark:text-cyan-200">
                {{ $roleLabel }}
            </span>
        </div>
    </div>

    <details class="panel group overflow-hidden" open>
        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 rounded-2xl p-1">
            <div>
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-100">Filter Laporan</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Semua angka akan otomatis menyesuaikan filter.</p>
            </div>
            <svg class="h-4 w-4 text-slate-500 transition-transform duration-200 group-open:rotate-180 dark:text-slate-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.167l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </summary>

        <div class="mt-4 border-t border-slate-200 pt-4 dark:border-white/10">
            <form method="GET" class="grid gap-3 md:grid-cols-12">
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Dari</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Sampai</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Gedung</label>
                    <select name="gedung_id" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                        <option value="0">Semua gedung</option>
                        @foreach ($gedungList as $gedung)
                            <option value="{{ $gedung->id }}" @selected((int) $filters['gedung_id'] === (int) $gedung->id)>{{ $gedung->nama_gedung }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Ruangan</label>
                    <select name="ruangan_id" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                        <option value="0">Semua ruangan</option>
                        @foreach ($ruanganList as $ruangan)
                            <option value="{{ $ruangan->id }}" @selected((int) $filters['ruangan_id'] === (int) $ruangan->id)>{{ $ruangan->nama_ruangan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Kategori</label>
                    <select name="kategori_id" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                        <option value="0">Semua kategori</option>
                        @foreach ($kategoriList as $kategori)
                            <option value="{{ $kategori->id }}" @selected((int) $filters['kategori_id'] === (int) $kategori->id)>{{ $kategori->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status Aset</label>
                    <select name="status" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                        <option value="">Semua status</option>
                        <option value="AKTIF" @selected($filters['status'] === 'AKTIF')>AKTIF</option>
                        <option value="NONAKTIF" @selected($filters['status'] === 'NONAKTIF')>NONAKTIF</option>
                    </select>
                </div>
                <div class="md:col-span-9">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Pencarian</label>
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Kode aset, nama aset, atau judul pengajuan..." class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                </div>
                <div class="flex items-end gap-2 md:col-span-3">
                    <a href="{{ url()->current() }}" class="btn-secondary w-full justify-center">Reset</a>
                    <button type="submit" class="btn-primary w-full justify-center">Terapkan</button>
                </div>
            </form>
        </div>
    </details>

    <section class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="panel">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Aset</p>
            <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($kpi['total_aset']) }}</p>
            <p class="mt-1 text-xs text-slate-500">Aktif {{ number_format($kpi['aset_aktif']) }} • Nonaktif {{ number_format($kpi['aset_nonaktif']) }}</p>
        </div>
        <div class="panel">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Aset Perlu Perhatian</p>
            <p class="mt-2 text-2xl font-bold text-amber-600 dark:text-amber-300">{{ number_format($kpi['aset_rusak']) }}</p>
            <p class="mt-1 text-xs text-slate-500">Kondisi ringan, berat, tidak layak</p>
        </div>
        <div class="panel">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pengajuan</p>
            <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($kpi['total_pengajuan']) }}</p>
            <p class="mt-1 text-xs text-slate-500">Menunggu {{ number_format($kpi['pengajuan_menunggu']) }} • Selesai {{ number_format($kpi['pengajuan_selesai']) }}</p>
        </div>
        <div class="panel">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Laporan Kerusakan</p>
            <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($kpi['total_kerusakan']) }}</p>
            <p class="mt-1 text-xs text-slate-500">Aktif {{ number_format($kpi['kerusakan_aktif']) }} • Selesai {{ number_format($kpi['kerusakan_selesai']) }}</p>
        </div>
    </section>

    <section class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-5">
        <div class="panel xl:col-span-1">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Estimasi Pengajuan</p>
            <p class="mt-2 text-lg font-bold text-slate-900 dark:text-white">Rp {{ number_format($finance['estimasi_total'], 0, ',', '.') }}</p>
        </div>
        <div class="panel xl:col-span-1">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Realisasi Perawatan</p>
            <p class="mt-2 text-lg font-bold text-slate-900 dark:text-white">Rp {{ number_format($finance['realisasi_perawatan'], 0, ',', '.') }}</p>
        </div>
        <div class="panel xl:col-span-1">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Realisasi Penggantian</p>
            <p class="mt-2 text-lg font-bold text-slate-900 dark:text-white">Rp {{ number_format($finance['realisasi_penggantian'], 0, ',', '.') }}</p>
        </div>
        <div class="panel xl:col-span-1">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Realisasi</p>
            <p class="mt-2 text-lg font-bold text-blue-600 dark:text-cyan-300">Rp {{ number_format($finance['total_realisasi'], 0, ',', '.') }}</p>
        </div>
        <div class="panel xl:col-span-1">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Selisih Anggaran</p>
            <p class="mt-2 text-lg font-bold {{ $finance['selisih_anggaran'] >= 0 ? 'text-emerald-600 dark:text-emerald-300' : 'text-rose-600 dark:text-rose-300' }}">
                Rp {{ number_format($finance['selisih_anggaran'], 0, ',', '.') }}
            </p>
        </div>
    </section>

    <section class="mt-4 grid gap-4 xl:grid-cols-2">
        <div class="panel">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-100">Tren Pengajuan Bulanan</h2>
            <div class="mt-3 space-y-2">
                @forelse ($trenPengajuan as $row)
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 text-sm dark:bg-slate-900/50">
                        <span class="font-medium text-slate-700 dark:text-slate-200">{{ \Carbon\Carbon::createFromFormat('Y-m', $row->bulan)->translatedFormat('M Y') }}</span>
                        <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-500/20 dark:text-blue-200">{{ number_format($row->total) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada data pengajuan pada periode ini.</p>
                @endforelse
            </div>
        </div>

        <div class="panel">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-100">Tren Kerusakan Bulanan</h2>
            <div class="mt-3 space-y-2">
                @forelse ($trenKerusakan as $row)
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 text-sm dark:bg-slate-900/50">
                        <span class="font-medium text-slate-700 dark:text-slate-200">{{ \Carbon\Carbon::createFromFormat('Y-m', $row->bulan)->translatedFormat('M Y') }}</span>
                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-200">{{ number_format($row->total) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada data kerusakan pada periode ini.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="mt-4 grid gap-4 xl:grid-cols-2">
        <div class="panel overflow-hidden p-0">
            <div class="border-b border-slate-200 px-4 py-3 dark:border-white/10">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-100">Pengajuan Terbaru</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] divide-y divide-slate-200 text-sm dark:divide-white/10">
                    <thead class="bg-slate-50 dark:bg-white/[0.04]">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Kode Aset</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Judul</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @forelse ($latestPengajuan as $row)
                            <tr class="bg-white/70 dark:bg-transparent">
                                <td class="px-4 py-2 font-mono text-xs text-slate-700 dark:text-slate-200">{{ $row->aset?->kode_aset ?? '-' }}</td>
                                <td class="px-4 py-2 text-slate-700 dark:text-slate-200">{{ $row->judul_pengajuan }}</td>
                                <td class="px-4 py-2 text-slate-600 dark:text-slate-300">{{ str_replace('_', ' ', $row->status_pengajuan) }}</td>
                                <td class="px-4 py-2 text-slate-600 dark:text-slate-300">{{ $row->created_at?->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-center text-sm text-slate-500">Belum ada data pengajuan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel overflow-hidden p-0">
            <div class="border-b border-slate-200 px-4 py-3 dark:border-white/10">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-100">Aset Perlu Tindak Lanjut</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] divide-y divide-slate-200 text-sm dark:divide-white/10">
                    <thead class="bg-slate-50 dark:bg-white/[0.04]">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Kode</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Nama</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Lokasi</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Kondisi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @forelse ($asetPerluPerhatian as $aset)
                            <tr class="bg-white/70 dark:bg-transparent">
                                <td class="px-4 py-2 font-mono text-xs text-slate-700 dark:text-slate-200">{{ $aset->kode_aset }}</td>
                                <td class="px-4 py-2 text-slate-700 dark:text-slate-200">{{ $aset->nama_aset }}</td>
                                <td class="px-4 py-2 text-slate-600 dark:text-slate-300">{{ $aset->ruangan?->nama_ruangan }} - {{ $aset->ruangan?->gedung?->nama_gedung }}</td>
                                <td class="px-4 py-2">
                                    <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-200">{{ $aset->kondisi_terkini }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-center text-sm text-slate-500">Tidak ada aset yang perlu perhatian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-layouts.sbadmin>



