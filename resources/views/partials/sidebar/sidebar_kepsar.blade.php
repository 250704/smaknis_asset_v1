@php
    $feature = request()->route('feature');
    $asetOpen = in_array($feature, ['data-aset', 'histori-aset'], true);
    $validasiOpen = in_array($feature, ['validasi-kerusakan', 'approval-teknis'], true);
@endphp

<nav class="space-y-1">
    <a href="{{ route('kepala_sarana.dashboard') }}" class="side-nav-link {{ request()->routeIs('kepala_sarana.dashboard') ? 'active' : '' }}">
        <i class="fas fa-home w-4 text-center text-xs"></i>
        <span>Dashboard</span>
    </a>

    <a href="{{ route('kepala_sarana.scan') }}" class="side-nav-link {{ request()->routeIs('kepala_sarana.scan', 'kepala_sarana.scan.action') ? 'active' : '' }}">
        <i class="fas fa-qrcode w-4 text-center text-xs"></i>
        <span>Scan QR</span>
    </a>

    <details class="side-group group" @if($asetOpen) open @endif>
        <summary class="side-nav-link cursor-pointer list-none [&::-webkit-details-marker]:hidden">
            <i class="fas fa-boxes w-4 text-center text-xs"></i>
            <span class="flex-1">Aset</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition group-open:rotate-180"></i>
        </summary>
        <div class="space-y-1 pb-2 pl-9 pr-2">
            <a href="{{ route('kepala_sarana.feature', ['feature' => 'data-aset']) }}" class="side-sub-link {{ $feature === 'data-aset' ? 'active' : '' }}">Data Aset</a>
            <a href="{{ route('kepala_sarana.feature', ['feature' => 'histori-aset']) }}" class="side-sub-link {{ $feature === 'histori-aset' ? 'active' : '' }}">Histori Aset</a>
        </div>
    </details>

    <details class="side-group group" @if($validasiOpen) open @endif>
        <summary class="side-nav-link cursor-pointer list-none [&::-webkit-details-marker]:hidden">
            <i class="fas fa-check-circle w-4 text-center text-xs"></i>
            <span class="flex-1">Validasi</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition group-open:rotate-180"></i>
        </summary>
        <div class="space-y-1 pb-2 pl-9 pr-2">
            <a href="{{ route('kepala_sarana.feature', ['feature' => 'validasi-kerusakan']) }}" class="side-sub-link {{ $feature === 'validasi-kerusakan' ? 'active' : '' }}">Kerusakan KR1-KR3</a>
            <a href="{{ route('kepala_sarana.feature', ['feature' => 'approval-teknis']) }}" class="side-sub-link {{ $feature === 'approval-teknis' ? 'active' : '' }}">Approval Teknis</a>
        </div>
    </details>

    <a href="{{ route('kepala_sarana.feature', ['feature' => 'pelaporan']) }}" class="side-nav-link {{ $feature === 'pelaporan' ? 'active' : '' }}">
        <i class="fas fa-chart-line w-4 text-center text-xs"></i>
        <span>Pelaporan</span>
    </a>

    <a href="{{ route('kepala_sarana.feature', ['feature' => 'notifikasi']) }}" class="side-nav-link {{ $feature === 'notifikasi' ? 'active' : '' }}">
        <i class="fas fa-bell w-4 text-center text-xs"></i>
        <span>Notifikasi</span>
    </a>
</nav>

