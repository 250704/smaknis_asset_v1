<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriAset extends Model
{
    use HasFactory;

    protected $table = 'kategori_aset';

    protected $fillable = [
        'nama_kategori',
    ];

    public function aset(): HasMany
    {
        return $this->hasMany(Aset::class, 'kategori_id');
    }

    public function detailPengadaan(): HasMany
    {
        return $this->hasMany(DetailPengadaan::class, 'kategori_id');
    }
}
