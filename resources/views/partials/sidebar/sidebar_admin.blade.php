@php
    $feature = request()->route('feature');
    $inventarisOpen = request()->routeIs('admin.aset.*', 'admin.cetak-qr.*') || in_array($feature, ['mutasi-aset', 'cetak-qr'], true);
    $sistemOpen = request()->routeIs('admin.users.*') || in_array($feature, ['manajemen-user', 'log-aktivitas'], true);
    $masterOpen = request()->routeIs('admin.master.*');
@endphp

<nav class="space-y-1">
    <a href="{{ route('admin.dashboard') }}" class="side-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="fas fa-home w-4 text-center text-xs"></i>
        <span>Dashboard</span>
    </a>

    <a href="{{ route('admin.scan') }}" class="side-nav-link {{ request()->routeIs('admin.scan', 'admin.scan.action') ? 'active' : '' }}">
        <i class="fas fa-qrcode w-4 text-center text-xs"></i>
        <span>Scan QR</span>
    </a>

    <details class="side-group group" @if($inventarisOpen) open @endif>
        <summary class="side-nav-link cursor-pointer list-none [&::-webkit-details-marker]:hidden">
            <i class="fas fa-boxes w-4 text-center text-xs"></i>
            <span class="flex-1">Inventaris</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition group-open:rotate-180"></i>
        </summary>
        <div class="space-y-1 pb-2 pl-9 pr-2">
            <a href="{{ route('admin.aset.index') }}" class="side-sub-link {{ request()->routeIs('admin.aset.index', 'admin.aset.show', 'admin.aset.edit') ? 'active' : '' }}">Data Aset</a>
            <a href="{{ route('admin.aset.create') }}" class="side-sub-link {{ request()->routeIs('admin.aset.create') ? 'active' : '' }}">Tambah Aset</a>
            <a href="{{ route('admin.feature', ['feature' => 'mutasi-aset']) }}" class="side-sub-link {{ $feature === 'mutasi-aset' ? 'active' : '' }}">Mutasi Aset</a>
            <a href="{{ route('admin.cetak-qr.index') }}" class="side-sub-link {{ request()->routeIs('admin.cetak-qr.*') || $feature === 'cetak-qr' ? 'active' : '' }}">Cetak QR</a>
        </div>
    </details>

    <details class="side-group group" @if($masterOpen) open @endif>
        <summary class="side-nav-link cursor-pointer list-none [&::-webkit-details-marker]:hidden">
            <i class="fas fa-database w-4 text-center text-xs"></i>
            <span class="flex-1">Master Data</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition group-open:rotate-180"></i>
        </summary>
        <div class="space-y-1 pb-2 pl-9 pr-2">
            <a href="{{ route('admin.master.gedung.index') }}" class="side-sub-link {{ request()->routeIs('admin.master.gedung.*') ? 'active' : '' }}">Gedung</a>
            <a href="{{ route('admin.master.ruangan.index') }}" class="side-sub-link {{ request()->routeIs('admin.master.ruangan.*') ? 'active' : '' }}">Ruangan</a>
            <a href="{{ route('admin.master.kategori-aset.index') }}" class="side-sub-link {{ request()->routeIs('admin.master.kategori-aset.*') ? 'active' : '' }}">Kategori Aset</a>
        </div>
    </details>

    <a href="{{ route('admin.feature', ['feature' => 'semua-pengajuan']) }}" class="side-nav-link {{ $feature === 'semua-pengajuan' ? 'active' : '' }}">
        <i class="fas fa-file-signature w-4 text-center text-xs"></i>
        <span>Pengajuan</span>
    </a>

    <a href="{{ route('admin.feature', ['feature' => 'realisasi']) }}" class="side-nav-link {{ $feature === 'realisasi' ? 'active' : '' }}">
        <i class="fas fa-check-double w-4 text-center text-xs"></i>
        <span>Realisasi</span>
    </a>

    <a href="{{ route('admin.feature', ['feature' => 'pelaporan']) }}" class="side-nav-link {{ $feature === 'pelaporan' ? 'active' : '' }}">
        <i class="fas fa-chart-line w-4 text-center text-xs"></i>
        <span>Laporan</span>
    </a>

    <details class="side-group group" @if($sistemOpen) open @endif>
        <summary class="side-nav-link cursor-pointer list-none [&::-webkit-details-marker]:hidden">
            <i class="fas fa-cogs w-4 text-center text-xs"></i>
            <span class="flex-1">Sistem</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition group-open:rotate-180"></i>
        </summary>
        <div class="space-y-1 pb-2 pl-9 pr-2">
            <a href="{{ route('admin.users.index') }}" class="side-sub-link {{ request()->routeIs('admin.users.*') || $feature === 'manajemen-user' ? 'active' : '' }}">Manajemen User</a>
            <a href="{{ route('admin.feature', ['feature' => 'log-aktivitas']) }}" class="side-sub-link {{ $feature === 'log-aktivitas' ? 'active' : '' }}">Log Aktivitas</a>
        </div>
    </details>
</nav>
