<x-layouts.sbadmin>
    <div class="mb-6">
        <h1 class="page-title">Dashboard Guru / Staf</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Mulai dari scan QR, lalu ajukan proses yang dibutuhkan.</p>
    </div>

    <div class="panel">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Akses Cepat</h2>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('guru.scan') }}" class="btn-primary">Scan QR</a>
            <a href="{{ route('guru.feature', ['feature' => 'buat-pengajuan']) }}" class="btn-secondary">Buat Pengajuan</a>
            <a href="{{ route('guru.feature', ['feature' => 'riwayat-pengajuan']) }}" class="btn-secondary">Riwayat Pengajuan</a>
            <a href="{{ route('guru.feature', ['feature' => 'notifikasi']) }}" class="btn-secondary">Notifikasi</a>
        </div>
        <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Alur utama guru/staf: scan aset lalu ajukan tindakan sesuai kondisi.</p>
    </div>
</x-layouts.sbadmin>


