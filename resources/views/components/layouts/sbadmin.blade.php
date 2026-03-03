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

        <div
            x-show="sidebarOpen"
            x-transition.opacity
            @click="sidebarOpen = false"
            class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"
            style="display: none;"
        ></div>

        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="sidebar-shell fixed inset-y-0 left-0 z-40 w-72 transform overflow-y-auto px-4 pb-6 pt-5 transition-transform duration-200 lg:translate-x-0 lg:shadow-none"
        >
            <div class="mb-6 flex items-center gap-3 px-2">
                <div class="app-float flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-400 text-white shadow-lg shadow-cyan-500/20">
                    <i class="fas fa-shield-alt text-sm"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Sistem Inventaris</p>
                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $roleLabel }}</p>
                </div>
            </div>

            @if($user?->hasRole('admin'))
                @include('partials.sidebar.sidebar_admin')
            @elseif($user?->hasRole('guru'))
                @include('partials.sidebar.sidebar_guru')
            @elseif($user?->hasRole('kepala_sarana'))
                @include('partials.sidebar.sidebar_kepsar')
            @elseif($user?->hasRole('bendahara'))
                @include('partials.sidebar.sidebar_bendahara')
            @elseif($user?->hasRole('kepala_sekolah'))
                @include('partials.sidebar.sidebar_kepsek')
            @endif
        </aside>

        <div class="lg:pl-72">
            <header class="sticky top-0 z-20 px-4 pt-4 sm:px-6">
                <div class="topbar-shell mx-auto flex h-16 max-w-screen-2xl items-center justify-between px-4 sm:px-5">
                    <div class="flex items-center gap-3">
                        <button
                            @click="sidebarOpen = true"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-300 bg-white text-slate-700 lg:hidden dark:border-white/15 dark:bg-white/5 dark:text-slate-200"
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
                        <div class="hidden items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 md:flex dark:border-white/10 dark:bg-white/[0.04]">
                            <i class="fas fa-search text-xs text-slate-400 dark:text-slate-500"></i>
                            <input
                                type="text"
                                placeholder="Cari menu atau data..."
                                class="w-56 border-0 bg-transparent p-0 text-sm text-slate-700 placeholder:text-slate-400 focus:ring-0 dark:text-slate-100 dark:placeholder:text-slate-500"
                            >
                        </div>

                        <button
                            type="button"
                            @click="toggleTheme()"
                            class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-white/15 dark:bg-white/[0.03] dark:text-slate-200 dark:hover:bg-white/[0.07]"
                        >
                            <i class="fas fa-sun text-[11px] text-amber-500 dark:hidden"></i>
                            <i class="fas fa-moon text-[11px] text-cyan-300 hidden dark:inline"></i>
                            <span x-text="theme === 'dark' ? 'Dark' : 'Light'"></span>
                        </button>

                        <div class="relative">
                            <button
                                @click="profileOpen = !profileOpen"
                                type="button"
                                class="inline-flex items-center gap-3 rounded-xl border border-slate-300 bg-white px-3 py-2 text-left dark:border-white/15 dark:bg-white/[0.03]"
                            >
                                <span class="hidden text-sm font-medium text-slate-700 dark:text-slate-200 sm:block">{{ $user?->display_name }}</span>
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-cyan-400 text-xs font-bold text-white">
                                    {{ strtoupper(substr((string) $user?->display_name, 0, 1)) }}
                                </span>
                            </button>

                            <div
                                x-show="profileOpen"
                                @click.outside="profileOpen = false"
                                x-transition
                                class="glass-surface absolute right-0 mt-2 w-56 rounded-xl p-2"
                                style="display: none;"
                            >
                                <div class="px-3 py-2">
                                    <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ $user?->display_name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $user?->email }}</p>
                                </div>
                                <div class="my-1 h-px bg-slate-200 dark:bg-white/10"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-rose-600 hover:bg-rose-500/15 dark:text-rose-300"
                                    >
                                        <i class="fas fa-sign-out-alt text-xs"></i>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-screen-2xl px-4 py-6 sm:px-6 sm:py-8">
                <div class="app-fade-up space-y-6">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
</body>

</html>
