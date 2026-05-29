<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    public function sendToUserId(int $userId, string $judul, string $isi, ?string $url = null): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $user = User::query()->find($userId);
        if (!$user) {
            return;
        }

        $phone = $this->normalizePhone((string) ($user->nomor_telepon ?? ''));
        if (!$phone) {
            return;
        }

        $message = $this->buildMessage($judul, $isi, $url);
        if ($message === '') {
            return;
        }

        $provider = (string) config('services.whatsapp.provider', 'fonnte');
        if ($provider === 'fonnte') {
            $this->sendViaFonnte($phone, $message);
            return;
        }

        $this->sendViaGeneric($phone, $message);
    }

    private function isEnabled(): bool
    {
        return (bool) config('services.whatsapp.enabled', false);
    }

    private function buildMessage(string $judul, string $isi, ?string $url = null): string
    {
        $parts = [];
        $judul = trim($judul);
        $isi = trim($isi);
        $url = trim((string) $url);

        if ($judul !== '') {
            $parts[] = '*' . $judul . '*';
        }

        if ($isi !== '') {
            $parts[] = $isi;
        }

        if ($this->isPublicUrl($url)) {
            $parts[] = "Buka: {$url}";
        }

        return trim(implode("\n\n", $parts));
    }

    private function isPublicUrl(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || trim($host) === '') {
            return false;
        }

        $host = strtolower(trim($host));

        if ($host === 'localhost' || $host === '127.0.0.1' || $host === '::1') {
            return false;
        }

        if (str_ends_with($host, '.local') || str_ends_with($host, '.test')) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false
            && filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return false;
        }

        return true;
    }

    private function normalizePhone(string $raw): ?string
    {
        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        if (!str_starts_with($digits, '62')) {
            return null;
        }

        return $digits;
    }

    private function sendViaFonnte(string $phone, string $message): void
    {
        $token = (string) config('services.whatsapp.token', '');
        $endpoint = (string) config('services.whatsapp.endpoint', 'https://api.fonnte.com/send');
        $timeout = (int) config('services.whatsapp.timeout', 12);

        if ($token === '' || $endpoint === '') {
            Log::warning('WhatsApp not sent: missing token/endpoint for fonnte provider.');
            return;
        }

        try {
            $response = Http::asForm()
                ->timeout($timeout)
                ->withHeaders([
                    'Authorization' => $token,
                ])
                ->post($endpoint, [
                    'target' => $phone,
                    'message' => $message,
                ]);

            $payload = [
                'provider' => 'fonnte',
                'phone' => $phone,
                'status' => $response->status(),
                'ok' => $response->successful(),
                'body' => $response->body(),
            ];

            if ($response->failed()) {
                Log::warning('WhatsApp send failed (fonnte).', [
                    ...$payload,
                ]);
            } else {
                Log::info('WhatsApp send success (fonnte).', $payload);
            }
        } catch (\Throwable $e) {
            Log::error('WhatsApp send exception (fonnte).', [
                'provider' => 'fonnte',
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendViaGeneric(string $phone, string $message): void
    {
        $token = (string) config('services.whatsapp.token', '');
        $endpoint = (string) config('services.whatsapp.endpoint', '');
        $timeout = (int) config('services.whatsapp.timeout', 12);

        if ($token === '' || $endpoint === '') {
            Log::warning('WhatsApp not sent: missing token/endpoint for generic provider.');
            return;
        }

        try {
            $response = Http::timeout($timeout)
                ->withToken($token)
                ->post($endpoint, [
                    'to' => $phone,
                    'message' => $message,
                ]);

            $payload = [
                'provider' => 'generic',
                'phone' => $phone,
                'status' => $response->status(),
                'ok' => $response->successful(),
                'body' => $response->body(),
            ];

            if ($response->failed()) {
                Log::warning('WhatsApp send failed (generic).', [
                    ...$payload,
                ]);
            } else {
                Log::info('WhatsApp send success (generic).', $payload);
            }
        } catch (\Throwable $e) {
            Log::error('WhatsApp send exception (generic).', [
                'provider' => 'generic',
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
