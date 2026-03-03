<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Perawatan extends Model
{
    use HasFactory;

    protected $table = 'perawatan';

    protected $fillable = [
        'pengajuan_id',
        'tanggal_perawatan',
        'foto_sebelum',
        'foto_sesudah',
        'biaya_realisasi',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_perawatan' => 'date',
        ];
    }

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(Pengajuan::class);
    }
}
