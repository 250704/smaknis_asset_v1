<x-layouts.sbadmin>
    @php
        $isKepalaSekolahContext = request()->routeIs('kepala_sekolah.*');
        $validateRoute = request()->routeIs('kepala_sekolah.*')
            ? 'kepala_sekolah.kerusakan.validate'
            : 'kepala_sarana.kerusakan.validate';
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
    @endphp

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="page-title">
                {{ $isKepalaSekolahContext ? 'Validasi Kerusakan (Kepala Sekolah)' : 'Validasi Kerusakan' }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ $isKepalaSekolahContext
                    ? 'Halaman ini khusus validasi laporan kerusakan yang dilaporkan oleh Kepala Sarana.'
                    : 'Validasi laporan kerusakan ringan/berat/tidak layak dari seluruh pelapor.' }}
            </p>
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
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-100">Filter Laporan</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                    {{ $isKepalaSekolahContext
                        ? 'Cari berdasarkan kode/nama aset yang dilaporkan Kepala Sarana.'
                        : 'Cari berdasarkan kode/nama aset atau status laporan.' }}
                </p>
            </div>
            <svg class="h-4 w-4 text-slate-500 transition-transform duration-200 group-open:rotate-180 dark:text-slate-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.167l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </summary>

        <div class="mt-4 border-t border-slate-200 pt-4 dark:border-white/10">
            <form method="GET" class="space-y-4">
                <div class="grid gap-3 md:grid-cols-12">
                    <div class="{{ $isKepalaSekolahContext ? 'md:col-span-12' : 'md:col-span-7' }}">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Pencarian</label>
                        <input
                            type="text"
                            name="q"
                            value="{{ $filters['q'] }}"
                            placeholder="Kode aset / nama aset..."
                            class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                        >
                    </div>
                    @unless ($isKepalaSekolahContext)
                        <div class="md:col-span-5">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</label>
                            <select name="status" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                                <option value="">Semua status</option>
                                @foreach ($statusList as $status)
                                    <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $statusLabelMap[$status] ?? $status }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endunless
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
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Pelapor</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Tingkat</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach ($riwayat as $item)
                                <tr class="bg-white/70 transition hover:bg-blue-50/60 dark:bg-transparent dark:hover:bg-cyan-500/10">
                                    <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-700 dark:text-slate-200">
                                        {{ $item->aset?->kode_aset ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                                        <p class="font-semibold">{{ $item->aset?->nama_aset }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item->aset?->ruangan?->nama_ruangan }} - {{ $item->aset?->ruangan?->gedung?->nama_gedung }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                        {{ $item->user?->display_name ?? '-' }}
                                        @if ($isKepalaSekolahContext)
                                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Kepala Sarana</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                        {{ $tingkatLabelMap[$item->tingkat_kerusakan] ?? $item->tingkat_kerusakan }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{
                                            $item->status === 'DILAPORKAN'
                                                ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200'
                                                : ($item->status === 'DITOLAK'
                                                    ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200'
                                                    : ($item->status === 'SELESAI'
                                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200'
                                                        : 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-200'))
                                        }}">
                                            {{ $statusLabelMap[$item->status] ?? $item->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                        {{ $item->created_at?->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($item->status === 'DILAPORKAN')
                                            <form method="POST" action="{{ route($validateRoute, $item) }}" class="flex flex-col gap-2">
                                                @csrf
                                                <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                                                    <select name="tingkat_kerusakan" class="rounded-lg border-slate-300 bg-white text-xs text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                                                        @foreach ($kondisiList as $kondisi)
                                                            <option value="{{ $kondisi }}" @selected($item->tingkat_kerusakan === $kondisi)>{{ $kondisi }}</option>
                                                        @endforeach
                                                    </select>
                                                    <select name="rekomendasi_tindakan" class="rounded-lg border-slate-300 bg-white text-xs text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                                                        <option value="PERAWATAN" @selected(old('rekomendasi_tindakan', $item->rekomendasi_tindakan ?? 'PERAWATAN') === 'PERAWATAN')>Rekomendasi: Perawatan</option>
                                                        <option value="PENGGANTIAN" @selected(old('rekomendasi_tindakan', $item->rekomendasi_tindakan ?? '') === 'PENGGANTIAN')>Rekomendasi: Penggantian</option>
                                                    </select>
                                                    <input
                                                        type="number"
                                                        name="estimasi_biaya"
                                                        step="1000"
                                                        min="0"
                                                        value="{{ old('estimasi_biaya') }}"
                                                        placeholder="Estimasi biaya (Rp)"
                                                        class="rounded-lg border-slate-300 bg-white text-xs text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                                                        required
                                                    >
                                                    <input
                                                        type="text"
                                                        name="catatan"
                                                        value="{{ old('catatan', $item->catatan_validasi) }}"
                                                        placeholder="Catatan singkat"
                                                        class="rounded-lg border-slate-300 bg-white text-xs text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                                                    >
                                                </div>
                                                <div class="flex flex-wrap items-center justify-end gap-2">
                                                    <button
                                                        type="submit"
                                                        name="action"
                                                        value="TOLAK"
                                                        class="btn-danger"
                                                        data-confirm-title="Konfirmasi Penolakan"
                                                        data-confirm-message="Apakah Anda yakin ingin menolak laporan kerusakan ini?"
                                                        data-confirm-confirm-label="Ya, Tolak"
                                                        data-confirm-variant="danger"
                                                    >Tolak</button>
                                                    <button
                                                        type="submit"
                                                        name="action"
                                                        value="VALIDASI"
                                                        class="btn-primary"
                                                        data-confirm-title="Konfirmasi Validasi"
                                                        data-confirm-message="Apakah Anda yakin ingin memvalidasi laporan kerusakan ini?"
                                                        data-confirm-confirm-label="Ya, Validasi"
                                                        data-confirm-variant="success"
                                                    >
                                                        {{ $isKepalaSekolahContext ? 'Validasi & Ajukan ke Bendahara' : 'Validasi & Ajukan' }}
                                                    </button>
                                                </div>
                                            </form>
                                        @elseif ($item->status === 'DITOLAK')
                                            <span class="text-xs font-semibold text-rose-600 dark:text-rose-300">Ditolak</span>
                                        @elseif ($item->status === 'SELESAI')
                                            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-300">Selesai</span>
                                        @else
                                            <span class="text-xs text-slate-500 dark:text-slate-400">Divalidasi, pengajuan otomatis masuk antrean bendahara.</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="bg-slate-50/60 dark:bg-white/[0.03]">
                                    <td colspan="7" class="px-4 py-3 text-xs text-slate-600 dark:text-slate-300">
                                        <span class="font-semibold uppercase text-slate-500 dark:text-slate-400">Deskripsi:</span>
                                        {{ $item->deskripsi }}
                                        @if ($item->rekomendasi_tindakan)
                                            <span class="ml-3 font-semibold uppercase text-slate-500 dark:text-slate-400">Rekomendasi:</span>
                                            {{ $rekomendasiLabelMap[$item->rekomendasi_tindakan] ?? $item->rekomendasi_tindakan }}
                                        @endif
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
