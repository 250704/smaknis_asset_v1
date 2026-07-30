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
        'sarana_lama_id',
        'sarana_baru_id',
        'foto_sarana_lama',
        'foto_sarana_baru',
        'foto_bukti',
        'biaya_realisasi',
        'status_realisasi',
        'tanggal_penggantian',
        'keterangan',
        'nama_teknisi',
        'kontak_teknisi',
        'nama_vendor',
        'kontak_vendor',
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

    public function saranaLama(): BelongsTo
    {
        return $this->belongsTo(Sarana::class, 'sarana_lama_id');
    }

    public function saranaBaru(): BelongsTo
    {
        return $this->belongsTo(Sarana::class, 'sarana_baru_id');
    }
}
