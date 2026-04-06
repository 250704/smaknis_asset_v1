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

    {{-- Aset - Dropdown --}}
    <details class="side-group group" @if($asetOpen) open @endif>
        <summary class="side-nav-link cursor-pointer list-none [&::-webkit-details-marker]:hidden">
            <i class="fas fa-boxes w-4 text-center text-xs"></i>
            <span class="flex-1">Aset</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition group-open:rotate-180"></i>
        </summary>
        <div class="space-y-1 pb-2 pl-9 pr-2">
            <a href="{{ route('kepala_sarana.aset.index') }}" class="side-sub-link {{ request()->routeIs('kepala_sarana.aset.index') || $feature === 'data-aset' ? 'active' : '' }}">Data Aset</a>
            <a href="{{ route('kepala_sarana.aset.histori') }}" class="side-sub-link {{ request()->routeIs('kepala_sarana.aset.histori') || $feature === 'histori-aset' ? 'active' : '' }}">Histori Aset</a>
        </div>
    </details>

    {{-- Validasi & Approval - Dropdown (GABUNGAN) --}}
    <details class="side-group group" @if($validasiOpen) open @endif>
        <summary class="side-nav-link cursor-pointer list-none [&::-webkit-details-marker]:hidden">
            <i class="fas fa-check-double w-4 text-center text-xs"></i>
            <span class="flex-1">Validasi & Approval</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition group-open:rotate-180"></i>
        </summary>
        <div class="space-y-1 pb-2 pl-9 pr-2">
            <a href="{{ route('kepala_sarana.kerusakan.index') }}" class="side-sub-link {{ request()->routeIs('kepala_sarana.kerusakan.index') || $feature === 'validasi-kerusakan' ? 'active' : '' }}">Validasi Kerusakan</a>
            <a href="{{ route('kepala_sarana.pengajuan.approval') }}" class="side-sub-link {{ request()->routeIs('kepala_sarana.pengajuan.approval') || $feature === 'approval-teknis' ? 'active' : '' }}">Approval</a>
            <a href="{{ route('kepala_sarana.pengajuan.mine') }}" class="side-sub-link {{ request()->routeIs('kepala_sarana.pengajuan.mine') ? 'active' : '' }}">Pengajuan Saya</a>
            <a href="{{ route('kepala_sarana.validasi.semua') }}" class="side-sub-link {{ request()->routeIs('kepala_sarana.validasi.semua') || $feature === 'semua-proses' ? 'active' : '' }}">Semua Proses</a>
        </div>
    </details>

    {{-- Laporan - Langsung --}}
    <a href="{{ route('kepala_sarana.laporan.index') }}" class="side-nav-link {{ request()->routeIs('kepala_sarana.laporan.index') || $feature === 'pelaporan' ? 'active' : '' }}">
        <i class="fas fa-chart-bar w-4 text-center text-xs"></i>
        <span>Laporan</span>
    </a>
</nav>
