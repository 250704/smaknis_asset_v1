@php
    $feature = request()->route('feature');
    $reviewOpen = in_array($feature, ['semua-review', 'approval-anggaran'], true);
@endphp

<nav class="space-y-1">
    <a href="{{ route('bendahara.dashboard') }}" class="side-nav-link {{ request()->routeIs('bendahara.dashboard') ? 'active' : '' }}">
        <i class="fas fa-home w-4 text-center text-xs"></i>
        <span>Dashboard</span>
    </a>

    <a href="{{ route('bendahara.scan') }}" class="side-nav-link {{ request()->routeIs('bendahara.scan', 'bendahara.scan.action') ? 'active' : '' }}">
        <i class="fas fa-qrcode w-4 text-center text-xs"></i>
        <span>Scan QR</span>
    </a>

    <details class="side-group group" @if($reviewOpen) open @endif>
        <summary class="side-nav-link cursor-pointer list-none [&::-webkit-details-marker]:hidden">
            <i class="fas fa-search-dollar w-4 text-center text-xs"></i>
            <span class="flex-1">Review Pengajuan</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition group-open:rotate-180"></i>
        </summary>
        <div class="space-y-1 pb-2 pl-9 pr-2">
            <a href="{{ route('bendahara.feature', ['feature' => 'semua-review']) }}" class="side-sub-link {{ $feature === 'semua-review' ? 'active' : '' }}">Semua Review</a>
            <a href="{{ route('bendahara.feature', ['feature' => 'approval-anggaran']) }}" class="side-sub-link {{ $feature === 'approval-anggaran' ? 'active' : '' }}">Approval Anggaran</a>
        </div>
    </details>

    <a href="{{ route('bendahara.feature', ['feature' => 'pelaporan']) }}" class="side-nav-link {{ $feature === 'pelaporan' ? 'active' : '' }}">
        <i class="fas fa-chart-line w-4 text-center text-xs"></i>
        <span>Pelaporan</span>
    </a>

    <a href="{{ route('bendahara.feature', ['feature' => 'notifikasi']) }}" class="side-nav-link {{ $feature === 'notifikasi' ? 'active' : '' }}">
        <i class="fas fa-bell w-4 text-center text-xs"></i>
        <span>Notifikasi</span>
    </a>
</nav>

