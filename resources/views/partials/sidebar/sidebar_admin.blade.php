@php
    $feature = request()->route('feature');
    $inventarisOpen = request()->routeIs('admin.aset.*', 'admin.cetak-qr.*') || in_array($feature, ['mutasi-aset', 'cetak-qr'], true);
    $masterOpen = request()->routeIs('admin.master.*');
    $sistemOpen = request()->routeIs('admin.users.*') || in_array($feature, ['manajemen-user', 'log-aktivitas'], true);
@endphp

<nav class="space-y-1">
    <a href="{{ route('admin.dashboard') }}" class="side-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-home"></i>
        <span>Dashboard</span>
    </a>

    {{-- Inventaris --}}
    <details class="side-group group" @if($inventarisOpen) open @endif>
        <summary class="side-nav-link cursor-pointer list-none [&::-webkit-details-marker]:hidden">
            <i class="w-4 text-xs text-center fas fa-boxes"></i>
            <span class="flex-1">Inventaris</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition group-open:rotate-180"></i>
        </summary>
        <div class="pb-2 pr-2 space-y-1 pl-9">
            <a href="{{ route('admin.aset.index') }}" class="side-sub-link {{ request()->routeIs('admin.aset.index', 'admin.aset.show', 'admin.aset.edit') ? 'active' : '' }}">Data Aset</a>
            <a href="{{ route('admin.aset.create') }}" class="side-sub-link {{ request()->routeIs('admin.aset.create') ? 'active' : '' }}">Tambah Aset</a>
            <a href="{{ route('admin.cetak-qr.index') }}" class="side-sub-link {{ request()->routeIs('admin.cetak-qr.*') || $feature === 'cetak-qr' ? 'active' : '' }}">Cetak QR</a>
        </div>
    </details>

    {{-- Master Data --}}
    <details class="side-group group" @if($masterOpen) open @endif>
        <summary class="side-nav-link cursor-pointer list-none [&::-webkit-details-marker]:hidden">
            <i class="w-4 text-xs text-center fas fa-database"></i>
            <span class="flex-1">Master Data</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition group-open:rotate-180"></i>
        </summary>
        <div class="pb-2 pr-2 space-y-1 pl-9">
            <a href="{{ route('admin.master.gedung.index') }}" class="side-sub-link {{ request()->routeIs('admin.master.gedung.*') ? 'active' : '' }}">Gedung</a>
            <a href="{{ route('admin.master.ruangan.index') }}" class="side-sub-link {{ request()->routeIs('admin.master.ruangan.*') ? 'active' : '' }}">Ruangan</a>
            <a href="{{ route('admin.master.kategori-aset.index') }}" class="side-sub-link {{ request()->routeIs('admin.master.kategori-aset.*') ? 'active' : '' }}">Kategori Aset</a>
        </div>
    </details>

    {{-- Sistem --}}
    <details class="side-group group" @if($sistemOpen) open @endif>
        <summary class="side-nav-link cursor-pointer list-none [&::-webkit-details-marker]:hidden">
            <i class="w-4 text-xs text-center fas fa-cog"></i>
            <span class="flex-1">Sistem</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition group-open:rotate-180"></i>
        </summary>
        <div class="pb-2 pr-2 space-y-1 pl-9">
            <a href="{{ route('admin.feature', ['feature' => 'log-aktivitas']) }}" class="side-sub-link {{ $feature === 'log-aktivitas' ? 'active' : '' }}">Log Aktivitas</a>
            <a href="{{ route('admin.users.index') }}" class="side-sub-link {{ request()->routeIs('admin.users.*') || $feature === 'manajemen-user' ? 'active' : '' }}">Manajemen User</a>
        </div>
    </details>

    {{-- FITUR BARU --}}
    <a href="{{ route('admin.scan') }}" class="side-nav-link {{ request()->routeIs('admin.scan', 'admin.scan.action') ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-qrcode"></i>
        <span>Scan QR</span>
    </a>

    <a href="{{ route('admin.kerusakan.create') }}" class="side-nav-link {{ request()->routeIs('admin.kerusakan.create') ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-exclamation-triangle"></i>
        <span>Lapor Kerusakan</span>
    </a>

    <a href="{{ route('admin.pengajuan.index') }}" class="side-nav-link {{ request()->routeIs('admin.pengajuan.index', 'admin.pengajuan.show') || $feature === 'semua-pengajuan' ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-file-signature"></i>
        <span>Semua Pengajuan</span>
    </a>

    <a href="{{ route('admin.pengajuan.create') }}" class="side-nav-link {{ request()->routeIs('admin.pengajuan.create') ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-plus-circle"></i>
        <span>Buat Pengadaan</span>
    </a>

    <a href="{{ route('admin.realisasi.index') }}" class="side-nav-link {{ request()->routeIs('admin.realisasi.*') || $feature === 'realisasi' ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-tools"></i>
        <span>Realisasi</span>
    </a>

    <a href="{{ route('admin.laporan.index') }}" class="side-nav-link {{ request()->routeIs('admin.laporan.index') || $feature === 'pelaporan' ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-chart-bar"></i>
        <span>Laporan</span>
    </a>
</nav>