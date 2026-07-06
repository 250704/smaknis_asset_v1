@php
    $feature = request()->route('feature');
    $reviewOpen = request()->routeIs('bendahara.pengajuan.*') || in_array($feature, ['semua-review', 'approval-anggaran'], true);
@endphp

<nav class="space-y-1">
    {{-- Dashboard --}}
    <a href="{{ route('bendahara.dashboard') }}" class="side-nav-link {{ request()->routeIs('bendahara.dashboard') ? 'active' : '' }}">
        <i class="fas fa-home w-4 text-center text-xs"></i>
        <span>Dashboard</span>
    </a>

    {{-- Scan QR --}}
    <a href="{{ route('bendahara.scan') }}" class="side-nav-link {{ request()->routeIs('bendahara.scan', 'bendahara.scan.action') ? 'active' : '' }}">
        <i class="fas fa-qrcode w-4 text-center text-xs"></i>
        <span>Scan QR</span>
    </a>

    <a href="{{ route('bendahara.kerusakan.create') }}" class="side-nav-link {{ request()->routeIs('bendahara.kerusakan.create') ? 'active' : '' }}">
        <i class="fas fa-exclamation-triangle w-4 text-center text-xs"></i>
        <span>Lapor Kerusakan</span>
    </a>

    <a href="{{ route('bendahara.pengajuan.create') }}" class="side-nav-link {{ request()->routeIs('bendahara.pengajuan.create') ? 'active' : '' }}">
        <i class="fas fa-plus-circle w-4 text-center text-xs"></i>
        <span>Buat Pengajuan</span>
    </a>

    {{-- Review Pengajuan - Dropdown --}}
    <div class="side-group" x-data="{ open: @js($reviewOpen) }" :class="open ? 'border-white/15 bg-white/[0.07]' : ''">
        <button type="button" @click="open = !open" :aria-expanded="open.toString()" class="side-nav-link w-full cursor-pointer text-left">
            <i class="fas fa-coins w-4 text-center text-xs"></i>
            <span class="flex-1">Review Pengajuan</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-300 ease-out" :class="open ? 'rotate-180' : ''"></i>
        </button>
        <div
            x-cloak
            x-show="open"
            x-transition.opacity.duration.220ms
            x-transition.scale.origin.top.duration.220ms
            class="space-y-1 pb-2 pl-9 pr-2 origin-top"
        >
            <a href="{{ route('bendahara.pengajuan.approval') }}" class="side-sub-link {{ request()->routeIs('bendahara.pengajuan.approval') || $feature === 'approval-anggaran' ? 'active' : '' }}">Approval Anggaran</a>
            <a href="{{ route('bendahara.pengajuan.mine') }}" class="side-sub-link {{ request()->routeIs('bendahara.pengajuan.mine') ? 'active' : '' }}">Pengajuan Saya</a>
            <a href="{{ route('bendahara.pengajuan.index') }}" class="side-sub-link {{ request()->routeIs('bendahara.pengajuan.index') || $feature === 'semua-review' ? 'active' : '' }}">Semua Pengajuan</a>
        </div>
    </div>

    {{-- Laporan - Langsung --}}
    <a href="{{ route('bendahara.laporan.index') }}" class="side-nav-link {{ request()->routeIs('bendahara.laporan.index') || $feature === 'pelaporan' ? 'active' : '' }}">
        <i class="fas fa-chart-bar w-4 text-center text-xs"></i>
        <span>Laporan</span>
    </a>
</nav>
