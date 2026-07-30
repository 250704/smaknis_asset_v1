<x-guest-layout>
    @php
        $scanRoute = route('scan-qr');
    @endphp

    @if ($kodeSarana === '')
        <!-- Header -->
        <div class="text-center mb-3">
            <h2 class="title-font text-lg font-bold tracking-tight text-slate-900">Pindai QR Code</h2>
            <p class="text-[10px] text-slate-500 mt-0.5">Arahkan kamera ke QR Code sarana</p>
        </div>

        <section id="qr-camera-panel" class="space-y-3">
            <!-- Camera Video Wrapper -->
            <div class="relative overflow-hidden border border-slate-200 rounded-2xl bg-slate-950">
                <video id="qr-video" class="h-[190px] w-full bg-slate-950 object-cover" autoplay muted playsinline></video>
                
                <!-- Scanning Frame Overlay -->
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div id="scan-frame" class="relative h-24 w-24 rounded-2xl border border-cyan-500/30 shadow-[0_0_0_9999px_rgba(255,255,255,.65)] transition-all duration-300">
                        <span class="absolute -left-1 -top-1 h-4 w-4 border-l-4 border-t-4 border-cyan-500 rounded-tl-md"></span>
                        <span class="absolute -right-1 -top-1 h-4 w-4 border-r-4 border-t-4 border-cyan-500 rounded-tr-md"></span>
                        <span class="absolute -bottom-1 -left-1 h-4 w-4 border-b-4 border-l-4 border-cyan-500 rounded-bl-md"></span>
                        <span class="absolute -bottom-1 -right-1 h-4 w-4 border-b-4 border-r-4 border-cyan-500 rounded-br-md"></span>
                        <div class="scan-line"></div>
                        <div id="scan-success-badge" class="absolute -top-9 left-1/2 hidden -translate-x-1/2 rounded-full bg-emerald-500 px-3 py-1 text-[10px] font-semibold text-white shadow-lg">
                            QR Berhasil
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controls & Status -->
            <div class="flex gap-2 justify-center">
                <button type="button" id="btn-start-camera" class="flex-1 py-1.5 px-3 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl text-[10.5px] transition-colors duration-200">Aktifkan Kamera</button>
                <button type="button" id="btn-switch-camera" class="hidden py-1.5 px-3 bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 font-semibold rounded-xl text-[10.5px] transition-colors duration-200">Ganti Kamera</button>
                <button type="button" id="btn-stop-camera" class="hidden py-1.5 px-3 bg-rose-600 hover:bg-rose-500 text-white font-semibold rounded-xl text-[10.5px] transition-colors duration-200">Matikan</button>
            </div>

            <div id="camera-status" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-[10px] text-slate-500 text-center">
                Kamera belum aktif. Klik tombol di atas untuk memindai.
            </div>
        </section>

    @elseif ($sarana)
        <!-- Header -->
        <div class="text-center mb-1">
            <h2 class="title-font text-base font-bold tracking-tight text-slate-900">Detail Sarana</h2>
        </div>

        <!-- Detail Card -->
        <section class="space-y-3.5">
            <div class="text-[11px] divide-y divide-slate-100">
                <div class="py-2 flex justify-between items-center gap-4">
                    <span class="font-bold text-slate-400 uppercase tracking-wider text-[8px]">Kode Sarana</span>
                    <span class="font-mono font-bold text-slate-800 select-all">{{ $sarana->kode_sarana }}</span>
                </div>
                <div class="py-2 flex justify-between items-center gap-4">
                    <span class="font-bold text-slate-400 uppercase tracking-wider text-[8px]">Nama Sarana</span>
                    <span class="font-bold text-slate-800">{{ $sarana->nama_sarana }}</span>
                </div>
                <div class="py-2 flex justify-between items-start gap-4 text-right">
                    <span class="font-bold text-slate-400 uppercase tracking-wider text-[8px] text-left">Kategori & Lokasi</span>
                    <span class="text-slate-700 font-semibold leading-tight">
                        <span class="text-slate-400 text-[8px] block font-normal">{{ $sarana->kategori?->nama_kategori }}</span>
                        Ruang {{ $sarana->ruangan?->nama_ruangan }}
                    </span>
                </div>
                <div class="py-2 flex justify-between items-center gap-4">
                    <span class="font-bold text-slate-400 uppercase tracking-wider text-[8px]">Kondisi & Status</span>
                    <div class="flex gap-1">
                        <span class="rounded-full px-2 py-0.5 text-[8.5px] font-extrabold {{ $sarana->kondisi_terkini === 'BAIK' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-amber-50 text-amber-600 border border-amber-200' }}">
                            {{ $sarana->kondisi_terkini }}
                        </span>
                        <span class="rounded-full px-2 py-0.5 text-[8.5px] font-extrabold {{ $sarana->status_sarana === 'AKTIF' ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'bg-slate-50 text-slate-600 border border-slate-200' }}">
                            {{ $sarana->status_sarana }}
                        </span>
                    </div>
                </div>
            </div>

            @php
                $publicActions = [
                    'lapor-kerusakan' => ['label' => 'Lapor Kerusakan', 'icon' => 'fa-triangle-exclamation', 'style' => 'bg-rose-500 text-white hover:bg-rose-600'],
                    'usulan-mutasi' => ['label' => 'Usulan Mutasi', 'icon' => 'fa-arrows-left-right', 'style' => 'bg-blue-600 text-white hover:bg-blue-700'],
                    'histori-sarana' => ['label' => 'Histori Sarana', 'icon' => 'fa-clock-rotate-left', 'style' => 'bg-white text-blue-700 ring-1 ring-slate-200 hover:bg-slate-50'],
                ];
                // Histori tersedia bagi pengunjung sebelum login; pengguna masuk menggunakan dua aksi operasional.
                if (auth()->check()) {
                    unset($publicActions['histori-sarana']);
                }
            @endphp

            <div class="border-t border-slate-100 pt-3">
                <h3 class="mb-2.5 text-center text-[8.5px] font-bold uppercase tracking-wider text-slate-400">Aksi Sarana</h3>
                <div class="flex flex-wrap justify-center gap-2">
                    @foreach ($publicActions as $action => $item)
                        <a href="{{ route('scan-qr.action', ['sarana' => $sarana, 'action' => $action]) }}"
                           class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg px-3 text-[10px] font-bold transition duration-200 hover:-translate-y-0.5 hover:shadow-sm {{ $item['style'] }}">
                            <i class="fas {{ $item['icon'] }} text-[9px]"></i>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="hidden">
            <!-- Role Action Buttons (Requires Auth) -->
            <div class="border-t border-slate-100 pt-3">
                <h3 class="text-[8.5px] font-bold uppercase tracking-wider text-slate-400 text-center mb-2">Pilih Aksi (Perlu Login)</h3>
                
                <div class="grid grid-cols-2 gap-1.5">
                    <!-- Guru Actions -->
                    <a href="{{ route('guru.scan.action', ['sarana' => $sarana, 'action' => 'lapor-kerusakan']) }}" class="flex items-center gap-1.5 py-1.5 px-2 bg-slate-50 hover:bg-slate-100/80 border border-slate-200 text-slate-700 font-semibold rounded-xl text-[10px] transition-all duration-200 group">
                        <svg class="w-3.5 h-3.5 text-rose-500 transition-transform group-hover:scale-105" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Lapor Rusak</span>
                    </a>
                    <a href="{{ route('guru.scan.action', ['sarana' => $sarana, 'action' => 'usulan-mutasi']) }}" class="flex items-center gap-1.5 py-1.5 px-2 bg-slate-50 hover:bg-slate-100/80 border border-slate-200 text-slate-700 font-semibold rounded-xl text-[10px] transition-all duration-200 group">
                        <svg class="w-3.5 h-3.5 text-indigo-500 transition-transform group-hover:scale-105" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        <span>Usul Mutasi</span>
                    </a>

                    <!-- Kepala Sarana Actions -->
                    <a href="{{ route('kepala_sarana.scan.action', ['sarana' => $sarana, 'action' => 'detail-sarana']) }}" class="flex items-center gap-1.5 py-1.5 px-2 bg-slate-50 hover:bg-slate-100/80 border border-slate-200 text-slate-700 font-semibold rounded-xl text-[10px] transition-all duration-200 group">
                        <svg class="w-3.5 h-3.5 text-blue-500 transition-transform group-hover:scale-105" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Detail Lengkap</span>
                    </a>
                    <a href="{{ route('kepala_sarana.scan.action', ['sarana' => $sarana, 'action' => 'histori-sarana']) }}" class="flex items-center gap-1.5 py-1.5 px-2 bg-slate-50 hover:bg-slate-100/80 border border-slate-200 text-slate-700 font-semibold rounded-xl text-[10px] transition-all duration-200 group">
                        <svg class="w-3.5 h-3.5 text-emerald-500 transition-transform group-hover:scale-105" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Log Histori</span>
                    </a>

                    <!-- Other Roles Quick Links -->
                    <a href="{{ route('bendahara.scan.action', ['sarana' => $sarana, 'action' => 'review-pengajuan']) }}" class="flex items-center gap-1.5 py-1.5 px-2 bg-slate-50 hover:bg-slate-100/80 border border-slate-200 text-slate-700 font-semibold rounded-xl text-[10px] transition-all duration-200 group">
                        <svg class="w-3.5 h-3.5 text-amber-500 transition-transform group-hover:scale-105" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <span>Review Biaya</span>
                    </a>
                    <a href="{{ route('kepala_sekolah.scan.action', ['sarana' => $sarana, 'action' => 'approval-final']) }}" class="flex items-center gap-1.5 py-1.5 px-2 bg-slate-50 hover:bg-slate-100/80 border border-slate-200 text-slate-700 font-semibold rounded-xl text-[10px] transition-all duration-200 group">
                        <svg class="w-3.5 h-3.5 text-cyan-500 transition-transform group-hover:scale-105" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5 .618V12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Approval Final</span>
                    </a>
                </div>
            </div>

            <!-- Back to Scanner -->
            <div class="pt-1">
                <a href="{{ $scanRoute }}" class="flex items-center justify-center gap-1 w-full py-1.5 px-3 bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-600 font-semibold rounded-xl text-[10px] transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3 text-slate-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <span>Pindai Sarana Lain</span>
                </a>
            </div>
            </div>
        </section>

    @else
        <!-- Scan Error -->
        <div class="text-center mb-6">
            <h2 class="title-font text-2xl font-bold tracking-tight text-slate-900">Pemindaian Gagal</h2>
            <p class="text-xs text-slate-500 mt-1">Kode QR sarana tidak ditemukan</p>
        </div>

        <section class="space-y-4">
            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-700 text-xs text-center leading-relaxed">
                Kode QR yang dipindai (<span class="font-mono text-slate-950 font-bold select-all">{{ $kodeSarana }}</span>) tidak valid atau tidak terdaftar di basis data sarpras kami.
            </div>

            <a href="{{ $scanRoute }}" class="flex items-center justify-center gap-2 w-full py-3.5 px-6 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold rounded-2xl transition-all duration-200">
                <span>Pindai Ulang</span>
            </a>
        </section>
    @endif

    <form id="qr-scan-form" method="GET" action="{{ $scanRoute }}" class="hidden">
        <input id="kode_sarana" name="kode_sarana" type="text" value="{{ $kodeSarana }}">
    </form>

    <style>
        #scan-frame {
            width: 8rem;
            height: 8rem;
        }
        .scan-line {
            position: absolute;
            left: 0.25rem;
            right: 0.25rem;
            height: 3px;
            background: linear-gradient(90deg, transparent 10%, #22d3ee 50%, transparent 90%);
            box-shadow: 0 0 12px #22d3ee, 0 0 4px #22d3ee;
            animation: scanLine 2.5s ease-in-out infinite;
        }
        @keyframes scanLine {
            0% { top: 15%; }
            50% { top: 85%; }
            100% { top: 15%; }
        }
    </style>

    <!-- Scanner script -->
    @if ($kodeSarana === '')
        <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
        <script>
            (function () {
                const form = document.getElementById('qr-scan-form');
                const input = document.getElementById('kode_sarana');
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
                let scanInFlight = false;

                const exactPattern = /^SRN-[A-Z0-9]{3}-[A-Z0-9]{3}-L\d{2}-\d{4}-\d{4}$/;
                const FALLBACK_SCAN_INTERVAL_MS = 60;
                const FALLBACK_MAX_WIDTH = 480;
                const FALLBACK_MIN_WIDTH = 240;
                const FALLBACK_CROP_RATIO = 0.72;

                function setStatus(message, isError = false) {
                    statusBox.textContent = message;
                    statusBox.classList.toggle('border-rose-500/20', isError);
                    statusBox.classList.toggle('bg-rose-500/10', isError);
                    statusBox.classList.toggle('text-rose-600', isError);
                }

                function updateButtons(active) {
                    btnStart.classList.toggle('hidden', active);
                    btnStop.classList.toggle('hidden', !active);
                    btnSwitch.classList.toggle('hidden', !active);
                }

                function resolveCameraError(error) {
                    if (!error) return 'Kamera gagal diaktifkan.';
                    const name = String(error.name || '');
                    if (name === 'NotAllowedError' || name === 'SecurityError') {
                        return 'Izin kamera ditolak. Aktifkan izin kamera di peramban Anda.';
                    }
                    if (name === 'NotFoundError' || name === 'DevicesNotFoundError') {
                        return 'Kamera tidak ditemukan.';
                    }
                    if (name === 'NotReadableError' || name === 'TrackStartError') {
                        return 'Kamera sedang digunakan aplikasi lain.';
                    }
                    return 'Gagal membuka kamera: ' + name;
                }

                function normalizeCode(rawValue) {
                    const raw = String(rawValue || '').trim();
                    const upper = raw.toUpperCase();
                    if (exactPattern.test(upper)) return upper;
                    const matches = upper.match(/SRN-[A-Z0-9]{3}-[A-Z0-9]{3}-L\d{2}-\d{4}-\d{4}/g);
                    return (matches && matches.length > 0) ? matches[0] : raw.replace(/\s+/g, '');
                }

                function submitCode(code) {
                    if (!code || isSubmitting) return;
                    isSubmitting = true;
                    input.value = code;
                    setStatus('QR terdeteksi: ' + code);
                    scanFrameEl.classList.add('border-emerald-500', 'scale-105');
                    scanSuccessBadge.classList.remove('hidden');
                    stopCamera();
                    window.setTimeout(function () {
                        form.submit();
                    }, 800);
                }

                function decodeCanvasRegion(sourceX, sourceY, sourceWidth, sourceHeight, targetWidth, targetHeight) {
                    if (!context2d || !canvas || !window.jsQR) return null;
                    if (canvas.width !== targetWidth || canvas.height !== targetHeight) {
                        canvas.width = targetWidth;
                        canvas.height = targetHeight;
                    }
                    context2d.imageSmoothingEnabled = false;
                    context2d.drawImage(video, sourceX, sourceY, sourceWidth, sourceHeight, 0, 0, targetWidth, targetHeight);
                    const imageData = context2d.getImageData(0, 0, canvas.width, canvas.height);
                    return window.jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: 'attemptBoth' });
                }

                function processScanFrame() {
                    if (!scanLoopEnabled) return;
                    if (scanInFlight) {
                        queueNextScan();
                        return;
                    }
                    scanInFlight = true;
                    try {
                        detectFrame();
                    } finally {
                        scanInFlight = false;
                    }
                    if (scanLoopEnabled) queueNextScan();
                }

                function queueNextScan() {
                    if (!scanLoopEnabled) return;
                    if (typeof video.requestVideoFrameCallback === 'function') {
                        scanTimer = video.requestVideoFrameCallback(processScanFrame);
                    } else {
                        scanTimer = window.setTimeout(processScanFrame, FALLBACK_SCAN_INTERVAL_MS);
                    }
                }

                function detectFrame() {
                    if (!scanLoopEnabled || !video || video.readyState < 2 || isDetecting) return;
                    try {
                        isDetecting = true;
                        if (usingFallback && context2d && canvas && typeof window.jsQR === 'function') {
                            const sourceWidth = video.videoWidth || 0;
                            const sourceHeight = video.videoHeight || 0;
                            if (!sourceWidth || !sourceHeight) return;

                            const cropSize = Math.max(FALLBACK_MIN_WIDTH, Math.floor(Math.min(sourceWidth, sourceHeight) * FALLBACK_CROP_RATIO));
                            const cropX = Math.max(0, Math.floor((sourceWidth - cropSize) / 2));
                            const cropY = Math.max(0, Math.floor((sourceHeight - cropSize) / 2));
                            const cropTarget = Math.min(FALLBACK_MAX_WIDTH, Math.max(FALLBACK_MIN_WIDTH, cropSize));

                            let result = decodeCanvasRegion(cropX, cropY, cropSize, cropSize, cropTarget, cropTarget);
                            if (!result) {
                                const fullTargetWidth = Math.max(FALLBACK_MIN_WIDTH, Math.min(FALLBACK_MAX_WIDTH, sourceWidth));
                                const fullTargetHeight = Math.max(180, Math.min(360, Math.floor((sourceHeight / sourceWidth) * fullTargetWidth)));
                                result = decodeCanvasRegion(0, 0, sourceWidth, sourceHeight, fullTargetWidth, fullTargetHeight);
                            }

                            if (result && result.data) {
                                const val = normalizeCode(result.data);
                                submitCode(val);
                            }
                        }
                    } catch (error) {
                        // ignore frame error
                    } finally {
                        isDetecting = false;
                    }
                }

                function startLoop() {
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

                async function startCamera() {
                    if (!('mediaDevices' in navigator) || !navigator.mediaDevices.getUserMedia) {
                        setStatus('Browser tidak mendukung akses kamera.', true);
                        return;
                    }
                    detector = null;
                    usingFallback = true;
                    isSubmitting = false;
                    isDetecting = false;
                    scanInFlight = false;
                    if (!canvas) {
                        canvas = document.createElement('canvas');
                        context2d = canvas.getContext('2d', { willReadFrequently: true });
                    }
                    stopCamera();
                    setStatus('Mengaktifkan kamera...');
                    try {
                        stream = await navigator.mediaDevices.getUserMedia({
                            video: { facingMode: { ideal: currentFacingMode } },
                            audio: false
                        });
                        video.srcObject = stream;
                        await video.play();
                        updateButtons(true);
                        setStatus('Kamera aktif. Arahkan ke QR Code sarana.');
                        startLoop();
                    } catch (error) {
                        setStatus(resolveCameraError(error), true);
                        updateButtons(false);
                    }
                }

                function stopCamera() {
                    stopLoop();
                    if (stream) {
                        stream.getTracks().forEach(track => track.stop());
                        stream = null;
                    }
                    video.srcObject = null;
                    updateButtons(false);
                }

                btnStart.addEventListener('click', startCamera);
                btnStop.addEventListener('click', stopCamera);
                btnSwitch.addEventListener('click', function () {
                    currentFacingMode = (currentFacingMode === 'user') ? 'environment' : 'user';
                    void startCamera();
                });

                // Auto-start camera on page load
                window.setTimeout(startCamera, 300);
            })();
        </script>
    @endif
</x-guest-layout>
