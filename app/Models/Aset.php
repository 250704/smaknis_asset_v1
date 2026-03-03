<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Aset extends Model
{
    use HasFactory, SoftDeletes;

    public const KONDISI_LIST = ['BAIK', 'KR1', 'KR2', 'KR3'];
    public const STATUS_LIST = ['AKTIF', 'NONAKTIF'];

    protected $table = 'aset';

    protected $fillable = [
        'kode_aset',
        'nama_aset',
        'kategori_id',
        'ruangan_id',
        'tahun_perolehan',
        'harga_perolehan',
        'kondisi_terkini',
        'status_aset',
        'foto_aset',
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
        return $this->belongsTo(KategoriAset::class, 'kategori_id');
    }

    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class);
    }

    public function riwayatKondisiAset(): HasMany
    {
        return $this->hasMany(RiwayatKondisiAset::class);
    }

    public function mutasiAset(): HasMany
    {
        return $this->hasMany(MutasiAset::class);
    }

    public function pengajuan(): HasMany
    {
        return $this->hasMany(Pengajuan::class);
    }

    public function penggantianAsetLama(): HasMany
    {
        return $this->hasMany(Penggantian::class, 'aset_lama_id');
    }

    public function penggantianAsetBaru(): HasMany
    {
        return $this->hasMany(Penggantian::class, 'aset_baru_id');
    }
}
