<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gedung extends Model
{
    use HasFactory;

    protected $table = 'gedung';

    protected $fillable = [
        'nama_gedung',
        'kode_gedung',
    ];

    public function ruangan(): HasMany
    {
        return $this->hasMany(Ruangan::class);
    }
}
