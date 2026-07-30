<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SARPRAS SMKNIS') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('sbadmin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = savedTheme ? savedTheme === 'dark' : prefersDark;
            document.documentElement.classList.toggle('dark', isDark);
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-white text-slate-800 transition-colors dark:bg-slate-950 dark:text-slate-100">
    @php
        $user = auth()->user();
        $roleCode = $user?->role_code;
        $roleLabel = match ($roleCode) {
            'admin' => 'Admin Sarpras',
            'guru' => 'Guru / Staf',
            'kepala_sarana' => 'Kepala Sarana',
            'bendahara' => 'Bendahara',
            'kepala_sekolah' => 'Kepala Sekolah',
            default => 'Pengguna',
        };
        $unreadCount = $user
            ? \Illuminate\Support\Facades\Cache::remember(
                'notif_unread_count:' . $user->id,
                now()->addSeconds(20),
                fn () => (int) $user->notifikasi()->where('is_read', false)->count()
            )
            : 0;
        $notifikasiRoute = match ($roleCode) {
            'admin' => 'admin.notifikasi.index',
            'guru' => 'guru.notifikasi.index',
            'kepala_sarana' => 'kepala_sarana.notifikasi.index',
            'bendahara' => 'bendahara.notifikasi.index',
            'kepala_sekolah' => 'kepala_sekolah.notifikasi.index',
            default => null,
        };
        $flashType = null;
        $flashMessage = null;
        foreach (['success', 'error', 'warning', 'info'] as $key) {
            if (session()->has($key)) {
                $flashType = $key;
                $flashMessage = (string) session($key);
                break;
            }
        }
    @endphp

    <div
        x-data="{
            sidebarOpen: false,
            profileOpen: false,
            theme: 'light',
            init() {
                const savedTheme = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                this.theme = savedTheme ?? (prefersDark ? 'dark' : 'light');
                this.applyTheme();
            },
            applyTheme() {
                document.documentElement.classList.toggle('dark', this.theme === 'dark');
                localStorage.setItem('theme', this.theme);
            },
            toggleTheme() {
                this.theme = this.theme === 'dark' ? 'light' : 'dark';
                this.applyTheme();
            }
        }"
        x-init="init()"
        class="app-shell"
    >

        @if ($flashMessage)
            @php
                $flashStyle = match ($flashType) {
                    'success' => 'border-emerald-200/80 bg-emerald-50/95 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/15 dark:text-emerald-100',
                    'error' => 'border-rose-200/80 bg-rose-50/95 text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/15 dark:text-rose-100',
                    'warning' => 'border-amber-200/80 bg-amber-50/95 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/15 dark:text-amber-100',
                    default => 'border-blue-200/80 bg-blue-50/95 text-blue-800 dark:border-blue-500/30 dark:bg-blue-500/15 dark:text-blue-100',
                };
                $flashIcon = match ($flashType) {
                    'success' => 'fa-check-circle',
                    'error' => 'fa-times-circle',
                    'warning' => 'fa-exclamation-triangle',
                    default => 'fa-info-circle',
                };
            @endphp
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 3800)"
                x-show="show"
                x-transition.opacity.duration.200ms
                class="fixed right-4 top-4 z-[80] w-[min(92vw,420px)]"
                style="display:none;"
            >
                <div class="flex items-start gap-3 rounded-xl border px-4 py-3 shadow-lg backdrop-blur {{ $flashStyle }}">
                    <i class="fas {{ $flashIcon }} mt-0.5"></i>
                    <p class="flex-1 text-sm font-medium leading-5">{{ $flashMessage }}</p>
                    <button type="button" @click="show = false" class="text-current/70 transition hover:text-current" aria-label="Tutup notifikasi">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
            </div>
        @endif

        <div
            x-show="sidebarOpen"
            x-transition.opacity
            @click="sidebarOpen = false"
            class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"
            style="display: none;"
        ></div>

        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-40 flex flex-col overflow-hidden transition-transform duration-300 ease-out transform sidebar-shell w-72 lg:translate-x-0 lg:shadow-none"
        >
            <div class="flex h-16 shrink-0 items-center gap-3 px-6">
                <img src="{{ asset('img/logo smk.png') }}" alt="SMK Nurul Islam" class="h-11 w-11 object-contain" />
                <div class="space-y-1">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-white">Sistem Sarana</p>
                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-blue-100">SMK Nurul Islam</p>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-4 pb-6 pt-4 sidebar-menu-scroll">
                @if($roleCode === 'admin')
                    @include('partials.sidebar.sidebar_admin')
                @elseif($roleCode === 'guru')
                    @include('partials.sidebar.sidebar_guru')
                @elseif($roleCode === 'kepala_sarana')
                    @include('partials.sidebar.sidebar_kepsar')
                @elseif($roleCode === 'bendahara')
                    @include('partials.sidebar.sidebar_bendahara')
                @elseif($roleCode === 'kepala_sekolah')
                    @include('partials.sidebar.sidebar_kepsek')
                @endif
            </div>
        </aside>

        <div class="lg:pl-72">
            <header class="sticky top-0 z-20">
                <div class="flex items-center justify-between h-16 px-4 topbar-shell sm:px-6">
                    <div class="flex items-center gap-3">
                        <button
                            @click="sidebarOpen = true"
                            class="btn-icon lg:hidden"
                            type="button"
                        >
                            <i class="fas fa-bars"></i>
                        </button>

                        <div class="hidden sm:block">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Panel</p>
                            <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ $roleLabel }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        @if ($notifikasiRoute && Route::has($notifikasiRoute))
                            <a
                                href="{{ route($notifikasiRoute) }}"
                                class="relative btn-icon"
                                aria-label="Notifikasi"
                            >
                                <i class="text-sm fas fa-bell"></i>
                                @if ($unreadCount > 0)
                                    <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-semibold text-white">
                                        {{ $unreadCount }}
                                    </span>
                                @endif
                            </a>
                        @endif

                        <button
                            type="button"
                            @click="toggleTheme()"
                            class="h-10 px-3 font-medium btn-secondary"
                        >
                            <i class="fas fa-sun text-[11px] text-amber-500 dark:hidden"></i>
                            <i class="fas fa-moon text-[11px] text-cyan-300 hidden dark:inline"></i>
                            <span x-text="theme === 'dark' ? 'Light' : 'Dark'"></span>
                        </button>

                        <div class="relative">
                            <button
                                @click="profileOpen = !profileOpen"
                                type="button"
                                class="btn-icon"
                                aria-label="Profil"
                            >
                                <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-bold text-white rounded-lg bg-gradient-to-br from-blue-500 to-cyan-400">
                                    <i class="fas fa-user text-[11px]"></i>
                                </span>
                            </button>

                            <div
                                x-show="profileOpen"
                                @click.outside="profileOpen = false"
                                x-transition
                                class="absolute right-0 w-56 p-2 mt-2 glass-surface rounded-xl"
                                style="display: none;"
                            >
                                <div class="px-3 py-2">
                                    <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ $user?->display_name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $user?->email }}</p>
                                </div>
                                <div class="h-px my-1 bg-slate-200 dark:bg-white/10"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="flex items-center w-full gap-2 px-3 py-2 text-sm font-medium rounded-lg text-rose-600 hover:bg-rose-500/15 dark:text-rose-300"
                                    >
                                        <i class="text-xs fas fa-sign-out-alt"></i>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="px-4 py-6 mx-auto max-w-screen-2xl sm:px-6 sm:py-8">
                <div class="space-y-6 app-fade-up">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <div
        id="action-confirm-modal"
        class="fixed inset-0 z-[120] hidden items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
        aria-hidden="true"
    >
        <div class="w-full max-w-md rounded-xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-white/10 dark:bg-slate-900">
            <div class="flex items-start gap-3">
                <span id="action-confirm-icon" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-200">
                    <i class="fas fa-exclamation-triangle text-sm"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <h2 id="action-confirm-title" class="text-base font-bold text-slate-900 dark:text-white">Konfirmasi Aksi</h2>
                    <p id="action-confirm-message" class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        Apakah Anda yakin ingin melanjutkan aksi ini?
                    </p>
                </div>
            </div>

            <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <button type="button" id="action-confirm-cancel" class="btn-secondary justify-center">Batal</button>
                <button type="button" id="action-confirm-approve" class="btn-primary justify-center">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('action-confirm-modal');
            const title = document.getElementById('action-confirm-title');
            const message = document.getElementById('action-confirm-message');
            const icon = document.getElementById('action-confirm-icon');
            const cancelButton = document.getElementById('action-confirm-cancel');
            const approveButton = document.getElementById('action-confirm-approve');
            let pendingConfirm = null;

            if (!modal || !title || !message || !icon || !cancelButton || !approveButton) {
                return;
            }

            function normalizeText(value) {
                return (value || '').replace(/\s+/g, ' ').trim();
            }

            function getFormMethod(form) {
                const spoofedMethod = form.querySelector('input[name="_method"]')?.value;
                return (spoofedMethod || form.getAttribute('method') || 'GET').toUpperCase();
            }

            function getActionLabel(submitter) {
                return normalizeText(submitter?.dataset.confirmAction)
                    || normalizeText(submitter?.innerText)
                    || normalizeText(submitter?.getAttribute('aria-label'))
                    || normalizeText(submitter?.getAttribute('title'))
                    || normalizeText(submitter?.value)
                    || 'melanjutkan';
            }
 
            function resolveConfig(form, submitter) {
                const actionLabel = getActionLabel(submitter);
                const method = getFormMethod(form);
                const combinedText = `${actionLabel} ${form.dataset.confirmAction || ''}`.toLowerCase();
                const explicitMessage = submitter?.dataset.confirmMessage || form.dataset.confirmMessage;
                const hasExplicitConfirm = explicitMessage || submitter?.dataset.confirmTitle || form.dataset.confirmTitle;
                const isDangerButton = submitter?.classList.contains('btn-danger') || submitter?.className?.toString().includes('text-rose');
                const isDeleteMethod = method === 'DELETE';
                const needsConfirm = hasExplicitConfirm
                    || isDeleteMethod
                    || isDangerButton
                    || /hapus|tolak|setujui|validasi|verifikasi|realisasi|import|reset password|update password|simpan perubahan|kirim laporan|kirim pengajuan|selesai/.test(combinedText);
 
                if (!needsConfirm || form.dataset.confirm === 'false' || submitter?.dataset.confirm === 'false') {
                    return null;
                }
 
                let variant = submitter?.dataset.confirmVariant || form.dataset.confirmVariant || 'warning';
                let defaultTitle = 'Konfirmasi Aksi';
                let defaultMessage = `Apakah Anda yakin ingin ${actionLabel.toLowerCase()}?`;
                let defaultConfirmLabel = actionLabel || 'Ya, Lanjutkan';

                if (isDeleteMethod || combinedText.includes('hapus')) {
                    variant = 'danger';
                    defaultTitle = 'Konfirmasi Hapus';
                    defaultMessage = 'Apakah Anda yakin ingin menghapus data tersebut?';
                    defaultConfirmLabel = 'Ya, Hapus';
                } else if (combinedText.includes('tolak')) {
                    variant = 'danger';
                    defaultTitle = 'Konfirmasi Penolakan';
                    defaultMessage = 'Apakah Anda yakin ingin menolak data ini?';
                    defaultConfirmLabel = 'Ya, Tolak';
                } else if (combinedText.includes('setujui')) {
                    variant = 'success';
                    defaultTitle = 'Konfirmasi Persetujuan';
                    defaultMessage = 'Apakah Anda yakin ingin menyetujui data ini?';
                    defaultConfirmLabel = 'Ya, Setujui';
                } else if (combinedText.includes('validasi')) {
                    variant = 'success';
                    defaultTitle = 'Konfirmasi Validasi';
                    defaultMessage = 'Apakah Anda yakin ingin memvalidasi data ini?';
                    defaultConfirmLabel = 'Ya, Validasi';
                } else if (combinedText.includes('verifikasi')) {
                    variant = 'success';
                    defaultTitle = 'Konfirmasi Verifikasi';
                    defaultMessage = 'Apakah Anda yakin ingin menyimpan verifikasi ini?';
                    defaultConfirmLabel = 'Ya, Verifikasi';
                } else if (combinedText.includes('reset password') || combinedText.includes('update password')) {
                    defaultTitle = 'Konfirmasi Password';
                    defaultMessage = 'Apakah Anda yakin ingin memperbarui password user ini?';
                    defaultConfirmLabel = 'Ya, Update';
                } else if (combinedText.includes('import')) {
                    variant = 'success';
                    defaultTitle = 'Konfirmasi Import';
                    defaultMessage = 'Apakah Anda yakin ingin mengimport data ini?';
                    defaultConfirmLabel = 'Ya, Import';
                } else if (combinedText.includes('realisasi') || combinedText.includes('selesai')) {
                    variant = 'success';
                    defaultTitle = 'Konfirmasi Realisasi';
                    defaultMessage = 'Apakah Anda yakin ingin menyimpan realisasi ini?';
                    defaultConfirmLabel = 'Ya, Simpan';
                }

                return {
                    title: submitter?.dataset.confirmTitle || form.dataset.confirmTitle || defaultTitle,
                    message: explicitMessage || defaultMessage,
                    confirmLabel: submitter?.dataset.confirmConfirmLabel || form.dataset.confirmConfirmLabel || defaultConfirmLabel,
                    variant,
                };
            }

            function setVariant(variant) {
                const variants = {
                    danger: {
                        icon: 'fa-trash',
                        iconClass: 'bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-200',
                        buttonClass: 'bg-rose-600 hover:bg-rose-700 focus:ring-rose-500/40',
                    },
                    success: {
                        icon: 'fa-check-circle',
                        iconClass: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-200',
                        buttonClass: 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500/40',
                    },
                    warning: {
                        icon: 'fa-exclamation-triangle',
                        iconClass: 'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-200',
                        buttonClass: 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500/40',
                    },
                };
                const selected = variants[variant] || variants.warning;
                icon.className = `inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ${selected.iconClass}`;
                icon.innerHTML = `<i class="fas ${selected.icon} text-sm"></i>`;
                approveButton.className = `inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm transition focus:outline-none focus:ring-2 ${selected.buttonClass}`;
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.setAttribute('aria-hidden', 'true');
                pendingConfirm = null;
                window.dispatchEvent(new CustomEvent('action-confirm-closed'));
            }

            function openModal(config, onConfirm) {
                title.textContent = config.title;
                message.textContent = config.message;
                approveButton.textContent = config.confirmLabel;
                setVariant(config.variant);
                pendingConfirm = onConfirm;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modal.setAttribute('aria-hidden', 'false');
                cancelButton.focus();
            }

            window.SarprasConfirm = {
                open(config) {
                    openModal({
                        title: config.title || 'Konfirmasi Aksi',
                        message: config.message || 'Apakah Anda yakin ingin melanjutkan aksi ini?',
                        confirmLabel: config.confirmLabel || 'Ya, Lanjutkan',
                        variant: config.variant || 'warning',
                    }, config.onConfirm || function () {});
                },
            };

            cancelButton.addEventListener('click', closeModal);
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });
            approveButton.addEventListener('click', function () {
                const confirmAction = pendingConfirm;
                closeModal();
                if (confirmAction) {
                    confirmAction();
                }
            });

            document.addEventListener('submit', function (event) {
                const form = event.target;
                if (!(form instanceof HTMLFormElement)) {
                    return;
                }

                if (event.defaultPrevented) {
                    return;
                }

                if (form.dataset.confirmed === 'true') {
                    delete form.dataset.confirmed;
                    return;
                }

                const submitter = event.submitter || document.activeElement;
                const config = resolveConfig(form, submitter);
                if (!config) {
                    return;
                }

                event.preventDefault();
                openModal(config, function () {
                    form.dataset.confirmed = 'true';
                    if (submitter instanceof HTMLElement && submitter.form === form && typeof form.requestSubmit === 'function') {
                        form.requestSubmit(submitter);
                        return;
                    }
                    form.submit();
                });
            });
        })();
    </script>
</body>

</html>
