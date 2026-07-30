<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use App\Models\Notifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    private const READ_RETENTION_DAYS = 30;
    private const MAX_STORED_PER_USER = 200;

    public function index(Request $request): View
    {
        $user = $request->user();
        if ($user) {
            $this->normalizeLegacyNotificationUrls($user->id, (string) ($user->role_code ?? ''));
            $this->collapseDuplicateTrackingThreads($user->id);
            $this->pruneNotifications($user->id);
            \Illuminate\Support\Facades\Cache::forget('notif_unread_count:' . $user->id);
        }

        $notifikasi = $user
            ? $user->notifikasi()->latest()->paginate(12)->withQueryString()
            : collect();

        $unreadCount = $user
            ? $user->notifikasi()->where('is_read', false)->count()
            : 0;

        return view('shared.notifikasi.index', [
            'notifikasi' => $notifikasi,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function markRead(Request $request, Notifikasi $notifikasi): RedirectResponse
    {
        $user = $request->user();
        if (!$user || $notifikasi->user_id !== $user->id) {
            abort(403);
        }

        $targetUrl = $notifikasi->url;
        if (
            (string) ($user->role_code ?? '') === 'bendahara'
            && is_string($targetUrl)
            && str_contains($targetUrl, '/bendahara/kerusakan/create')
        ) {
            $targetUrl = route('bendahara.pengajuan.approval');
            $notifikasi->url = $targetUrl;
        }

        if (
            (string) ($user->role_code ?? '') === 'kepala_sekolah'
            && is_string($targetUrl)
            && preg_match('#/kepala_sekolah/pengajuan/\\d+$#', $targetUrl) === 1
            && str_contains((string) $notifikasi->isi, 'Menunggu Approval Kepala Sekolah')
        ) {
            $targetUrl = route('kepala_sekolah.pengajuan.index');
            $notifikasi->url = $targetUrl;
        }

        if (
            (string) ($user->role_code ?? '') === 'admin'
            && is_string($targetUrl)
            && preg_match('#/admin/pengajuan/\\d+$#', $targetUrl) === 1
        ) {
            $targetUrl = $this->resolveAdminNotificationUrl($notifikasi);
            $notifikasi->url = $targetUrl;
        }

        if (is_string($targetUrl) && preg_match('#/pengajuan/(\d+)#', $targetUrl, $matches) === 1) {
            $pengajuanId = (int) $matches[1];
            if (!\App\Models\Pengajuan::query()->where('id', $pengajuanId)->exists()) {
                $roleCode = (string) ($user->role_code ?? 'guru');
                $fallbackRoute = match ($roleCode) {
                    'admin' => route('admin.pengajuan.index'),
                    'kepala_sarana' => route('kepala_sarana.pengajuan.index'),
                    'bendahara' => route('bendahara.pengajuan.approval'),
                    'kepala_sekolah' => route('kepala_sekolah.pengajuan.index'),
                    default => route('guru.pengajuan.index'),
                };

                $notifikasi->update(['is_read' => true, 'url' => $fallbackRoute]);
                \Illuminate\Support\Facades\Cache::forget('notif_unread_count:' . $user->id);

                return redirect($fallbackRoute)->with('info', 'Data pengajuan terkait pada notifikasi ini sudah tidak tersedia atau telah dihapus.');
            }
        }

        $notifikasi->update(['is_read' => true]);
        \Illuminate\Support\Facades\Cache::forget('notif_unread_count:' . $user->id);

        if ($targetUrl) {
            return redirect($targetUrl);
        }

        return redirect()->back();
    }

    public function markAll(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user) {
            $user->notifikasi()->where('is_read', false)->update(['is_read' => true]);
            \Illuminate\Support\Facades\Cache::forget('notif_unread_count:' . $user->id);
            $this->pruneNotifications($user->id);
        }

        return redirect()->back();
    }

    private function pruneNotifications(int $userId): void
    {
        Notifikasi::query()
            ->where('user_id', $userId)
            ->where('is_read', true)
            ->where('created_at', '<', now()->subDays(self::READ_RETENTION_DAYS))
            ->delete();

        $keepIds = Notifikasi::query()
            ->where('user_id', $userId)
            ->latest('id')
            ->limit(self::MAX_STORED_PER_USER)
            ->pluck('id')
            ->all();

        if ($keepIds === []) {
            return;
        }

        Notifikasi::query()
            ->where('user_id', $userId)
            ->where('is_read', true)
            ->whereNotIn('id', $keepIds, 'and')
            ->delete();
    }

    private function normalizeLegacyNotificationUrls(int $userId, string $roleCode): void
    {
        $mutasiUrl = match ($roleCode) {
            'admin' => route('admin.mutasi.index'),
            'kepala_sarana' => route('kepala_sarana.mutasi.index'),
            'bendahara' => route('bendahara.mutasi.index'),
            'kepala_sekolah' => route('kepala_sekolah.mutasi.index'),
            default => route('guru.mutasi.index'),
        };

        Notifikasi::query()
            ->where('user_id', $userId)
            ->where('judul', 'like', '%Mutasi%')
            ->whereNull('url')
            ->update([
                'url' => $mutasiUrl,
            ]);

        if ($roleCode === 'bendahara') {
            Notifikasi::query()
                ->where('user_id', $userId)
                ->where('url', 'like', '%/bendahara/kerusakan/create%')
                ->update([
                    'url' => route('bendahara.pengajuan.approval'),
                ]);
        }

        if ($roleCode === 'admin') {
            Notifikasi::query()
                ->where('user_id', $userId)
                ->where('url', 'like', '%/admin/pengajuan/%')
                ->get()
                ->each(function (Notifikasi $notifikasi) {
                    $notifikasi->update([
                        'url' => $this->resolveAdminNotificationUrl($notifikasi),
                    ]);
                });
        }

        if ($roleCode === 'kepala_sekolah') {
            Notifikasi::query()
                ->where('user_id', $userId)
                ->where('url', 'like', '%/kepala_sekolah/pengajuan/%')
                ->where('isi', 'like', '%Menunggu Approval Kepala Sekolah%')
                ->update([
                    'url' => route('kepala_sekolah.pengajuan.index'),
                ]);
        }
    }

    private function resolveAdminNotificationUrl(Notifikasi $notifikasi): string
    {
        $isi = (string) $notifikasi->isi;
        $judul = (string) $notifikasi->judul;

        if (
            str_contains($judul, 'Realisasi')
            || str_contains($isi, 'Realisasi Diproses')
            || str_contains($isi, 'Menunggu Verifikasi Teknis')
            || str_contains($isi, 'Menunggu Verifikasi Keuangan')
            || str_contains($isi, 'Selesai')
        ) {
            return route('admin.realisasi.index');
        }

        return route('admin.pengajuan.index');
    }

    private function collapseDuplicateTrackingThreads(int $userId): void
    {
        $items = Notifikasi::query()
            ->where('user_id', $userId)
            ->where(function ($query) {
                $query->where('judul', 'like', '%Tracking Pengajuan%')
                    ->orWhere('judul', 'like', '%Tracking Kerusakan%');
            })
            ->latest('id')
            ->get(['id', 'judul', 'isi']);

        $latestByThread = [];
        $pengajuanBySarana = [];
        $deleteIds = [];

        foreach ($items as $item) {
            $threadKey = $this->extractTrackingThreadKey((string) $item->judul, (string) $item->isi);
            if (!$threadKey) {
                continue;
            }

            if (isset($latestByThread[$threadKey])) {
                $deleteIds[] = (int) $item->id;
                continue;
            }

            $latestByThread[$threadKey] = (int) $item->id;
            if (str_starts_with($threadKey, 'pengajuan:')) {
                $kodeSarana = strtoupper((string) substr($threadKey, strlen('pengajuan:')));
                if ($kodeSarana !== '') {
                    $pengajuanBySarana[$kodeSarana] = true;
                }
            }
        }

        if ($pengajuanBySarana === []) {
            if ($deleteIds !== []) {
                Notifikasi::query()
                    ->where('user_id', $userId)
                    ->whereIn('id', array_values(array_unique($deleteIds)), 'and', false)
                    ->delete();
            }
            return;
        }

        foreach ($items as $item) {
            $threadKey = $this->extractTrackingThreadKey((string) $item->judul, (string) $item->isi);
            if (!$threadKey || !str_starts_with($threadKey, 'kerusakan:')) {
                continue;
            }

            $kodeSarana = strtoupper((string) substr($threadKey, strlen('kerusakan:')));
            if ($kodeSarana !== '' && isset($pengajuanBySarana[$kodeSarana])) {
                $deleteIds[] = (int) $item->id;
            }
        }

        if ($deleteIds === []) {
            return;
        }

        Notifikasi::query()
            ->where('user_id', $userId)
            ->whereIn('id', array_values(array_unique($deleteIds)), 'and', false)
            ->delete();
    }

    private function extractTrackingThreadKey(string $judul, string $isi): ?string
    {
        if (str_contains($judul, 'Tracking Pengajuan')) {
            $pengajuanKey = $this->extractTrackingPengajuanKey($isi);
            if ($pengajuanKey !== null) {
                return 'pengajuan:' . strtoupper($pengajuanKey);
            }
        }

        if (str_contains($judul, 'Tracking Kerusakan')) {
            $kodeSarana = $this->extractSaranaCode($isi);
            if ($kodeSarana !== null) {
                return 'kerusakan:' . $kodeSarana;
            }
        }

        return null;
    }

    private function extractTrackingPengajuanKey(string $text): ?string
    {
        if (preg_match('/^Judul:\\s*(.+)$/m', $text, $matches) === 1) {
            $value = trim((string) $matches[1]);
            if ($value !== '') {
                return $value;
            }
        }

        return $this->extractSaranaCode($text);
    }

    private function extractSaranaCode(string $text): ?string
    {
        if (preg_match('/(?:AST|SRN)-[A-Z0-9-]+/i', $text, $matches) === 1) {
            return strtoupper((string) $matches[0]);
        }

        return null;
    }
}
