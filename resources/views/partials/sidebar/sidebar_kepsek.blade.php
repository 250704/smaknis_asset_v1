@php
    $feature = request()->route('feature');
@endphp

<nav class="space-y-1">
    {{-- Dashboard --}}
    <a href="{{ route('kepala_sekolah.dashboard') }}" class="side-nav-link {{ request()->routeIs('kepala_sekolah.dashboard') ? 'active' : '' }}">
        <i class="fas fa-home w-4 text-center text-xs"></i>
        <span>Dashboard</span>
    </a>

    <a href="{{ route('kepala_sekolah.kerusakan.create') }}" class="side-nav-link {{ request()->routeIs('kepala_sekolah.kerusakan.create') ? 'active' : '' }}">
        <i class="fas fa-exclamation-triangle w-4 text-center text-xs"></i>
        <span>Lapor Kerusakan</span>
    </a>

    <a href="{{ route('kepala_sekolah.pengajuan.semua') }}" class="side-nav-link {{ request()->routeIs('kepala_sekolah.pengajuan.semua') ? 'active' : '' }}">
        <i class="fas fa-file-signature w-4 text-center text-xs"></i>
        <span>Semua Pengajuan</span>
    </a>

    <a href="{{ route('kepala_sekolah.pengajuan.index') }}" class="side-nav-link {{ request()->routeIs('kepala_sekolah.pengajuan.index', 'kepala_sekolah.pengajuan.show') || $feature === 'approval-final' ? 'active' : '' }}">
        <i class="fas fa-stamp w-4 text-center text-xs"></i>
        <span>Approval Final</span>
    </a>

    <a href="{{ route('kepala_sekolah.laporan.index') }}" class="side-nav-link {{ request()->routeIs('kepala_sekolah.laporan.index') || $feature === 'pelaporan' ? 'active' : '' }}">
        <i class="fas fa-chart-bar w-4 text-center text-xs"></i>
        <span>Laporan</span>
    </a>

    <a href="{{ route('kepala_sekolah.scan') }}" class="side-nav-link {{ request()->routeIs('kepala_sekolah.scan', 'kepala_sekolah.scan.action') ? 'active' : '' }}">
        <i class="fas fa-qrcode w-4 text-center text-xs"></i>
        <span>Scan QR</span>
    </a>
</nav>
