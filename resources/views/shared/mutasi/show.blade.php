<x-layouts.sbadmin>
    <div class="mb-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="page-title">Detail Usulan Mutasi</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Detail data pengajuan pemindahan sarana dan status verifikasi.</p>
            </div>
            <div>
                <a href="{{ route($role . '.mutasi.index') }}" class="btn-secondary flex items-center gap-2">
                    <i class="fas fa-arrow-left text-xs"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="grid gap-6 md:grid-cols-3">
        {{-- Card Kiri: Detail Informasi --}}
        <div class="md:col-span-2 space-y-6">
            <div class="panel">
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
                    <i class="fas fa-exchange-alt text-blue-500"></i>
                    Informasi Perpindahan
                </h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2 p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/60 dark:border-white/5">
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Sarana</span>
                        <div class="text-sm font-bold text-slate-850 dark:text-slate-100 mt-0.5">{{ $mutasi->sarana?->nama_sarana ?? '-' }}</div>
                        <div class="text-xs font-mono text-blue-600 dark:text-blue-400 font-semibold mt-0.5">{{ $mutasi->sarana?->kode_sarana ?? '-' }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kategori: {{ $mutasi->sarana?->kategori?->nama_kategori ?? '-' }}</div>
                    </div>

                    <div>
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Ruangan Asal</span>
                        <div class="text-sm font-semibold text-slate-700 dark:text-slate-300 mt-0.5">Ruang {{ $mutasi->ruanganAsal?->nama_ruangan ?? '-' }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $mutasi->ruanganAsal?->gedung?->nama_gedung ?? '-' }}</div>
                    </div>

                    <div>
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Ruangan Tujuan</span>
                        <div class="text-sm font-bold text-blue-600 dark:text-blue-450 mt-0.5">Ruang {{ $mutasi->ruanganTujuan?->nama_ruangan ?? '-' }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $mutasi->ruanganTujuan?->gedung?->nama_gedung ?? '-' }}</div>
                    </div>

                    <div class="sm:col-span-2 pt-4 border-t border-slate-100 dark:border-white/5">
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Keterangan / Alasan</span>
                        <div class="text-sm text-slate-700 dark:text-slate-300 mt-1 whitespace-pre-line leading-relaxed bg-slate-50 dark:bg-slate-900/40 p-3 rounded-xl border border-slate-100 dark:border-white/5">
                            {{ $mutasi->keterangan ?: 'Tidak ada keterangan.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Kanan: Status & Validasi --}}
        <div class="space-y-6">
            <div class="panel">
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
                    <i class="fas fa-info-circle text-blue-500"></i>
                    Status Pengajuan
                </h2>

                <div class="space-y-4">
                    <div>
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Status Saat Ini</span>
                        <div class="mt-1">
                            @if($mutasi->status_mutasi === 'DISETUJUI')
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                    DISETUJUI
                                </span>
                            @elseif($mutasi->status_mutasi === 'DITOLAK')
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                    DITOLAK
                                </span>
                            @else
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                    DIAJUKAN
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Diusulkan Oleh</span>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $mutasi->userPengaju?->display_name ?? '-' }}</span>
                        <span class="block text-xs text-slate-400">{{ $mutasi->created_at?->format('d M Y, H:i') }}</span>
                    </div>

                    <div>
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Tanggal Rencana Mutasi</span>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $mutasi->tanggal_mutasi?->format('d M Y') ?? '-' }}</span>
                    </div>

                    @if($mutasi->status_mutasi !== 'DIAJUKAN')
                        <div class="pt-3 border-t border-slate-100 dark:border-white/5">
                            <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Diproses Oleh</span>
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $mutasi->validator?->display_name ?? '-' }}</span>
                            @if($mutasi->status_mutasi === 'DISETUJUI')
                                <span class="block text-xs text-slate-400">Tanggal Eksekusi: {{ $mutasi->tanggal_mutasi?->format('d M Y') }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Panel Approval (Tampil khusus untuk Kepala Sarana jika status DIAJUKAN) --}}
            @if($mutasi->status_mutasi === 'DIAJUKAN' && $role === 'kepala_sarana')
                <div class="panel border border-blue-100 dark:border-blue-900/30 bg-blue-50/20 dark:bg-blue-950/10">
                    <h2 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
                        <i class="fas fa-gavel text-blue-600"></i>
                        Tindakan Approval
                    </h2>

                    <div class="space-y-4">
                        {{-- Form Setujui --}}
                        <form method="POST" action="{{ route($role . '.mutasi.approve', $mutasi->id) }}">
                            @csrf
                            <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menyetujui usulan mutasi ini? Tindakan ini akan langsung merubah ruangan sarana.')" class="w-full btn-primary flex justify-center items-center gap-2">
                                <i class="fas fa-check text-xs"></i>
                                <span>Setujui Usulan</span>
                            </button>
                        </form>

                        {{-- Form Tolak --}}
                        <div class="pt-4 border-t border-slate-200/50 dark:border-white/5">
                            <button type="button" onclick="document.getElementById('reject-form-wrapper').classList.toggle('hidden')" class="w-full btn-danger flex justify-center items-center gap-2">
                                <i class="fas fa-times text-xs"></i>
                                <span>Tolak Usulan</span>
                            </button>

                            <div id="reject-form-wrapper" class="hidden mt-4">
                                <form method="POST" action="{{ route($role . '.mutasi.reject', $mutasi->id) }}" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label for="catatan_penolakan" class="block text-xs font-semibold text-slate-500 uppercase">Alasan Penolakan <span class="text-rose-500">*</span></label>
                                        <textarea id="catatan_penolakan" name="catatan_penolakan" required rows="2" placeholder="Tulis alasan penolakan..." class="mt-1 w-full text-xs bg-white rounded-lg border-slate-300 text-slate-800 focus:border-blue-500 focus:ring-blue-500/30 dark:bg-slate-900 dark:text-slate-100 dark:border-white/10"></textarea>
                                    </div>
                                    <button type="submit" class="w-full py-2 px-4 bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold rounded-lg shadow transition-colors flex justify-center items-center gap-1.5">
                                        <span>Konfirmasi Tolak</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.sbadmin>
