<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutasiAset extends Model
{
    use HasFactory;

    protected $table = 'mutasi_aset';

    protected $fillable = [
        'aset_id',
        'pengajuan_id',
        'ruangan_asal',
        'ruangan_tujuan',
        'user_pengaju_id',
        'status_mutasi',
        'validated_by',
        'tanggal_mutasi',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mutasi' => 'date',
        ];
    }

    public function aset(): BelongsTo
    {
        return $this->belongsTo(Aset::class);
    }

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(Pengajuan::class);
    }

    public function ruanganAsal(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_asal');
    }

    public function ruanganTujuan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_tujuan');
    }

    public function userPengaju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_pengaju_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
