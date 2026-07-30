<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'SARPRAS SMAKNIS') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">

        <!-- Tailwind CSS -->
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
            /* Custom white card and ambient glow */
            .glass-card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                box-shadow: 0 15px 35px -5px rgba(15, 23, 42, 0.08), 0 8px 15px -6px rgba(15, 23, 42, 0.04);
            }
            .glow-blob {
                opacity: 0.4;
                animation: float-glow 12s ease-in-out infinite alternate;
            }
            @keyframes float-glow {
                0% { transform: translate(0, 0) scale(1); }
                100% { transform: translate(20px, -30px) scale(1.1); }
            }
            /* Custom transition and scaling optimization for background slideshow */
            .slide-bg {
                transition: opacity 1.2s ease-in-out;
                backface-visibility: hidden;
                transform: translateZ(0); /* force GPU acceleration */
                filter: brightness(1.04) saturate(1.12); /* make school images rich and glowing */
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
            
            <!-- Background Image -->
            <div class="fixed inset-0 pointer-events-none overflow-hidden bg-slate-950">
                <img src="/img/bg-sekolah-new-1.png" class="absolute inset-0 w-full h-full object-cover" style="filter: brightness(1.04) saturate(1.12);" alt="Background SMK Nurul Islam">
            </div>

            <!-- Glass Glow Overlay -->
            <div class="fixed inset-0 backdrop-blur-[2px] bg-gradient-to-br from-white/35 via-white/20 to-white/35 pointer-events-none"></div>

            <!-- Grainy noise overlay -->
            <div class="fixed inset-0 noise-overlay pointer-events-none"></div>

            <!-- Soft Glow Lights -->
            <div class="fixed top-[-15%] left-[-10%] w-[55%] h-[55%] rounded-full bg-blue-400/20 pointer-events-none" style="filter: blur(80px);"></div>
            <div class="fixed bottom-[-15%] right-[-10%] w-[55%] h-[55%] rounded-full bg-indigo-400/20 pointer-events-none" style="filter: blur(80px);"></div>
            <div class="fixed top-[20%] right-[10%] w-[40%] h-[40%] rounded-full bg-cyan-300/15 pointer-events-none" style="filter: blur(80px);"></div>

            <!-- Main Container -->
            <div class="relative z-10 w-full max-w-[400px] transition-all duration-500 hover:scale-[1.01]">
                
                <!-- Glassmorphic Card (Light Theme) -->
                <div class="glass-card rounded-3xl p-8 flex flex-col items-center">
                    
                    <!-- Logo Container -->
                    <div class="relative mb-6 flex items-center justify-center transition-transform hover:scale-[1.03]">
                        <img src="/img/logo smk.png" class="w-20 h-20 object-contain" alt="Logo SMK Nurul Islam">
                    </div>

                    <!-- Brand Title -->
                    <h1 class="title-font text-3xl font-extrabold tracking-tight mb-2 bg-gradient-to-r from-slate-950 to-slate-800 bg-clip-text text-transparent">
                        SARPRAS
                    </h1>
                    <p class="text-xs font-bold tracking-[0.2em] text-blue-600 uppercase mb-4">
                        SMK Nurul Islam Cianjur
                    </p>
                    
                    <!-- Description -->
                    <p class="text-sm text-slate-600 text-center mb-8 leading-relaxed max-w-[280px]">
                        
                    </p>

                    <!-- Actions Container -->
                    <div class="w-full space-y-4">
                        <!-- Login Button -->
                        <a href="{{ route('login') }}" class="group relative flex items-center justify-center gap-2 w-full py-3.5 px-6 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold rounded-2xl shadow-[0_4px_25px_rgba(37,99,235,0.25)] transition-all duration-300 hover:translate-y-[-2px] active:translate-y-0">
                            <span>Masuk ke Sistem</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>

                        <!-- Scan QR Button -->
                        <a href="{{ route('scan-qr') }}" class="flex items-center justify-center gap-2 w-full py-3.5 px-6 bg-slate-100 hover:bg-slate-200/80 border border-slate-200/60 hover:border-slate-300 text-blue-700 hover:text-blue-800 font-semibold rounded-2xl transition-all duration-300 hover:translate-y-[-2px] active:translate-y-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-blue-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                            </svg>
                            <span>Pindai QR Code</span>
                        </a>
                    </div>

                    <!-- Footer / Info -->
                    <div class="mt-8 text-xs text-slate-400">
                        &copy; {{ date('Y') }} SMAKNIS. All rights reserved.
                    </div>

                </div>

            </div>

        </div>

        <!-- Automatic Background Slideshow Script -->


    </body>
</html>
