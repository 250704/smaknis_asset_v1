<x-layouts.sbadmin>
    @php
        $isRealisasiPage = (bool) ($isRealisasiPage ?? false);
        $backRoute = $backRoute ?? null;
        $statusLabels = [
            'DIAJUKAN' => 'Menunggu Approval Kepala Sarana',
            'DISETUJUI_KASARANA' => 'Menunggu Approval Bendahara',
            'DISETUJUI_BENDAHARA' => 'Menunggu Approval Kepala Sekolah',
            'DISETUJUI_KEPSEK' => 'Disetujui Final',
            'DIPROSES' => 'Realisasi Diproses',
            'MENUNGGU_VERIFIKASI_TEKNIS' => 'Menunggu Verifikasi Teknis',
            'MENUNGGU_VERIFIKASI_KEUANGAN' => 'Menunggu Verifikasi Keuangan',
            'DITOLAK' => 'Ditolak',
            'SELESAI' => 'Selesai',
        ];
        $approvalRoleLabels = [
            'KASARANA' => 'Kepala Sarana',
            'BENDAHARA' => 'Bendahara',
            'KEPSEK' => 'Kepala Sekolah',
            'KASARANA_VERIFIKASI' => 'Verifikasi Teknis',
            'BENDAHARA_VERIFIKASI' => 'Verifikasi Keuangan',
        ];
        $approvalStatusLabels = [
            'DISETUJUI' => 'Disetujui',
            'DITOLAK' => 'Ditolak',
        ];
    @endphp

    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="page-title">{{ $isRealisasiPage ? 'Form Realisasi' : 'Detail Pengajuan' }}</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ $isRealisasiPage ? 'Lengkapi data realisasi pengajuan sarana.' : 'Informasi lengkap pengajuan sarana.' }}
            </p>
        </div>
        <a href="{{ $backRoute ?: url()->previous() }}" class="btn-secondary">Kembali</a>
    </div>

    @if ($isRealisasiPage)
        @php
            $user = auth()->user();
            $canRealisasi = $user?->hasRole('admin')
                && in_array($pengajuan->status_pengajuan, ['DISETUJUI_KEPSEK', 'DIPROSES'], true);
        @endphp

        @if (!$canRealisasi)
            <div class="panel">
                <p class="text-sm text-slate-600 dark:text-slate-300">Pengajuan ini belum bisa direalisasikan pada tahap saat ini.</p>
            </div>
        @else
            <section>
                <div class="max-w-5xl mx-auto border-2 border-blue-200 panel dark:border-cyan-500/40">
                    <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">
                        Form Realisasi {{ $pengajuan->jenis_pengajuan === 'PERAWATAN' ? 'Perawatan' : 'Penggantian' }}
                    </h2>

                    @if ($pengajuan->jenis_pengajuan === 'PERAWATAN')
                        <form method="POST" action="{{ route('admin.pengajuan.perawatan', $pengajuan) }}" enctype="multipart/form-data" class="grid gap-4 mt-4 md:grid-cols-2">
                            @csrf
                            <div>
                                <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Tanggal Perawatan <span class="text-rose-500">*</span></label>
                                <input type="date" name="tanggal_perawatan" value="{{ old('tanggal_perawatan', $pengajuan->perawatan?->tanggal_perawatan?->format('Y-m-d')) }}" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Biaya Realisasi (Rp) <span class="text-rose-500">*</span></label>
                                <input type="number" name="biaya_realisasi" value="{{ old('biaya_realisasi', $pengajuan->perawatan?->biaya_realisasi) }}" min="0" step="1000" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Nama Teknisi <span class="text-rose-500">*</span></label>
                                <input type="text" name="nama_teknisi" value="{{ old('nama_teknisi', $pengajuan->perawatan?->nama_teknisi) }}" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Kontak Teknisi</label>
                                <input type="text" name="kontak_teknisi" value="{{ old('kontak_teknisi', $pengajuan->perawatan?->kontak_teknisi) }}" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Vendor / Penyedia <span class="text-rose-500">*</span></label>
                                <input type="text" name="nama_vendor" value="{{ old('nama_vendor', $pengajuan->perawatan?->nama_vendor) }}" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Kontak Vendor</label>
                                <input type="text" name="kontak_vendor" value="{{ old('kontak_vendor', $pengajuan->perawatan?->kontak_vendor) }}" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Keterangan Pekerjaan <span class="text-rose-500">*</span></label>
                                <textarea name="keterangan" rows="2" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>{{ old('keterangan', $pengajuan->perawatan?->keterangan) }}</textarea>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Foto Bukti Kuitansi/Pembelian</label>
                                <input type="file" name="foto_bukti" accept="image/*" class="w-full px-3 py-2 text-sm bg-white border border-dashed rounded-lg border-slate-300 text-slate-600 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-200">
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Foto Sesudah Perawatan <span class="text-rose-500">*</span></label>
                                <input type="file" name="foto_sesudah" accept="image/*" class="w-full px-3 py-2 text-sm bg-white border border-dashed rounded-lg border-slate-300 text-slate-600 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-200" required>
                            </div>
                            <div class="md:col-span-2">
                                <button type="submit" class="justify-center w-full btn-primary"><i class="mr-2 fas fa-save"></i>Simpan Realisasi</button>
                            </div>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.pengajuan.penggantian', $pengajuan) }}" enctype="multipart/form-data" class="grid gap-4 mt-4 md:grid-cols-2">
                            @csrf
                            <div>
                                <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Tanggal Penggantian <span class="text-rose-500">*</span></label>
                                <input type="date" name="tanggal_penggantian" value="{{ old('tanggal_penggantian', $pengajuan->penggantian?->tanggal_penggantian?->format('Y-m-d')) }}" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Biaya Realisasi (Rp) <span class="text-rose-500">*</span></label>
                                <input type="number" name="biaya_realisasi" value="{{ old('biaya_realisasi', $pengajuan->penggantian?->biaya_realisasi) }}" min="0" step="1000" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Nama Teknisi <span class="text-rose-500">*</span></label>
                                <input type="text" name="nama_teknisi" value="{{ old('nama_teknisi', $pengajuan->penggantian?->nama_teknisi) }}" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Kontak Teknisi</label>
                                <input type="text" name="kontak_teknisi" value="{{ old('kontak_teknisi', $pengajuan->penggantian?->kontak_teknisi) }}" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Vendor / Penyedia <span class="text-rose-500">*</span></label>
                                <input type="text" name="nama_vendor" value="{{ old('nama_vendor', $pengajuan->penggantian?->nama_vendor) }}" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Kontak Vendor</label>
                                <input type="text" name="kontak_vendor" value="{{ old('kontak_vendor', $pengajuan->penggantian?->kontak_vendor) }}" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Keterangan Penggantian <span class="text-rose-500">*</span></label>
                                <textarea name="keterangan" rows="2" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>{{ old('keterangan', $pengajuan->penggantian?->keterangan) }}</textarea>
                            </div>

                            <div>
                                <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Foto Bukti Kuitansi/Pembelian</label>
                                <input type="file" name="foto_bukti" accept="image/*" class="w-full px-3 py-2 text-sm bg-white border border-dashed rounded-lg border-slate-300 text-slate-600 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-200">
                            </div>
                            <div class="md:col-span-2">
                                 <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Foto Sarana Baru (Sesudah)</label>
                                 <input type="file" name="foto_sarana_baru" accept="image/*" class="w-full px-3 py-2 text-sm bg-white border border-dashed rounded-lg border-slate-300 text-slate-600 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-200">
                             </div>
                             <div class="md:col-span-2">
                                <button type="submit" class="justify-center w-full btn-primary"><i class="mr-2 fas fa-save"></i>Simpan Realisasi</button>
                            </div>
                        </form>
                    @endif
                </div>
            </section>
        @endif
    @else
    <section class="grid gap-5 lg:grid-cols-12 items-stretch">
        {{-- Left Column (lg:col-span-8) --}}
        <div class="lg:col-span-8 flex flex-col h-full">
            {{-- Master Card: Informasi Detail Pengajuan --}}
            <div class="panel flex-1 flex flex-col justify-between p-5 h-full">
                <div>
                    {{-- Header Pengajuan --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-white/5">
                        <div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400 uppercase tracking-wider">
                                Jenis: {{ $pengajuan->jenis_pengajuan }}
                            </span>
                            <h2 class="mt-1.5 text-base font-bold leading-snug text-slate-800 dark:text-slate-100">{{ $pengajuan->judul_pengajuan }}</h2>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                Diajukan pada {{ $pengajuan->created_at?->format('d M Y, H:i') }} WIB
                            </p>
                        </div>
                        <div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{
                                $pengajuan->status_pengajuan === 'DITOLAK'
                                    ? 'bg-rose-50 text-rose-750 dark:bg-rose-500/10 dark:text-rose-450'
                                    : ($pengajuan->status_pengajuan === 'SELESAI' || $pengajuan->status_pengajuan === 'DISETUJUI_KEPSEK'
                                        ? 'bg-emerald-50 text-emerald-750 dark:bg-emerald-500/10 dark:text-emerald-455'
                                        : 'bg-blue-50 text-blue-750 dark:bg-blue-500/10 dark:text-blue-400')
                            }}">
                                {{ $statusLabels[$pengajuan->status_pengajuan] ?? $pengajuan->status_pengajuan }}
                            </span>
                        </div>
                    </div>

                    {{-- Grid Detail Utama --}}
                    {{-- Grid Detail Utama --}}
                    <div class="py-3.5 border-b border-slate-100 dark:border-white/5">
                        <h3 class="mb-2 text-xs font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">
                            {{ $pengajuan->jenis_pengajuan === 'PENGADAAN' ? 'Detail Target & Biaya Pengadaan' : 'Detail Sarana & Target Realisasi' }}
                        </h3>
                        <dl class="grid gap-4 text-xs sm:grid-cols-2">
                            @if ($pengajuan->sarana)
                                <div>
                                    <dt class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase">Sarana</dt>
                                    <dd class="mt-0.5 font-semibold text-slate-700 dark:text-slate-200">
                                        {{ $pengajuan->sarana->nama_sarana }}
                                        <span class="block mt-0.5 font-mono text-[10px] text-slate-500">{{ $pengajuan->sarana->kode_sarana }}</span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase">Lokasi Penempatan</dt>
                                    <dd class="mt-0.5 text-slate-700 dark:text-slate-200">
                                        {{ $pengajuan->sarana->ruangan?->nama_ruangan }} - {{ $pengajuan->sarana->ruangan?->gedung?->nama_gedung }}
                                        @if ($pengajuan->sarana->ruangan?->lantai)
                                            <span class="block mt-0.5 text-[10px] text-slate-500">Lantai {{ $pengajuan->sarana->ruangan->lantai }}</span>
                                        @endif
                                    </dd>
                                </div>
                            @endif

                            <div>
                                <dt class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase">Estimasi Biaya</dt>
                                <dd class="mt-0.5 font-bold text-slate-800 dark:text-slate-100">
                                    @if ($pengajuan->estimasi_biaya !== null)
                                        Rp {{ number_format((float) $pengajuan->estimasi_biaya, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase">Target Realisasi</dt>
                                <dd class="mt-0.5 text-slate-700 dark:text-slate-200">
                                    {{ $pengajuan->target_realisasi?->format('d M Y') ?? '-' }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Deskripsi Section --}}
                    <div class="py-3 border-b border-slate-100 dark:border-white/5">
                        <h3 class="mb-1.5 text-xs font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">
                            {{ $pengajuan->jenis_pengajuan === 'PENGADAAN' ? 'Deskripsi Kebutuhan Pengadaan' : 'Deskripsi Laporan Kerusakan' }}
                        </h3>
                        <div class="p-3 border rounded-xl bg-slate-50/50 border-slate-100 dark:bg-slate-950/40 dark:border-white/5">
                            <p class="text-xs leading-relaxed whitespace-pre-line text-slate-700 dark:text-slate-200">{{ $pengajuan->deskripsi }}</p>
                        </div>
                    </div>

                    {{-- Dokumentasi Foto Section --}}
                    <div class="py-3 border-b border-slate-100 dark:border-white/5">
                        <h3 class="mb-2 text-xs font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Dokumentasi Foto Realisasi</h3>
                        
                        @if ($pengajuan->jenis_pengajuan === 'PENGADAAN')
                            {{-- Dokumentasi khusus Pengadaan Barang Baru (Tanpa Foto Kerusakan) --}}
                            <div class="grid gap-3.5 sm:grid-cols-2">
                                {{-- 1. Foto Bukti Kuitansi --}}
                                <div class="flex flex-col">
                                    <span class="block text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500 mb-1">Bukti Kuitansi / Pembelian</span>
                                    <div class="relative w-full aspect-[4/3] rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-955 overflow-hidden flex items-center justify-center p-1">
                                        @php
                                            $fotoBukti = null;
                                            if ($pengajuan->perawatan && $pengajuan->perawatan->foto_bukti) {
                                                $fotoBukti = $pengajuan->perawatan->foto_bukti;
                                            } elseif ($pengajuan->penggantian && $pengajuan->penggantian->foto_bukti) {
                                                $fotoBukti = $pengajuan->penggantian->foto_bukti;
                                            }
                                        @endphp
                                        @if ($fotoBukti)
                                            <a href="{{ asset('storage/' . $fotoBukti) }}" target="_blank" class="w-full h-full block">
                                                <img src="{{ asset('storage/' . $fotoBukti) }}" alt="Bukti Kuitansi" class="w-full h-full object-cover rounded-lg transition duration-200 hover:scale-102">
                                            </a>
                                        @else
                                            <div class="flex flex-col items-center justify-center text-center p-2">
                                                <i class="fas fa-receipt text-slate-300 dark:text-slate-700 text-lg mb-1"></i>
                                                <span class="text-[9px] text-slate-400 dark:text-slate-600">Belum ada bukti kuitansi realisasi</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- 2. Foto Barang Baru Hasil Realisasi --}}
                                <div class="flex flex-col">
                                    <span class="block text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500 mb-1">Foto Barang Baru (Hasil Realisasi)</span>
                                    <div class="relative w-full aspect-[4/3] rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-955 overflow-hidden flex items-center justify-center p-1">
                                        @php
                                            $fotoSesudah = null;
                                            if ($pengajuan->perawatan && $pengajuan->perawatan->foto_sesudah) {
                                                $fotoSesudah = $pengajuan->perawatan->foto_sesudah;
                                            } elseif ($pengajuan->penggantian && $pengajuan->penggantian->foto_sarana_baru) {
                                                $fotoSesudah = $pengajuan->penggantian->foto_sarana_baru;
                                            }
                                        @endphp
                                        @if ($fotoSesudah)
                                            <a href="{{ asset('storage/' . $fotoSesudah) }}" target="_blank" class="w-full h-full block">
                                                <img src="{{ asset('storage/' . $fotoSesudah) }}" alt="Foto Barang Baru" class="w-full h-full object-cover rounded-lg transition duration-200 hover:scale-102">
                                            </a>
                                        @else
                                            <div class="flex flex-col items-center justify-center text-center p-2">
                                                <i class="fas fa-box-open text-slate-300 dark:text-slate-700 text-lg mb-1"></i>
                                                <span class="text-[9px] text-slate-400 dark:text-slate-600">Belum ada foto barang baru (Menunggu Realisasi)</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Dokumentasi Perawatan / Penggantian (Dengan Foto Kerusakan) --}}
                            <div class="grid gap-3.5 sm:grid-cols-3">
                                {{-- 1. Foto Kerusakan --}}
                                <div class="flex flex-col">
                                    <span class="block text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500 mb-1">Foto Kerusakan (Sebelum)</span>
                                    <div class="relative w-full aspect-[4/3] rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-955 overflow-hidden flex items-center justify-center p-1">
                                        @php
                                            $fotoKerusakan = $latestKerusakan?->foto_kerusakan;
                                            if (!$fotoKerusakan && is_array($pengajuan->lampiran)) {
                                                foreach ($pengajuan->lampiran as $lampItem) {
                                                    if (is_string($lampItem) && $lampItem !== '') {
                                                        $fotoKerusakan = $lampItem;
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp
                                        @if ($fotoKerusakan)
                                            <a href="{{ asset('storage/' . $fotoKerusakan) }}" target="_blank" class="w-full h-full block">
                                                <img src="{{ asset('storage/' . $fotoKerusakan) }}" alt="Foto Kerusakan" class="w-full h-full object-cover rounded-lg transition duration-200 hover:scale-102">
                                            </a>
                                        @else
                                            <div class="flex flex-col items-center justify-center text-center p-2">
                                                <i class="fas fa-image text-slate-300 dark:text-slate-700 text-lg mb-1"></i>
                                                <span class="text-[9px] text-slate-400 dark:text-slate-600">Tidak ada foto kerusakan</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- 2. Foto Bukti Kuitansi --}}
                                <div class="flex flex-col">
                                    <span class="block text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500 mb-1">Bukti Kuitansi / Pembelian</span>
                                    <div class="relative w-full aspect-[4/3] rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-955 overflow-hidden flex items-center justify-center p-1">
                                        @php
                                            $fotoBukti = null;
                                            if ($pengajuan->perawatan && $pengajuan->perawatan->foto_bukti) {
                                                $fotoBukti = $pengajuan->perawatan->foto_bukti;
                                            } elseif ($pengajuan->penggantian && $pengajuan->penggantian->foto_bukti) {
                                                $fotoBukti = $pengajuan->penggantian->foto_bukti;
                                            }
                                        @endphp
                                        @if ($fotoBukti)
                                            <a href="{{ asset('storage/' . $fotoBukti) }}" target="_blank" class="w-full h-full block">
                                                <img src="{{ asset('storage/' . $fotoBukti) }}" alt="Bukti Kuitansi" class="w-full h-full object-cover rounded-lg transition duration-200 hover:scale-102">
                                            </a>
                                        @else
                                            <div class="flex flex-col items-center justify-center text-center p-2">
                                                <i class="fas fa-receipt text-slate-300 dark:text-slate-700 text-lg mb-1"></i>
                                                <span class="text-[9px] text-slate-400 dark:text-slate-600">Tidak ada kuitansi / pembelian</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- 3. Foto Setelah Perbaikan / Penggantian --}}
                                <div class="flex flex-col">
                                    <span class="block text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500 mb-1">Setelah Perbaikan / Penggantian</span>
                                    <div class="relative w-full aspect-[4/3] rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-955 overflow-hidden flex items-center justify-center p-1">
                                        @php
                                            $fotoSesudah = null;
                                            if ($pengajuan->perawatan && $pengajuan->perawatan->foto_sesudah) {
                                                $fotoSesudah = $pengajuan->perawatan->foto_sesudah;
                                            } elseif ($pengajuan->penggantian && $pengajuan->penggantian->foto_sarana_baru) {
                                                $fotoSesudah = $pengajuan->penggantian->foto_sarana_baru;
                                            }
                                        @endphp
                                        @if ($fotoSesudah)
                                            <a href="{{ asset('storage/' . $fotoSesudah) }}" target="_blank" class="w-full h-full block">
                                                <img src="{{ asset('storage/' . $fotoSesudah) }}" alt="Foto Sesudah" class="w-full h-full object-cover rounded-lg transition duration-200 hover:scale-102">
                                            </a>
                                        @else
                                            <div class="flex flex-col items-center justify-center text-center p-2">
                                                <i class="fas fa-check-circle text-slate-300 dark:text-slate-700 text-lg mb-1"></i>
                                                <span class="text-[9px] text-slate-400 dark:text-slate-600">Belum ada foto setelah realisasi</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Lampiran Section --}}
                @if (!empty($pengajuan->lampiran))
                    <div class="pt-3">
                        <h3 class="mb-1.5 text-xs font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Lampiran Dokumen</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($pengajuan->lampiran as $lampiran)
                                <a href="{{ asset('storage/' . $lampiran['path']) }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] text-slate-600 hover:bg-slate-50 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/[0.04] transition">
                                    <i class="fas fa-paperclip text-slate-400"></i>
                                    <span class="truncate max-w-[200px]">{{ $lampiran['name'] ?? basename($lampiran['path']) }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Detail Pengadaan (jika jenis PENGADAAN) --}}
            @if ($pengajuan->jenis_pengajuan === 'PENGADAAN')
                <div class="panel mt-4">
                    <h2 class="text-xs font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Detail Rencana Item Pengadaan</h2>
                    @if ($pengajuan->detailPengadaan->isEmpty())
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Belum ada item pengadaan.</p>
                    @else
                        <div class="mt-3 overflow-x-auto border rounded-xl border-slate-200 dark:border-white/10">
                            <table class="min-w-full text-xs divide-y divide-slate-200 dark:divide-white/10">
                                <thead class="bg-slate-50 dark:bg-white/[0.04]">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-300">Item</th>
                                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-300">Kategori</th>
                                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-300">Ruangan</th>
                                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-300">Jumlah</th>
                                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-300">Estimasi/Unit</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                                    @foreach ($pengajuan->detailPengadaan as $detail)
                                        <tr>
                                            <td class="px-4 py-2.5 text-slate-700 dark:text-slate-200 font-medium">{{ $detail->nama_sarana_rencana }}</td>
                                            <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $detail->kategori?->nama_kategori }}</td>
                                            <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $detail->ruangan?->nama_ruangan }} - {{ $detail->ruangan?->gedung?->nama_gedung }}</td>
                                            <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $detail->jumlah }}</td>
                                            <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">Rp {{ number_format((float) $detail->estimasi_harga, 0, ',', '.') }}</td>
                                        </tr>
                                        @if ($detail->spesifikasi)
                                            <tr class="bg-slate-50/30 dark:bg-white/[0.01]">
                                                <td colspan="5" class="px-4 py-1.5 text-[10px] text-slate-500 dark:text-slate-400">
                                                    <span class="font-bold text-slate-400">Spesifikasi:</span> {{ $detail->spesifikasi }}
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Right Column (lg:col-span-4) --}}
        <div class="lg:col-span-4 flex flex-col h-full">
            @php
                $user = auth()->user();

                $userRole = null;
                if ($user?->hasRole('kepala_sarana')) {
                    $userRole = 'kepala_sarana';
                } elseif ($user?->hasRole('bendahara')) {
                    $userRole = 'bendahara';
                } elseif ($user?->hasRole('kepala_sekolah')) {
                    $userRole = 'kepala_sekolah';
                }

                $canApprove = false;
                $approveRoute = null;
                $rejectRoute = null;

                if ($userRole === 'kepala_sarana' && $pengajuan->status_pengajuan === \App\Models\Pengajuan::STATUS_DIAJUKAN && (int) $pengajuan->user_id !== (int) $user->id) {
                    $canApprove = true;
                    $approveRoute = route('kepala_sarana.pengajuan.approve', $pengajuan);
                    $rejectRoute = route('kepala_sarana.pengajuan.reject', $pengajuan);
                } elseif ($userRole === 'bendahara' && $pengajuan->status_pengajuan === \App\Models\Pengajuan::STATUS_DISETUJUI_KASARANA && (int) $pengajuan->user_id !== (int) $user->id) {
                    $canApprove = true;
                    $approveRoute = route('bendahara.pengajuan.approve', $pengajuan);
                    $rejectRoute = route('bendahara.pengajuan.reject', $pengajuan);
                } elseif ($userRole === 'kepala_sekolah' && $pengajuan->status_pengajuan === \App\Models\Pengajuan::STATUS_DISETUJUI_BENDAHARA && (int) $pengajuan->user_id !== (int) $user->id) {
                    $canApprove = true;
                    $approveRoute = route('kepala_sekolah.pengajuan.approve', $pengajuan);
                    $rejectRoute = route('kepala_sekolah.pengajuan.reject', $pengajuan);
                }

                $canRealisasi = $user?->hasRole('admin')
                    && $isRealisasiPage
                    && in_array($pengajuan->status_pengajuan, ['DISETUJUI_KEPSEK', 'DIPROSES'], true);
                $canOpenRealisasi = $user?->hasRole('admin')
                    && !$isRealisasiPage
                    && in_array($pengajuan->status_pengajuan, ['DISETUJUI_KEPSEK', 'DIPROSES'], true);
                $openFormByDefault = $isRealisasiPage || $errors->any();
            @endphp

            {{-- Master Card Kanan: Alur Approval & Info Pengaju --}}
            <div class="panel flex-1 flex flex-col justify-between p-5 h-full space-y-4.5">
                <div class="space-y-4">
                    {{-- Info Pengaju --}}
                    <div class="pb-3 border-b border-slate-100 dark:border-white/5">
                        <h2 class="text-xs font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Pengaju</h2>
                        <div class="flex items-center gap-2.5 mt-2">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                <i class="text-xs fas fa-user"></i>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-800 dark:text-slate-100 leading-none">{{ $pengajuan->user?->display_name ?? '-' }}</p>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 leading-none">{{ $pengajuan->user?->email }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Realisasi (jika ada dan sudah selesai) --}}
                    @if ($pengajuan->perawatan || $pengajuan->penggantian)
                        <div class="pb-3 border-b border-slate-100 dark:border-white/5">
                            <h2 class="mb-2 text-xs font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Info Realisasi</h2>
                            @if ($pengajuan->perawatan)
                                <div class="space-y-1.5 text-xs text-slate-700 dark:text-slate-350">
                                    <p class="flex justify-between"><span>Tanggal Perawatan:</span> <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $pengajuan->perawatan->tanggal_perawatan?->format('d M Y') }}</span></p>
                                    <p class="flex justify-between"><span>Biaya Realisasi:</span> <span class="font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format((float) ($pengajuan->perawatan->biaya_realisasi ?? 0), 0, ',', '.') }}</span></p>
                                    <p class="flex justify-between"><span>Vendor:</span> <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $pengajuan->perawatan->nama_vendor ?? '-' }}</span></p>
                                    @if ($pengajuan->perawatan->keterangan)
                                        <p class="mt-1.5 pt-1.5 border-t border-slate-100 dark:border-white/5 text-[10px] text-slate-500 leading-relaxed">{{ $pengajuan->perawatan->keterangan }}</p>
                                    @endif
                                </div>
                            @elseif ($pengajuan->penggantian)
                                <div class="space-y-1.5 text-xs text-slate-700 dark:text-slate-350">
                                    <p class="flex justify-between"><span>Tanggal Penggantian:</span> <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $pengajuan->penggantian->tanggal_penggantian?->format('d M Y') }}</span></p>
                                    <p class="flex justify-between"><span>Status:</span> <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $pengajuan->penggantian->status_realisasi === 'MENUNGGU_SARANA_BARU' ? 'Menunggu Sarana Baru' : 'Selesai' }}</span></p>
                                    <p class="flex justify-between"><span>Sarana:</span> <span class="font-mono font-semibold text-slate-850 dark:text-slate-250">{{ $pengajuan->penggantian->saranaLama?->kode_sarana ?? '-' }}</span></p>
                                    <p class="flex justify-between"><span>Vendor:</span> <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $pengajuan->penggantian->nama_vendor ?? '-' }}</span></p>
                                    @if ($pengajuan->penggantian->keterangan)
                                        <p class="mt-1.5 pt-1.5 border-t border-slate-100 dark:border-white/5 text-[10px] text-slate-500 leading-relaxed">{{ $pengajuan->penggantian->keterangan }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Riwayat Approval --}}
                    <div>
                        <h2 class="mb-2 text-xs font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Alur Persetujuan</h2>
                        <div class="space-y-3">
                            @forelse ($pengajuan->approvalPengajuan as $approval)
                                <div class="relative pb-1 pl-4.5 border-l border-slate-200 dark:border-white/10">
                                    <div class="absolute -left-[4px] top-1 h-1.5 w-1.5 rounded-full bg-blue-500 dark:bg-cyan-400 shadow-sm"></div>
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-200 leading-none">
                                        {{ $approvalRoleLabels[$approval->role_approval] ?? $approval->role_approval }}
                                        <span class="ml-1 px-1 py-0.1 rounded text-[8px] font-bold {{ $approval->status === 'DISETUJUI' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                                            {{ $approvalStatusLabels[$approval->status] ?? $approval->status }}
                                        </span>
                                    </p>
                                    <p class="text-[9px] text-slate-400 dark:text-slate-500 mt-1 leading-none">
                                        Oleh: <span class="font-medium">{{ $approval->approver?->display_name ?? '-' }}</span> &bull; {{ $approval->approved_at?->format('d M Y H:i') }}
                                    </p>
                                    @if ($approval->catatan)
                                        <div class="mt-1 rounded-lg bg-slate-50/70 border border-slate-100 px-2 py-0.5 text-[10px] text-slate-500 dark:bg-slate-950/60 dark:border-white/5 dark:text-slate-400 leading-normal">
                                            Catatan: {{ $approval->catatan }}
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-[10px] text-slate-450 dark:text-slate-550">Belum ada riwayat approval.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Panel Action Persetujuan (Kepala Sarana / Bendahara / Kepala Sekolah) --}}
                @if ($canApprove)
                    <div class="mt-4 p-4 border-2 border-emerald-300 dark:border-emerald-500/40 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 space-y-3" x-data="{ showRejectModal: false }">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300 flex items-center gap-2">
                                <i class="fas fa-file-signature text-emerald-600 dark:text-emerald-400"></i>
                                Panel Persetujuan ({{ str_replace('_', ' ', strtoupper($userRole)) }})
                            </h3>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300">
                                Menunggu Aksi Anda
                            </span>
                        </div>

                        <p class="text-xs text-slate-600 dark:text-slate-300">
                            Silakan tinjau rincian pengajuan ini. Anda dapat memberikan catatan dan memilih untuk menyetujui atau menolak pengajuan ini.
                        </p>

                        {{-- Form Setujui --}}
                        <form method="POST" action="{{ $approveRoute }}" class="space-y-3 pt-1">
                            @csrf
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Catatan Persetujuan <span class="text-slate-400 font-normal">(Opsional)</span>
                                </label>
                                <textarea name="catatan" rows="2" placeholder="Tuliskan catatan persetujuan jika ada..." class="w-full text-xs bg-white dark:bg-slate-900 border border-slate-300 dark:border-white/15 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-slate-800 dark:text-slate-100"></textarea>
                            </div>

                            <div class="flex items-center gap-2 pt-1">
                                <button type="submit" class="flex-1 btn-primary py-2 text-xs font-bold justify-center bg-emerald-600 hover:bg-emerald-700 text-white shadow-md transition">
                                    <i class="fas fa-check-circle mr-1.5"></i>
                                    Setujui Pengajuan
                                </button>

                                <button type="button" @click="showRejectModal = true" class="px-4 py-2 text-xs font-bold justify-center bg-rose-600 hover:bg-rose-700 text-white rounded-lg shadow-md transition flex items-center gap-1.5">
                                    <i class="fas fa-times-circle"></i>
                                    Tolak
                                </button>
                            </div>
                        </form>

                        {{-- Modal Input Catatan Penolakan --}}
                        <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-transition>
                            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 shadow-2xl border border-slate-200 dark:border-white/10 space-y-4" @click.away="showRejectModal = false">
                                <div class="flex items-center justify-between border-b pb-3 border-slate-100 dark:border-white/10">
                                    <h4 class="text-sm font-bold text-rose-600 dark:text-rose-400 flex items-center gap-2">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Penolakan Pengajuan
                                    </h4>
                                    <button type="button" @click="showRejectModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <form method="POST" action="{{ $rejectRoute }}" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Alasan Penolakan <span class="text-rose-500">*</span>
                                        </label>
                                        <textarea name="catatan" rows="3" required placeholder="Jelaskan alasan penolakan pengajuan ini..." class="w-full text-xs bg-white dark:bg-slate-900 border border-slate-300 dark:border-white/15 rounded-lg shadow-sm focus:ring-rose-500 focus:border-rose-500 text-slate-800 dark:text-slate-100"></textarea>
                                    </div>

                                    <div class="flex items-center justify-end gap-2 pt-2">
                                        <button type="button" @click="showRejectModal = false" class="px-3 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 rounded-lg hover:bg-slate-200">
                                            Batal
                                        </button>
                                        <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-md transition flex items-center gap-1.5">
                                            <i class="fas fa-paper-plane"></i>
                                            Kirim Penolakan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @elseif ($userRole && (int) $pengajuan->user_id === (int) $user->id && in_array($pengajuan->status_pengajuan, [\App\Models\Pengajuan::STATUS_DIAJUKAN, \App\Models\Pengajuan::STATUS_DISETUJUI_KASARANA, \App\Models\Pengajuan::STATUS_DISETUJUI_BENDAHARA], true))
                    <div class="mt-3 p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-500/30 text-xs text-amber-800 dark:text-amber-300 flex items-center gap-2">
                        <i class="fas fa-info-circle text-amber-500"></i>
                        <span>Pengajuan ini dibuat oleh Anda sendiri, sehingga persetujuan dilakukan oleh pejabat lain pada jenjangnya.</span>
                    </div>
                @endif

                @if ($canOpenRealisasi)
                    <a href="{{ route('admin.realisasi.show', $pengajuan) }}" class="justify-center w-full btn-primary text-xs py-1.5 mt-2">Realisasi</a>
                @endif
            </div>

            {{-- Form Realisasi (Admin) --}}
            @if ($canRealisasi)
                <div class="border-2 border-blue-200 panel dark:border-cyan-500/40" x-data="{ showForm: {{ $openFormByDefault ? 'true' : 'false' }} }">
                    <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Panel Realisasi</h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Gunakan form di bawah ini untuk mengisi laporan bukti pekerjaan setelah selesai dilakukan.</p>

                    @if (!$isRealisasiPage)
                        <button type="button" class="justify-center w-full mt-4 btn-primary" @click="showForm = !showForm">
                            <span x-show="!showForm" x-cloak>Tampilkan Form Realisasi</span>
                            <span x-show="showForm" x-cloak>Sembunyikan Form Realisasi</span>
                        </button>
                    @endif

                    <div x-show="showForm" x-transition class="mt-4">
                        @if ($pengajuan->jenis_pengajuan === 'PERAWATAN')
                            <form method="POST" action="{{ route('admin.pengajuan.perawatan', $pengajuan) }}" enctype="multipart/form-data" class="p-4 space-y-4 border rounded-xl border-emerald-200 dark:border-emerald-600/40">
                                @csrf
                                <h3 class="text-sm font-semibold tracking-wide uppercase text-emerald-700 dark:text-emerald-300">Form Realisasi Perawatan</h3>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Tanggal Perawatan <span class="text-rose-500">*</span></label>
                                        <input type="date" name="tanggal_perawatan" value="{{ old('tanggal_perawatan', $pengajuan->perawatan?->tanggal_perawatan?->format('Y-m-d')) }}" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                                    </div>
                                    <div>
                                        <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Biaya Realisasi (Rp) <span class="text-rose-500">*</span></label>
                                        <input type="number" name="biaya_realisasi" value="{{ old('biaya_realisasi', $pengajuan->perawatan?->biaya_realisasi) }}" min="0" step="1000" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Nama Teknisi <span class="text-rose-500">*</span></label>
                                        <input type="text" name="nama_teknisi" value="{{ old('nama_teknisi', $pengajuan->perawatan?->nama_teknisi) }}" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                                    </div>
                                    <div>
                                        <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Kontak Teknisi</label>
                                        <input type="text" name="kontak_teknisi" value="{{ old('kontak_teknisi', $pengajuan->perawatan?->kontak_teknisi) }}" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Vendor / Penyedia <span class="text-rose-500">*</span></label>
                                        <input type="text" name="nama_vendor" value="{{ old('nama_vendor', $pengajuan->perawatan?->nama_vendor) }}" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                                    </div>
                                    <div>
                                        <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Kontak Vendor</label>
                                        <input type="text" name="kontak_vendor" value="{{ old('kontak_vendor', $pengajuan->perawatan?->kontak_vendor) }}" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                                    </div>
                                </div>

                                <div>
                                    <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Keterangan Pekerjaan <span class="text-rose-500">*</span></label>
                                    <textarea name="keterangan" rows="3" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>{{ old('keterangan', $pengajuan->perawatan?->keterangan) }}</textarea>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Foto Bukti Kuitansi/Pembelian</label>
                                        <input type="file" name="foto_bukti" accept="image/*" class="w-full px-3 py-2 text-sm bg-white border border-dashed rounded-lg border-slate-300 text-slate-600 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-200">
                                    </div>
                                    <div>
                                        <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Foto Sesudah Perawatan <span class="text-rose-500">*</span></label>
                                        <input type="file" name="foto_sesudah" accept="image/*" class="w-full px-3 py-2 text-sm bg-white border border-dashed rounded-lg border-slate-300 text-slate-600 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-200" {{ $pengajuan->perawatan ? '' : 'required' }}>
                                    </div>
                                </div>

                                <button type="submit" class="justify-center w-full btn-primary">
                                    <i class="mr-2 fas fa-save"></i>
                                    Simpan Realisasi Status Selesai
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.pengajuan.penggantian', $pengajuan) }}" enctype="multipart/form-data" class="p-4 space-y-4 border rounded-xl border-amber-200 dark:border-amber-600/40">
                                @csrf
                                <h3 class="text-sm font-semibold tracking-wide uppercase text-amber-700 dark:text-amber-300">Form Realisasi Penggantian</h3>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Tanggal Penggantian <span class="text-rose-500">*</span></label>
                                        <input type="date" name="tanggal_penggantian" value="{{ old('tanggal_penggantian', $pengajuan->penggantian?->tanggal_penggantian?->format('Y-m-d')) }}" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                                    </div>
                                    <div>
                                        <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Biaya Realisasi (Rp) <span class="text-rose-500">*</span></label>
                                        <input type="number" name="biaya_realisasi" value="{{ old('biaya_realisasi', $pengajuan->penggantian?->biaya_realisasi) }}" min="0" step="1000" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Nama Teknisi <span class="text-rose-500">*</span></label>
                                        <input type="text" name="nama_teknisi" value="{{ old('nama_teknisi', $pengajuan->penggantian?->nama_teknisi) }}" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                                    </div>
                                    <div>
                                        <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Kontak Teknisi</label>
                                        <input type="text" name="kontak_teknisi" value="{{ old('kontak_teknisi', $pengajuan->penggantian?->kontak_teknisi) }}" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Vendor / Penyedia <span class="text-rose-500">*</span></label>
                                        <input type="text" name="nama_vendor" value="{{ old('nama_vendor', $pengajuan->penggantian?->nama_vendor) }}" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                                    </div>
                                    <div>
                                        <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Kontak Vendor</label>
                                        <input type="text" name="kontak_vendor" value="{{ old('kontak_vendor', $pengajuan->penggantian?->kontak_vendor) }}" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                                    </div>
                                </div>

                                <div>
                                    <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Keterangan Penggantian <span class="text-rose-500">*</span></label>
                                    <textarea name="keterangan" rows="3" class="w-full text-sm bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>{{ old('keterangan', $pengajuan->penggantian?->keterangan) }}</textarea>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Foto Bukti Kuitansi/Pembelian</label>
                                        <input type="file" name="foto_bukti" accept="image/*" class="w-full px-3 py-2 text-sm bg-white border border-dashed rounded-lg border-slate-300 text-slate-600 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-200">
                                    </div>
                                     <div>
                                         <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-700 dark:text-slate-300">Foto Sarana Baru (Sesudah)</label>
                                         <input type="file" name="foto_sarana_baru" accept="image/*" class="w-full px-3 py-2 text-sm bg-white border border-dashed rounded-lg border-slate-300 text-slate-600 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-200">
                                     </div>
                                 </div>

                                <button type="submit" class="justify-center w-full btn-primary">
                                    <i class="mr-2 fas fa-save"></i>
                                    Simpan Realisasi Status Selesai
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </section>
    @endif
</x-layouts.sbadmin>
