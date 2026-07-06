<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'role' => trim((string) $request->query('role', '')),
            'status' => trim((string) $request->query('status', '')),
        ];

        $users = User::query()
            ->with('roleRelation')
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($inner) use ($filters) {
                    $inner->where('nama', 'like', "%{$filters['q']}%")
                        ->orWhere('name', 'like', "%{$filters['q']}%")
                        ->orWhere('email', 'like', "%{$filters['q']}%")
                        ->orWhere('nomor_telepon', 'like', "%{$filters['q']}%");
                });
            })
            ->when($filters['role'] !== '', function ($query) use ($filters) {
                $query->where(function ($inner) use ($filters) {
                    $inner->where('role', $filters['role'])
                        ->orWhereHas('roleRelation', fn ($roleQuery) => $roleQuery->where('nama_role', $filters['role']));
                });
            })
            ->when($filters['status'] !== '', fn ($query) => $query->where('status_akun', $filters['status']))
            ->orderBy('nama')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $filters,
            'roles' => Role::query()->orderBy('nama_role')->pluck('nama_role')->all(),
            'statusList' => ['AKTIF', 'NONAKTIF'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'nomor_telepon' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'string', 'exists:roles,nama_role'],
            'status_akun' => ['required', Rule::in(['AKTIF', 'NONAKTIF'])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $role = Role::query()->where('nama_role', $validated['role'])->firstOrFail();

        User::query()->create([
            'name' => $validated['nama'],
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'nomor_telepon' => $validated['nomor_telepon'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'role_id' => $role->id,
            'status_akun' => $validated['status_akun'],
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User baru berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validateWithBag('editUser', [
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'nomor_telepon' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'string', 'exists:roles,nama_role'],
            'status_akun' => ['required', Rule::in(['AKTIF', 'NONAKTIF'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $authUser = $request->user();
        if ($authUser && $authUser->id === $user->id) {
            if ($validated['status_akun'] === 'NONAKTIF') {
                return redirect()->back()->with('error', 'Akun admin yang sedang login tidak bisa dinonaktifkan.');
            }

            if ($validated['role'] !== 'admin') {
                return redirect()->back()->with('error', 'Akun admin yang sedang login tidak bisa diubah rolenya.');
            }
        }

        $role = Role::query()->where('nama_role', $validated['role'])->firstOrFail();

        $payload = [
            'name' => $validated['nama'],
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'nomor_telepon' => $validated['nomor_telepon'] ?? null,
            'role' => $validated['role'],
            'role_id' => $role->id,
            'status_akun' => $validated['status_akun'],
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);

        return redirect()
            ->back()
            ->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $authUser = $request->user();
        if ($authUser && $authUser->id === $user->id) {
            return redirect()
                ->back()
                ->with('error', 'Akun yang sedang login tidak bisa dihapus.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
