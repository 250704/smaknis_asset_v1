<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasi';

    protected $fillable = [
        'user_id',
        'judul',
        'isi',
        'url',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (Notifikasi $notifikasi) {
            if ($notifikasi->user_id) {
                Cache::forget('notif_unread_count:' . $notifikasi->user_id);
            }
        });

        static::deleted(function (Notifikasi $notifikasi) {
            if ($notifikasi->user_id) {
                Cache::forget('notif_unread_count:' . $notifikasi->user_id);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
