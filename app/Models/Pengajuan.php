<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pengajuan extends Model
{
    use HasFactory;

    protected $table = 'pengajuan';

    public const STATUS_DIAJUKAN = 'DIAJUKAN';
    public const STATUS_DISETUJUI_KASARANA = 'DISETUJUI_KASARANA';
    public const STATUS_DISETUJUI_BENDAHARA = 'DISETUJUI_BENDAHARA';
    public const STATUS_DISETUJUI_KEPSEK = 'DISETUJUI_KEPSEK';
    public const STATUS_DITOLAK = 'DITOLAK';
    public const STATUS_DIPROSES = 'DIPROSES';
    public const STATUS_MENUNGGU_VERIFIKASI_TEKNIS = 'MENUNGGU_VERIFIKASI_TEKNIS';
    public const STATUS_MENUNGGU_VERIFIKASI_KEUANGAN = 'MENUNGGU_VERIFIKASI_KEUANGAN';
    public const STATUS_SELESAI = 'SELESAI';

    protected $fillable = [
        'aset_id',
        'user_id',
        'judul_pengajuan',
        'jenis_pengajuan',
        'deskripsi',
        'estimasi_biaya',
        'target_realisasi',
        'status_pengajuan',
        'lampiran',
    ];

    protected function casts(): array
    {
        return [
            'target_realisasi' => 'date',
            'lampiran' => 'array',
        ];
    }

    public function aset(): BelongsTo
    {
        return $this->belongsTo(Aset::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvalPengajuan(): HasMany
    {
        return $this->hasMany(ApprovalPengajuan::class);
    }

    public function perawatan(): HasOne
    {
        return $this->hasOne(Perawatan::class);
    }

    public function penggantian(): HasOne
    {
        return $this->hasOne(Penggantian::class);
    }

    public function detailPengadaan(): HasMany
    {
        return $this->hasMany(DetailPengadaan::class);
    }

}
