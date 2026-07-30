<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ruangan extends Model
{
    use HasFactory;

    protected $table = 'ruangan';

    protected $fillable = [
        'gedung_id',
        'nama_ruangan',
        'kode_ruangan',
        'lantai',
    ];

    public function gedung(): BelongsTo
    {
        return $this->belongsTo(Gedung::class);
    }

    public function sarana(): HasMany
    {
        return $this->hasMany(Sarana::class, 'ruangan_id');
    }

    public function mutasiAsal(): HasMany
    {
        return $this->hasMany(MutasiSarana::class, 'ruangan_asal');
    }

    public function mutasiTujuan(): HasMany
    {
        return $this->hasMany(MutasiSarana::class, 'ruangan_tujuan');
    }

    public function detailPengadaan(): HasMany
    {
        return $this->hasMany(DetailPengadaan::class);
    }
}
