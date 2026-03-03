@php
    $feature = request()->route('feature');
    $pengajuanOpen = in_array($feature, ['buat-pengajuan', 'riwayat-pengajuan'], true);
@endphp

<nav class="space-y-1">
    <a href="{{ route('guru.dashboard') }}" class="side-nav-link {{ request()->routeIs('guru.dashboard') ? 'active' : '' }}">
        <i class="fas fa-home w-4 text-center text-xs"></i>
        <span>Dashboard</span>
    </a>

    <a href="{{ route('guru.scan') }}" class="side-nav-link {{ request()->routeIs('guru.scan', 'guru.scan.action') ? 'active' : '' }}">
        <i class="fas fa-qrcode w-4 text-center text-xs"></i>
        <span>Scan QR</span>
    </a>

    <details class="side-group group" @if($pengajuanOpen) open @endif>
        <summary class="side-nav-link cursor-pointer list-none [&::-webkit-details-marker]:hidden">
            <i class="fas fa-file-signature w-4 text-center text-xs"></i>
            <span class="flex-1">Pengajuan</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition group-open:rotate-180"></i>
        </summary>
        <div class="space-y-1 pb-2 pl-9 pr-2">
            <a href="{{ route('guru.feature', ['feature' => 'buat-pengajuan']) }}" class="side-sub-link {{ $feature === 'buat-pengajuan' ? 'active' : '' }}">Buat Pengajuan</a>
            <a href="{{ route('guru.feature', ['feature' => 'riwayat-pengajuan']) }}" class="side-sub-link {{ $feature === 'riwayat-pengajuan' ? 'active' : '' }}">Riwayat Pengajuan</a>
        </div>
    </details>

    <a href="{{ route('guru.feature', ['feature' => 'notifikasi']) }}" class="side-nav-link {{ $feature === 'notifikasi' ? 'active' : '' }}">
        <i class="fas fa-bell w-4 text-center text-xs"></i>
        <span>Notifikasi</span>
    </a>
</nav>

