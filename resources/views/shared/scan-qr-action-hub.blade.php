<x-layouts.sbadmin>
    @php
        $roleLabel = match ($role) {
            'admin' => 'Admin Sarpras',
            'guru' => 'Guru / Staf',
            'kepala_sarana' => 'Kepala Sarana',
            'bendahara' => 'Bendahara',
            'kepala_sekolah' => 'Kepala Sekolah',
            default => 'Pengguna',
        };

        $scanRoute = route($role . '.scan');
    @endphp

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="page-title">Scan QR Action Hub</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Input hasil scan QR, lihat detail aset, lalu pilih aksi proses.</p>
        </div>
        <span class="inline-flex rounded-full border border-cyan-400/30 bg-cyan-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-cyan-700 dark:text-cyan-200">
            {{ $roleLabel }}
        </span>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <section class="panel">
        <form id="qr-scan-form" method="GET" action="{{ $scanRoute }}" class="grid gap-3 md:grid-cols-[1fr_auto]">
            <div>
                <label for="kode_aset" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Hasil QR / Kode Aset</label>
                <input
                    id="kode_aset"
                    name="kode_aset"
                    type="text"
                    value="{{ $kodeAset }}"
                    placeholder="Contoh: AST-GDA-LAB-L02-2026-0001"
                    class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"
                    inputmode="text"
                    enterkeyhint="search"
                    autocapitalize="characters"
                    spellcheck="false"
                    maxlength="50"
                    oninput="this.value = this.value.toUpperCase().replace(/\s+/g, '')"
                    autofocus
                >
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Bisa tempel dari scanner barcode atau ketik manual. Hanya huruf, angka, dan tanda "-".</p>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary">Cari Aset</button>
                <a href="{{ $scanRoute }}" class="btn-secondary">Reset</a>
            </div>
        </form>

        @if ($scanError)
            <div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-400/30 dark:bg-rose-500/10 dark:text-rose-200">
                {{ $scanError }}
            </div>
        @elseif ($kodeAset !== '' && $isExactFormat)
            <div class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-medium text-emerald-700 dark:border-emerald-400/30 dark:bg-emerald-500/10 dark:text-emerald-200">
                Format QR valid.
            </div>
        @endif

    </section>

    @if ($kodeAset === '')
        <section class="panel" id="qr-camera-panel">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Scan Langsung Kamera</h2>
            <div class="flex flex-wrap gap-2">
                <button type="button" id="btn-start-camera" class="btn-primary">Start Kamera</button>
                <button type="button" id="btn-switch-camera" class="btn-secondary hidden">Ganti Kamera</button>
                <button type="button" id="btn-stop-camera" class="btn-secondary hidden">Stop Kamera</button>
            </div>
        </div>

        <div class="mt-3 overflow-hidden rounded-xl border border-slate-200 bg-slate-50 dark:border-white/10 dark:bg-slate-900/50">
            <div class="relative">
                <video id="qr-video" class="h-[280px] w-full bg-slate-950 object-cover sm:h-[360px]" autoplay muted playsinline></video>
                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                    <div id="scan-frame" class="relative h-36 w-36 rounded-lg border border-cyan-300/70 shadow-[0_0_0_9999px_rgba(2,6,23,.4)] transition-all duration-300 sm:h-40 sm:w-40">
                        <span class="absolute -left-0.5 -top-0.5 h-5 w-5 border-l-2 border-t-2 border-cyan-300"></span>
                        <span class="absolute -right-0.5 -top-0.5 h-5 w-5 border-r-2 border-t-2 border-cyan-300"></span>
                        <span class="absolute -bottom-0.5 -left-0.5 h-5 w-5 border-b-2 border-l-2 border-cyan-300"></span>
                        <span class="absolute -bottom-0.5 -right-0.5 h-5 w-5 border-b-2 border-r-2 border-cyan-300"></span>
                        <div id="scan-success-badge" class="absolute -top-9 left-1/2 hidden -translate-x-1/2 rounded-full bg-emerald-500 px-3 py-1 text-[11px] font-semibold text-white shadow-lg">
                            QR Berhasil
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="camera-status" class="mt-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-300">
            Kamera belum aktif.
        </div>
        </section>
    @endif

    @if ($kodeAset !== '')
        <section class="panel mt-5">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Hasil Pencarian</h2>
            @if ($searchResults->isEmpty())
                <p class="mt-3 text-sm text-rose-600 dark:text-rose-300">Aset dengan kode/kata kunci tersebut tidak ditemukan.</p>
            @else
                <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 dark:border-white/10">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-white/10">
                        <thead class="bg-slate-50 dark:bg-white/[0.04]">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Kode</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Nama</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Lokasi</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach ($searchResults as $item)
                                <tr>
                                    <td class="px-4 py-3 font-mono text-xs text-slate-700 dark:text-slate-200">{{ $item->kode_aset }}</td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $item->nama_aset }}</td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $item->ruangan?->nama_ruangan }} - {{ $item->ruangan?->gedung?->nama_gedung }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ $scanRoute . '?kode_aset=' . urlencode($item->kode_aset) }}" class="btn-secondary">Pilih</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endif

    @if ($aset)
        <div class="mt-5 grid gap-5 lg:grid-cols-12">
            <section class="panel lg:col-span-7">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Detail Aset Terscan</h2>
                <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Kode Aset</dt>
                        <dd class="font-mono text-sm text-slate-700 dark:text-slate-200">{{ $aset->kode_aset }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Nama Aset</dt>
                        <dd class="text-sm text-slate-700 dark:text-slate-200">{{ $aset->nama_aset }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Kategori</dt>
                        <dd class="text-sm text-slate-700 dark:text-slate-200">{{ $aset->kategori?->nama_kategori }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Lokasi</dt>
                        <dd class="text-sm text-slate-700 dark:text-slate-200">
                            {{ $aset->ruangan?->nama_ruangan }} [{{ $aset->ruangan?->kode_ruangan ?? '---' }}] -
                            {{ $aset->ruangan?->gedung?->nama_gedung }} [{{ $aset->ruangan?->gedung?->kode_gedung ?? '---' }}]
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

                <div class="mt-5 rounded-xl border border-cyan-200 bg-cyan-50 p-4 dark:border-cyan-400/30 dark:bg-cyan-500/10">
                    <h3 class="text-sm font-semibold text-cyan-800 dark:text-cyan-200">Aksi Cepat Setelah Scan</h3>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($actions as $actionKey => $label)
                            <a href="{{ route($role . '.scan.action', ['aset' => $aset, 'action' => $actionKey]) }}" class="btn-secondary justify-center text-center">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="panel lg:col-span-5">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Foto Aset</h2>
                <div class="mt-4">
                    @if ($aset->foto_aset)
                        <img src="{{ asset('storage/' . $aset->foto_aset) }}" alt="Foto aset" class="h-56 w-full rounded-xl object-cover">
                    @else
                        <div class="flex h-56 items-center justify-center rounded-xl border border-dashed border-slate-300 text-sm text-slate-500 dark:border-white/20 dark:text-slate-400">
                            Belum ada foto aset
                        </div>
                    @endif
                </div>

                <h3 class="mt-5 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Riwayat Kondisi Terbaru</h3>
                <div class="mt-2 space-y-2">
                    @forelse ($aset->riwayatKondisiAset as $riwayat)
                        <div class="rounded-lg border border-slate-200 px-3 py-2 dark:border-white/10">
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $riwayat->created_at?->format('d M Y H:i') }}</p>
                            <p class="text-sm text-slate-700 dark:text-slate-200">{{ $riwayat->tingkat_kerusakan }} - {{ $riwayat->status }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400">Belum ada riwayat kondisi.</p>
                    @endforelse
                </div>
            </section>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
    <script>
        (function () {
            const form = document.getElementById('qr-scan-form');
            const input = document.getElementById('kode_aset');
            const btnStart = document.getElementById('btn-start-camera');
            const btnStop = document.getElementById('btn-stop-camera');
            const btnSwitch = document.getElementById('btn-switch-camera');
            const video = document.getElementById('qr-video');
            const statusBox = document.getElementById('camera-status');
            const scanFrameEl = document.getElementById('scan-frame');
            const scanSuccessBadge = document.getElementById('scan-success-badge');

            if (!form || !input || !btnStart || !btnStop || !btnSwitch || !video || !statusBox || !scanFrameEl || !scanSuccessBadge) {
                return;
            }

            let stream = null;
            let detector = null;
            let scanTimer = null;
            let currentFacingMode = 'environment';
            let canvas = null;
            let context2d = null;
            let usingFallback = false;
            let isSubmitting = false;

            const exactPattern = /^AST-[A-Z0-9]{3}-[A-Z0-9]{3}-L\d{2}-\d{4}-\d{4}$/;

            function setStatus(message, isError = false) {
                statusBox.textContent = message;
                statusBox.classList.toggle('border-rose-200', isError);
                statusBox.classList.toggle('bg-rose-50', isError);
                statusBox.classList.toggle('text-rose-700', isError);
                statusBox.classList.toggle('dark:border-rose-400/30', isError);
                statusBox.classList.toggle('dark:bg-rose-500/10', isError);
                statusBox.classList.toggle('dark:text-rose-200', isError);
            }

            function updateButtons(active) {
                btnStart.classList.toggle('hidden', active);
                btnStop.classList.toggle('hidden', !active);
                btnSwitch.classList.toggle('hidden', !active);
            }

            function resolveCameraError(error) {
                if (!error) {
                    return 'Kamera gagal diaktifkan.';
                }

                const name = String(error.name || '');
                if (name === 'NotAllowedError' || name === 'SecurityError') {
                    return 'Izin kamera ditolak. Aktifkan izin kamera di browser untuk situs ini.';
                }
                if (name === 'NotFoundError' || name === 'DevicesNotFoundError') {
                    return 'Kamera tidak ditemukan pada perangkat ini.';
                }
                if (name === 'NotReadableError' || name === 'TrackStartError') {
                    return 'Kamera sedang dipakai aplikasi lain. Tutup aplikasi lain lalu coba lagi.';
                }
                if (name === 'OverconstrainedError' || name === 'ConstraintNotSatisfiedError') {
                    return 'Pengaturan kamera tidak cocok. Mencoba mode kamera dasar...';
                }
                if (name === 'TypeError') {
                    return 'Perangkat/browser tidak mendukung konfigurasi kamera ini.';
                }

                return 'Kamera gagal diaktifkan: ' + name;
            }

            function normalizeCode(rawValue) {
                const raw = String(rawValue || '').trim().toUpperCase();
                if (exactPattern.test(raw)) {
                    return raw;
                }

                const matches = raw.match(/AST-[A-Z0-9]{3}-[A-Z0-9]{3}-L\d{2}-\d{4}-\d{4}/g);
                if (matches && matches.length > 0) {
                    return matches[0];
                }

                return raw.replace(/\s+/g, '');
            }

            function submitCode(code) {
                if (!code || isSubmitting) {
                    return;
                }

                isSubmitting = true;
                input.value = code;
                setStatus('QR terdeteksi: ' + code + '. Klik "Cari Aset" untuk melihat detail.');
                scanFrameEl.classList.remove('border-cyan-300/70');
                scanFrameEl.classList.add('border-emerald-400', 'scale-105');
                scanSuccessBadge.classList.remove('hidden');
                stopCamera();
                window.setTimeout(function () {
                    isSubmitting = false;
                    scanSuccessBadge.classList.add('hidden');
                    scanFrameEl.classList.remove('border-emerald-400', 'scale-105');
                    scanFrameEl.classList.add('border-cyan-300/70');
                }, 1200);
            }

            async function detectFrame() {
                if (!video || video.readyState < 2) {
                    return;
                }

                try {
                    if (detector) {
                        const barcodes = await detector.detect(video);
                        if (barcodes && barcodes.length > 0) {
                            const value = normalizeCode(barcodes[0].rawValue || '');
                            submitCode(value);
                        }
                        return;
                    }

                    if (usingFallback && context2d && canvas && typeof window.jsQR === 'function') {
                        if (canvas.width !== video.videoWidth || canvas.height !== video.videoHeight) {
                            canvas.width = video.videoWidth;
                            canvas.height = video.videoHeight;
                        }

                        context2d.drawImage(video, 0, 0, canvas.width, canvas.height);
                        const imageData = context2d.getImageData(0, 0, canvas.width, canvas.height);
                        const result = window.jsQR(imageData.data, imageData.width, imageData.height, {
                            inversionAttempts: 'dontInvert'
                        });

                        if (result && result.data) {
                            const value = normalizeCode(result.data);
                            submitCode(value);
                        }
                    }
                } catch (error) {
                    setStatus('Gagal membaca frame kamera. Coba ulangi start kamera.', true);
                }
            }

            function startLoop() {
                stopLoop();
                scanTimer = window.setInterval(detectFrame, 250);
            }

            function stopLoop() {
                if (scanTimer) {
                    window.clearInterval(scanTimer);
                    scanTimer = null;
                }
            }

            async function openCameraWithFallback() {
                const primaryConstraints = {
                    video: {
                        facingMode: { ideal: currentFacingMode },
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false
                };

                try {
                    return await navigator.mediaDevices.getUserMedia(primaryConstraints);
                } catch (error) {
                    const message = resolveCameraError(error);
                    if (!message.includes('Mencoba mode kamera dasar')) {
                        throw error;
                    }
                }

                return navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            }

            async function startCamera() {
                if (!('mediaDevices' in navigator) || !navigator.mediaDevices.getUserMedia) {
                    setStatus('Browser tidak mendukung akses kamera. Gunakan input manual.', true);
                    return;
                }

                if (!window.isSecureContext) {
                    setStatus('Akses kamera butuh secure context. Gunakan https atau localhost.', true);
                    return;
                }

                detector = null;
                usingFallback = false;
                isSubmitting = false;
                scanSuccessBadge.classList.add('hidden');
                scanFrameEl.classList.remove('border-emerald-400', 'scale-105');
                scanFrameEl.classList.add('border-cyan-300/70');

                if ('BarcodeDetector' in window) {
                    try {
                        detector = new window.BarcodeDetector({ formats: ['qr_code'] });
                    } catch (error) {
                        detector = null;
                    }
                }

                if (!detector) {
                    if (typeof window.jsQR !== 'function') {
                        setStatus('Browser tidak mendukung scanner QR otomatis. Gunakan input manual/scanner eksternal.', true);
                        return;
                    }
                    usingFallback = true;
                    if (!canvas) {
                        canvas = document.createElement('canvas');
                        context2d = canvas.getContext('2d', { willReadFrequently: true });
                    }
                }

                stopCamera();
                setStatus('Meminta izin kamera...');

                try {
                    stream = await openCameraWithFallback();

                    video.srcObject = stream;
                    await video.play();
                    updateButtons(true);
                    setStatus(usingFallback
                        ? 'Kamera aktif (mode kompatibilitas). Arahkan QR aset ke kotak scan.'
                        : 'Kamera aktif. Arahkan QR aset ke kotak scan.');
                    startLoop();
                } catch (error) {
                    setStatus(resolveCameraError(error), true);
                    updateButtons(false);
                }
            }

            function stopCamera() {
                stopLoop();
                if (stream) {
                    stream.getTracks().forEach((track) => track.stop());
                    stream = null;
                }
                video.srcObject = null;
                updateButtons(false);
            }

            btnStart.addEventListener('click', startCamera);
            btnStop.addEventListener('click', function () {
                stopCamera();
                setStatus('Kamera dihentikan.');
            });

            btnSwitch.addEventListener('click', async function () {
                currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
                await startCamera();
            });

            window.addEventListener('beforeunload', stopCamera);
            document.addEventListener('visibilitychange', function () {
                if (document.hidden) {
                    stopCamera();
                }
            });
        })();
    </script>
</x-layouts.sbadmin>
