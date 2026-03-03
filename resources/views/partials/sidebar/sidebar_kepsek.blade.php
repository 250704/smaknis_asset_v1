@php
    $feature = request()->route('feature');
@endphp

<nav class="space-y-1">
    <a href="{{ route('kepala_sekolah.dashboard') }}" class="side-nav-link {{ request()->routeIs('kepala_sekolah.dashboard') ? 'active' : '' }}">
        <i class="fas fa-home w-4 text-center text-xs"></i>
        <span>Dashboard</span>
    </a>

    <a href="{{ route('kepala_sekolah.scan') }}" class="side-nav-link {{ request()->routeIs('kepala_sekolah.scan', 'kepala_sekolah.scan.action') ? 'active' : '' }}">
        <i class="fas fa-qrcode w-4 text-center text-xs"></i>
        <span>Scan QR</span>
    </a>

    <a href="{{ route('kepala_sekolah.feature', ['feature' => 'approval-final']) }}" class="side-nav-link {{ $feature === 'approval-final' ? 'active' : '' }}">
        <i class="fas fa-stamp w-4 text-center text-xs"></i>
        <span>Approval Final</span>
    </a>

    <a href="{{ route('kepala_sekolah.feature', ['feature' => 'pelaporan']) }}" class="side-nav-link {{ $feature === 'pelaporan' ? 'active' : '' }}">
        <i class="fas fa-chart-line w-4 text-center text-xs"></i>
        <span>Pelaporan</span>
    </a>

    <a href="{{ route('kepala_sekolah.feature', ['feature' => 'notifikasi']) }}" class="side-nav-link {{ $feature === 'notifikasi' ? 'active' : '' }}">
        <i class="fas fa-bell w-4 text-center text-xs"></i>
        <span>Notifikasi</span>
    </a>
</nav>

