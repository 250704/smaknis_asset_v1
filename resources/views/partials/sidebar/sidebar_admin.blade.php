@php
    $feature = request()->route('feature');
    $inventarisOpen = request()->routeIs('admin.sarana.*', 'admin.cetak-qr.*', 'admin.mutasi.*') || in_array($feature, ['data-sarana', 'tambah-sarana', 'cetak-qr', 'mutasi-sarana'], true);
    $masterOpen = request()->routeIs('admin.master.*', 'admin.users.*') || in_array($feature, ['manajemen-user'], true);
    $pengajuanOpen = request()->routeIs(
        'admin.pengajuan.*',
        'admin.realisasi.*'
    ) || in_array($feature, ['semua-pengajuan', 'realisasi'], true);
@endphp

<nav class="space-y-1">
    <a href="{{ route('admin.dashboard') }}" class="side-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-home"></i>
        <span>Dashboard</span>
    </a>

    {{-- Kelola Sarana --}}
    <div class="side-group" x-data="{ open: @js($inventarisOpen) }" :class="open ? 'border-white/15 bg-white/[0.07]' : ''">
        <button type="button" @click="open = !open" :aria-expanded="open.toString()" class="w-full text-left cursor-pointer side-nav-link">
            <i class="w-4 text-xs text-center fas fa-warehouse"></i>
            <span class="flex-1">Kelola Sarana</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-300 ease-out" :class="open ? 'rotate-180' : ''"></i>
        </button>
        <div
            x-cloak
            x-show="open"
            x-transition.opacity.duration.220ms
            x-transition.scale.origin.top.duration.220ms
            class="pb-2 pr-2 space-y-1 origin-top pl-9"
        >
            <a href="{{ route('admin.sarana.index') }}" class="side-sub-link {{ request()->routeIs('admin.sarana.index', 'admin.sarana.show', 'admin.sarana.edit') || $feature === 'data-sarana' ? 'active' : '' }}">Data Sarana</a>
            <a href="{{ route('admin.sarana.create') }}" class="side-sub-link {{ request()->routeIs('admin.sarana.create') || $feature === 'tambah-sarana' ? 'active' : '' }}">Tambah Sarana</a>
            <a href="{{ route('admin.mutasi.index') }}" class="side-sub-link {{ request()->routeIs('admin.mutasi.*') || $feature === 'mutasi-sarana' ? 'active' : '' }}">Mutasi Sarana</a>
            <a href="{{ route('admin.cetak-qr.index') }}" class="side-sub-link {{ request()->routeIs('admin.cetak-qr.*') || $feature === 'cetak-qr' ? 'active' : '' }}">Cetak QR Code</a>
        </div>
    </div>

    {{-- Master Data --}}
    <div class="side-group" x-data="{ open: @js($masterOpen) }" :class="open ? 'border-white/15 bg-white/[0.07]' : ''">
        <button type="button" @click="open = !open" :aria-expanded="open.toString()" class="w-full text-left cursor-pointer side-nav-link">
            <i class="w-4 text-xs text-center fas fa-database"></i>
            <span class="flex-1">Master Data</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-300 ease-out" :class="open ? 'rotate-180' : ''"></i>
        </button>
        <div
            x-cloak
            x-show="open"
            x-transition.opacity.duration.220ms
            x-transition.scale.origin.top.duration.220ms
            class="pb-2 pr-2 space-y-1 origin-top pl-9"
        >
            <a href="{{ route('admin.master.gedung.index') }}" class="side-sub-link {{ request()->routeIs('admin.master.gedung.*') ? 'active' : '' }}">Gedung</a>
            <a href="{{ route('admin.master.ruangan.index') }}" class="side-sub-link {{ request()->routeIs('admin.master.ruangan.*') ? 'active' : '' }}">Ruangan</a>
            <a href="{{ route('admin.master.kategori-sarana.index') }}" class="side-sub-link {{ request()->routeIs('admin.master.kategori-sarana.*') ? 'active' : '' }}">Kategori Sarana</a>
            <a href="{{ route('admin.users.index') }}" class="side-sub-link {{ request()->routeIs('admin.users.*') || $feature === 'manajemen-user' ? 'active' : '' }}">Manajemen User</a>
        </div>
    </div>

    {{-- Pengajuan --}}
    <div class="side-group" x-data="{ open: @js($pengajuanOpen) }" :class="open ? 'border-white/15 bg-white/[0.07]' : ''">
        <button type="button" @click="open = !open" :aria-expanded="open.toString()" class="w-full text-left cursor-pointer side-nav-link">
            <i class="w-4 text-xs text-center fas fa-file-signature"></i>
            <span class="flex-1">Pengajuan</span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-300 ease-out" :class="open ? 'rotate-180' : ''"></i>
        </button>
        <div
            x-cloak
            x-show="open"
            x-transition.opacity.duration.220ms
            x-transition.scale.origin.top.duration.220ms
            class="pb-2 pr-2 space-y-1 origin-top pl-9"
        >
            <a href="{{ route('admin.pengajuan.create') }}" class="side-sub-link {{ request()->routeIs('admin.pengajuan.create') ? 'active' : '' }}">Buat Pengajuan</a>
            <a href="{{ route('admin.realisasi.index') }}" class="side-sub-link {{ request()->routeIs('admin.realisasi.*') || $feature === 'realisasi' ? 'active' : '' }}">Realisasi</a>
            <a href="{{ route('admin.pengajuan.index') }}" class="side-sub-link {{ request()->routeIs('admin.pengajuan.index', 'admin.pengajuan.show') || $feature === 'semua-pengajuan' ? 'active' : '' }}">Semua Pengajuan</a>
            <a href="{{ route('admin.pengajuan.mine') }}" class="side-sub-link {{ request()->routeIs('admin.pengajuan.mine') ? 'active' : '' }}">Pengajuan Saya</a>
            <a href="{{ route('admin.mutasi.index') }}" class="side-sub-link {{ request()->routeIs('admin.mutasi.*') || $feature === 'mutasi-sarana' ? 'active' : '' }}">Mutasi Sarana</a>
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

    <a href="{{ route('admin.laporan.index') }}" class="side-nav-link {{ request()->routeIs('admin.laporan.index') || $feature === 'pelaporan' ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-chart-bar"></i>
        <span>Laporan</span>
    </a>

    <a href="{{ route('admin.log-aktivitas.index') }}" class="side-nav-link {{ request()->routeIs('admin.log-aktivitas.*') || $feature === 'log-aktivitas' ? 'active' : '' }}">
        <i class="w-4 text-xs text-center fas fa-history"></i>
        <span>Log Aktivitas</span>
    </a>
</nav>
