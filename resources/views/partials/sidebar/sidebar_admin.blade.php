@php
    $feature = request()->route('feature');
    $inventarisOpen = request()->routeIs('admin.aset.*', 'admin.cetak-qr.*') || in_array($feature, ['data-aset', 'tambah-aset', 'cetak-qr'], true);
    $masterOpen = request()->routeIs('admin.master.*', 'admin.users.*') || in_array($feature, ['manajemen-user'], true);
@endphp

<nav class="space-y-1">
    <a href="{{ route('admin.dashboard') }}" class="side-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-home"></i>
        <span>Dashboard</span>
    </a>

    {{-- Kelola Sarana --}}
    <div class="side-group" x-data="{ open: @js($inventarisOpen) }" :class="open ? 'border-white/15 bg-white/[0.07]' : ''">
        <button type="button" @click="open = !open" :aria-expanded="open.toString()" class="side-nav-link w-full cursor-pointer text-left">
            <i class="w-4 text-xs text-center fas fa-warehouse"></i>
            <span class="flex-1">Kelola Sarana</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-300 ease-out" :class="open ? 'rotate-180' : ''"></i>
        </button>
        <div
            x-cloak
            x-show="open"
            x-transition.opacity.duration.220ms
            x-transition.scale.origin.top.duration.220ms
            class="space-y-1 pb-2 pl-9 pr-2 origin-top"
        >
            <a href="{{ route('admin.aset.index') }}" class="side-sub-link {{ request()->routeIs('admin.aset.index', 'admin.aset.show', 'admin.aset.edit') || $feature === 'data-aset' ? 'active' : '' }}">Data Sarana</a>
            <a href="{{ route('admin.aset.create') }}" class="side-sub-link {{ request()->routeIs('admin.aset.create') || $feature === 'tambah-aset' ? 'active' : '' }}">Tambah Sarana</a>
            <a href="{{ route('admin.cetak-qr.index') }}" class="side-sub-link {{ request()->routeIs('admin.cetak-qr.*') || $feature === 'cetak-qr' ? 'active' : '' }}">Cetak QR Code</a>
        </div>
    </div>

    {{-- Master Data --}}
    <div class="side-group" x-data="{ open: @js($masterOpen) }" :class="open ? 'border-white/15 bg-white/[0.07]' : ''">
        <button type="button" @click="open = !open" :aria-expanded="open.toString()" class="side-nav-link w-full cursor-pointer text-left">
            <i class="w-4 text-xs text-center fas fa-database"></i>
            <span class="flex-1">Master Data</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-300 ease-out" :class="open ? 'rotate-180' : ''"></i>
        </button>
        <div
            x-cloak
            x-show="open"
            x-transition.opacity.duration.220ms
            x-transition.scale.origin.top.duration.220ms
            class="space-y-1 pb-2 pl-9 pr-2 origin-top"
        >
            <a href="{{ route('admin.master.gedung.index') }}" class="side-sub-link {{ request()->routeIs('admin.master.gedung.*') ? 'active' : '' }}">Gedung</a>
            <a href="{{ route('admin.master.ruangan.index') }}" class="side-sub-link {{ request()->routeIs('admin.master.ruangan.*') ? 'active' : '' }}">Ruangan</a>
            <a href="{{ route('admin.master.kategori-aset.index') }}" class="side-sub-link {{ request()->routeIs('admin.master.kategori-aset.*') ? 'active' : '' }}">Kategori Sarana</a>
            <a href="{{ route('admin.users.index') }}" class="side-sub-link {{ request()->routeIs('admin.users.*') || $feature === 'manajemen-user' ? 'active' : '' }}">Manajemen User</a>
        </div>
    </div>

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
        <span>Buat Pengajuan</span>
    </a>

    <a href="{{ route('admin.realisasi.index') }}" class="side-nav-link {{ request()->routeIs('admin.realisasi.*') || $feature === 'realisasi' ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-tools"></i>
        <span>Realisasi</span>
    </a>

    <a href="{{ route('admin.laporan.index') }}" class="side-nav-link {{ request()->routeIs('admin.laporan.index') || $feature === 'pelaporan' ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-chart-bar"></i>
        <span>Laporan</span>
    </a>

    <a href="{{ route('admin.feature', ['feature' => 'log-aktivitas']) }}" class="side-nav-link {{ $feature === 'log-aktivitas' ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-history"></i>
        <span>Log Aktivitas</span>
    </a>
</nav>
