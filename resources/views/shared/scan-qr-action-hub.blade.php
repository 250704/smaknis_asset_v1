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

    @if (!$sarana)
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="page-title">Scan QR Action Hub</h1>
        </div>
        <span class="inline-flex px-3 py-1 text-xs font-semibold tracking-wide uppercase border rounded-full border-cyan-400/30 bg-cyan-400/10 text-cyan-700 dark:text-cyan-200">
            {{ $roleLabel }}
        </span>
    </div>
    @endif

    @if (session('success'))
        <div class="px-4 py-3 mb-4 text-sm border rounded-xl border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @php
        $invalidQrMessage = 'QR code tidak valid. Pastikan QR milik sarana yang terdaftar.';
        $shouldOpenInvalidQrPopup = $scanError === $invalidQrMessage;
    @endphp

    @if ($scanError && !$shouldOpenInvalidQrPopup)
        <div class="flex items-center justify-between px-4 py-3 mb-4 text-sm border rounded-xl border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-400/30 dark:bg-rose-500/10 dark:text-rose-200 shadow-sm">
            <span class="flex items-center gap-1.5">
                <i class="fas fa-exclamation-triangle"></i>
                {{ $scanError }}
            </span>
            <a href="{{ $scanRoute }}" class="px-2.5 py-1 text-[10px] font-bold bg-white text-rose-700 border border-rose-200 hover:bg-rose-100 rounded-lg transition-colors">
                Reset
            </a>
        </div>
    @elseif (!$sarana && $kodeSarana !== '' && $isExactFormat)
        <div class="flex items-center justify-between px-4 py-2.5 mb-4 text-xs font-bold border rounded-xl border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-400/30 dark:bg-emerald-500/10 dark:text-emerald-200 shadow-sm">
            <span class="flex items-center gap-1.5">
                <i class="fas fa-check-circle"></i>
                Format QR valid.
            </span>
            <a href="{{ $scanRoute }}" class="px-2.5 py-1 text-[10px] font-bold bg-white text-emerald-700 border border-emerald-200 hover:bg-emerald-100 dark:bg-slate-800 dark:text-emerald-300 dark:border-emerald-500/20 dark:hover:bg-slate-700 rounded-lg transition-colors">
                Cari / Scan Lainnya
            </a>
        </div>
    @endif

    <form id="qr-scan-form" method="GET" action="{{ $scanRoute }}" class="hidden">
        <input id="kode_sarana" name="kode_sarana" type="text" value="{{ $kodeSarana }}">
    </form>

    @if ($kodeSarana === '')
        <section class="panel" id="qr-camera-panel">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Scan Langsung Kamera</h2>
            <div class="flex flex-wrap gap-2">
                <button type="button" id="btn-start-camera" class="btn-primary">Start Kamera</button>
                <button type="button" id="btn-switch-camera" class="hidden btn-secondary">Ganti Kamera</button>
                <button type="button" id="btn-stop-camera" class="hidden btn-secondary">Stop Kamera</button>
            </div>
        </div>

        <div class="mt-3 overflow-hidden border rounded-xl border-slate-200 bg-slate-50 dark:border-white/10 dark:bg-slate-900/50">
            <div class="relative">
                <video id="qr-video" class="h-[280px] w-full bg-slate-950 object-cover sm:h-[360px]" autoplay muted playsinline></video>
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
<div id="scan-frame" class="relative h-36 w-36 rounded-2xl border border-cyan-400/30 shadow-[0_0_0_9999px_rgba(2,6,23,.55)] transition-all duration-300 sm:h-40 sm:w-40">
                        <span class="absolute -left-1 -top-1 h-6 w-6 border-l-4 border-t-4 border-cyan-400 rounded-tl-md"></span>
                        <span class="absolute -right-1 -top-1 h-6 w-6 border-r-4 border-t-4 border-cyan-400 rounded-tr-md"></span>
                        <span class="absolute -bottom-1 -left-1 h-6 w-6 border-b-4 border-l-4 border-cyan-400 rounded-bl-md"></span>
                        <span class="absolute -bottom-1 -right-1 h-6 w-6 border-b-4 border-r-4 border-cyan-400 rounded-br-md"></span>
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
            left: 0.5rem;
            right: 0.5rem;
            height: 3px;
            background: linear-gradient(90deg, transparent 10%, #22d3ee 50%, transparent 90%);
            box-shadow: 0 0 12px #22d3ee, 0 0 4px #22d3ee;
            animation: scanLine 2.5s ease-in-out infinite;
        }

        @keyframes scanLine {
            0% {
                top: 15%;
            }
            50% {
                top: 85%;
            }
            100% {
                top: 15%;
            }
        }
    </style>

    @if ($kodeSarana !== '' && !$sarana)
        <section class="mt-5 panel">
            <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Hasil Pencarian</h2>
            @if ($searchResults->isEmpty() && !$scanError)
                <p class="mt-3 text-sm text-rose-600 dark:text-rose-300">Sarana dengan kode/kata kunci tersebut tidak ditemukan.</p>
            @elseif ($searchResults->isNotEmpty())
                <div class="mt-3 overflow-x-auto border rounded-xl border-slate-200 dark:border-white/10">
                    <table class="min-w-full text-sm divide-y divide-slate-200 dark:divide-white/10">
                        <thead class="bg-slate-50 dark:bg-white/[0.04]">
                            <tr>
                                <th class="px-4 py-3 font-semibold text-left text-slate-600 dark:text-slate-300">Kode</th>
                                <th class="px-4 py-3 font-semibold text-left text-slate-600 dark:text-slate-300">Nama</th>
                                <th class="px-4 py-3 font-semibold text-left text-slate-600 dark:text-slate-300">Lokasi</th>
                                <th class="px-4 py-3 font-semibold text-left text-slate-600 dark:text-slate-300">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach ($searchResults as $item)
                                <tr>
                                    <td class="px-4 py-3 font-mono text-xs text-slate-700 dark:text-slate-200">{{ $item->kode_sarana }}</td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $item->nama_sarana }}</td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $item->ruangan?->nama_ruangan }} - {{ $item->ruangan?->gedung?->nama_gedung }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ $scanRoute . '?kode_sarana=' . urlencode($item->kode_sarana) }}" class="btn-secondary">Pilih</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endif

    @if ($sarana)

        @php
            $scanActions = [
                'lapor-kerusakan' => [
                    'label' => 'Lapor Kerusakan',
                    'description' => 'Kirim laporan kondisi sarana.',
                    'icon' => 'fa-triangle-exclamation',
                    'theme' => 'rose',
                ],
                'usulan-mutasi' => [
                    'label' => 'Usulan Mutasi',
                    'description' => 'Ajukan perpindahan lokasi sarana.',
                    'icon' => 'fa-arrows-left-right',
                    'theme' => 'indigo',
                ],
            ];
        @endphp

        <section class="qr-result-card mt-5 flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">
            <header class="flex shrink-0 items-center justify-between gap-4 border-b border-slate-100 px-5 py-4 dark:border-white/10">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                        @if ($sarana->foto_sarana)
                            <img src="{{ asset('storage/' . $sarana->foto_sarana) }}" alt="{{ $sarana->nama_sarana }}" class="h-full w-full object-cover">
                        @else
                            <i class="fas fa-box"></i>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-base font-bold text-slate-800 dark:text-white">{{ $sarana->nama_sarana }}</p>
                        <p class="mt-0.5 font-mono text-[11px] text-slate-400">{{ $sarana->kode_sarana }}</p>
                    </div>
                </div>
                <div class="flex shrink-0 flex-col items-end gap-1">
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $sarana->kondisi_terkini === 'BAIK' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' }}">{{ $sarana->kondisi_terkini }}</span>
                    <span class="rounded-full bg-blue-100 px-2.5 py-1 text-[10px] font-bold text-blue-700 dark:bg-blue-500/15 dark:text-blue-300">{{ $sarana->status_sarana }}</span>
                </div>
            </header>

            <div class="flex flex-col gap-5 p-5">
                <div class="space-y-5">
                    <div class="grid grid-cols-2 gap-x-5 gap-y-4 sm:grid-cols-3">
                        @foreach ([
                            'Kategori' => $sarana->kategori?->nama_kategori ?? '—',
                            'Ruangan' => $sarana->ruangan?->nama_ruangan ?? '—',
                            'Gedung' => $sarana->ruangan?->gedung?->nama_gedung ?? '—',
                            'Lantai' => $sarana->ruangan?->lantai ? 'Lantai ' . $sarana->ruangan->lantai : '—',
                            'Tahun Perolehan' => $sarana->tahun_perolehan ?? '—',
                            'Harga Perolehan' => $sarana->harga_perolehan ? 'Rp ' . number_format((float) $sarana->harga_perolehan, 0, ',', '.') : '—',
                        ] as $label => $value)
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                                <p class="mt-1 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $value }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="rounded-xl bg-slate-50 p-4 dark:bg-white/[0.04]">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Kondisi Terakhir</p>
                        @if ($sarana->riwayatKondisiSarana->isNotEmpty())
                            @php
                                $riwayat = $sarana->riwayatKondisiSarana->first();
                            @endphp
                            <div class="mt-2 flex items-center justify-between gap-3">
                                <div class="min-w-0"><p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $riwayat->tingkat_kerusakan }}</p><p class="truncate text-xs text-slate-500">{{ $riwayat->deskripsi ?: $riwayat->status }}</p></div>
                                <span class="shrink-0 text-[11px] text-slate-400">{{ $riwayat->created_at?->format('d M Y') }}</span>
                            </div>
                        @else
                            <p class="mt-2 text-sm text-slate-500">Belum ada riwayat kondisi tercatat.</p>
                        @endif
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4 dark:border-white/10">
                    <p class="mb-2.5 text-center text-[10px] font-bold uppercase tracking-widest text-slate-400">Aksi Sarana</p>
                    <div class="flex flex-wrap justify-center gap-2.5">
                    @foreach ($scanActions as $key => $action)
                        <a href="{{ route($role . '.scan.action', ['sarana' => $sarana, 'action' => $key]) }}"
                           class="inline-flex h-10 min-w-[8.75rem] items-center justify-center gap-2 rounded-lg px-4 text-sm font-semibold transition duration-200 hover:-translate-y-0.5 hover:shadow-md {{ $action['theme'] === 'rose' ? 'bg-rose-500 text-white shadow-rose-500/20 hover:bg-rose-600' : ($action['theme'] === 'indigo' ? 'bg-blue-600 text-white shadow-blue-600/20 hover:bg-blue-700' : 'bg-white text-blue-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-blue-300 dark:ring-white/10 dark:hover:bg-slate-700') }}">
                            <i class="fas {{ $action['icon'] }} text-xs"></i>
                            <span>{{ $action['label'] }}</span>
                        </a>
                    @endforeach
                    </div>
                </div>
            </div>
        </section>

        <div class="hidden">

        {{-- Sarana Detail Card --}}
        <div class="mt-5 rounded-2xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/[0.02] overflow-hidden shadow-sm">

            {{-- Foto Sarana --}}
            @if ($sarana->foto_sarana)
                <div class="w-full h-48 overflow-hidden">
                    <img src="{{ asset('storage/' . $sarana->foto_sarana) }}"
                         alt="{{ $sarana->nama_sarana }}"
                         class="w-full h-full object-cover">
                </div>
            @endif

            {{-- Header strip --}}
            <div class="flex items-start justify-between gap-3 px-5 pt-5 pb-4 border-b border-slate-100 dark:border-white/5">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-400">
                        <i class="fas fa-box text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-slate-800 dark:text-slate-100 leading-snug">{{ $sarana->nama_sarana }}</p>
                        <p class="mt-0.5 text-[11px] font-mono text-slate-400 dark:text-slate-500 select-all">{{ $sarana->kode_sarana }}</p>
                    </div>
                </div>
                <div class="shrink-0 flex flex-col items-end gap-1.5 pt-0.5">
                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold
                        {{ $sarana->kondisi_terkini === 'BAIK'
                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
                            : 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' }}">
                        {{ $sarana->kondisi_terkini }}
                    </span>
                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold
                        {{ $sarana->status_sarana === 'AKTIF'
                            ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300'
                            : 'bg-slate-200 text-slate-600 dark:bg-slate-700/50 dark:text-slate-300' }}">
                        {{ $sarana->status_sarana }}
                    </span>
                </div>
            </div>

            {{-- Grid Informasi --}}
            <div class="px-5 py-4">
                <div class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
                    <div>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider font-semibold mb-0.5">Kategori</p>
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $sarana->kategori?->nama_kategori ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider font-semibold mb-0.5">Ruangan</p>
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">
                            {{ $sarana->ruangan?->nama_ruangan ?? '—' }}
                            @if($sarana->ruangan?->kode_ruangan)
                                <span class="font-normal text-slate-400 dark:text-slate-500">[{{ $sarana->ruangan->kode_ruangan }}]</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider font-semibold mb-0.5">Gedung</p>
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $sarana->ruangan?->gedung?->nama_gedung ?? '—' }}</p>
                    </div>
                    @if($sarana->ruangan?->lantai)
                        <div>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider font-semibold mb-0.5">Lantai</p>
                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">Lantai {{ $sarana->ruangan->lantai }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider font-semibold mb-0.5">Tahun Perolehan</p>
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $sarana->tahun_perolehan ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider font-semibold mb-0.5">Harga Perolehan</p>
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">
                            @if($sarana->harga_perolehan)
                                Rp {{ number_format((float)$sarana->harga_perolehan, 0, ',', '.') }}
                            @else
                                —
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- Riwayat Kondisi Singkat --}}
            @if($sarana->riwayatKondisiSarana->isNotEmpty())
                <div class="px-5 pb-5 border-t border-slate-100 dark:border-white/5 pt-4">
                    <p class="text-[10px] font-bold tracking-widest uppercase text-slate-400 dark:text-slate-500 mb-3">Riwayat Kondisi Terakhir</p>
                    <div class="space-y-3">
                        @foreach($sarana->riwayatKondisiSarana->take(3) as $riwayat)
                            <div class="flex items-start gap-3">
                                <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-slate-300 dark:bg-slate-600"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $riwayat->tingkat_kerusakan }}</span>
                                        <span class="text-[10px] text-slate-400 shrink-0">{{ $riwayat->created_at?->format('d M Y') }}</span>
                                    </div>
                                    @if($riwayat->deskripsi)
                                        <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ $riwayat->deskripsi }}</p>
                                    @endif
                                    <span class="mt-1 inline-block text-[9px] font-bold tracking-wide uppercase text-slate-400 dark:text-slate-500">{{ $riwayat->status }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Aksi --}}
        @php
            $coreActions = [
                'lapor-kerusakan' => [
                    'label'      => 'Laporkan Kerusakan',
                    'desc'       => 'Kirimkan laporan kerusakan sarana ini ke tim sarpras untuk ditindaklanjuti.',
                    'icon'       => 'fas fa-triangle-exclamation',
                    'pill'       => 'Pelaporan',
                    'pill_color' => 'bg-rose-100 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400',
                    'icon_bg'    => 'bg-rose-50 text-rose-500 dark:bg-rose-500/10 dark:text-rose-400',
                    'border'     => 'border-rose-100 dark:border-rose-500/20 hover:border-rose-300 dark:hover:border-rose-500/40',
                    'arrow'      => 'text-rose-400',
                ],
                'usulan-mutasi' => [
                    'label'      => 'Usulan Mutasi',
                    'desc'       => 'Ajukan pemindahan sarana ini ke ruangan atau lokasi lain.',
                    'icon'       => 'fas fa-arrows-left-right',
                    'pill'       => 'Mutasi',
                    'pill_color' => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400',
                    'icon_bg'    => 'bg-indigo-50 text-indigo-500 dark:bg-indigo-500/10 dark:text-indigo-400',
                    'border'     => 'border-indigo-100 dark:border-indigo-500/20 hover:border-indigo-300 dark:hover:border-indigo-500/40',
                    'arrow'      => 'text-indigo-400',
                ],
                'histori-sarana' => [
                    'label'      => 'Histori Sarana',
                    'desc'       => 'Lihat log riwayat lengkap pengajuan, perawatan, dan mutasi sarana ini.',
                    'icon'       => 'fas fa-clock-rotate-left',
                    'pill'       => 'Histori',
                    'pill_color' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
                    'icon_bg'    => 'bg-emerald-50 text-emerald-500 dark:bg-emerald-500/10 dark:text-emerald-400',
                    'border'     => 'border-emerald-100 dark:border-emerald-500/20 hover:border-emerald-300 dark:hover:border-emerald-500/40',
                    'arrow'      => 'text-emerald-400',
                ],
                'lihat-histori' => [
                    'label'      => 'Histori Sarana',
                    'desc'       => 'Lihat log riwayat lengkap pengajuan, perawatan, dan mutasi sarana ini.',
                    'icon'       => 'fas fa-clock-rotate-left',
                    'pill'       => 'Histori',
                    'pill_color' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
                    'icon_bg'    => 'bg-emerald-50 text-emerald-500 dark:bg-emerald-500/10 dark:text-emerald-400',
                    'border'     => 'border-emerald-100 dark:border-emerald-500/20 hover:border-emerald-300 dark:hover:border-emerald-500/40',
                    'arrow'      => 'text-emerald-400',
                ],
            ];
            // Exclude 'detail-sarana' — detail sudah tampil langsung di halaman ini
            $filteredActions = array_filter($actions, fn($key) => $key !== 'detail-sarana', ARRAY_FILTER_USE_KEY);
        @endphp

        <div class="mt-5">
            <p class="text-[10px] font-bold tracking-widest uppercase text-slate-400 dark:text-slate-500 mb-3">Aksi</p>
            <div class="flex flex-col gap-2.5">
                @foreach ($coreActions as $actionKey => $meta)
                    @if (isset($filteredActions[$actionKey]))
                        <a href="{{ route($role . '.scan.action', ['sarana' => $sarana, 'action' => $actionKey]) }}"
                           class="group flex items-center gap-4 px-4 py-3.5 rounded-xl border bg-white dark:bg-white/[0.02] transition-all duration-200 hover:shadow-sm hover:-translate-y-px {{ $meta['border'] }}">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $meta['icon_bg'] }} transition-transform duration-150 group-hover:scale-105">
                                <i class="{{ $meta['icon'] }} text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $meta['label'] }}</span>
                                    <span class="hidden sm:inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $meta['pill_color'] }}">{{ $meta['pill'] }}</span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $meta['desc'] }}</p>
                            </div>
                            <i class="fas fa-chevron-right text-[10px] shrink-0 {{ $meta['arrow'] }} transition-transform duration-150 group-hover:translate-x-0.5"></i>
                        </a>
                    @endif
                @endforeach

                {{-- Aksi tambahan per role (review-pengajuan, approval-final, dst) --}}
                @php
                    $extraActions = array_diff_key($filteredActions, $coreActions);
                @endphp
                @if (!empty($extraActions))
                    <div class="flex flex-wrap gap-2 pt-1">
                        @foreach ($extraActions as $actionKey => $label)
                            <a href="{{ route($role . '.scan.action', ['sarana' => $sarana, 'action' => $actionKey]) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-200 dark:border-white/10 text-slate-600 dark:text-slate-400 bg-white dark:bg-white/[0.03] hover:bg-slate-50 dark:hover:bg-white/[0.06] transition-colors">
                                <i class="fas fa-arrow-right text-[10px]"></i>
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Scan Again Link --}}
        <div class="mt-5 text-center">
            <a href="{{ $scanRoute }}" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                <i class="fas fa-rotate-right text-[10px]"></i>
                Scan sarana lain
            </a>
        </div>

        </div>

    @endif

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
            let invalidDialogOpen = false;
            let scanInFlight = false;

            const exactPattern = /^SRN-[A-Z0-9]{3}-[A-Z0-9]{3}-L\d{2}-\d{4}-\d{4}$/;
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

                const matches = upper.match(/SRN-[A-Z0-9]{3}-[A-Z0-9]{3}-L\d{2}-\d{4}-\d{4}/g);
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
                const extracted = extractSaranaCode(value);
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
                            const extracted = extractSaranaCode(value);
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

            function extractSaranaCode(value) {
                const raw = String(value || '').toUpperCase();
                if (exactPattern.test(raw)) {
                    return raw;
                }

                const match = raw.match(/SRN-[A-Z0-9]{3}-[A-Z0-9]{3}-L\d{2}-\d{4}-\d{4}/);
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
