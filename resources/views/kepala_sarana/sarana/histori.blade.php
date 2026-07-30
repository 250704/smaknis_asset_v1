<x-layouts.sbadmin>
    @php
        $role = auth()->user()?->role_code;
        $scanRoute = in_array($role, ['admin', 'guru', 'kepala_sarana', 'bendahara', 'kepala_sekolah'], true)
            ? route($role . '.scan')
            : route('scan-qr');
    @endphp
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="page-title">Histori Sarana</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Riwayat kondisi kerusakan dan tindak lanjut sarana.</p>
        </div>
        <a href="{{ $scanRoute }}" class="btn-secondary">Kembali ke Scan QR</a>
    </div>

    <section class="panel">
        <form method="GET" action="{{ route('scan.sarana.histori') }}" class="filter-grid">
            <div class="xl:col-span-2">
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200" for="q">Cari Sarana</label>
                <input
                    type="text"
                    id="q"
                    name="q"
                    value="{{ $filters['q'] }}"
                    placeholder="Kode sarana atau nama sarana..."
                    class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                >
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200" for="status">Status</label>
                <select id="status" name="status" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                    <option value="">Semua status</option>
                    @foreach ($statusList as $status)
                        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-primary">Terapkan Filter</button>
                <a href="{{ route('scan.sarana.histori') }}" class="btn-secondary">Reset</a>
            </div>
        </form>
    </section>

    <section class="panel mt-5">
        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-white/10">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-white/10">
                <thead class="bg-slate-50 dark:bg-white/[0.04]">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Tanggal</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Sarana</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Tingkat</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Pelapor</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Validator</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse ($histori as $item)
                        <tr>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $item->created_at?->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                                <p class="font-mono text-xs">{{ $item->sarana?->kode_sarana ?? '-' }}</p>
                                <p class="font-semibold">{{ $item->sarana?->nama_sarana ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $item->tingkat_kerusakan }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $item->status === 'DITOLAK' ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200' : ($item->status === 'SELESAI' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200' : ($item->status === 'DITINDAKLANJUTI' ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200')) }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $item->user?->display_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $item->validator?->display_name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if ($item->foto_kerusakan)
                                    <a href="{{ asset('storage/' . $item->foto_kerusakan) }}" target="_blank" class="btn-secondary">Foto</a>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">Belum ada histori kondisi sarana.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $histori->links() }}
        </div>
    </section>
</x-layouts.sbadmin>
