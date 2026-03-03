<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penggantian extends Model
{
    use HasFactory;

    protected $table = 'penggantian';

    protected $fillable = [
        'pengajuan_id',
        'aset_lama_id',
        'aset_baru_id',
        'foto_aset_lama',
        'foto_aset_baru',
        'biaya_realisasi',
        'status_realisasi',
        'tanggal_penggantian',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_penggantian' => 'date',
        ];
    }

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(Pengajuan::class);
    }

    public function asetLama(): BelongsTo
    {
        return $this->belongsTo(Aset::class, 'aset_lama_id');
    }

    public function asetBaru(): BelongsTo
    {
        return $this->belongsTo(Aset::class, 'aset_baru_id');
    }
}
