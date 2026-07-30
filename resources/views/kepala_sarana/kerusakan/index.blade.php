<x-layouts.sbadmin>
    @php
        $validateRoute = 'kepala_sarana.kerusakan.validate';
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

    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="page-title">Validasi Kerusakan</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Validasi laporan kerusakan dari seluruh pelapor dan tentukan tindak lanjut teknisnya.
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="px-4 py-3 mb-4 text-sm border rounded-xl border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="px-4 py-3 mb-4 text-sm border rounded-xl border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
            {{ session('error') }}
        </div>
    @endif

    <section class="panel">
        <form method="GET" class="filter-grid">
            <div class="xl:col-span-4">
                <label class="filter-label" for="q">Pencarian</label>
                <input
                    type="text"
                    id="q"
                    name="q"
                    value="{{ $filters['q'] }}"
                    placeholder="Kode sarana / nama sarana..."
                    class="filter-control"
                >
            </div>
            <div class="xl:col-span-2">
                <label class="filter-label" for="status">Status</label>
                <select id="status" name="status" class="filter-control">
                    <option value="">Semua status</option>
                    @foreach ($statusList as $status)
                        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $statusLabelMap[$status] ?? $status }}</option>
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

    <section class="mt-5">
        @if ($riwayat->isEmpty())
            <div class="text-sm panel text-slate-500 dark:text-slate-400">Belum ada laporan kerusakan.</div>
        @else
            <div class="p-0 overflow-hidden panel">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm divide-y divide-slate-200 dark:divide-white/10">
                        <thead class="bg-slate-50 dark:bg-white/[0.04]">
                            <tr>
                                <th class="px-4 py-3 text-xs font-semibold tracking-wide text-left uppercase text-slate-500 dark:text-slate-300">Kode</th>
                                <th class="px-4 py-3 text-xs font-semibold tracking-wide text-left uppercase text-slate-500 dark:text-slate-300">Sarana</th>
                                <th class="px-4 py-3 text-xs font-semibold tracking-wide text-left uppercase text-slate-500 dark:text-slate-300">Pelapor</th>
                                <th class="px-4 py-3 text-xs font-semibold tracking-wide text-left uppercase text-slate-500 dark:text-slate-300">Tingkat</th>
                                <th class="px-4 py-3 text-xs font-semibold tracking-wide text-left uppercase text-slate-500 dark:text-slate-300">Status</th>
                                <th class="px-4 py-3 text-xs font-semibold tracking-wide text-left uppercase text-slate-500 dark:text-slate-300">Tanggal</th>
                                <th class="px-4 py-3 text-xs font-semibold tracking-wide text-left uppercase text-slate-500 dark:text-slate-300">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach ($riwayat as $item)
                                <tr class="transition bg-white/70 hover:bg-blue-50/60 dark:bg-transparent dark:hover:bg-cyan-500/10">
                                    <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-700 dark:text-slate-200">
                                        {{ $item->sarana?->kode_sarana ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                                        <p class="font-semibold">{{ $item->sarana?->nama_sarana }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item->sarana?->ruangan?->nama_ruangan }} - {{ $item->sarana?->ruangan?->gedung?->nama_gedung }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                        {{ $item->user?->display_name ?? '-' }}
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
                                                    <select name="tingkat_kerusakan" class="text-xs bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                                                        @foreach ($kondisiList as $kondisi)
                                                            <option value="{{ $kondisi }}" @selected($item->tingkat_kerusakan === $kondisi)>{{ $kondisi }}</option>
                                                        @endforeach
                                                    </select>
                                                    <select name="rekomendasi_tindakan" class="text-xs bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
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
                                                        class="text-xs bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                                                        required
                                                    >
                                                    <input
                                                        type="text"
                                                        name="catatan"
                                                        value="{{ old('catatan', $item->catatan_validasi) }}"
                                                        placeholder="Catatan singkat"
                                                        class="text-xs bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
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
                                                        Validasi Teknis & Ajukan
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
                                            <a href="{{ asset('storage/' . $item->foto_kerusakan) }}" target="_blank" class="inline-flex items-center gap-1 ml-2 text-cyan-700 hover:underline dark:text-cyan-200">
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
