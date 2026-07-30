<x-layouts.sbadmin>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="page-title">Detail Sarana</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $sarana->kode_sarana }} - {{ $sarana->nama_sarana }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('kepala_sarana.sarana.histori', ['q' => $sarana->kode_sarana]) }}" class="btn-secondary">Lihat Histori</a>
            <a href="{{ route('kepala_sarana.sarana.index') }}" class="btn-secondary">Kembali</a>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-12">
        <section class="panel lg:col-span-7">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Informasi Utama</h2>
            <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Kode Sarana</dt>
                    <dd class="font-mono text-sm text-slate-700 dark:text-slate-200">{{ $sarana->kode_sarana }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Nama Sarana</dt>
                    <dd class="text-sm text-slate-700 dark:text-slate-200">{{ $sarana->nama_sarana }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Kategori</dt>
                    <dd class="text-sm text-slate-700 dark:text-slate-200">{{ $sarana->kategori?->nama_kategori }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Lokasi</dt>
                    <dd class="text-sm text-slate-700 dark:text-slate-200">{{ $sarana->ruangan?->nama_ruangan }} - {{ $sarana->ruangan?->gedung?->nama_gedung }} (Lt. {{ $sarana->ruangan?->lantai ?? '-' }})</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Tahun Perolehan</dt>
                    <dd class="text-sm text-slate-700 dark:text-slate-200">{{ $sarana->tahun_perolehan }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Harga Perolehan</dt>
                    <dd class="text-sm text-slate-700 dark:text-slate-200">
                        {{ $sarana->harga_perolehan ? 'Rp ' . number_format((float) $sarana->harga_perolehan, 0, ',', '.') : '-' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Kondisi</dt>
                    <dd>
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $sarana->kondisi_terkini === 'BAIK' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200' }}">
                            {{ $sarana->kondisi_terkini }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</dt>
                    <dd>
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $sarana->status_sarana === 'AKTIF' ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-200' : 'bg-slate-200 text-slate-700 dark:bg-slate-600/30 dark:text-slate-200' }}">
                            {{ $sarana->status_sarana }}
                        </span>
                    </dd>
                </div>
            </dl>
        </section>

        <section class="panel lg:col-span-5">
            <div class="rounded-xl border border-slate-200 p-4 dark:border-white/10">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">QR Unit Sarana</h3>
                <div class="mt-3 flex flex-col items-center gap-3">
                    <div id="detail-qr" class="rounded-lg bg-white p-2"></div>
                    <p class="font-mono text-[11px] text-slate-600 dark:text-slate-300">{{ $sarana->kode_sarana }}</p>
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
            const kodesarana = @json($sarana->kode_sarana);

            if (!qrContainer || !window.QRCode) {
                return;
            }

            new window.QRCode(qrContainer, {
                text: kodesarana,
                width: 160,
                height: 160,
                correctLevel: window.QRCode.CorrectLevel.M,
            });
        })();
    </script>
</x-layouts.sbadmin>
