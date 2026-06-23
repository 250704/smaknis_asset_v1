<x-layouts.sbadmin>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="page-title">Data Aset</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Daftar inventaris aset per unit fisik dengan filter cepat.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.aset.create') }}" class="btn-primary">
                <i class="fas fa-plus mr-2 text-xs"></i>
                Tambah Aset
            </a>

            <details class="group relative">
                <summary class="inline-flex cursor-pointer list-none items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">
                    <i class="fas fa-trash text-xs"></i>
                    Hapus Aset
                    <i class="fas fa-chevron-down text-[10px] transition group-open:rotate-180"></i>
                </summary>
                <div class="absolute right-0 z-20 mt-2 w-56 rounded-xl border border-slate-200 bg-white p-2 shadow-xl dark:border-white/10 dark:bg-slate-900">
                    <button
                        form="bulk-delete-form"
                        type="submit"
                        id="btn-delete-selected"
                        disabled
                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm font-semibold text-rose-700 transition hover:bg-rose-50 disabled:cursor-not-allowed disabled:text-slate-400 dark:text-rose-300 dark:hover:bg-rose-500/10 dark:disabled:text-slate-500"
                        data-confirm-title="Konfirmasi Hapus Aset"
                        data-confirm-message="Apakah Anda yakin ingin menghapus aset yang dipilih? Aset yang punya relasi akan diarsipkan."
                        data-confirm-confirm-label="Ya, Hapus"
                        data-confirm-variant="danger"
                    >
                        <span>Hapus Terpilih</span>
                        <span id="selected-count" class="text-xs">0</span>
                    </button>

                    <form
                        method="POST"
                        action="{{ route('admin.aset.destroy-all') }}"
                        class="mt-1"
                        data-confirm-title="Konfirmasi Hapus Semua Aset"
                        data-confirm-message="Apakah Anda yakin ingin menghapus semua aset? Aset yang punya relasi akan diarsipkan."
                        data-confirm-confirm-label="Ya, Hapus Semua"
                        data-confirm-variant="danger"
                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm font-semibold text-rose-700 transition hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-500/10">
                            <span>Hapus Semua</span>
                            <i class="fas fa-exclamation-triangle text-xs"></i>
                        </button>
                    </form>
                </div>
            </details>
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

    <section class="panel">
        <form method="GET" action="{{ route('admin.aset.index') }}" class="grid gap-3 grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-7">
            <div class="xl:col-span-2">
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200" for="q">Cari Aset</label>
                <input
                    type="text"
                    id="q"
                    name="q"
                    value="{{ $filters['q'] }}"
                    placeholder="Kode aset atau nama aset..."
                    class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                >
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200" for="kategori_id">Kategori</label>
                <select id="kategori_id" name="kategori_id" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                    <option value="">Semua kategori</option>
                    @foreach ($kategoriList as $kategori)
                        <option value="{{ $kategori->id }}" @selected((string) $filters['kategori_id'] === (string) $kategori->id)>
                            {{ $kategori->nama_kategori }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200" for="gedung_id">Gedung</label>
                <select id="gedung_id" name="gedung_id" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                    <option value="">Semua gedung</option>
                    @foreach ($gedungList as $gedung)
                        <option value="{{ $gedung->id }}" @selected((string) $filters['gedung_id'] === (string) $gedung->id)>
                            {{ $gedung->nama_gedung }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200" for="ruangan_id">Ruangan</label>
                <select id="ruangan_id" name="ruangan_id" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                    <option value="">Semua ruangan</option>
                    @foreach ($ruanganList as $ruangan)
                        <option value="{{ $ruangan->id }}" @selected((string) $filters['ruangan_id'] === (string) $ruangan->id)>
                            {{ $ruangan->nama_ruangan }} (Lt. {{ $ruangan->lantai ?? '-' }}) - {{ $ruangan->gedung?->nama_gedung }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200" for="kondisi_terkini">Kondisi</label>
                <select id="kondisi_terkini" name="kondisi_terkini" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                    <option value="">Semua kondisi</option>
                    @foreach ($kondisiList as $kondisi)
                        <option value="{{ $kondisi }}" @selected((string) $filters['kondisi_terkini'] === (string) $kondisi)>
                            {{ $kondisi }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200" for="status_aset">Status</label>
                <select id="status_aset" name="status_aset" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                    <option value="">Semua status</option>
                    @foreach ($statusList as $status)
                        <option value="{{ $status }}" @selected((string) $filters['status_aset'] === (string) $status)>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2 xl:col-span-1">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-filter text-xs"></i>Filter
                </button>
                <a href="{{ route('admin.aset.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-white/15 dark:bg-slate-800 dark:text-slate-300">
                    <i class="fas fa-undo text-xs"></i>Reset
                </a>
            </div>
        </form>
    </section>

    <section class="panel mt-5">
        <p class="mb-4 text-sm text-slate-600 dark:text-slate-300">
            Pilih aset lalu klik <span class="font-semibold">Hapus Aset</span> di sebelah tombol tambah.
        </p>

        <form id="bulk-delete-form" method="POST" action="{{ route('admin.aset.destroy-selected') }}">
            @csrf
            @method('DELETE')
            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-white/10">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-white/10">
                    <thead class="bg-slate-50 dark:bg-white/[0.04]">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <input id="select-all-aset" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/40">
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Kode</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Aset</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Kategori</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Lokasi</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Kondisi</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Status</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-transparent dark:divide-white/5">
                        @forelse ($aset as $item)
                            <tr>
                                <td class="px-4 py-3">
                                    <input
                                        type="checkbox"
                                        name="aset_ids[]"
                                        value="{{ $item->id }}"
                                        class="aset-select-item h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/40"
                                    >
                                </td>
                                <td class="px-4 py-3 font-mono text-xs whitespace-nowrap text-slate-500 dark:text-slate-400">{{ $item->kode_aset }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-700 dark:text-slate-200">{{ $item->nama_aset }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Tahun {{ $item->tahun_perolehan }}</p>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-300">{{ $item->kategori?->nama_kategori }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-300">
                                    {{ $item->ruangan?->nama_ruangan }}<br>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ $item->ruangan?->gedung?->nama_gedung }} - Lt. {{ $item->ruangan?->lantai ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                        {{ $item->kondisi_terkini === 'BAIK' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200' }}">
                                        {{ $item->kondisi_terkini }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                        {{ $item->status_aset === 'AKTIF' ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-200' : 'bg-slate-200 text-slate-700 dark:bg-slate-600/30 dark:text-slate-200' }}">
                                        {{ $item->status_aset }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('admin.aset.show', $item) }}"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500 text-xs font-semibold text-white transition hover:bg-blue-600 hover:shadow-md"
                                           title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.aset.edit', $item) }}"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-yellow-500 text-xs font-semibold text-white transition hover:bg-yellow-600 hover:shadow-md"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form
                                            action="{{ route('admin.aset.destroy', $item) }}"
                                            method="POST"
                                            class="inline"
                                            data-confirm-title="Konfirmasi Hapus Aset"
                                            data-confirm-message="Apakah Anda yakin ingin menghapus aset ini?"
                                            data-confirm-confirm-label="Ya, Hapus"
                                            data-confirm-variant="danger"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-red-600 text-xs font-semibold text-white transition hover:bg-red-700 hover:shadow-md"
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Belum ada data aset.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        <div class="mt-4">
            {{ $aset->links() }}
        </div>
    </section>

    <script>
        (function () {
            const selectAll = document.getElementById('select-all-aset');
            const rowChecks = Array.from(document.querySelectorAll('.aset-select-item'));
            const deleteSelectedBtn = document.getElementById('btn-delete-selected');
            const selectedCount = document.getElementById('selected-count');

            if (!selectAll || !deleteSelectedBtn || !selectedCount || rowChecks.length === 0) {
                return;
            }

            function refreshSelectionState() {
                const checkedCount = rowChecks.filter((item) => item.checked).length;
                deleteSelectedBtn.disabled = checkedCount === 0;
                selectedCount.textContent = `${checkedCount}`;
                selectAll.checked = checkedCount > 0 && checkedCount === rowChecks.length;
                selectAll.indeterminate = checkedCount > 0 && checkedCount < rowChecks.length;
            }

            selectAll.addEventListener('change', function () {
                rowChecks.forEach((item) => {
                    item.checked = selectAll.checked;
                });
                refreshSelectionState();
            });

            rowChecks.forEach((item) => {
                item.addEventListener('change', refreshSelectionState);
            });
        })();
    </script>
</x-layouts.sbadmin>
