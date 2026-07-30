@php
    $feature = request()->route('feature');
    $saranaOpen = request()->routeIs('kepala_sarana.sarana.*') || in_array($feature, ['data-sarana', 'histori-sarana'], true);
    $validasiOpen = request()->routeIs('kepala_sarana.kerusakan.*', 'kepala_sarana.pengajuan.approval', 'kepala_sarana.pengajuan.index', 'kepala_sarana.mutasi.*') || in_array($feature, ['validasi-kerusakan', 'approval-teknis', 'semua-pengajuan', 'mutasi-sarana'], true);
@endphp

<nav class="space-y-1">
    {{-- Dashboard --}}
    <a href="{{ route('kepala_sarana.dashboard') }}" class="side-nav-link {{ request()->routeIs('kepala_sarana.dashboard') ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-home"></i>
        <span>Dashboard</span>
    </a>

    {{-- Scan QR --}}
    <a href="{{ route('kepala_sarana.scan') }}" class="side-nav-link {{ request()->routeIs('kepala_sarana.scan', 'kepala_sarana.scan.action') ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-qrcode"></i>
        <span>Scan QR</span>
    </a>

    <a href="{{ route('kepala_sarana.kerusakan.create') }}" class="side-nav-link {{ request()->routeIs('kepala_sarana.kerusakan.create') ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-exclamation-triangle"></i>
        <span>Lapor Kerusakan</span>
    </a>

    <a href="{{ route('kepala_sarana.pengajuan.create') }}" class="side-nav-link {{ request()->routeIs('kepala_sarana.pengajuan.create') ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-plus-circle"></i>
        <span>Buat Pengajuan</span>
    </a>

    {{-- Sarana - Dropdown --}}
    <div class="side-group" x-data="{ open: @js($saranaOpen) }" :class="open ? 'border-white/15 bg-white/[0.07]' : ''">
        <button type="button" @click="open = !open" :aria-expanded="open.toString()" class="w-full text-left cursor-pointer side-nav-link">
            <i class="w-4 text-xs text-center fas fa-boxes"></i>
            <span class="flex-1">Sarana</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-300 ease-out" :class="open ? 'rotate-180' : ''"></i>
        </button>
        <div
            x-cloak
            x-show="open"
            x-transition.opacity.duration.220ms
            x-transition.scale.origin.top.duration.220ms
            class="pb-2 pr-2 space-y-1 origin-top pl-9"
        >
            <a href="{{ route('kepala_sarana.sarana.index') }}" class="side-sub-link {{ request()->routeIs('kepala_sarana.sarana.index') || $feature === 'data-sarana' ? 'active' : '' }}">Data Sarana</a>
            <a href="{{ route('kepala_sarana.sarana.histori') }}" class="side-sub-link {{ request()->routeIs('kepala_sarana.sarana.histori') || $feature === 'histori-sarana' ? 'active' : '' }}">Histori Sarana</a>
        </div>
    </div>

    {{-- Pengajuan - Dropdown --}}
    <div class="side-group" x-data="{ open: @js($validasiOpen) }" :class="open ? 'border-white/15 bg-white/[0.07]' : ''">
        <button type="button" @click="open = !open" :aria-expanded="open.toString()" class="w-full text-left cursor-pointer side-nav-link">
            <i class="w-4 text-xs text-center fas fa-check-double"></i>
            <span class="flex-1">Pengajuan</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-300 ease-out" :class="open ? 'rotate-180' : ''"></i>
        </button>
        <div
            x-cloak
            x-show="open"
            x-transition.opacity.duration.220ms
            x-transition.scale.origin.top.duration.220ms
            class="pb-2 pr-2 space-y-1 origin-top pl-9"
        >
            <a href="{{ route('kepala_sarana.pengajuan.approval') }}" class="side-sub-link {{ request()->routeIs('kepala_sarana.pengajuan.approval', 'kepala_sarana.kerusakan.index') || in_array($feature, ['approval-teknis', 'validasi-kerusakan'], true) ? 'active' : '' }}">Approval</a>
            <a href="{{ route('kepala_sarana.pengajuan.mine') }}" class="side-sub-link {{ request()->routeIs('kepala_sarana.pengajuan.mine') ? 'active' : '' }}">Pengajuan Saya</a>
            <a href="{{ route('kepala_sarana.pengajuan.index') }}" class="side-sub-link {{ request()->routeIs('kepala_sarana.pengajuan.index') || $feature === 'semua-pengajuan' ? 'active' : '' }}">Semua Pengajuan</a>
            <a href="{{ route('kepala_sarana.mutasi.index') }}" class="side-sub-link {{ request()->routeIs('kepala_sarana.mutasi.*') || $feature === 'mutasi-sarana' ? 'active' : '' }}">Mutasi Sarana</a>
        </div>
    </div>

    {{-- Laporan - Langsung --}}
    <a href="{{ route('kepala_sarana.laporan.index') }}" class="side-nav-link {{ request()->routeIs('kepala_sarana.laporan.index') || $feature === 'pelaporan' ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-chart-bar"></i>
        <span>Laporan</span>
    </a>
</nav>
