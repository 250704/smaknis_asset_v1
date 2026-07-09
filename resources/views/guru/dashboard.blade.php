<x-layouts.sbadmin>
    <div class="mb-6">
        <h1 class="page-title">Dashboard Guru / Staf</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Alur utama: scan sarana, lapor kerusakan jika ada masalah, atau ajukan pengadaan barang baru.</p>
    </div>

    <div class="panel">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Akses Cepat</h2>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('guru.scan') }}" class="btn-primary">Scan QR</a>
            <a href="{{ route('guru.kerusakan.create') }}" class="btn-danger">Lapor Kerusakan</a>
            <a href="{{ route('guru.pengajuan.create') }}" class="btn-secondary">Buat Pengajuan</a>
            <a href="{{ route('guru.pengajuan.index') }}" class="btn-secondary">Pengajuan Saya</a>
            <a href="{{ route('guru.notifikasi.index') }}" class="btn-secondary">Notifikasi</a>
        </div>
        <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Catatan: perawatan/penggantian tidak diajukan manual oleh guru, tetapi otomatis dari hasil validasi laporan kerusakan.</p>
    </div>
</x-layouts.sbadmin>
