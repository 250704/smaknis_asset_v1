@php
    $feature = request()->route('feature');
@endphp

<nav class="space-y-1">
    {{-- Dashboard --}}
    <a href="{{ route('guru.dashboard') }}" class="side-nav-link {{ request()->routeIs('guru.dashboard') ? 'active' : '' }}">
        <i class="fas fa-home w-4 text-center text-xs"></i>
        <span>Dashboard</span>
    </a>

    {{-- Scan QR --}}
    <a href="{{ route('guru.scan') }}" class="side-nav-link {{ request()->routeIs('guru.scan', 'guru.scan.action') ? 'active' : '' }}">
        <i class="fas fa-qrcode w-4 text-center text-xs"></i>
        <span>Scan QR</span>
    </a>

    {{-- Lapor Kerusakan - LANGSUNG --}}
    <a href="{{ route('guru.kerusakan.create') }}" class="side-nav-link {{ request()->routeIs('guru.kerusakan.create') ? 'active' : '' }}">
        <i class="fas fa-exclamation-triangle w-4 text-center text-xs"></i>
        <span>Lapor Kerusakan</span>
    </a>

    {{-- Buat Pengajuan - LANGSUNG --}}
    <a href="{{ route('guru.pengajuan.create') }}" class="side-nav-link {{ request()->routeIs('guru.pengajuan.create') ? 'active' : '' }}">
        <i class="fas fa-plus-circle w-4 text-center text-xs"></i>
        <span>Buat Pengajuan</span>
    </a>

    {{-- Pengajuan Saya - LANGSUNG --}}
    <a href="{{ route('guru.pengajuan.index') }}" class="side-nav-link {{ request()->routeIs('guru.pengajuan.index') || $feature === 'riwayat-pengajuan' ? 'active' : '' }}">
        <i class="fas fa-history w-4 text-center text-xs"></i>
        <span>Pengajuan Saya</span>
    </a>

    {{-- Mutasi Sarana --}}
    <a href="{{ route('guru.mutasi.index') }}" class="side-nav-link {{ request()->routeIs('guru.mutasi.*') ? 'active' : '' }}">
        <i class="fas fa-exchange-alt w-4 text-center text-xs"></i>
        <span>Mutasi Sarana</span>
    </a>
</nav>
