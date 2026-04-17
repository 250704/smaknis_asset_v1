<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerifikasiSarana extends Model
{
    use HasFactory;

    protected $table = 'verifikasi_sarana';

    public const STATUS_LAYAK = 'LAYAK';
    public const STATUS_TIDAK_LAYAK = 'TIDAK_LAYAK';

    protected $fillable = [
        'pengajuan_id',
        'verifikator_id',
        'status_verifikasi',
        'rekomendasi',
        'estimasi_biaya',
        'detail_estimasi',
        'catatan',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'estimasi_biaya' => 'decimal:2',
            'detail_estimasi' => 'array',
            'verified_at' => 'datetime',
        ];
    }

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(Pengajuan::class);
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_id');
    }
}
