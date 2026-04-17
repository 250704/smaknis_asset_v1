<x-layouts.sbadmin>
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="page-title">Manajemen User</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kelola akun, role, status akun, dan reset password user sistem.</p>
        </div>
        <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-200">
            {{ number_format($users->total()) }} user
        </span>
    </div>

    <section class="grid gap-4 xl:grid-cols-3">
        <div class="panel xl:col-span-1">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-100">Tambah User Baru</h2>
            <form method="POST" action="{{ route('admin.users.store') }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Nama</label>
                    <input type="text" name="nama" value="{{ old('nama') }}" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Role</label>
                        <select name="role" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" @selected(old('role') === $role)>{{ strtoupper(str_replace('_', ' ', $role)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</label>
                        <select name="status_akun" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                            @foreach ($statusList as $status)
                                <option value="{{ $status }}" @selected(old('status_akun', 'AKTIF') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Password</label>
                    <input type="password" name="password" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100" required>
                </div>
                <button type="submit" class="btn-primary w-full justify-center">Simpan User</button>
            </form>
        </div>

        <div class="panel xl:col-span-2">
            <details class="group" open>
                <summary class="flex cursor-pointer list-none items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-100">Filter User</h2>
                    <i class="fas fa-chevron-down text-xs text-slate-500 transition-transform group-open:rotate-180"></i>
                </summary>
                <form method="GET" class="mt-3 grid gap-3 md:grid-cols-12">
                    <div class="md:col-span-6">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Pencarian</label>
                        <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Nama / email..." class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                    </div>
                    <div class="md:col-span-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Role</label>
                        <select name="role" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                            <option value="">Semua role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" @selected($filters['role'] === $role)>{{ strtoupper(str_replace('_', ' ', $role)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</label>
                        <select name="status" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60 dark:text-slate-100">
                            <option value="">Semua status</option>
                            @foreach ($statusList as $status)
                                <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-12 flex justify-end gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn-secondary">Reset</a>
                        <button type="submit" class="btn-primary">Terapkan</button>
                    </div>
                </form>
            </details>
        </div>
    </section>

    @if ($errors->any())
        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <section class="mt-5">
        <div class="panel overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] divide-y divide-slate-200 text-sm dark:divide-white/10">
                    <thead class="bg-slate-50 dark:bg-white/[0.04]">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">User</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Role</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @forelse ($users as $item)
                            <tr class="bg-white/70 dark:bg-transparent">
                                <td class="px-4 py-3 align-top font-medium text-slate-700 dark:text-slate-200">{{ $item->display_name }}</td>
                                <td class="px-4 py-3 align-top text-slate-600 dark:text-slate-300">{{ $item->email }}</td>
                                <td class="px-4 py-3 align-top text-slate-600 dark:text-slate-300">{{ strtoupper(str_replace('_', ' ', (string) $item->role_code)) }}</td>
                                <td class="px-4 py-3 align-top">
                                    @php
                                        $isAktif = ($item->status_akun ?? 'AKTIF') === 'AKTIF';
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $isAktif ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200' : 'bg-slate-200 text-slate-700 dark:bg-slate-500/30 dark:text-slate-200' }}">
                                        {{ $item->status_akun ?? 'AKTIF' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <div class="flex flex-wrap gap-2">
                                        <details class="group">
                                            <summary class="btn-secondary btn-sm cursor-pointer list-none">Edit User</summary>
                                            <form method="POST" action="{{ route('admin.users.update', $item) }}" class="mt-2 w-72 space-y-2 rounded-xl border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-slate-900/70">
                                                @csrf
                                                @method('PATCH')
                                                <input type="text" name="nama" value="{{ $item->display_name }}" placeholder="Nama lengkap" class="w-full rounded-lg border-slate-300 bg-white text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60" required>
                                                <input type="email" name="email" value="{{ $item->email }}" placeholder="Email" class="w-full rounded-lg border-slate-300 bg-white text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60" required>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <select name="role" class="w-full rounded-lg border-slate-300 bg-white text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60">
                                                        @foreach ($roles as $role)
                                                            <option value="{{ $role }}" @selected($item->role_code === $role)>{{ strtoupper(str_replace('_', ' ', $role)) }}</option>
                                                        @endforeach
                                                    </select>
                                                    <select name="status_akun" class="w-full rounded-lg border-slate-300 bg-white text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60">
                                                        @foreach ($statusList as $status)
                                                            <option value="{{ $status }}" @selected(($item->status_akun ?? 'AKTIF') === $status)>{{ $status }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn-primary btn-sm w-full justify-center">Simpan Perubahan</button>
                                            </form>
                                        </details>
                                        <details class="group">
                                            <summary class="btn-primary btn-sm cursor-pointer list-none">Reset Password</summary>
                                            <form method="POST" action="{{ route('admin.users.reset-password', $item) }}" class="mt-2 w-72 space-y-2 rounded-xl border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-slate-900/70">
                                                @csrf
                                                <input type="password" name="password" placeholder="Password baru (min 8 karakter)" class="w-full rounded-lg border-slate-300 bg-white text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60" required>
                                                <input type="password" name="password_confirmation" placeholder="Konfirmasi password" class="w-full rounded-lg border-slate-300 bg-white text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500/30 dark:border-white/15 dark:bg-slate-900/60" required>
                                                <button type="submit" class="btn-primary btn-sm w-full justify-center">Update Password</button>
                                            </form>
                                        </details>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">Belum ada data user.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </section>
</x-layouts.sbadmin>
