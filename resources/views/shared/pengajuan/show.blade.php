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
                <div class="panel mx-auto max-w-5xl border-2 border-blue-200 dark:border-cyan-500/40">
                    <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">
                        Form Realisasi {{ $pengajuan->jenis_pengajuan === 'PERAWATAN' ? 'Perawatan' : 'Penggantian' }}
                    </h2>

                    @if ($pengajuan->jenis_pengajuan === 'PERAWATAN')
                        <form method="POST" action="{{ route('admin.pengajuan.perawatan', $pengajuan) }}" enctype="multipart/form-data" class="mt-4 grid gap-4 md:grid-cols-2">
                            @csrf
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Tanggal Perawatan <span class="text-rose-500">*</span></label>
                                <input type="date" name="tanggal_perawatan" value="{{ old('tanggal_perawatan', $pengajuan->perawatan?->tanggal_perawatan?->format('Y-m-d')) }}" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Biaya Realisasi (Rp) <span class="text-rose-500">*</span></label>
                                <input type="number" name="biaya_realisasi" value="{{ old('biaya_realisasi', $pengajuan->perawatan?->biaya_realisasi) }}" min="0" step="1000" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Nama Teknisi <span class="text-rose-500">*</span></label>
                                <input type="text" name="nama_teknisi" value="{{ old('nama_teknisi', $pengajuan->perawatan?->nama_teknisi) }}" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Kontak Teknisi</label>
                                <input type="text" name="kontak_teknisi" value="{{ old('kontak_teknisi', $pengajuan->perawatan?->kontak_teknisi) }}" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Vendor / Penyedia <span class="text-rose-500">*</span></label>
                                <input type="text" name="nama_vendor" value="{{ old('nama_vendor', $pengajuan->perawatan?->nama_vendor) }}" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Kontak Vendor</label>
                                <input type="text" name="kontak_vendor" value="{{ old('kontak_vendor', $pengajuan->perawatan?->kontak_vendor) }}" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Keterangan Pekerjaan <span class="text-rose-500">*</span></label>
                                <textarea name="keterangan" rows="2" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>{{ old('keterangan', $pengajuan->perawatan?->keterangan) }}</textarea>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Foto Bukti Kuitansi/Pembelian</label>
                                <input type="file" name="foto_bukti" accept="image/*" class="w-full rounded-lg border border-dashed border-slate-300 bg-white px-3 py-2 text-sm text-slate-600 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-200">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Foto Sesudah Perawatan <span class="text-rose-500">*</span></label>
                                <input type="file" name="foto_sesudah" accept="image/*" class="w-full rounded-lg border border-dashed border-slate-300 bg-white px-3 py-2 text-sm text-slate-600 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-200" required>
                            </div>
                            <div class="md:col-span-2">
                                <button type="submit" class="btn-primary w-full justify-center"><i class="fas fa-save mr-2"></i>Simpan Realisasi</button>
                            </div>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.pengajuan.penggantian', $pengajuan) }}" enctype="multipart/form-data" class="mt-4 grid gap-4 md:grid-cols-2">
                            @csrf
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Tanggal Penggantian <span class="text-rose-500">*</span></label>
                                <input type="date" name="tanggal_penggantian" value="{{ old('tanggal_penggantian', $pengajuan->penggantian?->tanggal_penggantian?->format('Y-m-d')) }}" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Biaya Realisasi (Rp) <span class="text-rose-500">*</span></label>
                                <input type="number" name="biaya_realisasi" value="{{ old('biaya_realisasi', $pengajuan->penggantian?->biaya_realisasi) }}" min="0" step="1000" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Nama Teknisi <span class="text-rose-500">*</span></label>
                                <input type="text" name="nama_teknisi" value="{{ old('nama_teknisi', $pengajuan->penggantian?->nama_teknisi) }}" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Kontak Teknisi</label>
                                <input type="text" name="kontak_teknisi" value="{{ old('kontak_teknisi', $pengajuan->penggantian?->kontak_teknisi) }}" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Vendor / Penyedia <span class="text-rose-500">*</span></label>
                                <input type="text" name="nama_vendor" value="{{ old('nama_vendor', $pengajuan->penggantian?->nama_vendor) }}" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Kontak Vendor</label>
                                <input type="text" name="kontak_vendor" value="{{ old('kontak_vendor', $pengajuan->penggantian?->kontak_vendor) }}" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Keterangan Penggantian <span class="text-rose-500">*</span></label>
                                <textarea name="keterangan" rows="2" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>{{ old('keterangan', $pengajuan->penggantian?->keterangan) }}</textarea>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Kode Sarana Baru (jika ada)</label>
                                <input type="text" name="kode_aset_baru" value="{{ old('kode_aset_baru', $pengajuan->penggantian?->asetBaru?->kode_aset) }}" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Foto Bukti Kuitansi/Pembelian</label>
                                <input type="file" name="foto_bukti" accept="image/*" class="w-full rounded-lg border border-dashed border-slate-300 bg-white px-3 py-2 text-sm text-slate-600 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-200">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Foto Sarana Baru (Sesudah)</label>
                                <input type="file" name="foto_aset_baru" accept="image/*" class="w-full rounded-lg border border-dashed border-slate-300 bg-white px-3 py-2 text-sm text-slate-600 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-200">
                            </div>
                            <div class="md:col-span-2">
                                <button type="submit" class="btn-primary w-full justify-center"><i class="fas fa-save mr-2"></i>Simpan Realisasi</button>
                            </div>
                        </form>
                    @endif
                </div>
            </section>
        @endif
    @else
    <section class="grid gap-5 lg:grid-cols-12">
        <div class="space-y-5 lg:col-span-8">
            <div class="panel">
                <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Ringkasan</h2>
                <dl class="grid gap-3 mt-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs tracking-wide uppercase text-slate-500 dark:text-slate-400">Judul</dt>
                        <dd class="text-sm text-slate-700 dark:text-slate-200">{{ $pengajuan->judul_pengajuan }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs tracking-wide uppercase text-slate-500 dark:text-slate-400">Jenis</dt>
                        <dd class="text-sm text-slate-700 dark:text-slate-200">{{ $pengajuan->jenis_pengajuan }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs tracking-wide uppercase text-slate-500 dark:text-slate-400">Status</dt>
                        <dd class="text-sm text-slate-700 dark:text-slate-200">{{ $statusLabels[$pengajuan->status_pengajuan] ?? $pengajuan->status_pengajuan }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs tracking-wide uppercase text-slate-500 dark:text-slate-400">Tanggal</dt>
                        <dd class="text-sm text-slate-700 dark:text-slate-200">{{ $pengajuan->created_at?->format('d M Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            @if (!$isRealisasiPage && $pengajuan->aset)
                <div class="panel">
                    <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Sarana Terkait</h2>
                    <dl class="grid gap-3 mt-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs tracking-wide uppercase text-slate-500 dark:text-slate-400">Kode Sarana</dt>
                            <dd class="font-mono text-sm text-slate-700 dark:text-slate-200">{{ $pengajuan->aset->kode_aset }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs tracking-wide uppercase text-slate-500 dark:text-slate-400">Nama Sarana</dt>
                            <dd class="text-sm text-slate-700 dark:text-slate-200">{{ $pengajuan->aset->nama_aset }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs tracking-wide uppercase text-slate-500 dark:text-slate-400">Lokasi</dt>
                            <dd class="text-sm text-slate-700 dark:text-slate-200">
                                {{ $pengajuan->aset->ruangan?->nama_ruangan }} - {{ $pengajuan->aset->ruangan?->gedung?->nama_gedung }}
                            </dd>
                        </div>
                    </dl>
                </div>
            @endif

            @if (!$isRealisasiPage)
            <div class="panel">
                <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Deskripsi</h2>
                <p class="mt-3 text-sm whitespace-pre-line text-slate-700 dark:text-slate-200">{{ $pengajuan->deskripsi }}</p>
                <div class="grid gap-3 mt-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs tracking-wide uppercase text-slate-500 dark:text-slate-400">Estimasi Biaya</dt>
                        <dd class="text-sm text-slate-700 dark:text-slate-200">
                            @if ($pengajuan->estimasi_biaya !== null)
                                Rp {{ number_format((float) $pengajuan->estimasi_biaya, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs tracking-wide uppercase text-slate-500 dark:text-slate-400">Target Realisasi</dt>
                        <dd class="text-sm text-slate-700 dark:text-slate-200">
                            {{ $pengajuan->target_realisasi?->format('d M Y') ?? '-' }}
                        </dd>
                    </div>
                </div>
            </div>
            @endif

            @if (!$isRealisasiPage && !empty($pengajuan->lampiran))
                <div class="panel">
                    <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Lampiran</h2>
                    <div class="mt-3 space-y-2 text-sm">
                        @foreach ($pengajuan->lampiran as $lampiran)
                            <a href="{{ asset('storage/' . $lampiran['path']) }}" target="_blank" class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-slate-600 hover:bg-slate-50 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/[0.04]">
                                <i class="text-xs fas fa-paperclip"></i>
                                <span class="truncate">{{ $lampiran['name'] ?? basename($lampiran['path']) }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (!$isRealisasiPage && $pengajuan->jenis_pengajuan === 'PENGADAAN')
                <div class="panel">
                    <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Detail Pengadaan</h2>
                    @if ($pengajuan->detailPengadaan->isEmpty())
                        <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">Belum ada item pengadaan.</p>
                    @else
                        <div class="mt-4 overflow-x-auto border rounded-xl border-slate-200 dark:border-white/10">
                            <table class="min-w-full text-sm divide-y divide-slate-200 dark:divide-white/10">
                                <thead class="bg-slate-50 dark:bg-white/[0.04]">
                                    <tr>
                                        <th class="px-4 py-3 text-xs font-semibold tracking-wide text-left uppercase text-slate-500 dark:text-slate-300">Item</th>
                                        <th class="px-4 py-3 text-xs font-semibold tracking-wide text-left uppercase text-slate-500 dark:text-slate-300">Kategori</th>
                                        <th class="px-4 py-3 text-xs font-semibold tracking-wide text-left uppercase text-slate-500 dark:text-slate-300">Ruangan</th>
                                        <th class="px-4 py-3 text-xs font-semibold tracking-wide text-left uppercase text-slate-500 dark:text-slate-300">Jumlah</th>
                                        <th class="px-4 py-3 text-xs font-semibold tracking-wide text-left uppercase text-slate-500 dark:text-slate-300">Estimasi/Unit</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                                    @foreach ($pengajuan->detailPengadaan as $detail)
                                        <tr>
                                            <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $detail->nama_aset_rencana }}</td>
                                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $detail->kategori?->nama_kategori }}</td>
                                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $detail->ruangan?->nama_ruangan }} - {{ $detail->ruangan?->gedung?->nama_gedung }}</td>
                                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $detail->jumlah }}</td>
                                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                                @if ($detail->estimasi_harga_satuan !== null)
                                                    Rp {{ number_format((float) $detail->estimasi_harga_satuan, 0, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        @if ($detail->spesifikasi)
                                            <tr class="bg-slate-50/60 dark:bg-white/[0.03]">
                                                <td colspan="5" class="px-4 py-3 text-xs text-slate-600 dark:text-slate-300">
                                                    <span class="font-semibold uppercase text-slate-500 dark:text-slate-400">Spesifikasi:</span>
                                                    {{ $detail->spesifikasi }}
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

        <div class="space-y-5 lg:col-span-4">
            @php
                $user = auth()->user();
                $canRealisasi = $user?->hasRole('admin')
                    && $isRealisasiPage
                    && in_array($pengajuan->status_pengajuan, ['DISETUJUI_KEPSEK', 'DIPROSES'], true);
                $canOpenRealisasi = $user?->hasRole('admin')
                    && !$isRealisasiPage
                    && in_array($pengajuan->status_pengajuan, ['DISETUJUI_KEPSEK', 'DIPROSES'], true);
                $canVerifyTeknis = $user?->hasRole('kepala_sarana') && $pengajuan->status_pengajuan === 'MENUNGGU_VERIFIKASI_TEKNIS';
                $canVerifyKeuangan = $user?->hasRole('bendahara') && $pengajuan->status_pengajuan === 'MENUNGGU_VERIFIKASI_KEUANGAN';
                $openFormByDefault = $isRealisasiPage || $errors->any();
            @endphp

            <div class="panel">
                <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Pengaju</h2>
                <p class="mt-3 text-sm text-slate-700 dark:text-slate-200">{{ $pengajuan->user?->display_name ?? '-' }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $pengajuan->user?->email }}</p>

                <h3 class="mt-4 text-xs font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Riwayat Approval</h3>
                <div class="mt-2 space-y-2">
                    @forelse ($pengajuan->approvalPengajuan as $approval)
                        <div class="rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-600 dark:border-white/10 dark:text-slate-300">
                            <p class="font-semibold text-slate-700 dark:text-slate-200">{{ $approvalRoleLabels[$approval->role_approval] ?? $approval->role_approval }} - {{ $approvalStatusLabels[$approval->status] ?? $approval->status }}</p>
                            <p>{{ $approval->approver?->display_name ?? '-' }} | {{ $approval->approved_at?->format('d M Y H:i') }}</p>
                            @if ($approval->catatan)
                                <p class="mt-1 text-slate-500 dark:text-slate-400">Catatan: {{ $approval->catatan }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 dark:text-slate-400">Belum ada riwayat approval.</p>
                    @endforelse
                </div>

                @if ($canOpenRealisasi)
                    <a href="{{ route('admin.realisasi.show', $pengajuan) }}" class="justify-center w-full mt-4 btn-primary">Realisasi</a>
                @endif
            </div>

            @if ($canRealisasi)
                <div class="panel border-2 border-blue-200 dark:border-cyan-500/40" x-data="{ showForm: {{ $openFormByDefault ? 'true' : 'false' }} }">
                    <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Panel Realisasi</h2>

                    <div class="mt-3 rounded-lg border border-slate-200 p-3 dark:border-white/10">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Pengaju</p>
                        <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $pengajuan->user?->display_name ?? '-' }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $pengajuan->user?->email }}</p>
                    </div>

                    <div class="mt-3 rounded-lg border border-slate-200 p-3 dark:border-white/10">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Riwayat Approval</p>
                        <div class="mt-2 space-y-2">
                            @forelse ($pengajuan->approvalPengajuan as $approval)
                                <div class="rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-600 dark:border-white/10 dark:text-slate-300">
                                    <p class="font-semibold text-slate-700 dark:text-slate-200">{{ $approvalRoleLabels[$approval->role_approval] ?? $approval->role_approval }} - {{ $approvalStatusLabels[$approval->status] ?? $approval->status }}</p>
                                    <p>{{ $approval->approver?->display_name ?? '-' }} | {{ $approval->approved_at?->format('d M Y H:i') }}</p>
                                    @if ($approval->catatan)
                                        <p class="mt-1 text-slate-500 dark:text-slate-400">Catatan: {{ $approval->catatan }}</p>
                                    @endif
                                </div>
                            @empty
                                <p class="text-xs text-slate-500 dark:text-slate-400">Belum ada riwayat approval.</p>
                            @endforelse
                        </div>
                    </div>

                    @if (!$isRealisasiPage)
                        <button type="button" class="justify-center w-full mt-4 btn-primary" @click="showForm = !showForm">
                            <span x-show="!showForm" x-cloak>Tampilkan Form Realisasi</span>
                            <span x-show="showForm" x-cloak>Sembunyikan Form Realisasi</span>
                        </button>
                    @endif

                    <div x-show="showForm" x-transition class="mt-4">
                        @if ($pengajuan->jenis_pengajuan === 'PERAWATAN')
                            <form method="POST" action="{{ route('admin.pengajuan.perawatan', $pengajuan) }}" enctype="multipart/form-data" class="space-y-4 rounded-xl border border-emerald-200 p-4 dark:border-emerald-600/40">
                                @csrf
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Form Realisasi Perawatan</h3>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Tanggal Perawatan <span class="text-rose-500">*</span></label>
                                        <input type="date" name="tanggal_perawatan" value="{{ old('tanggal_perawatan', $pengajuan->perawatan?->tanggal_perawatan?->format('Y-m-d')) }}" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Biaya Realisasi (Rp) <span class="text-rose-500">*</span></label>
                                        <input type="number" name="biaya_realisasi" value="{{ old('biaya_realisasi', $pengajuan->perawatan?->biaya_realisasi) }}" min="0" step="1000" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Nama Teknisi <span class="text-rose-500">*</span></label>
                                        <input type="text" name="nama_teknisi" value="{{ old('nama_teknisi', $pengajuan->perawatan?->nama_teknisi) }}" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Kontak Teknisi</label>
                                        <input type="text" name="kontak_teknisi" value="{{ old('kontak_teknisi', $pengajuan->perawatan?->kontak_teknisi) }}" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Vendor / Penyedia <span class="text-rose-500">*</span></label>
                                        <input type="text" name="nama_vendor" value="{{ old('nama_vendor', $pengajuan->perawatan?->nama_vendor) }}" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Kontak Vendor</label>
                                        <input type="text" name="kontak_vendor" value="{{ old('kontak_vendor', $pengajuan->perawatan?->kontak_vendor) }}" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Keterangan Pekerjaan <span class="text-rose-500">*</span></label>
                                    <textarea name="keterangan" rows="3" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>{{ old('keterangan', $pengajuan->perawatan?->keterangan) }}</textarea>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Foto Bukti Kuitansi/Pembelian</label>
                                        <input type="file" name="foto_bukti" accept="image/*" class="w-full rounded-lg border border-dashed border-slate-300 bg-white px-3 py-2 text-sm text-slate-600 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-200">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Foto Sesudah Perawatan <span class="text-rose-500">*</span></label>
                                        <input type="file" name="foto_sesudah" accept="image/*" class="w-full rounded-lg border border-dashed border-slate-300 bg-white px-3 py-2 text-sm text-slate-600 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-200" {{ $pengajuan->perawatan ? '' : 'required' }}>
                                    </div>
                                </div>

                                <button type="submit" class="justify-center w-full btn-primary">
                                    <i class="mr-2 fas fa-save"></i>
                                    Simpan Realisasi Status Selesai
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.pengajuan.penggantian', $pengajuan) }}" enctype="multipart/form-data" class="space-y-4 rounded-xl border border-amber-200 p-4 dark:border-amber-600/40">
                                @csrf
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">Form Realisasi Penggantian</h3>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Tanggal Penggantian <span class="text-rose-500">*</span></label>
                                        <input type="date" name="tanggal_penggantian" value="{{ old('tanggal_penggantian', $pengajuan->penggantian?->tanggal_penggantian?->format('Y-m-d')) }}" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Biaya Realisasi (Rp) <span class="text-rose-500">*</span></label>
                                        <input type="number" name="biaya_realisasi" value="{{ old('biaya_realisasi', $pengajuan->penggantian?->biaya_realisasi) }}" min="0" step="1000" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Nama Teknisi <span class="text-rose-500">*</span></label>
                                        <input type="text" name="nama_teknisi" value="{{ old('nama_teknisi', $pengajuan->penggantian?->nama_teknisi) }}" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Kontak Teknisi</label>
                                        <input type="text" name="kontak_teknisi" value="{{ old('kontak_teknisi', $pengajuan->penggantian?->kontak_teknisi) }}" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Vendor / Penyedia <span class="text-rose-500">*</span></label>
                                        <input type="text" name="nama_vendor" value="{{ old('nama_vendor', $pengajuan->penggantian?->nama_vendor) }}" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Kontak Vendor</label>
                                        <input type="text" name="kontak_vendor" value="{{ old('kontak_vendor', $pengajuan->penggantian?->kontak_vendor) }}" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Keterangan Penggantian <span class="text-rose-500">*</span></label>
                                    <textarea name="keterangan" rows="3" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>{{ old('keterangan', $pengajuan->penggantian?->keterangan) }}</textarea>
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Kode Sarana Baru (jika ada)</label>
                                    <input type="text" name="kode_aset_baru" value="{{ old('kode_aset_baru', $pengajuan->penggantian?->asetBaru?->kode_aset) }}" class="w-full rounded-lg border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-amber-500 focus:ring-amber-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Foto Bukti Kuitansi/Pembelian</label>
                                        <input type="file" name="foto_bukti" accept="image/*" class="w-full rounded-lg border border-dashed border-slate-300 bg-white px-3 py-2 text-sm text-slate-600 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-200">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Foto Sarana Baru (Sesudah)</label>
                                        <input type="file" name="foto_aset_baru" accept="image/*" class="w-full rounded-lg border border-dashed border-slate-300 bg-white px-3 py-2 text-sm text-slate-600 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-200">
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

            

            @if ($canVerifyTeknis)
                <div class="panel">
                    <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Verifikasi Teknis</h2>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Pastikan hasil pekerjaan/perawatan sudah sesuai kondisi lapangan.</p>
                    <form method="POST" action="{{ route('kepala_sarana.pengajuan.verifikasi-teknis', $pengajuan) }}" class="mt-3 space-y-3">
                        @csrf
                        <div>
                            <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Catatan (opsional)</label>
                            <textarea name="catatan" rows="2" class="w-full text-xs bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"></textarea>
                        </div>
                        <button type="submit" class="w-full btn-primary">Konfirmasi Verifikasi Teknis</button>
                    </form>
                </div>
            @endif

            @if ($canVerifyKeuangan)
                <div class="panel">
                    <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Verifikasi Keuangan</h2>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Pastikan bukti pembayaran dan nilai realisasi sudah sesuai.</p>
                    <form method="POST" action="{{ route('bendahara.pengajuan.verifikasi-keuangan', $pengajuan) }}" class="mt-3 space-y-3">
                        @csrf
                        <div>
                            <label class="block mb-1 text-xs font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Catatan (opsional)</label>
                            <textarea name="catatan" rows="2" class="w-full text-xs bg-white rounded-lg shadow-sm border-slate-300 text-slate-700 focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/40"></textarea>
                        </div>
                        <button type="submit" class="w-full btn-primary">Konfirmasi Verifikasi Keuangan</button>
                    </form>
                </div>
            @endif

            @if ($pengajuan->perawatan)
                <div class="panel">
                    <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Realisasi Perawatan</h2>
                    <p class="mt-3 text-sm text-slate-700 dark:text-slate-200">Tanggal: {{ $pengajuan->perawatan->tanggal_perawatan?->format('d M Y') }}</p>
                    <p class="text-sm text-slate-700 dark:text-slate-200">Biaya: Rp {{ number_format((float) ($pengajuan->perawatan->biaya_realisasi ?? 0), 0, ',', '.') }}</p>
                    <p class="text-sm text-slate-700 dark:text-slate-200">Vendor: {{ $pengajuan->perawatan->nama_vendor ?? '-' }}</p>
                    @if ($pengajuan->perawatan->foto_bukti)
                        <a href="{{ asset('storage/' . $pengajuan->perawatan->foto_bukti) }}" target="_blank" class="inline-flex mt-2 text-xs font-semibold text-blue-600 hover:underline dark:text-cyan-300">Lihat Foto Bukti</a>
                    @endif
                    @if ($pengajuan->perawatan->keterangan)
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ $pengajuan->perawatan->keterangan }}</p>
                    @endif
                </div>
            @endif

            @if ($pengajuan->penggantian)
                <div class="panel">
                    <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Realisasi Penggantian</h2>
                    <p class="mt-3 text-sm text-slate-700 dark:text-slate-200">Tanggal: {{ $pengajuan->penggantian->tanggal_penggantian?->format('d M Y') }}</p>
                    <p class="text-sm text-slate-700 dark:text-slate-200">Status: {{ $pengajuan->penggantian->status_realisasi === 'MENUNGGU_ASET_BARU' ? 'Menunggu Aset Baru' : 'Selesai' }}</p>
                    <p class="text-sm text-slate-700 dark:text-slate-200">Aset Lama: {{ $pengajuan->penggantian->asetLama?->kode_aset ?? '-' }}</p>
                    <p class="text-sm text-slate-700 dark:text-slate-200">Aset Baru: {{ $pengajuan->penggantian->asetBaru?->kode_aset ?? '-' }}</p>
                    <p class="text-sm text-slate-700 dark:text-slate-200">Vendor: {{ $pengajuan->penggantian->nama_vendor ?? '-' }}</p>
                    @if ($pengajuan->penggantian->foto_bukti)
                        <a href="{{ asset('storage/' . $pengajuan->penggantian->foto_bukti) }}" target="_blank" class="inline-flex mt-2 text-xs font-semibold text-blue-600 hover:underline dark:text-cyan-300">Lihat Foto Bukti</a>
                    @endif
                </div>
            @endif
        </div>
    </section>
    @endif
</x-layouts.sbadmin>
