@php
    $feature = request()->route('feature');
    $asetOpen = request()->routeIs('kepala_sarana.aset.*') || in_array($feature, ['data-aset', 'histori-aset'], true);
    $validasiOpen = request()->routeIs('kepala_sarana.kerusakan.*', 'kepala_sarana.pengajuan.approval', 'kepala_sarana.validasi.semua') || in_array($feature, ['validasi-kerusakan', 'approval-teknis', 'semua-proses'], true);
@endphp

<nav class="space-y-1">
    {{-- Dashboard --}}
    <a href="{{ route('kepala_sarana.dashboard') }}" class="side-nav-link {{ request()->routeIs('kepala_sarana.dashboard') ? 'active' : '' }}">
        <i class="fas fa-home w-4 text-center text-xs"></i>
        <span>Dashboard</span>
    </a>

    {{-- Scan QR --}}
    <a href="{{ route('kepala_sarana.scan') }}" class="side-nav-link {{ request()->routeIs('kepala_sarana.scan', 'kepala_sarana.scan.action') ? 'active' : '' }}">
        <i class="fas fa-qrcode w-4 text-center text-xs"></i>
        <span>Scan QR</span>
    </a>

    <a href="{{ route('kepala_sarana.kerusakan.create') }}" class="side-nav-link {{ request()->routeIs('kepala_sarana.kerusakan.create') ? 'active' : '' }}">
        <i class="fas fa-exclamation-triangle w-4 text-center text-xs"></i>
        <span>Lapor Kerusakan</span>
    </a>

    <a href="{{ route('kepala_sarana.pengajuan.create') }}" class="side-nav-link {{ request()->routeIs('kepala_sarana.pengajuan.create') ? 'active' : '' }}">
        <i class="fas fa-plus-circle w-4 text-center text-xs"></i>
        <span>Buat Pengajuan</span>
    </a>

    {{-- Sarana - Dropdown --}}
    <div class="side-group" x-data="{ open: @js($asetOpen) }" :class="open ? 'border-white/15 bg-white/[0.07]' : ''">
        <button type="button" @click="open = !open" :aria-expanded="open.toString()" class="side-nav-link w-full cursor-pointer text-left">
            <i class="fas fa-boxes w-4 text-center text-xs"></i>
            <span class="flex-1">Sarana</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-300 ease-out" :class="open ? 'rotate-180' : ''"></i>
        </button>
        <div
            x-cloak
            x-show="open"
            x-transition.opacity.duration.220ms
            x-transition.scale.origin.top.duration.220ms
            class="space-y-1 pb-2 pl-9 pr-2 origin-top"
        >
            <a href="{{ route('kepala_sarana.aset.index') }}" class="side-sub-link {{ request()->routeIs('kepala_sarana.aset.index') || $feature === 'data-aset' ? 'active' : '' }}">Data Sarana</a>
            <a href="{{ route('kepala_sarana.aset.histori') }}" class="side-sub-link {{ request()->routeIs('kepala_sarana.aset.histori') || $feature === 'histori-aset' ? 'active' : '' }}">Histori Sarana</a>
        </div>
    </div>

    {{-- Validasi & Approval - Dropdown (GABUNGAN) --}}
    <div class="side-group" x-data="{ open: @js($validasiOpen) }" :class="open ? 'border-white/15 bg-white/[0.07]' : ''">
        <button type="button" @click="open = !open" :aria-expanded="open.toString()" class="side-nav-link w-full cursor-pointer text-left">
            <i class="fas fa-check-double w-4 text-center text-xs"></i>
            <span class="flex-1">Validasi & Approval</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-300 ease-out" :class="open ? 'rotate-180' : ''"></i>
        </button>
        <div
            x-cloak
            x-show="open"
            x-transition.opacity.duration.220ms
            x-transition.scale.origin.top.duration.220ms
            class="space-y-1 pb-2 pl-9 pr-2 origin-top"
        >
            <a href="{{ route('kepala_sarana.kerusakan.index') }}" class="side-sub-link {{ request()->routeIs('kepala_sarana.kerusakan.index') || $feature === 'validasi-kerusakan' ? 'active' : '' }}">Validasi Kerusakan</a>
            <a href="{{ route('kepala_sarana.pengajuan.approval') }}" class="side-sub-link {{ request()->routeIs('kepala_sarana.pengajuan.approval') || $feature === 'approval-teknis' ? 'active' : '' }}">Approval</a>
            <a href="{{ route('kepala_sarana.pengajuan.mine') }}" class="side-sub-link {{ request()->routeIs('kepala_sarana.pengajuan.mine') ? 'active' : '' }}">Pengajuan Saya</a>
            <a href="{{ route('kepala_sarana.validasi.semua') }}" class="side-sub-link {{ request()->routeIs('kepala_sarana.validasi.semua') || $feature === 'semua-proses' ? 'active' : '' }}">Semua Proses</a>
        </div>
    </div>

    {{-- Laporan - Langsung --}}
    <a href="{{ route('kepala_sarana.laporan.index') }}" class="side-nav-link {{ request()->routeIs('kepala_sarana.laporan.index') || $feature === 'pelaporan' ? 'active' : '' }}">
        <i class="fas fa-chart-bar w-4 text-center text-xs"></i>
        <span>Laporan</span>
    </a>
</nav>
