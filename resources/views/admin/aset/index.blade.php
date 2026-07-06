<x-layouts.sbadmin>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="page-title">Data Sarana</h1>
            {{-- <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Daftar inventaris aset per unit fisik dengan filter cepat.</p> --}}
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.aset.create') }}" class="btn-primary">
                <i class="mr-2 text-xs fas fa-plus"></i>
                Tambah Sarana
            </a>

            <a href="{{ route('admin.cetak-qr.index') }}" class="btn-secondary">
                <i class="mr-2 text-xs fas fa-qrcode"></i>
                Cetak QR Code
            </a>

            <details class="relative group">
                <summary class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white list-none transition rounded-lg cursor-pointer bg-rose-600 hover:bg-rose-700">
                    <i class="text-xs fas fa-trash"></i>
                    Hapus Sarana
                    <i class="fas fa-chevron-down text-[10px] transition group-open:rotate-180"></i>
                </summary>
                <div class="absolute right-0 z-20 w-56 p-2 mt-2 bg-white border shadow-xl rounded-xl border-slate-200 dark:border-white/10 dark:bg-slate-900">
                    <button
                        form="bulk-delete-form"
                        type="submit"
                        id="btn-delete-selected"
                        disabled
                        class="flex items-center justify-between w-full px-3 py-2 text-sm font-semibold text-left transition rounded-lg text-rose-700 hover:bg-rose-50 disabled:cursor-not-allowed disabled:text-slate-400 dark:text-rose-300 dark:hover:bg-rose-500/10 dark:disabled:text-slate-500"
                        data-confirm-title="Konfirmasi Hapus Sarana"
                        data-confirm-message="Apakah Anda yakin ingin menghapus sarana yang dipilih? Sarana yang punya relasi akan diarsipkan."
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
                        data-confirm-title="Konfirmasi Hapus Semua Sarana"
                        data-confirm-message="Apakah Anda yakin ingin menghapus semua sarana? Sarana yang punya relasi akan diarsipkan."
                        data-confirm-confirm-label="Ya, Hapus Semua"
                        data-confirm-variant="danger"
                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="flex items-center justify-between w-full px-3 py-2 text-sm font-semibold text-left transition rounded-lg text-rose-700 hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-500/10">
                            <span>Hapus Semua</span>
                            <i class="text-xs fas fa-exclamation-triangle"></i>
                        </button>
                    </form>
                </div>
            </details>
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
        <form method="GET" action="{{ route('admin.aset.index') }}" class="filter-grid">
            <div class="xl:col-span-2">
                <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200" for="q">Cari Sarana</label>
                <input
                    type="text"
                    id="q"
                    name="q"
                    value="{{ $filters['q'] }}"
                    placeholder="Kode sarana atau nama sarana..."
                    class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                >
            </div>

            <div>
                <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200" for="kategori_id">Kategori</label>
                <select id="kategori_id" name="kategori_id" class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                    <option value="">Semua kategori</option>
                    @foreach ($kategoriList as $kategori)
                        <option value="{{ $kategori->id }}" @selected((string) $filters['kategori_id'] === (string) $kategori->id)>
                            {{ $kategori->nama_kategori }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200" for="gedung_id">Gedung</label>
                <select id="gedung_id" name="gedung_id" class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                    <option value="">Semua gedung</option>
                    @foreach ($gedungList as $gedung)
                        <option value="{{ $gedung->id }}" @selected((string) $filters['gedung_id'] === (string) $gedung->id)>
                            {{ $gedung->nama_gedung }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200" for="ruangan_id">Ruangan</label>
                <select id="ruangan_id" name="ruangan_id" class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                    <option value="">Semua ruangan</option>
                    @foreach ($ruanganList as $ruangan)
                        <option value="{{ $ruangan->id }}" @selected((string) $filters['ruangan_id'] === (string) $ruangan->id)>
                            {{ $ruangan->nama_ruangan }} (Lt. {{ $ruangan->lantai ?? '-' }}) - {{ $ruangan->gedung?->nama_gedung }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200" for="kondisi_terkini">Kondisi</label>
                <select id="kondisi_terkini" name="kondisi_terkini" class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                    <option value="">Semua kondisi</option>
                    @foreach ($kondisiList as $kondisi)
                        <option value="{{ $kondisi }}" @selected((string) $filters['kondisi_terkini'] === (string) $kondisi)>
                            {{ $kondisi }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-1 text-sm font-medium text-slate-700 dark:text-slate-200" for="status_aset">Status</label>
                <select id="status_aset" name="status_aset" class="w-full text-sm bg-white shadow-sm rounded-xl border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                    <option value="">Semua status</option>
                    @foreach ($statusList as $status)
                        <option value="{{ $status }}" @selected((string) $filters['status_aset'] === (string) $status)>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white transition bg-blue-600 rounded-lg hover:bg-blue-700">
                    <i class="text-xs fas fa-filter"></i>Filter
                </button>
                <a href="{{ route('admin.aset.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold transition bg-white border rounded-lg border-slate-300 text-slate-700 hover:bg-slate-50 dark:border-white/15 dark:bg-slate-800 dark:text-slate-300">
                    <i class="text-xs fas fa-undo"></i>Reset
                </a>
            </div>
        </form>
    </section>

    <section class="mt-5 panel">
        {{-- <p class="mb-4 text-sm text-slate-600 dark:text-slate-300">
            Pilih sarana lalu klik <span class="font-semibold">Hapus Sarana</span> di sebelah tombol tambah.
        </p> --}}

        <form id="bulk-delete-form" method="POST" action="{{ route('admin.aset.destroy-selected') }}">
            @csrf
            @method('DELETE')
            <div class="overflow-x-auto border rounded-xl border-slate-200 dark:border-white/10">
                <table class="min-w-full text-sm divide-y divide-slate-200 dark:divide-white/10">
                    <thead class="bg-slate-50 dark:bg-white/[0.04]">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <input id="select-all-aset" type="checkbox" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500/40">
                            </th>
                            <th class="px-4 py-3 font-semibold text-left text-slate-600 dark:text-slate-300">Kode</th>
                            <th class="px-4 py-3 font-semibold text-left text-slate-600 dark:text-slate-300">Sarana</th>
                            <th class="px-4 py-3 font-semibold text-left text-slate-600 dark:text-slate-300">Kategori</th>
                            <th class="px-4 py-3 font-semibold text-left text-slate-600 dark:text-slate-300">Lokasi</th>
                            <th class="px-4 py-3 font-semibold text-left text-slate-600 dark:text-slate-300">Kondisi</th>
                            <th class="px-4 py-3 font-semibold text-left text-slate-600 dark:text-slate-300">Status</th>
                            <th class="px-4 py-3 font-semibold text-left text-slate-600 dark:text-slate-300 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-transparent divide-y divide-slate-100 dark:divide-white/5">
                        @forelse ($aset as $item)
                            <tr>
                                <td class="px-4 py-3">
                                    <input
                                        type="checkbox"
                                        name="aset_ids[]"
                                        value="{{ $item->id }}"
                                        class="w-4 h-4 text-blue-600 rounded aset-select-item border-slate-300 focus:ring-blue-500/40"
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
                                           class="inline-flex items-center justify-center w-8 h-8 text-xs font-semibold text-white transition bg-blue-500 rounded-lg hover:bg-blue-600 hover:shadow-md"
                                           title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.aset.edit', $item) }}"
                                           class="inline-flex items-center justify-center w-8 h-8 text-xs font-semibold text-white transition bg-yellow-500 rounded-lg hover:bg-yellow-600 hover:shadow-md"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form
                                            action="{{ route('admin.aset.destroy', $item) }}"
                                            method="POST"
                                            class="inline"
                                            data-confirm-title="Konfirmasi Hapus Sarana"
                                            data-confirm-message="Apakah Anda yakin ingin menghapus sarana ini?"
                                            data-confirm-confirm-label="Ya, Hapus"
                                            data-confirm-variant="danger"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center justify-center w-8 h-8 text-xs font-semibold text-white transition bg-red-600 rounded-lg hover:bg-red-700 hover:shadow-md"
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-sm text-center text-slate-500 dark:text-slate-400">
                                    Belum ada data sarana.
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
