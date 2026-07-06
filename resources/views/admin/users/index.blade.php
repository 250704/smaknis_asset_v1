<x-layouts.sbadmin>
    @php
        $hasAddErrors = $errors->any();
    @endphp

    <div x-data="{ addOpen: @js($hasAddErrors) }" class="space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="page-title">Manajemen User</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Kelola akun, role, status akun, dan reset password user sistem.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-200">
                    {{ number_format($users->total()) }} user
                </span>
                <button type="button" @click="addOpen = !addOpen" class="btn-primary">
                    <i class="fas fa-plus mr-2 text-xs"></i>
                    Tambah User
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
                {{ session('error') }}
            </div>
        @endif

        <section
            x-cloak
            x-show="addOpen"
            x-transition.opacity.duration.220ms
            x-transition.scale.origin.top.duration.220ms
            class="panel"
        >
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-100">Tambah User Baru</h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Isi data akun baru, lalu simpan untuk menambahkan user ke sistem.</p>
                </div>
                <button type="button" @click="addOpen = false" class="btn-secondary btn-sm">Tutup</button>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="mt-5 grid gap-4 lg:grid-cols-12">
                @csrf
                <div class="lg:col-span-4">
                    <label class="filter-label" for="nama">Nama</label>
                    <input
                        id="nama"
                        type="text"
                        name="nama"
                        value="{{ old('nama') }}"
                        placeholder="Nama lengkap"
                        class="filter-control"
                        required
                    >
                </div>
                <div class="lg:col-span-4">
                    <label class="filter-label" for="email">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="nama@email.com"
                        class="filter-control"
                        required
                    >
                </div>
                <div class="lg:col-span-4">
                    <label class="filter-label" for="nomor_telepon">Nomor Telepon</label>
                    <input
                        id="nomor_telepon"
                        type="text"
                        name="nomor_telepon"
                        value="{{ old('nomor_telepon') }}"
                        placeholder="contoh: 081234567890"
                        class="filter-control"
                    >
                </div>
                <div class="lg:col-span-3">
                    <label class="filter-label" for="role">Role</label>
                    <select id="role" name="role" class="filter-control" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected(old('role') === $role)>{{ strtoupper(str_replace('_', ' ', $role)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-3">
                    <label class="filter-label" for="status_akun">Status</label>
                    <select id="status_akun" name="status_akun" class="filter-control" required>
                        @foreach ($statusList as $status)
                            <option value="{{ $status }}" @selected(old('status_akun', 'AKTIF') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-3">
                    <label class="filter-label" for="password">Password</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="Minimal 8 karakter"
                        class="filter-control"
                        required
                    >
                </div>
                <div class="lg:col-span-3">
                    <label class="filter-label" for="password_confirmation">Konfirmasi Password</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        placeholder="Ulangi password"
                        class="filter-control"
                        required
                    >
                </div>
                <div class="lg:col-span-12 flex flex-wrap items-center justify-end gap-2">
                    <button type="button" @click="addOpen = false" class="filter-reset">Batal</button>
                    <button type="submit" class="filter-submit">
                        <i class="fas fa-plus text-xs"></i>Simpan User
                    </button>
                </div>
            </form>
        </section>

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="panel">
            <form method="GET" class="filter-grid">
                <div>
                    <label class="filter-label" for="q">Pencarian</label>
                    <input
                        type="text"
                        id="q"
                        name="q"
                        value="{{ $filters['q'] }}"
                        placeholder="Nama / email..."
                        class="filter-control"
                    >
                </div>
                <div>
                    <label class="filter-label" for="role_filter">Role</label>
                    <select id="role_filter" name="role" class="filter-control">
                        <option value="">Semua role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected($filters['role'] === $role)>{{ strtoupper(str_replace('_', ' ', $role)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="filter-label" for="status_filter">Status</label>
                    <select id="status_filter" name="status" class="filter-control">
                        <option value="">Semua status</option>
                        @foreach ($statusList as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-actions">
                    <a href="{{ route('admin.users.index') }}" class="filter-reset">Reset</a>
                    <button type="submit" class="filter-submit">
                        <i class="fas fa-filter text-xs"></i>Filter
                    </button>
                </div>
            </form>
        </section>

        <section class="panel overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] divide-y divide-slate-200 text-sm dark:divide-white/10">
                    <thead class="bg-slate-50 dark:bg-white/[0.04]">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">User</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">No. WA</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Role</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @forelse ($users as $item)
                            <tr class="bg-white/70 transition hover:bg-blue-50/60 dark:bg-transparent dark:hover:bg-cyan-500/10">
                                <td class="px-4 py-3 align-top font-medium text-slate-700 dark:text-slate-200">{{ $item->display_name }}</td>
                                <td class="px-4 py-3 align-top text-slate-600 dark:text-slate-300">{{ $item->email }}</td>
                                <td class="px-4 py-3 align-top text-slate-600 dark:text-slate-300">{{ $item->nomor_telepon ?: '-' }}</td>
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
                                        <div
                                            x-data="{
                                            editOpen: @js($errors->editUser->any() && (string) old('_edit_user_id') === (string) $item->id),
                                        }"
                                    >
                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" @click="editOpen = true" class="btn-secondary btn-sm">Edit User</button>
                                            <form
                                                method="POST"
                                                action="{{ route('admin.users.destroy', $item) }}"
                                                data-confirm-title="Konfirmasi Hapus User"
                                                data-confirm-message="Apakah Anda yakin ingin menghapus user ini? Data user akan dihapus dari daftar aktif."
                                                data-confirm-confirm-label="Ya, Hapus"
                                                data-confirm-variant="danger"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-danger btn-sm">Hapus</button>
                                            </form>
                                        </div>

                                        <div
                                            x-show="editOpen"
                                            x-cloak
                                            @keydown.escape.window="editOpen = false"
                                            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                                            style="display: none;"
                                        >
                                            <div
                                                x-show="editOpen"
                                                x-transition:enter="ease-out duration-200"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                x-transition:leave="ease-in duration-150"
                                                x-transition:leave-start="opacity-100 scale-100"
                                                x-transition:leave-end="opacity-0 scale-95"
                                                @click.outside="editOpen = false"
                                                class="w-full max-w-4xl p-8 bg-white border shadow-2xl rounded-2xl border-slate-200 dark:border-white/10 dark:bg-slate-900"
                                            >
                                                <div class="flex items-center justify-between mb-6">
                                                    <div>
                                                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit User</h3>
                                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Perbarui data akun, role, status, atau password user dari satu form.</p>
                                                    </div>
                                                    <button type="button" @click="editOpen = false" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-white/10 dark:hover:text-slate-300">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>

                                                @if ($errors->editUser->any())
                                                    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
                                                        <ul class="list-disc space-y-1 pl-5">
                                                            @foreach ($errors->editUser->all() as $error)
                                                                <li>{{ $error }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif

                                                <form method="POST" action="{{ route('admin.users.update', $item) }}" class="grid gap-5 lg:grid-cols-12">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="_edit_user_id" value="{{ $item->id }}">
                                                    <div class="lg:col-span-4">
                                                        <label class="filter-label" for="nama-{{ $item->id }}">Nama</label>
                                                        <input
                                                            id="nama-{{ $item->id }}"
                                                            type="text"
                                                            name="nama"
                                                            value="{{ old('nama', $item->display_name) }}"
                                                            placeholder="Nama lengkap"
                                                            class="filter-control"
                                                            required
                                                        >
                                                    </div>
                                                    <div class="lg:col-span-4">
                                                        <label class="filter-label" for="email-{{ $item->id }}">Email</label>
                                                        <input
                                                            id="email-{{ $item->id }}"
                                                            type="email"
                                                            name="email"
                                                            value="{{ old('email', $item->email) }}"
                                                            placeholder="nama@email.com"
                                                            class="filter-control"
                                                            required
                                                        >
                                                    </div>
                                                    <div class="lg:col-span-4">
                                                        <label class="filter-label" for="nomor_telepon-{{ $item->id }}">Nomor Telepon</label>
                                                        <input
                                                            id="nomor_telepon-{{ $item->id }}"
                                                            type="text"
                                                            name="nomor_telepon"
                                                            value="{{ old('nomor_telepon', $item->nomor_telepon) }}"
                                                            placeholder="contoh: 081234567890"
                                                            class="filter-control"
                                                        >
                                                    </div>
                                                    <div class="lg:col-span-3">
                                                        <label class="filter-label" for="role-{{ $item->id }}">Role</label>
                                                        <select id="role-{{ $item->id }}" name="role" class="filter-control" required>
                                                            @foreach ($roles as $role)
                                                                <option value="{{ $role }}" @selected(old('role', $item->role_code) === $role)>{{ strtoupper(str_replace('_', ' ', $role)) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="lg:col-span-3">
                                                        <label class="filter-label" for="status_akun-{{ $item->id }}">Status</label>
                                                        <select id="status_akun-{{ $item->id }}" name="status_akun" class="filter-control" required>
                                                            @foreach ($statusList as $status)
                                                                <option value="{{ $status }}" @selected(old('status_akun', $item->status_akun ?? 'AKTIF') === $status)>{{ $status }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="lg:col-span-3">
                                                        <label class="filter-label" for="password-{{ $item->id }}">Password</label>
                                                        <input
                                                            id="password-{{ $item->id }}"
                                                            type="password"
                                                            name="password"
                                                            placeholder="Minimal 8 karakter"
                                                            class="filter-control"
                                                        >
                                                    </div>
                                                    <div class="lg:col-span-3">
                                                        <label class="filter-label" for="password_confirmation-{{ $item->id }}">Konfirmasi Password</label>
                                                        <input
                                                            id="password_confirmation-{{ $item->id }}"
                                                            type="password"
                                                            name="password_confirmation"
                                                            placeholder="Ulangi password"
                                                            class="filter-control"
                                                        >
                                                    </div>

                                                    <div class="lg:col-span-12 flex flex-wrap items-center justify-end gap-2 pt-1">
                                                        <button type="button" @click="editOpen = false" class="filter-reset">Batal</button>
                                                        <button type="submit" class="filter-submit">
                                                            <i class="fas fa-save text-xs"></i>Simpan Perubahan
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">Belum ada data user.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>
</x-layouts.sbadmin>
