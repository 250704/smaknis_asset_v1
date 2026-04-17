<x-layouts.sbadmin>
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="page-title">Notifikasi</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pantau tracking proses pengajuan dan pelaporan.</p>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 shadow-sm dark:border-white/10 dark:bg-slate-900/60 dark:text-slate-200">
                    {{ number_format($unreadCount) }} belum dibaca
                </span>
                <span class="inline-flex items-center rounded-full border border-slate-200/80 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600 dark:border-white/10 dark:bg-slate-900/40 dark:text-slate-300">
                    {{ number_format($notifikasi->total() ?? $notifikasi->count()) }} total notifikasi
                </span>
            </div>
        </div>
        <form method="POST" action="{{ route('notifikasi.markAll') }}">
            @csrf
            <button type="submit" class="btn-secondary">Tandai Semua Dibaca</button>
        </form>
    </div>

    <section class="space-y-4">
        @if ($notifikasi->isEmpty())
            <div class="panel p-8 text-center text-sm text-slate-500 dark:text-slate-400">
                Belum ada notifikasi.
            </div>
        @else
            <div class="relative rounded-2xl border border-slate-200/80 bg-white/80 p-4 shadow-sm dark:border-white/10 dark:bg-slate-950/35 sm:p-5">
                <div class="pointer-events-none absolute bottom-4 left-[22px] top-4 w-px bg-gradient-to-b from-slate-200 via-slate-300 to-slate-100 dark:from-white/20 dark:via-white/10 dark:to-transparent"></div>

                <div class="space-y-4">
                    @foreach ($notifikasi as $item)
                        @php
                            $isUpdated = $item->updated_at && $item->created_at && $item->updated_at->gt($item->created_at);
                        @endphp
                        <article class="relative pl-11">
                            <div class="absolute left-0 top-2 flex h-6 w-6 items-center justify-center rounded-full border {{ $item->is_read ? 'border-slate-300 bg-white text-slate-500 dark:border-white/20 dark:bg-slate-900/80 dark:text-slate-300' : 'border-cyan-500/30 bg-cyan-500/10 text-cyan-600 dark:border-cyan-400/40 dark:bg-cyan-500/20 dark:text-cyan-200' }}">
                                <i class="fas {{ $item->is_read ? 'fa-check text-[10px]' : 'fa-bell text-[10px]' }}"></i>
                            </div>

                            <div class="rounded-xl border px-4 py-3 shadow-sm transition {{ $item->is_read ? 'border-slate-200/70 bg-white dark:border-white/10 dark:bg-slate-900/50' : 'border-cyan-200 bg-cyan-50/70 dark:border-cyan-400/30 dark:bg-cyan-500/10' }}">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="space-y-1.5">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $item->judul }}</p>
                                            @if ($isUpdated)
                                                <span class="badge-pill bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200">Diperbarui</span>
                                            @endif
                                            @if (!$item->is_read)
                                                <span class="badge-pill bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-200">Baru</span>
                                            @endif
                                        </div>
                                        <p class="whitespace-pre-line text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $item->isi }}</p>
                                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-400">
                                            <span>Dibuat: {{ $item->created_at?->format('d M Y H:i') }}</span>
                                            @if ($isUpdated)
                                                <span>Update: {{ $item->updated_at?->format('d M Y H:i') }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex shrink-0 items-center">
                                        @if ($item->url)
                                            <form method="POST" action="{{ route('notifikasi.read', $item) }}">
                                                @csrf
                                                <button type="submit" class="btn-primary">Buka</button>
                                            </form>
                                        @elseif (!$item->is_read)
                                            <form method="POST" action="{{ route('notifikasi.read', $item) }}">
                                                @csrf
                                                <button type="submit" class="btn-secondary">Tandai Dibaca</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <div class="mt-2">
                {{ $notifikasi->links() }}
            </div>
        @endif
    </section>
</x-layouts.sbadmin>
