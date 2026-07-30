<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sarana extends Model
{
    use HasFactory, SoftDeletes;

    public const KONDISI_LIST = ['BAIK', 'RINGAN', 'BERAT', 'TIDAK_LAYAK'];
    public const STATUS_LIST = ['AKTIF', 'NONAKTIF'];

    protected $table = 'sarana';

    protected $fillable = [
        'kode_sarana',
        'nama_sarana',
        'kategori_id',
        'ruangan_id',
        'tahun_perolehan',
        'harga_perolehan',
        'kondisi_terkini',
        'status_sarana',
        'foto_sarana',
    ];

    protected function casts(): array
    {
        return [
            'tahun_perolehan' => 'integer',
            'harga_perolehan' => 'decimal:2',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriSarana::class, 'kategori_id');
    }

    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class);
    }

    public function riwayatKondisiSarana(): HasMany
    {
        return $this->hasMany(RiwayatKondisiSarana::class, 'sarana_id');
    }

    public function mutasiSarana(): HasMany
    {
        return $this->hasMany(MutasiSarana::class, 'sarana_id');
    }

    public function pengajuan(): HasMany
    {
        return $this->hasMany(Pengajuan::class, 'sarana_id');
    }

    public function penggantianSaranaLama(): HasMany
    {
        return $this->hasMany(Penggantian::class, 'sarana_lama_id');
    }

    public function penggantianSaranaBaru(): HasMany
    {
        return $this->hasMany(Penggantian::class, 'sarana_baru_id');
    }
}
