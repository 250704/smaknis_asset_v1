<x-layouts.sbadmin>
    <div class="mb-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="page-title">Input Massal Sarana</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Input data sarana dalam jumlah banyak langsung dari web</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.sarana.create') }}" class="btn-secondary">
                    <i class="fas fa-arrow-left mr-2 text-xs"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.sarana.import-massal.store') }}" id="import-form">
        @csrf
        
        <div class="grid gap-6 xl:grid-cols-12">
            {{-- Dynamic Rows Container --}}
            <div class="xl:col-span-9">
                <div class="panel">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">Data Sarana</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Tambahkan data sarana yang akan diimport</p>
                        </div>
                        <button type="button" onclick="addRow()" class="btn-primary">
                            <i class="fas fa-plus mr-2 text-xs"></i>Tambah Baris
                        </button>
                    </div>

                    <div id="rows-container" class="space-y-4">
                        <!-- Rows will be added here dynamically -->
                    </div>

                    <div class="mt-4 flex justify-center">
                        <button type="button" onclick="addRow()" class="btn-secondary w-full">
                            <i class="fas fa-plus mr-2 text-xs"></i>Tambah Baris Baru
                        </button>
                    </div>
                </div>
            </div>

            {{-- Summary Sidebar --}}
            <aside class="xl:col-span-3">
                <div class="panel sticky top-6">
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        <i class="fas fa-chart-bar mr-2"></i>Ringkasan
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-slate-900/60">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Total Baris</p>
                            <p id="summary-rows" class="mt-1 text-2xl font-bold text-slate-800 dark:text-slate-100">0</p>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-slate-900/60">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Total Unit</p>
                            <p id="summary-units" class="mt-1 text-2xl font-bold text-slate-800 dark:text-slate-100">0</p>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-slate-900/60">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Estimasi Harga</p>
                            <p id="summary-price" class="mt-1 text-lg font-bold text-slate-800 dark:text-slate-100">Rp 0</p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <button
                            type="submit"
                            class="btn-primary w-full justify-center bg-emerald-600 hover:bg-emerald-700"
                            data-confirm-title="Konfirmasi Import"
                            data-confirm-confirm-label="Ya, Import"
                            data-confirm-variant="success"
                        >
                            <i class="fas fa-save mr-2 text-xs"></i>Import Sekarang
                        </button>
                        <button type="button" onclick="resetForm()" class="btn-secondary w-full">
                            <i class="fas fa-undo mr-2 text-xs"></i>Reset Form
                        </button>
                    </div>

                    <div class="mt-6 rounded-xl bg-blue-500/10 p-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-info-circle mt-0.5 text-blue-500"></i>
                            <div class="text-sm text-slate-700 dark:text-slate-300">
                                <p class="font-semibold">Tips</p>
                                <ul class="mt-2 space-y-1 text-xs">
                                    <li>• Max 50 baris per import</li>
                                    <li>• Isi semua field wajib (*)</li>
                                    <li>• Jumlah unit = total sarana dibuat</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </form>

    {{-- Row Template (Hidden) --}}
    <template id="row-template">
        <div class="import-row rounded-xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-slate-800/50" data-row="__ROW__">
            <div class="mb-3 flex items-center justify-between border-b border-slate-200 pb-2 dark:border-white/10">
                <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                    <span class="row-number">__ROW__</span>. Data Sarana
                </h4>
                <button type="button" onclick="removeRow(__ROW__)" class="text-rose-500 hover:text-rose-700">
                    <i class="fas fa-trash mr-1 text-xs"></i>Hapus Baris
                </button>
            </div>

            <div class="grid gap-3 md:grid-cols-3">
                <div class="md:col-span-3">
                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Nama Sarana <span class="text-rose-500">*</span></label>
                    <input type="text" name="rows[__ROW__][nama_sarana]" required placeholder="Contoh: PC All in One" 
                           class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900 dark:text-slate-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Kategori <span class="text-rose-500">*</span></label>
                    <select name="rows[__ROW__][kategori_id]" required 
                            class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">Pilih Kategori</option>
                        @foreach($kategoriList as $kategori)
                            <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Ruangan <span class="text-rose-500">*</span></label>
                    <select name="rows[__ROW__][ruangan_id]" required 
                            class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">Pilih Ruangan</option>
                        @foreach($ruanganList as $ruangan)
                            <option value="{{ $ruangan->id }}">{{ $ruangan->nama_ruangan }} - {{ $ruangan->gedung?->nama_gedung }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Tahun <span class="text-rose-500">*</span></label>
                    <input type="number" name="rows[__ROW__][tahun_perolehan]" required min="1900" max="{{ date('Y') + 1 }}" value="{{ date('Y') }}" 
                           class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900 dark:text-slate-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Kondisi <span class="text-rose-500">*</span></label>
                    <select name="rows[__ROW__][kondisi_terkini]" required 
                            class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">Pilih Kondisi</option>
                        @foreach($kondisiList as $kondisi)
                            <option value="{{ $kondisi }}">{{ $kondisi }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Status <span class="text-rose-500">*</span></label>
                    <select name="rows[__ROW__][status_sarana]" required 
                            class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">Pilih Status</option>
                        @foreach($statusList as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Jumlah Unit <span class="text-rose-500">*</span></label>
                    <input type="number" name="rows[__ROW__][jumlah_unit]" required min="1" max="500" value="1" 
                           class="unit-input w-full rounded-lg border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900 dark:text-slate-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Harga (Rp)</label>
                    <input type="number" name="rows[__ROW__][harga_perolehan]" min="0" step="0.01" value="0" 
                           class="price-input w-full rounded-lg border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900 dark:text-slate-100">
                </div>
            </div>
        </div>
    </template>

    <script>
        let rowCount = 0;
        const maxRows = 50;

        function addRow() {
            if (rowCount >= maxRows) {
                alert('Maksimal ' + maxRows + ' baris!');
                return;
            }

            rowCount++;
            const template = document.getElementById('row-template');
            const clone = template.content.cloneNode(true);
            
            // Replace __ROW__ with actual row number
            clone.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace('__ROW__', rowCount);
            });
            clone.querySelectorAll('.import-row').forEach(el => {
                el.setAttribute('data-row', rowCount);
            });
            clone.querySelectorAll('.row-number').forEach(el => {
                el.textContent = rowCount;
            });
            clone.querySelectorAll('button[onclick]').forEach(el => {
                el.setAttribute('onclick', el.getAttribute('onclick').replace('__ROW__', rowCount));
            });

            document.getElementById('rows-container').appendChild(clone);
            updateSummary();
        }

        function removeRow(rowId) {
            const deleteRow = function () {
                const row = document.querySelector(`[data-row="${rowId}"]`);
                if (row) {
                    row.remove();
                    reindexRows();
                    updateSummary();
                }
            };

            if (window.SarprasConfirm) {
                window.SarprasConfirm.open({
                    title: 'Konfirmasi Hapus Baris',
                    message: 'Apakah Anda yakin ingin menghapus baris data ini?',
                    confirmLabel: 'Ya, Hapus',
                    variant: 'danger',
                    onConfirm: deleteRow,
                });
                return;
            }

            deleteRow();
        }

        function reindexRows() {
            const rows = document.querySelectorAll('.import-row');
            rowCount = 0;
            rows.forEach((row, index) => {
                rowCount++;
                const newRow = index + 1;
                const oldRow = row.getAttribute('data-row');
                
                row.setAttribute('data-row', newRow);
                row.querySelector('.row-number').textContent = newRow;
                
                // Update all field names
                row.querySelectorAll('[name]').forEach(el => {
                    el.name = el.name.replace(`rows[${oldRow}]`, `rows[${newRow}]`);
                });
                
                // Update delete button
                const deleteBtn = row.querySelector('button[onclick*="removeRow"]');
                if (deleteBtn) {
                    deleteBtn.setAttribute('onclick', `removeRow(${newRow})`);
                }
            });
        }

        function updateSummary() {
            const rows = document.querySelectorAll('.import-row');
            const totalRows = rows.length;
            let totalUnits = 0;
            let totalPrice = 0;

            rows.forEach(row => {
                const unitInput = row.querySelector('.unit-input');
                const priceInput = row.querySelector('.price-input');
                
                const units = parseInt(unitInput?.value) || 0;
                const price = parseFloat(priceInput?.value) || 0;
                
                totalUnits += units;
                totalPrice += (price * units);
            });

            document.getElementById('summary-rows').textContent = totalRows;
            document.getElementById('summary-units').textContent = totalUnits.toLocaleString('id-ID');
            document.getElementById('summary-price').textContent = 'Rp ' + totalPrice.toLocaleString('id-ID');
        }

        function resetForm() {
            const resetRows = function () {
                document.getElementById('import-form').reset();
                document.getElementById('rows-container').innerHTML = '';
                rowCount = 0;
                addRow();
                updateSummary();
            };

            if (window.SarprasConfirm) {
                window.SarprasConfirm.open({
                    title: 'Konfirmasi Reset Form',
                    message: 'Apakah Anda yakin ingin mereset semua data import?',
                    confirmLabel: 'Ya, Reset',
                    variant: 'warning',
                    onConfirm: resetRows,
                });
                return;
            }

            resetRows();
        }

        // Add event listeners for real-time summary update
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('unit-input') || e.target.classList.contains('price-input')) {
                updateSummary();
            }
        });

        // Add first row on load
        document.addEventListener('DOMContentLoaded', function() {
            addRow();
        });

        // Form validation before submit
        document.getElementById('import-form').addEventListener('submit', function(e) {
            const rows = document.querySelectorAll('.import-row');
            if (rows.length === 0) {
                e.preventDefault();
                alert('Minimal 1 baris data harus diisi!');
                return false;
            }

            this.dataset.confirmMessage = `Apakah Anda yakin ingin mengimport ${rows.length} baris data (${document.getElementById('summary-units').textContent} unit)?`;
        });
    </script>
</x-layouts.sbadmin>
