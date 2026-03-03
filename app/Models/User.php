<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'nama',
        'email',
        'password',
        'role',
        'role_id',
        'status_akun',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roleRelation(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function pengajuan(): HasMany
    {
        return $this->hasMany(Pengajuan::class);
    }

    public function approvalPengajuan(): HasMany
    {
        return $this->hasMany(ApprovalPengajuan::class, 'approver_id');
    }

    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class);
    }

    public function logAktivitas(): HasMany
    {
        return $this->hasMany(LogAktivitas::class);
    }

    public function riwayatKondisiAset(): HasMany
    {
        return $this->hasMany(RiwayatKondisiAset::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->nama ?: $this->name;
    }

    public function getRoleCodeAttribute(): ?string
    {
        if ($this->roleRelation?->nama_role) {
            return $this->roleRelation->nama_role;
        }

        return $this->role;
    }

    public function hasRole(string $role): bool
    {
        return $this->role_code === $role;
    }

    public function isActive(): bool
    {
        return $this->status_akun === 'AKTIF';
    }
}
