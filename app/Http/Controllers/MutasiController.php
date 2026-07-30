<?php

namespace App\Http\Controllers;

use App\Models\Sarana;
use App\Models\Ruangan;
use App\Models\MutasiSarana;
use App\Models\LogAktivitas;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class MutasiController extends Controller
{
    private function getRole(Request $request): string
    {
        $user = $request->user();
        if ($user->hasRole('admin')) {
            return 'admin';
        }
        if ($user->hasRole('kepala_sarana')) {
            return 'kepala_sarana';
        }
        if ($user->hasRole('bendahara')) {
            return 'bendahara';
        }
        if ($user->hasRole('kepala_sekolah')) {
            return 'kepala_sekolah';
        }
        return 'guru';
    }

    public function index(Request $request): View
    {
        $role = $this->getRole($request);
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'gedung_id' => $request->query('gedung_id'),
            'ruangan_id' => $request->query('ruangan_id'),
            'status_mutasi' => $request->query('status_mutasi'),
        ];

        $query = MutasiSarana::query()
            ->with(['sarana.kategori', 'ruanganAsal.gedung', 'ruanganTujuan.gedung', 'userPengaju', 'validator'])
            ->when($filters['q'] !== '', function ($q) use ($filters) {
                $q->whereHas('sarana', function ($sQuery) use ($filters) {
                    $sQuery->where('nama_sarana', 'like', "%{$filters['q']}%")
                           ->orWhere('kode_sarana', 'like', "%{$filters['q']}%");
                });
            })
            ->when($filters['ruangan_id'], function ($q, $ruanganId) {
                $q->where(function ($sub) use ($ruanganId) {
                    $sub->where('ruangan_asal', $ruanganId)
                        ->orWhere('ruangan_tujuan', $ruanganId);
                });
            })
            ->when($filters['gedung_id'], function ($q, $gedungId) {
                $q->whereHas('ruanganAsal', fn ($r) => $r->where('gedung_id', $gedungId))
                  ->orWhereHas('ruanganTujuan', fn ($r) => $r->where('gedung_id', $gedungId));
            })
            ->when($filters['status_mutasi'], fn ($q, $status) => $q->where('status_mutasi', $status))
            ->latest('id');

        if (in_array($role, ['guru', 'bendahara', 'kepala_sekolah'], true)) {
            $query->where('user_pengaju_id', Auth::id());
        }

        $mutasiList = $query->paginate(15)->withQueryString();
        $gedungList = \App\Models\Gedung::query()->orderBy('nama_gedung')->get();
        $ruanganList = Ruangan::query()->with('gedung')->orderBy('nama_ruangan')->get();

        return view('shared.mutasi.index', compact('mutasiList', 'filters', 'gedungList', 'ruanganList', 'role'));
    }

    public function create(Request $request): View
    {
        $role = $this->getRole($request);
        $saranaId = $request->query('sarana_id');
        $sarana = null;

        if ($saranaId) {
            $sarana = Sarana::query()->with('ruangan.gedung')->find($saranaId);
        }

        $kategoriList = \App\Models\KategoriSarana::query()->orderBy('nama_kategori')->get();
        $gedungList = \App\Models\Gedung::query()->orderBy('nama_gedung')->get();
        $ruanganList = Ruangan::query()->with('gedung')->orderBy('nama_ruangan')->get();
        $saranaList = Sarana::query()
            ->with(['kategori', 'ruangan.gedung'])
            ->where('status_sarana', 'AKTIF')
            ->orderBy('nama_sarana')
            ->get();

        return view('shared.mutasi.create', compact('sarana', 'ruanganList', 'saranaList', 'kategoriList', 'gedungList', 'role'));
    }

    public function store(Request $request): RedirectResponse
    {
        $role = $this->getRole($request);

        $rules = [
            'sarana_id' => 'required|exists:sarana,id',
            'ruangan_tujuan' => 'required|exists:ruangan,id',
            'tanggal_mutasi' => 'required|date',
            'keterangan' => 'nullable|string|max:500',
        ];

        if (!in_array($role, ['kepala_sarana', 'kepala_sekolah'], true)) {
            $rules['eksekusi_langsung'] = 'nullable';
        } else {
            $rules['eksekusi_langsung'] = 'required|boolean';
        }

        $request->validate($rules);

        $sarana = Sarana::findOrFail($request->sarana_id);

        if ($sarana->ruangan_id == $request->ruangan_tujuan) {
            return back()->withErrors(['ruangan_tujuan' => 'Ruangan tujuan tidak boleh sama dengan ruangan asal.'])->withInput();
        }

        $eksekusiLangsung = in_array($role, ['kepala_sarana', 'kepala_sekolah'], true) && ($request->boolean('eksekusi_langsung') || !$request->has('eksekusi_langsung'));

        DB::beginTransaction();
        try {
            $mutasi = new MutasiSarana();
            $mutasi->sarana_id = $sarana->id;
            $mutasi->ruangan_asal = $sarana->ruangan_id;
            $mutasi->ruangan_tujuan = $request->ruangan_tujuan;
            $mutasi->user_pengaju_id = Auth::id();
            $mutasi->tanggal_mutasi = $request->tanggal_mutasi;
            $mutasi->keterangan = $request->keterangan;

            if ($eksekusiLangsung) {
                $mutasi->status_mutasi = 'DISETUJUI';
                $mutasi->validated_by = Auth::id();
                $mutasi->save();

                // Ubah ruangan sarana langsung
                $sarana->ruangan_id = $request->ruangan_tujuan;
                $sarana->save();

                // Log aktivitas
                $this->logActivity(
                    $request,
                    'MUTASI_SARANA',
                    'MUTASI_DIRECT',
                    sprintf('Mutasi langsung sarana %s dari ruangan %s ke %s', $sarana->kode_sarana, $mutasi->ruangan_asal, $mutasi->ruangan_tujuan)
                );
            } else {
                $mutasi->status_mutasi = 'DIAJUKAN';
                $mutasi->save();

                // Log aktivitas
                $this->logActivity(
                    $request,
                    'MUTASI_SARANA',
                    'MUTASI_PROPOSE',
                    sprintf('Mengajukan usulan mutasi sarana %s dari ruangan %s ke %s', $sarana->kode_sarana, $mutasi->ruangan_asal, $mutasi->ruangan_tujuan)
                );

                // Kirim notifikasi ke Kepala Sarana & Admin
                $this->notifyReviewers(
                    'Usulan Mutasi Baru',
                    sprintf('User %s mengajukan usulan mutasi untuk %s.', Auth::user()->display_name, $sarana->nama_sarana)
                );
            }

            DB::commit();

            $msg = $eksekusiLangsung ? 'Mutasi sarana berhasil dieksekusi.' : 'Usulan mutasi sarana berhasil diajukan.';
            return redirect()->route($role . '.mutasi.index')->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Request $request, $id): View
    {
        $role = $this->getRole($request);
        $mutasi = MutasiSarana::query()
            ->with(['sarana.kategori', 'ruanganAsal.gedung', 'ruanganTujuan.gedung', 'userPengaju', 'validator'])
            ->findOrFail($id);

        if (in_array($role, ['guru', 'bendahara', 'kepala_sekolah'], true) && $mutasi->user_pengaju_id !== Auth::id()) {
            abort(403);
        }

        return view('shared.mutasi.show', compact('mutasi', 'role'));
    }

    public function approve(Request $request, $id): RedirectResponse
    {
        $role = $this->getRole($request);
        if ($role !== 'kepala_sarana') {
            abort(403, 'Hanya Kepala Sarana yang berwenang menyetujui usulan mutasi.');
        }

        $mutasi = MutasiSarana::findOrFail($id);
        if ($mutasi->status_mutasi !== 'DIAJUKAN') {
            return back()->with('error', 'Usulan mutasi ini sudah diproses sebelumnya.');
        }

        $sarana = Sarana::findOrFail($mutasi->sarana_id);

        DB::beginTransaction();
        try {
            $mutasi->status_mutasi = 'DISETUJUI';
            $mutasi->validated_by = Auth::id();
            $mutasi->tanggal_mutasi = now()->toDateString();
            $mutasi->save();

            // Ubah ruangan sarana
            $sarana->ruangan_id = $mutasi->ruangan_tujuan;
            $sarana->save();

            // Log aktivitas
            $this->logActivity(
                $request,
                'MUTASI_SARANA',
                'MUTASI_APPROVED',
                sprintf('Menyetujui mutasi sarana %s ke ruangan %s', $sarana->kode_sarana, $mutasi->ruangan_tujuan)
            );

            // Notifikasi ke pengaju
            $pengajuRoleCode = (string) ($mutasi->userPengaju?->role_code ?? 'guru');
            Notifikasi::create([
                'user_id' => $mutasi->user_pengaju_id,
                'judul' => 'Usulan Mutasi Disetujui',
                'isi' => sprintf('Usulan mutasi untuk sarana %s telah disetujui oleh %s.', $sarana->nama_sarana, Auth::user()->display_name),
                'url' => route($pengajuRoleCode . '.mutasi.index'),
                'is_read' => false,
            ]);

            DB::commit();
            return redirect()->route($role . '.mutasi.index')->with('success', 'Usulan mutasi berhasil disetujui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id): RedirectResponse
    {
        $role = $this->getRole($request);
        if ($role !== 'kepala_sarana') {
            abort(403, 'Hanya Kepala Sarana yang berwenang menolak usulan mutasi.');
        }

        $request->validate([
            'catatan_penolakan' => 'required|string|max:255',
        ]);

        $mutasi = MutasiSarana::findOrFail($id);
        if ($mutasi->status_mutasi !== 'DIAJUKAN') {
            return back()->with('error', 'Usulan mutasi ini sudah diproses sebelumnya.');
        }

        $sarana = Sarana::findOrFail($mutasi->sarana_id);

        DB::beginTransaction();
        try {
            $mutasi->status_mutasi = 'DITOLAK';
            $mutasi->validated_by = Auth::id();
            $mutasi->keterangan = trim($mutasi->keterangan . "\n\n[Catatan Penolakan: " . $request->catatan_penolakan . "]");
            $mutasi->save();

            // Log aktivitas
            $this->logActivity(
                $request,
                'MUTASI_SARANA',
                'MUTASI_REJECTED',
                sprintf('Menolak usulan mutasi sarana %s dengan catatan: %s', $sarana->kode_sarana, $request->catatan_penolakan)
            );

            // Notifikasi ke pengaju
            $pengajuRoleCode = (string) ($mutasi->userPengaju?->role_code ?? 'guru');
            Notifikasi::create([
                'user_id' => $mutasi->user_pengaju_id,
                'judul' => 'Usulan Mutasi Ditolak',
                'isi' => sprintf('Usulan mutasi untuk sarana %s ditolak oleh %s. Catatan: %s', $sarana->nama_sarana, Auth::user()->display_name, $request->catatan_penolakan),
                'url' => route($pengajuRoleCode . '.mutasi.index'),
                'is_read' => false,
            ]);

            DB::commit();
            return redirect()->route($role . '.mutasi.index')->with('success', 'Usulan mutasi berhasil ditolak.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function logActivity(Request $request, string $aktivitas, string $modul, string $deskripsi): void
    {
        LogAktivitas::create([
            'user_id' => Auth::id(),
            'aktivitas' => $aktivitas,
            'modul' => $modul,
            'deskripsi' => $deskripsi,
            'ip_address' => $request->ip(),
        ]);
    }

    private function notifyReviewers(string $judul, string $isi): void
    {
        // Cari user yang memiliki role kepala sarana saja
        $reviewers = User::whereHas('roleRelation', function ($query) {
            $query->where('nama_role', 'kepala_sarana');
        })->get();

        foreach ($reviewers as $reviewer) {
            Notifikasi::create([
                'user_id' => $reviewer->id,
                'judul' => $judul,
                'isi' => $isi,
                'url' => route('kepala_sarana.mutasi.index'),
                'is_read' => false,
            ]);
        }
    }
}
