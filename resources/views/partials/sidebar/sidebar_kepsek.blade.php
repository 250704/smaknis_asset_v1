@php
    $feature = request()->route('feature');
    $pengajuanOpen = request()->routeIs('kepala_sekolah.pengajuan.*', 'kepala_sekolah.mutasi.*') || in_array($feature, ['approval-final', 'semua-pengajuan', 'mutasi-sarana'], true);
@endphp

<nav class="space-y-1">
    {{-- Dashboard --}}
    <a href="{{ route('kepala_sekolah.dashboard') }}" class="side-nav-link {{ request()->routeIs('kepala_sekolah.dashboard') ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-home"></i>
        <span>Dashboard</span>
    </a>

    <a href="{{ route('kepala_sekolah.kerusakan.create') }}" class="side-nav-link {{ request()->routeIs('kepala_sekolah.kerusakan.create') ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-exclamation-triangle"></i>
        <span>Lapor Kerusakan</span>
    </a>

    <a href="{{ route('kepala_sekolah.pengajuan.create') }}" class="side-nav-link {{ request()->routeIs('kepala_sekolah.pengajuan.create') ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-plus-circle"></i>
        <span>Buat Pengajuan</span>
    </a>

    {{-- Pengajuan - Dropdown --}}
    <div class="side-group" x-data="{ open: @js($pengajuanOpen) }" :class="open ? 'border-white/15 bg-white/[0.07]' : ''">
        <button type="button" @click="open = !open" :aria-expanded="open.toString()" class="w-full text-left cursor-pointer side-nav-link">
            <i class="w-4 text-xs text-center fas fa-file-signature"></i>
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
            <a href="{{ route('kepala_sekolah.pengajuan.index') }}" class="side-sub-link {{ request()->routeIs('kepala_sekolah.pengajuan.index', 'kepala_sekolah.pengajuan.show') || $feature === 'approval-final' ? 'active' : '' }}">Approval Final</a>
            <a href="{{ route('kepala_sekolah.pengajuan.mine') }}" class="side-sub-link {{ request()->routeIs('kepala_sekolah.pengajuan.mine') ? 'active' : '' }}">Pengajuan Saya</a>
            <a href="{{ route('kepala_sekolah.pengajuan.semua') }}" class="side-sub-link {{ request()->routeIs('kepala_sekolah.pengajuan.semua') ? 'active' : '' }}">Semua Pengajuan</a>
            <a href="{{ route('kepala_sekolah.mutasi.index') }}" class="side-sub-link {{ request()->routeIs('kepala_sekolah.mutasi.*') ? 'active' : '' }}">Mutasi Sarana</a>
        </div>
    </div>

    <a href="{{ route('kepala_sekolah.laporan.index') }}" class="side-nav-link {{ request()->routeIs('kepala_sekolah.laporan.index') || $feature === 'pelaporan' ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-chart-bar"></i>
        <span>Laporan</span>
    </a>

    <a href="{{ route('kepala_sekolah.scan') }}" class="side-nav-link {{ request()->routeIs('kepala_sekolah.scan', 'kepala_sekolah.scan.action') ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-qrcode"></i>
        <span>Scan QR</span>
    </a>
</nav>
