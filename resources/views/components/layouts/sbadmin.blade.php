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

<body class="font-sans antialiased bg-slate-100 text-slate-800 transition-colors dark:bg-[#090d1a] dark:text-slate-200">
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
        <div class="soft-dot left-[-120px] top-[-120px] h-72 w-72 bg-cyan-400/25"></div>
        <div class="soft-dot right-[-120px] top-[80px] h-72 w-72 bg-blue-500/20 [animation-delay:1s]"></div>
        <div class="soft-dot bottom-[-160px] left-[30%] h-80 w-80 bg-emerald-400/20 [animation-delay:2s]"></div>

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
            class="fixed inset-y-0 left-0 z-40 px-4 pt-5 pb-6 overflow-y-auto transition-transform duration-200 transform sidebar-shell w-72 lg:translate-x-0 lg:shadow-none"
        >
            <div class="flex items-center gap-3 px-2 mb-6">
                <img src="{{ asset('img/logo smk.png') }}" alt="SMK Nurul Islam" class="h-11 w-11 object-contain" />
                <div class="space-y-1">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-700 dark:text-slate-200">Sistem Inventaris</p>
                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">SMK Nurul Islam</p>
                </div>
            </div>

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
        </aside>

        <div class="lg:pl-72">
            <header class="sticky top-0 z-20 px-4 pt-0 sm:px-6">
                <div class="flex items-center justify-between h-16 px-4 mx-auto topbar-shell max-w-screen-2xl sm:px-5">
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
</body>

</html>
