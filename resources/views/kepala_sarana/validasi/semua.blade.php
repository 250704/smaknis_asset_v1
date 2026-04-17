<x-layouts.sbadmin>
    @php
        $statusGroupLabelMap = [
            'MENUNGGU' => 'Menunggu',
            'PROSES' => 'Dalam Proses',
            'SELESAI' => 'Selesai',
            'DITOLAK' => 'Ditolak',
        ];
        $jenisLabelMap = [
            'PERAWATAN' => 'Perawatan',
            'PENGGANTIAN' => 'Penggantian',
            'PENGADAAN' => 'Pengadaan',
            'KERUSAKAN' => 'Kerusakan',
        ];
    @endphp

    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="page-title">Semua Proses</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Monitoring status terakhir setiap aset secara ringkas dan tanpa duplikasi.
            </p>
            <div class="mt-3 flex items-center gap-2">
                <span class="inline-flex rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 dark:border-white/10 dark:bg-slate-900/60 dark:text-slate-200">
                    {{ number_format($stats['total']) }} aset terdeteksi
                </span>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('kepala_sarana.kerusakan.index') }}" class="btn-secondary">Validasi Kerusakan</a>
            <a href="{{ route('kepala_sarana.pengajuan.approval') }}" class="btn-primary">Approval</a>
        </div>
    </div>

    <section class="mb-5 grid gap-3 md:grid-cols-4">
        <div class="panel">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Menunggu</p>
            <p class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-300">{{ number_format($stats['menunggu']) }}</p>
        </div>
        <div class="panel">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Dalam Proses</p>
            <p class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-300">{{ number_format($stats['proses']) }}</p>
        </div>
        <div class="panel">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Selesai</p>
            <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-300">{{ number_format($stats['selesai']) }}</p>
        </div>
        <div class="panel">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Ditolak</p>
            <p class="mt-1 text-2xl font-bold text-rose-600 dark:text-rose-300">{{ number_format($stats['ditolak']) }}</p>
        </div>
    </section>

    <section class="panel mb-5">
        <form method="GET" class="grid gap-3 md:grid-cols-12">
            <div class="md:col-span-6">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Pencarian</label>
                <input
                    type="text"
                    name="q"
                    value="{{ $filters['q'] }}"
                    placeholder="Kode atau nama aset..."
                    class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100"
                >
            </div>
            <div class="md:col-span-2">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</label>
                <select name="status" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                    <option value="">Semua</option>
                    <option value="MENUNGGU" @selected($filters['status'] === 'MENUNGGU')>Menunggu</option>
                    <option value="PROSES" @selected($filters['status'] === 'PROSES')>Proses</option>
                    <option value="SELESAI" @selected($filters['status'] === 'SELESAI')>Selesai</option>
                    <option value="DITOLAK" @selected($filters['status'] === 'DITOLAK')>Ditolak</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Jenis</label>
                <select name="jenis" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                    <option value="">Semua</option>
                    <option value="PERAWATAN" @selected($filters['jenis'] === 'PERAWATAN')>Perawatan</option>
                    <option value="PENGGANTIAN" @selected($filters['jenis'] === 'PENGGANTIAN')>Penggantian</option>
                    <option value="PENGADAAN" @selected($filters['jenis'] === 'PENGADAAN')>Pengadaan</option>
                    <option value="KERUSAKAN" @selected($filters['jenis'] === 'KERUSAKAN')>Kerusakan</option>
                </select>
            </div>
            <div class="md:col-span-2 flex items-end justify-end gap-2">
                <a href="{{ route('kepala_sarana.validasi.semua') }}" class="btn-secondary">Reset</a>
                <button type="submit" class="btn-primary">Filter</button>
            </div>
        </form>
    </section>

    <section class="panel overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-white/10">
                <thead class="bg-slate-50 dark:bg-white/[0.04]">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Kode Aset</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Nama Aset</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Tahap Terakhir</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Jenis</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Approval Terakhir</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Update</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse ($monitoring as $row)
                        @php
                            $statusClass = match ($row['status_group']) {
                                'MENUNGGU' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200',
                                'PROSES' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-200',
                                'SELESAI' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200',
                                'DITOLAK' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-200',
                                default => 'bg-slate-200 text-slate-700 dark:bg-slate-600/30 dark:text-slate-200',
                            };
                        @endphp
                        <tr class="bg-white/70 transition hover:bg-blue-50/60 dark:bg-transparent dark:hover:bg-cyan-500/10">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $row['kode_aset'] }}</td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-700 dark:text-slate-200">{{ $row['nama_aset'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $row['lokasi'] }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $row['tahap_terakhir'] }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $jenisLabelMap[$row['jenis']] ?? $row['jenis'] }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ $statusGroupLabelMap[$row['status_group']] ?? $row['status_group'] }}
                                </span>
                                <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">{{ $row['status_label'] }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $row['approval_terakhir'] }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                {{ $row['updated_at'] ? $row['updated_at']->format('d M Y H:i') : '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ $row['detail_url'] }}" class="btn-secondary">Lihat</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">Data tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">
            {{ $monitoring->links() }}
        </div>
    </section>
</x-layouts.sbadmin>
