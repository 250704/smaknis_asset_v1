<x-layouts.sbadmin>
    @php
        $statusLabelMap = [
            'DILAPORKAN' => 'Dilaporkan',
            'DIVALIDASI' => 'Divalidasi',
            'DITINDAKLANJUTI' => 'Ditindaklanjuti',
            'SELESAI' => 'Selesai',
            'DITOLAK' => 'Ditolak',
        ];
        $tingkatLabelMap = [
            'RINGAN' => 'Ringan',
            'BERAT' => 'Berat',
            'TIDAK_LAYAK' => 'Tidak Layak',
        ];
        $rekomendasiLabelMap = [
            'PERAWATAN' => 'Perawatan',
            'PENGGANTIAN' => 'Penggantian',
        ];
        $pengajuanStatusLabelMap = [
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

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="page-title">Kerusakan Ditindaklanjuti</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Monitoring laporan kerusakan yang sudah divalidasi dan terhubung ke proses pengajuan.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
            {{ session('error') }}
        </div>
    @endif

    <details class="panel group overflow-hidden">
        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 rounded-2xl p-1">
            <div>
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-100">Filter Kerusakan</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Cari berdasarkan kode/nama aset atau status laporan.</p>
            </div>
            <svg class="h-4 w-4 text-slate-500 transition-transform duration-200 group-open:rotate-180 dark:text-slate-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.167l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </summary>

        <div class="mt-4 border-t border-slate-200 pt-4 dark:border-white/10">
            <form method="GET" class="space-y-4">
                <div class="grid gap-3 md:grid-cols-12">
                    <div class="md:col-span-7">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Pencarian</label>
                        <input
                            type="text"
                            name="q"
                            value="{{ $filters['q'] }}"
                            placeholder="Kode aset / nama aset..."
                            class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                        >
                    </div>
                    <div class="md:col-span-5">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</label>
                        <select name="status" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                            <option value="">Semua status</option>
                            @foreach ($statusList as $status)
                                <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $statusLabelMap[$status] ?? $status }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-200 pt-3 dark:border-white/10">
                    <a href="{{ url()->current() }}" class="btn-secondary">Reset</a>
                    <button type="submit" class="btn-primary">Terapkan Filter</button>
                </div>
            </form>
        </div>
    </details>

    <section class="mt-5">
        @if ($riwayat->isEmpty())
            <div class="panel text-sm text-slate-500 dark:text-slate-400">Belum ada laporan kerusakan.</div>
        @else
            <div class="panel overflow-hidden p-0">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-white/10">
                        <thead class="bg-slate-50 dark:bg-white/[0.04]">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Kode</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Aset</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Tingkat</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Rekomendasi</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Status Laporan</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Status Pengajuan</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach ($riwayat as $item)
                                @php
                                    $activePengajuan = $pengajuanMap[$item->aset_id] ?? collect();
                                    $firstPengajuan = $activePengajuan->first();
                                @endphp
                                <tr class="bg-white/70 transition hover:bg-blue-50/60 dark:bg-transparent dark:hover:bg-cyan-500/10">
                                    <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-700 dark:text-slate-200">
                                        {{ $item->aset?->kode_aset ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                                        <p class="font-semibold">{{ $item->aset?->nama_aset }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item->aset?->ruangan?->nama_ruangan }} - {{ $item->aset?->ruangan?->gedung?->nama_gedung }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $tingkatLabelMap[$item->tingkat_kerusakan] ?? $item->tingkat_kerusakan }}</td>
                                    <td class="px-4 py-3">
                                        @if ($item->rekomendasi_tindakan)
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $item->rekomendasi_tindakan === 'PENGGANTIAN' ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200' : 'bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-200' }}">
                                                {{ $rekomendasiLabelMap[$item->rekomendasi_tindakan] ?? $item->rekomendasi_tindakan }}
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{
                                            $item->status === 'SELESAI'
                                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200'
                                                : ($item->status === 'DITOLAK'
                                                    ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200'
                                                    : 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-200')
                                        }}">
                                            {{ $statusLabelMap[$item->status] ?? $item->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                        @if ($firstPengajuan)
                                            {{ $pengajuanStatusLabelMap[$firstPengajuan->status_pengajuan] ?? $firstPengajuan->status_pengajuan }}
                                        @else
                                            <span class="text-xs text-slate-500 dark:text-slate-400">Belum terbentuk (data lama)</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($firstPengajuan)
                                            <a href="{{ route('kepala_sarana.pengajuan.show', $firstPengajuan) }}" class="btn-secondary">Buka Pengajuan</a>
                                        @else
                                            <span class="text-xs text-slate-500 dark:text-slate-400">Tidak ada aksi</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="bg-slate-50/60 dark:bg-white/[0.03]">
                                    <td colspan="7" class="px-4 py-3 text-xs text-slate-600 dark:text-slate-300">
                                        <span class="font-semibold uppercase text-slate-500 dark:text-slate-400">Deskripsi:</span>
                                        {{ $item->deskripsi }}
                                        @if ($item->catatan_validasi)
                                            <span class="ml-3 font-semibold uppercase text-slate-500 dark:text-slate-400">Catatan:</span>
                                            {{ $item->catatan_validasi }}
                                        @endif
                                        @if ($item->foto_kerusakan)
                                            <a href="{{ asset('storage/' . $item->foto_kerusakan) }}" target="_blank" class="ml-2 inline-flex items-center gap-1 text-cyan-700 hover:underline dark:text-cyan-200">
                                                <i class="fas fa-image text-[10px]"></i>
                                                Lihat Foto
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $riwayat->links() }}
            </div>
        @endif
    </section>
</x-layouts.sbadmin>
