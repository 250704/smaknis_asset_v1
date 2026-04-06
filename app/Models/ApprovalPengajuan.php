<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalPengajuan extends Model
{
    use HasFactory;

    protected $table = 'approval_pengajuan';

    public const ROLE_KASARANA = 'KASARANA';
    public const ROLE_BENDAHARA = 'BENDAHARA';
    public const ROLE_KEPSEK = 'KEPSEK';
    public const ROLE_KASARANA_VERIFIKASI = 'KASARANA_VERIFIKASI';
    public const ROLE_BENDAHARA_VERIFIKASI = 'BENDAHARA_VERIFIKASI';

    public const STATUS_DISETUJUI = 'DISETUJUI';
    public const STATUS_DITOLAK = 'DITOLAK';

    protected $fillable = [
        'pengajuan_id',
        'approver_id',
        'role_approval',
        'status',
        'catatan',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(Pengajuan::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
