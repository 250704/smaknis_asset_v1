<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SARPRAS SMAKNIS') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            html, body {
                margin: 0;
                padding: 0;
                width: 100%;
                height: 100%;
                overflow: hidden;
                /* Premium Muted Slate-Blue & Indigo Mesh Gradient */
                background: radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.22) 0px, transparent 60%),
                            radial-gradient(at 100% 0%, rgba(56, 189, 248, 0.22) 0px, transparent 60%),
                            radial-gradient(at 50% 100%, rgba(139, 92, 246, 0.18) 0px, transparent 60%),
                            #e2e8f0; /* Rich Slate-200 Base */
            }
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
            }
            .title-font {
                font-family: 'Outfit', sans-serif;
            }
            /* Solid white card with premium layered shadow */
            .glass-card {
                background: #ffffff;
                border: 1px solid rgba(226, 232, 240, 0.8);
                box-shadow:
                    0 1px 2px rgba(15, 23, 42, 0.04),
                    0 4px 8px -1px rgba(15, 23, 42, 0.06),
                    0 16px 40px -8px rgba(15, 23, 42, 0.10),
                    0 32px 64px -16px rgba(15, 23, 42, 0.08);
                position: relative;
                overflow: hidden;
            }
            /* Glossy reflection on top of card */
            .glass-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 50%;
                background: linear-gradient(to bottom, rgba(255, 255, 255, 0.6) 0%, rgba(255, 255, 255, 0) 100%);
                border-radius: 1.5rem 1.5rem 0 0;
                pointer-events: none;
                z-index: 0;
            }
            .glass-card > * {
                position: relative;
                z-index: 1;
            }
            /* Dark cinematic vignette */
            .vignette-overlay {
                background: radial-gradient(ellipse at center, transparent 40%, rgba(15, 23, 42, 0.35) 100%);
            }
            /* Soft grainy noise overlay */
            .noise-overlay {
                background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.02'/%3E%3C/svg%3E");
            }
        </style>
    </head>
    <body class="bg-slate-900 text-slate-800">
        
        <!-- Screen-bound layout wrapper to lock centering -->
        <div class="relative w-full h-full flex items-center justify-center overflow-hidden p-4">
            
            <!-- Background Image (enhanced contrast + saturation + light blur) -->
            <div class="fixed inset-0 pointer-events-none overflow-hidden bg-slate-950">
                <img src="/img/bg-sekolah-new-1.png" class="absolute inset-0 w-full h-full object-cover" style="filter: brightness(1.08) saturate(1.25) contrast(1.06) blur(3px);" alt="Background SMK Nurul Islam">
            </div>

            <!-- Dark Cinematic Vignette -->
            <div class="fixed inset-0 vignette-overlay pointer-events-none"></div>

            <!-- Glass Glow Overlay -->
            <div class="fixed inset-0 bg-gradient-to-br from-white/25 via-white/12 to-white/25 pointer-events-none"></div>

            <!-- Grainy noise overlay -->
            <div class="fixed inset-0 noise-overlay pointer-events-none"></div>

            <!-- Soft Ambient Glow Lights -->
            <div class="fixed top-[-15%] left-[-10%] w-[55%] h-[55%] rounded-full bg-blue-400/15 pointer-events-none" style="filter: blur(100px);"></div>
            <div class="fixed bottom-[-15%] right-[-10%] w-[55%] h-[55%] rounded-full bg-indigo-400/15 pointer-events-none" style="filter: blur(100px);"></div>
            <div class="fixed top-[20%] right-[10%] w-[40%] h-[40%] rounded-full bg-cyan-300/10 pointer-events-none" style="filter: blur(100px);"></div>

            <!-- Blue Ambient Glow Behind Card -->
            <div class="absolute z-[5] w-[320px] h-[320px] rounded-full pointer-events-none" style="filter: blur(80px); background: radial-gradient(circle, rgba(59, 130, 246, 0.2) 0%, transparent 70%);"></div>

            <!-- Main Container -->
            <div class="relative z-10 w-full max-w-[400px] transition-all duration-500 hover:scale-[1.01]">
                
                <!-- Back to Welcome button -->
                <div class="{{ request()->routeIs('scan-qr') || request()->routeIs('login') ? 'mb-2' : 'mb-4' }} flex justify-start">
                    <a href="/" class="inline-flex items-center gap-1.5 text-xs font-semibold text-white bg-blue-600/80 hover:bg-blue-600 backdrop-blur-sm px-4 py-2 rounded-full shadow-md transition-all duration-200 hover:shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Kembali Ke Portal
                    </a>
                </div>

                <!-- Glassmorphic Card -->
                <div class="glass-card rounded-3xl flex flex-col {{ request()->routeIs('scan-qr') || request()->routeIs('login') ? 'p-5' : 'p-8' }}">
                    
                    <!-- Logo -->
                    @if (!request()->routeIs('scan-qr') && !request()->routeIs('login'))
                    <div class="flex flex-col items-center mb-6">
                        <a href="/" class="relative block transition-transform hover:scale-[1.03]">
                            <img src="/img/logo smk.png" class="w-14 h-14 object-contain" alt="Logo SMK Nurul Islam">
                        </a>
                    </div>
                    @endif

                    <!-- Session Status / Errors -->
                    @if (session('status'))
                        <div class="mb-4 p-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 text-sm rounded-xl text-center font-medium">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('info'))
                        <div class="mb-4 p-3 bg-blue-500/10 border border-blue-500/20 text-blue-600 text-sm rounded-xl text-center font-medium">
                            {{ session('info') }}
                        </div>
                    @endif

                    {{ $slot }}

                </div>

                <!-- Footer -->
                <div class="text-center text-xs text-white/60 {{ request()->routeIs('scan-qr') || request()->routeIs('login') ? 'mt-3' : 'mt-6' }}" style="text-shadow: 0 1px 3px rgba(0,0,0,0.3);">
                    &copy; {{ date('Y') }} SMAKNIS. All rights reserved.
                </div>

            </div>

        </div>



    </body>
</html>
