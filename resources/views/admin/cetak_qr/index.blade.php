<x-layouts.sbadmin>
    @php
        $totalAset = method_exists($aset, 'total') ? $aset->total() : $aset->count();
        $activeFilterCount = collect([
            $filters['q'] ?? null,
            $filters['gedung_id'] ?? null,
            $filters['ruangan_id'] ?? null,
            $filters['kategori_id'] ?? null,
            $filters['status_aset'] ?? null,
        ])->filter(static fn($value) => filled($value))->count();
    @endphp

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="page-title">Cetak QR Aset</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kelola dan cetak label QR untuk aset sekolah</p>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 shadow-sm dark:border-white/10 dark:bg-slate-900/60 dark:text-slate-200">
                    {{ number_format($totalAset) }} aset ditemukan
                </span>
                <span id="selected-count-header" class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 shadow-sm dark:border-blue-400/30 dark:bg-blue-500/10 dark:text-blue-200">
                    0 aset terpilih
                </span>
            </div>
        </div>
        <a href="{{ route('admin.aset.index') }}" class="btn-secondary">Data Aset</a>
    </div>

    <details class="panel group overflow-hidden">
        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 rounded-2xl p-1">
            <div>
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-100">Filter Data Aset</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Buka panel ini untuk menyaring daftar aset sebelum cetak QR</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:border-white/10 dark:bg-slate-800/70 dark:text-slate-300">
                    {{ $activeFilterCount }} aktif
                </span>
                <svg class="h-4 w-4 text-slate-500 transition-transform duration-200 group-open:rotate-180 dark:text-slate-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.167l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </div>
        </summary>

        <div class="mt-4 border-t border-slate-200 pt-4 dark:border-white/10">
            <form method="GET" action="{{ route('admin.cetak-qr.index') }}" class="space-y-4">
                <div class="grid gap-3 md:grid-cols-12">
                    <div class="md:col-span-4">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Pencarian</label>
                        <input
                            type="text"
                            name="q"
                            value="{{ $filters['q'] }}"
                            placeholder="Kode / nama aset..."
                            class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                        >
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Gedung</label>
                        <select name="gedung_id" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                            <option value="">Semua gedung</option>
                            @foreach ($gedungList as $gedung)
                                <option value="{{ $gedung->id }}" @selected((string) $filters['gedung_id'] === (string) $gedung->id)>{{ $gedung->nama_gedung }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Ruangan</label>
                        <select name="ruangan_id" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                            <option value="">Semua ruangan</option>
                            @foreach ($ruanganList as $ruangan)
                                <option value="{{ $ruangan->id }}" @selected((string) $filters['ruangan_id'] === (string) $ruangan->id)>{{ $ruangan->nama_ruangan }} - {{ $ruangan->gedung?->nama_gedung }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Kategori</label>
                        <select name="kategori_id" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                            <option value="">Semua kategori</option>
                            @foreach ($kategoriList as $kategori)
                                <option value="{{ $kategori->id }}" @selected((string) $filters['kategori_id'] === (string) $kategori->id)>{{ $kategori->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</label>
                        <select name="status_aset" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                            <option value="">Semua status</option>
                            @foreach ($statusList as $status)
                                <option value="{{ $status }}" @selected((string) $filters['status_aset'] === (string) $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-200 pt-3 dark:border-white/10">
                    <a href="{{ route('admin.cetak-qr.index') }}" class="btn-secondary">Reset</a>
                    <button type="submit" class="btn-primary">Terapkan Filter</button>
                </div>
            </form>
        </div>
    </details>

    <section class="mt-5 pb-24">
        @if ($aset->isEmpty())
            <div class="panel text-sm text-slate-500 dark:text-slate-400">
                Belum ada data aset untuk dicetak.
            </div>
        @else
            <div class="space-y-4">
                    <div class="panel overflow-hidden p-0">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 px-4 py-3 dark:border-white/10">
                            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-100">Daftar Aset</h2>
                            <div class="flex flex-wrap items-center gap-2">
                                <label for="print-paper" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-xs font-semibold text-slate-600 dark:border-white/10 dark:bg-slate-900/70 dark:text-slate-300">
                                    <span>Kertas</span>
                                    <select id="print-paper" class="rounded-lg border-slate-300 bg-white text-xs font-semibold text-slate-700 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40">
                                        <option value="f4" selected>F4 (25 / lembar)</option>
                                        <option value="a4">A4 (20 / lembar)</option>
                                    </select>
                                </label>
                                <button type="button" id="btn-select-all-visible" class="btn-secondary">Pilih Semua</button>
                                <button type="button" id="btn-clear-all" class="btn-secondary">Clear Pilihan</button>
                                <button type="button" id="btn-print-page" class="btn-secondary">Cetak Semua Halaman</button>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-white/10" id="qr-table">
                                <thead class="bg-slate-50 dark:bg-white/[0.04]">
                                    <tr>
                                        <th class="w-16 px-4 py-3 text-left">
                                            <input type="checkbox" id="check-all-header" class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500/40">
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Kode Aset</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Nama Aset</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Lokasi</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                                    @foreach ($aset as $item)
                                        <tr
                                            class="qr-row bg-white/70 transition hover:bg-blue-50/70 dark:bg-transparent dark:hover:bg-cyan-500/10"
                                            data-kode="{{ $item->kode_aset }}"
                                            data-nama="{{ $item->nama_aset }}"
                                            data-lokasi="{{ $item->ruangan?->nama_ruangan }} - {{ $item->ruangan?->gedung?->nama_gedung }}"
                                        >
                                            <td class="px-4 py-3">
                                                <input type="checkbox" class="qr-select h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500/40">
                                            </td>
                                            <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $item->kode_aset }}</td>
                                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $item->nama_aset }}</td>
                                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $item->ruangan?->nama_ruangan }} - {{ $item->ruangan?->gedung?->nama_gedung }}</td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $item->status_aset === 'AKTIF' ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-200' : 'bg-slate-200 text-slate-700 dark:bg-slate-600/30 dark:text-slate-200' }}">
                                                    {{ $item->status_aset }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        {{ $aset->links() }}
                    </div>
                </div>
        @endif
    </section>

    @if (!$aset->isEmpty())
        <div id="sticky-action-bar" class="fixed bottom-4 left-1/2 z-40 hidden w-[calc(100%-1.5rem)] max-w-3xl -translate-x-1/2 rounded-2xl border border-slate-200 bg-white/95 p-3 shadow-xl backdrop-blur dark:border-white/10 dark:bg-slate-900/90">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <span id="selected-count-sticky" class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 dark:border-blue-400/30 dark:bg-blue-500/10 dark:text-blue-200">0 aset terpilih</span>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" id="btn-print-selected-sticky" class="btn-primary cursor-not-allowed opacity-50" disabled>Cetak Terpilih</button>
                    <button type="button" id="btn-clear-sticky" class="btn-secondary">Clear Pilihan</button>
                </div>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        (function () {
            const table = document.getElementById('qr-table');
            if (!table) return;

            const rows = Array.from(table.querySelectorAll('.qr-row'));
            const headerCheck = document.getElementById('check-all-header');
            const selectedCountHeader = document.getElementById('selected-count-header');
            const selectedCountSticky = document.getElementById('selected-count-sticky');
            const stickyActionBar = document.getElementById('sticky-action-bar');
            const btnSelectAllVisible = document.getElementById('btn-select-all-visible');
            const btnClearAll = document.getElementById('btn-clear-all');
            const btnClearSticky = document.getElementById('btn-clear-sticky');
            const btnPrintSelectedSticky = document.getElementById('btn-print-selected-sticky');
            const btnPrintPage = document.getElementById('btn-print-page');
            const printPaper = document.getElementById('print-paper');

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function getSelectedRows() {
                return rows.filter((row) => row.querySelector('.qr-select')?.checked);
            }

            function toggleButtonState(button, disabled) {
                if (!button) return;
                button.disabled = disabled;
                button.classList.toggle('opacity-50', disabled);
                button.classList.toggle('cursor-not-allowed', disabled);
            }

            function updateState() {
                const selectedRows = getSelectedRows();
                const selectedCount = selectedRows.length;

                if (selectedCountHeader) selectedCountHeader.textContent = `${selectedCount} aset terpilih`;
                if (selectedCountSticky) selectedCountSticky.textContent = `${selectedCount} aset terpilih`;
                if (stickyActionBar) stickyActionBar.classList.toggle('hidden', selectedCount === 0);
                toggleButtonState(btnPrintSelectedSticky, selectedCount === 0);

                if (headerCheck) {
                    const allChecked = rows.length > 0 && selectedCount === rows.length;
                    headerCheck.checked = allChecked;
                    headerCheck.indeterminate = selectedCount > 0 && selectedCount < rows.length;
                }
            }

            function setAllRows(checked) {
                rows.forEach((row) => {
                    const checkbox = row.querySelector('.qr-select');
                    if (checkbox) checkbox.checked = checked;
                });
                updateState();
            }

            function getPrintOptions() {
                const paper = printPaper?.value === 'a4' ? 'a4' : 'f4';
                if (paper === 'a4') {
                    return {
                        paper: 'a4',
                        perSheet: 20,
                        columns: 5,
                        rows: 4,
                        pageSize: '210mm 297mm',
                        labelWidth: 37,
                        labelHeight: 56,
                        qrSize: 22,
                        gap: 2.2,
                    };
                }

                return {
                    paper: 'f4',
                    perSheet: 25,
                    columns: 5,
                    rows: 5,
                    pageSize: '215.9mm 330.2mm',
                    labelWidth: 37,
                    labelHeight: 56,
                    qrSize: 22,
                    gap: 2.2,
                };
            }

            function generateQrDataUrl(text, pxSize) {
                if (!window.QRCode) return '';

                const holder = document.createElement('div');
                holder.style.position = 'fixed';
                holder.style.left = '-9999px';
                holder.style.top = '-9999px';
                document.body.appendChild(holder);

                new window.QRCode(holder, {
                    text: text,
                    width: pxSize,
                    height: pxSize,
                    correctLevel: window.QRCode.CorrectLevel.M,
                });

                const canvas = holder.querySelector('canvas');
                const image = holder.querySelector('img');
                let dataUrl = '';

                if (canvas) {
                    dataUrl = canvas.toDataURL('image/png');
                } else if (image) {
                    dataUrl = image.src;
                }

                document.body.removeChild(holder);
                return dataUrl;
            }

            function mapData(targetRows, pxSize) {
                return targetRows.map((row) => {
                    const kode = row.dataset.kode || '';
                    return {
                        kode: kode,
                        nama: row.dataset.nama || '',
                        lokasi: row.dataset.lokasi || '',
                        qr: generateQrDataUrl(kode, pxSize),
                    };
                }).filter((item) => item.qr !== '');
            }

            function getMetaHtml(item) {
                return `
                    <div class="meta">
                        <p class="code">${escapeHtml(item.kode)}</p>
                        <p class="name">${escapeHtml(item.nama)}</p>
                        <p class="loc">${escapeHtml(item.lokasi)}</p>
                    </div>
                `;
            }

            function chunkItems(items, size) {
                const chunks = [];
                for (let index = 0; index < items.length; index += size) {
                    chunks.push(items.slice(index, index + size));
                }
                return chunks;
            }

            function printLabels(items, title) {
                if (!items.length) {
                    window.alert('Tidak ada data QR yang bisa dicetak.');
                    return;
                }

                const options = getPrintOptions();
                const popup = window.open('', '_blank', 'width=1200,height=860');
                if (!popup) {
                    window.alert('Popup diblokir browser. Izinkan popup untuk cetak QR.');
                    return;
                }

                const pages = chunkItems(items, options.perSheet);
                const pagesHtml = pages.map((pageItems, pageIndex) => {
                    const cards = pageItems.map((item) => `
                        <div class="label">
                            <img src="${item.qr}" alt="QR ${escapeHtml(item.kode)}">
                            ${getMetaHtml(item)}
                        </div>
                    `).join('');

                    return `
                        <section class="sheet ${pageIndex < pages.length - 1 ? 'sheet-break' : ''}">
                            <div class="grid">${cards}</div>
                        </section>
                    `;
                }).join('');

                const html = `
                    <!doctype html>
                    <html>
                    <head>
                        <meta charset="utf-8">
                        <title>${escapeHtml(title)}</title>
                        <style>
                            * { box-sizing: border-box; }
                            @page { size: ${options.pageSize}; margin: 8mm; }
                            body { margin: 0; color: #0f172a; font-family: Arial, sans-serif; }
                            .sheet-break { page-break-after: always; break-after: page; }
                            .grid { display: grid; grid-template-columns: repeat(${options.columns}, ${options.labelWidth}mm); gap: ${options.gap}mm; justify-content: center; }
                            .label {
                                border: 1px solid #cbd5e1;
                                border-radius: 3mm;
                                padding: 2mm 1.6mm;
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                justify-content: flex-start;
                                text-align: center;
                                gap: 1.4mm;
                                width: ${options.labelWidth}mm;
                                min-height: ${options.labelHeight}mm;
                                page-break-inside: avoid;
                                overflow: hidden;
                            }
                            .label img { width: ${options.qrSize}mm; height: ${options.qrSize}mm; object-fit: contain; display: block; }
                            .meta { width: 100%; line-height: 1.25; }
                            .meta p { margin: 0 0 0.8mm; overflow: hidden; text-overflow: ellipsis; }
                            .meta .code { font-family: monospace; font-size: 8px; font-weight: 700; white-space: nowrap; }
                            .meta .name { font-size: 8px; font-weight: 700; white-space: nowrap; }
                            .meta .loc { font-size: 7px; color: #475569; white-space: nowrap; }
                        </style>
                    </head>
                    <body>
                        ${pagesHtml}
                    </body>
                    </html>
                `;

                popup.document.open();
                popup.document.write(html);
                popup.document.close();
                popup.focus();
                setTimeout(() => popup.print(), 250);
            }

            function printSelected() {
                const selectedRows = getSelectedRows();
                if (!selectedRows.length) {
                    window.alert('Pilih minimal 1 aset untuk dicetak.');
                    return;
                }

                printLabels(mapData(selectedRows, 220), 'Cetak QR Aset (Terpilih)');
            }

            function printPage() {
                printLabels(mapData(rows, 220), 'Cetak QR Aset (Semua Halaman)');
            }

            if (headerCheck) {
                headerCheck.addEventListener('change', () => setAllRows(headerCheck.checked));
            }

            rows.forEach((row) => {
                const checkbox = row.querySelector('.qr-select');
                checkbox?.addEventListener('change', () => updateState());
            });

            btnSelectAllVisible?.addEventListener('click', () => setAllRows(true));
            btnClearAll?.addEventListener('click', () => setAllRows(false));
            btnClearSticky?.addEventListener('click', () => setAllRows(false));
            btnPrintSelectedSticky?.addEventListener('click', printSelected);
            btnPrintPage?.addEventListener('click', printPage);

            updateState();
        })();
    </script>
</x-layouts.sbadmin>
