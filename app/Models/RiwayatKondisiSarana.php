<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatKondisiSarana extends Model
{
    use HasFactory;

    protected $table = 'riwayat_kondisi_sarana';

    protected $fillable = [
        'sarana_id',
        'user_id',
        'tingkat_kerusakan',
        'deskripsi',
        'foto_kerusakan',
        'status',
        'validated_by',
        'validated_at',
        'rekomendasi_tindakan',
        'catatan_validasi',
    ];

    protected function casts(): array
    {
        return [
            'validated_at' => 'datetime',
        ];
    }

    public function sarana(): BelongsTo
    {
        return $this->belongsTo(Sarana::class, 'sarana_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
