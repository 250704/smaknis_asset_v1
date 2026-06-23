<x-layouts.sbadmin>
    @php
        $totalPengajuan = method_exists($pengajuan, 'total') ? $pengajuan->total() : $pengajuan->count();
        $showDualAction = (bool) ($showDualAction ?? false);
        $viewRoute = $viewRoute ?? $detailRoute;
        $realisasiRoute = $realisasiRoute ?? $detailRoute;
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
    @endphp

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 shadow-sm dark:border-white/10 dark:bg-slate-900/60 dark:text-slate-200">
                    {{ number_format($totalPengajuan) }} pengajuan
                </span>
            </div>
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

    @if (($showFilters ?? true) === true)
        <details class="panel group overflow-hidden">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 rounded-2xl p-1">
                <div>
                    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-100">Filter Pengajuan</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Saring daftar pengajuan sesuai kebutuhan</p>
                </div>
                <svg class="h-4 w-4 text-slate-500 transition-transform duration-200 group-open:rotate-180 dark:text-slate-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.167l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </summary>

            <div class="mt-4 border-t border-slate-200 pt-4 dark:border-white/10">
                <form method="GET" class="space-y-4">
                    <div class="grid gap-3 md:grid-cols-12">
                        <div class="md:col-span-5">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Pencarian</label>
                            <input
                                type="text"
                                name="q"
                                value="{{ $filters['q'] }}"
                                placeholder="Judul / kode aset / nama aset..."
                                class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                            >
                        </div>
                        <div class="md:col-span-3">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</label>
                            <select name="status" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                                <option value="">Semua status</option>
                                @foreach ($statusList as $status)
                                    <option value="{{ $status }}" @selected((string) $filters['status'] === (string) $status)>{{ $statusLabels[$status] ?? $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-4">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Jenis</label>
                            <select name="jenis" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                                <option value="">Semua jenis</option>
                                @foreach ($jenisList as $jenis)
                                    <option value="{{ $jenis }}" @selected((string) $filters['jenis'] === (string) $jenis)>{{ $jenis }}</option>
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
    @endif

    <section class="mt-5">
        @if ($pengajuan->isEmpty())
            <div class="panel text-sm text-slate-500 dark:text-slate-400">Belum ada pengajuan.</div>
        @else
            <div class="panel overflow-hidden p-0">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[960px] table-auto divide-y divide-slate-200 text-sm dark:divide-white/10">
                        <thead class="bg-slate-50 dark:bg-white/[0.04]">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Kode Aset</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Judul</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Jenis</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Estimasi</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Tanggal</th>
                                @if ($showUser)
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Pengaju</th>
                                @endif
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">
                                    {{ $showDualAction ? 'Aksi' : 'Detail' }}
                                </th>
                                @if ($canApprove)
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach ($pengajuan as $item)
                                <tr class="bg-white/70 transition hover:bg-blue-50/60 dark:bg-transparent dark:hover:bg-cyan-500/10">
                                    <td class="px-4 py-3 align-top font-mono text-xs font-semibold text-slate-700 dark:text-slate-200">
                                        <span class="inline-block max-w-[220px] break-words">{{ $item->aset?->kode_aset ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3 align-top text-slate-700 dark:text-slate-200">
                                        <p class="max-w-[260px] break-words leading-6">{{ $item->judul_pengajuan }}</p>
                                    </td>
                                    <td class="px-4 py-3 align-top text-slate-600 dark:text-slate-300">
                                        {{ $item->jenis_pengajuan }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-slate-600 dark:text-slate-300">
                                        @if ($item->estimasi_biaya !== null)
                                            Rp {{ number_format((float) $item->estimasi_biaya, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        @php
                                            $statusClass = match ($item->status_pengajuan) {
                                                'DIAJUKAN' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
                                                'DISETUJUI_KASARANA', 'DISETUJUI_BENDAHARA', 'DISETUJUI_KEPSEK' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-200',
                                                'MENUNGGU_VERIFIKASI_TEKNIS', 'MENUNGGU_VERIFIKASI_KEUANGAN' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
                                                'DIPROSES' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-200',
                                                'DITOLAK' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-200',
                                                'SELESAI' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200',
                                                default => 'bg-slate-200 text-slate-700 dark:bg-slate-600/30 dark:text-slate-200',
                                            };
                                        @endphp
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                            {{ $statusLabels[$item->status_pengajuan] ?? $item->status_pengajuan }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 align-top text-slate-600 dark:text-slate-300">
                                        {{ $item->created_at?->format('d M Y') }}
                                    </td>
                                    @if ($showUser)
                                        <td class="px-4 py-3 align-top text-slate-600 dark:text-slate-300">
                                            <span class="inline-block max-w-[140px] break-words">{{ $item->user?->display_name ?? '-' }}</span>
                                        </td>
                                    @endif
                                    <td class="px-4 py-3 align-middle">
                                        @if ($showDualAction)
                                            <div class="inline-flex items-center gap-2 whitespace-nowrap">
                                                <a href="{{ route($viewRoute, $item) }}" class="btn-secondary btn-sm">Lihat</a>
                                                <a href="{{ route($realisasiRoute, $item) }}" class="btn-primary btn-sm">Realisasi</a>
                                            </div>
                                        @else
                                            <a href="{{ route($detailRoute, $item) }}" class="btn-secondary">Lihat</a>
                                        @endif
                                    </td>
                                    @if ($canApprove)
                                        <td class="px-4 py-3 align-top">
                                            <form method="POST" class="w-full max-w-[260px] space-y-2">
                                                @csrf
                                                <textarea
                                                    name="catatan"
                                                    rows="2"
                                                    placeholder="Catatan approval (opsional)"
                                                    class="w-full rounded-lg border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                                                ></textarea>
                                                <div class="flex items-center gap-2">
                                                    <button
                                                        type="submit"
                                                        formaction="{{ route($approveRoute, $item) }}"
                                                        class="btn-primary w-full justify-center"
                                                        data-confirm-title="Konfirmasi Persetujuan"
                                                        data-confirm-message="Apakah Anda yakin ingin menyetujui pengajuan ini?"
                                                        data-confirm-confirm-label="Ya, Setujui"
                                                        data-confirm-variant="success"
                                                    >Setujui</button>
                                                    <button
                                                        type="submit"
                                                        formaction="{{ route($rejectRoute, $item) }}"
                                                        class="btn-danger w-full justify-center"
                                                        data-confirm-title="Konfirmasi Penolakan"
                                                        data-confirm-message="Apakah Anda yakin ingin menolak pengajuan ini?"
                                                        data-confirm-confirm-label="Ya, Tolak"
                                                        data-confirm-variant="danger"
                                                    >Tolak</button>
                                                </div>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $pengajuan->links() }}
            </div>
        @endif
    </section>
</x-layouts.sbadmin>
