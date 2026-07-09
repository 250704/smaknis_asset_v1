<x-layouts.sbadmin>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="page-title">Detail Sarana</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $aset->kode_aset }} - {{ $aset->nama_aset }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('kepala_sarana.aset.histori', ['q' => $aset->kode_aset]) }}" class="btn-secondary">Lihat Histori</a>
            <a href="{{ route('kepala_sarana.aset.index') }}" class="btn-secondary">Kembali</a>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-12">
        <section class="panel lg:col-span-7">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Informasi Utama</h2>
            <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Kode Sarana</dt>
                    <dd class="font-mono text-sm text-slate-700 dark:text-slate-200">{{ $aset->kode_aset }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Nama Sarana</dt>
                    <dd class="text-sm text-slate-700 dark:text-slate-200">{{ $aset->nama_aset }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Kategori</dt>
                    <dd class="text-sm text-slate-700 dark:text-slate-200">{{ $aset->kategori?->nama_kategori }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Lokasi</dt>
                    <dd class="text-sm text-slate-700 dark:text-slate-200">{{ $aset->ruangan?->nama_ruangan }} - {{ $aset->ruangan?->gedung?->nama_gedung }} (Lt. {{ $aset->ruangan?->lantai ?? '-' }})</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Tahun Perolehan</dt>
                    <dd class="text-sm text-slate-700 dark:text-slate-200">{{ $aset->tahun_perolehan }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Harga Perolehan</dt>
                    <dd class="text-sm text-slate-700 dark:text-slate-200">
                        {{ $aset->harga_perolehan ? 'Rp ' . number_format((float) $aset->harga_perolehan, 0, ',', '.') : '-' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Kondisi</dt>
                    <dd>
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $aset->kondisi_terkini === 'BAIK' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200' }}">
                            {{ $aset->kondisi_terkini }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</dt>
                    <dd>
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $aset->status_aset === 'AKTIF' ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-200' : 'bg-slate-200 text-slate-700 dark:bg-slate-600/30 dark:text-slate-200' }}">
                            {{ $aset->status_aset }}
                        </span>
                    </dd>
                </div>
            </dl>
        </section>

        <section class="panel lg:col-span-5">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Foto Sarana</h2>
            <div class="mt-4">
                @if ($aset->foto_aset)
                    <img src="{{ asset('storage/' . $aset->foto_aset) }}" alt="Foto aset" class="h-64 w-full rounded-xl object-cover">
                @else
                    <div class="flex h-64 items-center justify-center rounded-xl border border-dashed border-slate-300 text-sm text-slate-500 dark:border-white/20 dark:text-slate-400">
                        Belum ada foto sarana
                    </div>
                @endif
            </div>

            <div class="mt-5 rounded-xl border border-slate-200 p-4 dark:border-white/10">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">QR Unit Sarana</h3>
                <div class="mt-3 flex flex-col items-center gap-3">
                    <div id="detail-qr" class="rounded-lg bg-white p-2"></div>
                    <p class="font-mono text-[11px] text-slate-600 dark:text-slate-300">{{ $aset->kode_aset }}</p>
                </div>
            </div>
        </section>
    </div>

    <section class="panel mt-5">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Riwayat Kondisi Terbaru</h2>
        <div class="mt-4 space-y-3">
            @forelse ($riwayatKondisi as $riwayat)
                <div class="rounded-xl border border-slate-200 px-3 py-2 dark:border-white/10">
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $riwayat->created_at?->format('d M Y H:i') }}</p>
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $riwayat->tingkat_kerusakan }} - {{ $riwayat->status }}</p>
                    <p class="text-sm text-slate-600 dark:text-slate-300">{{ $riwayat->deskripsi }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500 dark:text-slate-400">Belum ada riwayat kondisi.</p>
            @endforelse
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        (function () {
            const qrContainer = document.getElementById('detail-qr');
            const kodeAset = @json($aset->kode_aset);

            if (!qrContainer || !window.QRCode) {
                return;
            }

            new window.QRCode(qrContainer, {
                text: kodeAset,
                width: 160,
                height: 160,
                correctLevel: window.QRCode.CorrectLevel.M,
            });
        })();
    </script>
</x-layouts.sbadmin>
