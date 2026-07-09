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
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Input hasil scan QR, lihat detail sarana, lalu pilih aksi proses.</p>
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

    @php
        $invalidQrMessage = 'QR code tidak valid. Pastikan QR milik sarana yang terdaftar.';
        $shouldOpenInvalidQrPopup = $scanError === $invalidQrMessage;
    @endphp

    <section class="panel">
        <form id="qr-scan-form" method="GET" action="{{ $scanRoute }}" class="filter-grid">
            <div>
                <label for="kode_aset" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Hasil QR / Kode Sarana</label>
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
            <div class="filter-actions">
                <button type="submit" class="btn-primary">Cari Sarana</button>
                <a href="{{ $scanRoute }}" class="btn-secondary">Reset</a>
            </div>
        </form>

        @if ($scanError && !$shouldOpenInvalidQrPopup)
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
                        <div class="scan-line"></div>
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

    <style>
        #scan-frame {
            width: 11rem;
            height: 11rem;
        }

        @media (min-width: 640px) {
            #scan-frame {
                width: 13rem;
                height: 13rem;
            }
        }

        @media (min-width: 1024px) {
            #scan-frame {
                width: 14rem;
                height: 14rem;
            }
        }

        .scan-line {
            position: absolute;
            left: 0.75rem;
            right: 0.75rem;
            height: 2px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(239, 68, 68, 0.95);
            box-shadow: 0 0 10px rgba(239, 68, 68, 0.85);
            animation: scanLine 2.2s ease-in-out infinite;
        }

        @keyframes scanLine {
            0% {
                top: 18%;
            }
            50% {
                top: 82%;
            }
            100% {
                top: 18%;
            }
        }
    </style>

    @if ($kodeAset !== '')
        <section class="panel mt-5">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Hasil Pencarian</h2>
            @if ($searchResults->isEmpty() && !$scanError)
                <p class="mt-3 text-sm text-rose-600 dark:text-rose-300">Sarana dengan kode/kata kunci tersebut tidak ditemukan.</p>
            @elseif ($searchResults->isNotEmpty())
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
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Detail Sarana Terscan</h2>
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

                <div class="mt-5">
                    <div class="mb-3 rounded-xl border border-cyan-200 bg-cyan-50 p-4 dark:border-cyan-400/30 dark:bg-cyan-500/10">
                        <h3 class="text-sm font-semibold text-cyan-800 dark:text-cyan-200">
                            <i class="fas fa-info-circle mr-2"></i>Aksi Tersedia
                        </h3>
                        <p class="mt-1 text-xs text-cyan-700 dark:text-cyan-300">
                            @if($role === 'guru')
                                <i class="fas fa-lightbulb mr-1"></i>
                                <strong>Tip:</strong> Gunakan "Lapor Kerusakan" jika sarana rusak. Pengajuan perawatan/penggantian akan otomatis dibuat setelah validasi Kepala Sarana.
                            @elseif($role === 'kepala_sarana')
                                <i class="fas fa-clipboard-check mr-1"></i>
                                <strong>Tip:</strong> Lihat detail aset dan histori sarana dari sini.
                            @elseif($role === 'bendahara')
                                <i class="fas fa-coins mr-1"></i>
                                <strong>Tip:</strong> Review pengajuan dan approval anggaran dari sini.
                            @elseif($role === 'kepala_sekolah')
                                <i class="fas fa-stamp mr-1"></i>
                                <strong>Tip:</strong> Lakukan approval final dari sini.
                            @else
                                <i class="fas fa-tools mr-1"></i>
                                <strong>Tip:</strong> Kelola mutasi dan lihat histori sarana dari sini.
                            @endif
                        </p>
                    </div>
                    
                    <h3 class="mb-2 text-sm font-semibold text-slate-700 dark:text-slate-200">Pilih Aksi:</h3>
                    <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($actions as $actionKey => $label)
                            <a href="{{ route($role . '.scan.action', ['aset' => $aset, 'action' => $actionKey]) }}" class="btn-secondary justify-center text-center hover:scale-105 transition-transform">
                                <i class="fas fa-arrow-right mr-2 text-xs"></i>
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="panel lg:col-span-5">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Foto Sarana</h2>
                <div class="mt-4">
                    @if ($aset->foto_aset)
                        <img src="{{ asset('storage/' . $aset->foto_aset) }}" alt="Foto sarana" class="h-56 w-full rounded-xl object-cover">
                    @else
                        <div class="flex h-56 items-center justify-center rounded-xl border border-dashed border-slate-300 text-sm text-slate-500 dark:border-white/20 dark:text-slate-400">
                            Belum ada foto sarana
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
            let isDetecting = false;
            let lastInvalidCode = '';
            let lastInvalidAt = 0;
            let lastAcceptedCode = '';
            let scanLoopEnabled = false;
            let invalidDialogOpen = false;
            let scanInFlight = false;

            const exactPattern = /^AST-[A-Z0-9]{3}-[A-Z0-9]{3}-L\d{2}-\d{4}-\d{4}$/;
            const FALLBACK_SCAN_INTERVAL_MS = 45;
            const FALLBACK_MAX_WIDTH = 480;
            const FALLBACK_MIN_WIDTH = 240;
            const FALLBACK_CROP_RATIO = 0.72;

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
                const raw = String(rawValue || '').trim();
                const upper = raw.toUpperCase();

                if (exactPattern.test(upper)) {
                    return upper;
                }

                const matches = upper.match(/AST-[A-Z0-9]{3}-[A-Z0-9]{3}-L\d{2}-\d{4}-\d{4}/g);
                if (matches && matches.length > 0) {
                    return matches[0];
                }

                return raw.replace(/\s+/g, '');
            }

            function openInvalidQrPopup(detail) {
                if (invalidDialogOpen || !window.SarprasConfirm) {
                    setStatus('QR code tidak valid. Pastikan QR milik sarana yang terdaftar.', true);
                    return;
                }

                invalidDialogOpen = true;
                stopCamera();
                window.SarprasConfirm.open({
                    title: 'QR code tidak valid',
                    message: detail || 'Pastikan QR yang dipindai adalah milik sarana yang terdaftar.',
                    confirmLabel: 'Tutup',
                    variant: 'danger',
                    onConfirm: function () {
                        invalidDialogOpen = false;
                        setStatus('Kamera dihentikan. Silakan scan ulang jika diperlukan.');
                    },
                });
            }

            function showInvalidQr(code) {
                const now = Date.now();
                if (!code || (code === lastInvalidCode && now - lastInvalidAt < 1500)) {
                    return;
                }

                lastInvalidCode = code;
                lastInvalidAt = now;
                openInvalidQrPopup('QR code tidak valid. Pastikan QR milik sarana yang terdaftar.');
            }

            function submitCode(code) {
                if (!code || isSubmitting) {
                    return;
                }

                if (code === lastAcceptedCode) {
                    return;
                }

                lastAcceptedCode = code;
                isSubmitting = true;
                input.value = code;
                setStatus('QR terdeteksi: ' + code + '. Memeriksa data sarana...');
                scanFrameEl.classList.remove('border-cyan-300/70');
                scanFrameEl.classList.add('border-emerald-400', 'scale-105');
                scanSuccessBadge.classList.remove('hidden');
                window.setTimeout(function () {
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                    isSubmitting = false;
                    scanSuccessBadge.classList.add('hidden');
                    scanFrameEl.classList.remove('border-emerald-400', 'scale-105');
                    scanFrameEl.classList.add('border-cyan-300/70');
                }, 900);
            }

            function decodeCanvasRegion(sourceX, sourceY, sourceWidth, sourceHeight, targetWidth, targetHeight) {
                if (!context2d || !canvas || !window.jsQR) {
                    return null;
                }

                if (canvas.width !== targetWidth || canvas.height !== targetHeight) {
                    canvas.width = targetWidth;
                    canvas.height = targetHeight;
                }

                context2d.imageSmoothingEnabled = false;
                context2d.drawImage(video, sourceX, sourceY, sourceWidth, sourceHeight, 0, 0, targetWidth, targetHeight);

                const imageData = context2d.getImageData(0, 0, canvas.width, canvas.height);
                return window.jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: 'attemptBoth'
                });
            }

            function handleDecodeResult(result) {
                if (!result || !result.data) {
                    return false;
                }

                const value = normalizeCode(result.data);
                const extracted = extractAssetCode(value);
                if (extracted) {
                    submitCode(extracted);
                    return true;
                }

                showInvalidQr(value);
                return true;
            }

            function queueNextScan() {
                if (!scanLoopEnabled) {
                    return;
                }

                if (typeof video.requestVideoFrameCallback === 'function') {
                    scanTimer = video.requestVideoFrameCallback(function () {
                        void processScanFrame();
                    });
                    return;
                }

                scanTimer = window.setTimeout(function () {
                    void processScanFrame();
                }, FALLBACK_SCAN_INTERVAL_MS);
            }

            async function processScanFrame() {
                if (!scanLoopEnabled) {
                    return;
                }

                if (scanInFlight) {
                    queueNextScan();
                    return;
                }

                scanInFlight = true;

                try {
                    await detectFrame();
                } finally {
                    scanInFlight = false;
                }

                if (scanLoopEnabled) {
                    queueNextScan();
                }
            }

            async function detectFrame() {
                if (!scanLoopEnabled || !video || video.readyState < 2 || isDetecting) {
                    return;
                }

                try {
                    isDetecting = true;
                    if (detector) {
                        const barcodes = await detector.detect(video);
                        if (barcodes && barcodes.length > 0) {
                            const value = normalizeCode(barcodes[0].rawValue || '');
                            const extracted = extractAssetCode(value);
                            if (extracted) {
                                submitCode(extracted);
                            } else {
                                showInvalidQr(value);
                            }
                        }
                        return;
                    }

                    if (usingFallback && context2d && canvas && typeof window.jsQR === 'function') {
                        const sourceWidth = video.videoWidth || 0;
                        const sourceHeight = video.videoHeight || 0;
                        if (!sourceWidth || !sourceHeight) {
                            return;
                        }

                        const cropSize = Math.max(
                            FALLBACK_MIN_WIDTH,
                            Math.floor(Math.min(sourceWidth, sourceHeight) * FALLBACK_CROP_RATIO)
                        );
                        const cropX = Math.max(0, Math.floor((sourceWidth - cropSize) / 2));
                        const cropY = Math.max(0, Math.floor((sourceHeight - cropSize) / 2));
                        const cropTarget = Math.min(FALLBACK_MAX_WIDTH, Math.max(FALLBACK_MIN_WIDTH, cropSize));

                        let result = decodeCanvasRegion(cropX, cropY, cropSize, cropSize, cropTarget, cropTarget);
                        if (!result) {
                            const fullTargetWidth = Math.max(FALLBACK_MIN_WIDTH, Math.min(FALLBACK_MAX_WIDTH, sourceWidth));
                            const fullTargetHeight = Math.max(180, Math.min(360, Math.floor((sourceHeight / sourceWidth) * fullTargetWidth)));
                            result = decodeCanvasRegion(0, 0, sourceWidth, sourceHeight, fullTargetWidth, fullTargetHeight);
                        }

                        if (handleDecodeResult(result)) {
                            return;
                        }
                    }
                } catch (error) {
                    setStatus('Gagal membaca frame kamera. Coba ulangi start kamera.', true);
                } finally {
                    isDetecting = false;
                }
            }

            function startLoop() {
                stopLoop();
                scanLoopEnabled = true;
                scanInFlight = false;
                queueNextScan();
            }

            function stopLoop() {
                scanLoopEnabled = false;
                if (scanTimer) {
                    if (typeof video.cancelVideoFrameCallback === 'function') {
                        video.cancelVideoFrameCallback(scanTimer);
                    } else {
                        window.clearTimeout(scanTimer);
                    }
                    scanTimer = null;
                }
            }

            async function optimizeActiveCamera(activeStream) {
                const track = activeStream?.getVideoTracks?.()[0];
                if (!track) {
                    return;
                }

                try {
                    const capabilities = typeof track.getCapabilities === 'function' ? track.getCapabilities() : {};
                    const constraints = {};

                    if (capabilities.focusMode && Array.isArray(capabilities.focusMode) && capabilities.focusMode.includes('continuous')) {
                        constraints.focusMode = 'continuous';
                    }

                    if (capabilities.zoom && typeof capabilities.zoom.max === 'number') {
                        constraints.zoom = Math.min(Math.max(capabilities.zoom.min ?? 1, 1.5), capabilities.zoom.max);
                    }

                    if (Object.keys(constraints).length > 0 && typeof track.applyConstraints === 'function') {
                        await track.applyConstraints(constraints);
                    }
                } catch (error) {
                    // Kamera tetap bisa dipakai meski optimasi fokus tidak didukung browser.
                }
            }

            async function openCameraWithFallback() {
                const primaryConstraints = {
                    video: {
                        facingMode: { ideal: currentFacingMode },
                        width: { ideal: 640 },
                        height: { ideal: 480 },
                        frameRate: { ideal: 30, max: 30 },
                        focusMode: { ideal: 'continuous' }
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
                isDetecting = false;
                scanInFlight = false;
                lastInvalidCode = '';
                lastInvalidAt = 0;
                lastAcceptedCode = '';
                scanSuccessBadge.classList.add('hidden');
                scanFrameEl.classList.remove('border-emerald-400', 'scale-105');
                scanFrameEl.classList.add('border-cyan-300/70');
                let canScan = true;

                if ('BarcodeDetector' in window) {
                    try {
                        detector = new window.BarcodeDetector({ formats: ['qr_code'] });
                    } catch (error) {
                        detector = null;
                    }
                }

                if (!detector) {
                    if (typeof window.jsQR !== 'function') {
                        canScan = false;
                    } else {
                        usingFallback = true;
                        if (!canvas) {
                            canvas = document.createElement('canvas');
                            context2d = canvas.getContext('2d', { willReadFrequently: true });
                        }
                    }
                }

                stopCamera();
                setStatus('Meminta izin kamera...');

                try {
                    stream = await openCameraWithFallback();

                    video.srcObject = stream;
                    await video.play();
                    await optimizeActiveCamera(stream);
                    updateButtons(true);
                    if (canScan) {
                        setStatus(usingFallback
                            ? 'Kamera aktif (mode kompatibilitas). Arahkan QR sarana ke kotak scan.'
                            : 'Kamera aktif. Arahkan QR sarana ke kotak scan.');
                        startLoop();
                    } else {
                        setStatus('Kamera aktif, tetapi scanner QR tidak tersedia di browser ini. Gunakan input manual.', true);
                        stopLoop();
                    }
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

            function extractAssetCode(value) {
                const raw = String(value || '').toUpperCase();
                if (exactPattern.test(raw)) {
                    return raw;
                }

                const match = raw.match(/AST-[A-Z0-9]{3}-[A-Z0-9]{3}-L\d{2}-\d{4}-\d{4}/);
                return match ? match[0] : null;
            }

            btnStop.addEventListener('click', function () {
                stopCamera();
                setStatus('Kamera dihentikan.');
            });

            btnStart.addEventListener('click', function () {
                void startCamera();
            });

            btnSwitch.addEventListener('click', async function () {
                currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
                await startCamera();
            });

            window.addEventListener('action-confirm-closed', function () {
                invalidDialogOpen = false;
            });

            @if ($shouldOpenInvalidQrPopup)
                openInvalidQrPopup(@json($scanError));
            @endif

            window.addEventListener('beforeunload', stopCamera);
            document.addEventListener('visibilitychange', function () {
                if (document.hidden) {
                    stopCamera();
                }
            });

            updateButtons(false);
        })();
    </script>
</x-layouts.sbadmin>
