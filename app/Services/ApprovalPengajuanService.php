<?php

namespace App\Services;

use App\Models\ApprovalPengajuan;
use App\Models\Pengajuan;
use App\Models\User;
use DomainException;

class ApprovalPengajuanService
{
    /**
     * Menentukan urutan approval berdasarkan status pengajuan saat ini.
     */
    public function getNextApprovalRole(Pengajuan $pengajuan): ?string
    {
        return match ($pengajuan->status_pengajuan) {
            Pengajuan::STATUS_DIAJUKAN => ApprovalPengajuan::ROLE_KASARANA,
            Pengajuan::STATUS_DISETUJUI_KASARANA => ApprovalPengajuan::ROLE_BENDAHARA,
            Pengajuan::STATUS_DISETUJUI_BENDAHARA => ApprovalPengajuan::ROLE_KEPSEK,
            default => null,
        };
    }

    /**
     * Proses approval bertingkat dan update status pengajuan.
     */
    public function approve(Pengajuan $pengajuan, User $approver, ?string $catatan = null): ApprovalPengajuan
    {
        $this->assertCanApprove($pengajuan, $approver);

        $roleApproval = $this->getNextApprovalRole($pengajuan);
        if (!$roleApproval) {
            throw new DomainException('Pengajuan ini tidak berada pada tahap approval.');
        }

        $approval = ApprovalPengajuan::query()->create([
            'pengajuan_id' => $pengajuan->id,
            'approver_id' => $approver->id,
            'role_approval' => $roleApproval,
            'status' => ApprovalPengajuan::STATUS_DISETUJUI,
            'catatan' => $catatan,
            'approved_at' => now(),
        ]);

        $nextStatus = match ($roleApproval) {
            ApprovalPengajuan::ROLE_KASARANA => Pengajuan::STATUS_DISETUJUI_KASARANA,
            ApprovalPengajuan::ROLE_BENDAHARA => Pengajuan::STATUS_DISETUJUI_BENDAHARA,
            // Final flow: setelah approval kepala sekolah, pengajuan masuk tahap realisasi.
            ApprovalPengajuan::ROLE_KEPSEK => Pengajuan::STATUS_DIPROSES,
            default => throw new DomainException('Role approval tidak valid.'),
        };

        $pengajuan->update(['status_pengajuan' => $nextStatus]);

        return $approval;
    }

    /**
     * Menolak pengajuan pada level approval aktif.
     */
    public function reject(Pengajuan $pengajuan, User $approver, ?string $catatan = null): ApprovalPengajuan
    {
        $this->assertCanApprove($pengajuan, $approver);

        $roleApproval = $this->getNextApprovalRole($pengajuan);
        if (!$roleApproval) {
            throw new DomainException('Pengajuan ini tidak berada pada tahap approval.');
        }

        $approval = ApprovalPengajuan::query()->create([
            'pengajuan_id' => $pengajuan->id,
            'approver_id' => $approver->id,
            'role_approval' => $roleApproval,
            'status' => ApprovalPengajuan::STATUS_DITOLAK,
            'catatan' => $catatan,
            'approved_at' => now(),
        ]);

        $pengajuan->update(['status_pengajuan' => Pengajuan::STATUS_DITOLAK]);

        return $approval;
    }

    /**
     * Validasi anti self-approval dan kecocokan role approver.
     */
    protected function assertCanApprove(Pengajuan $pengajuan, User $approver): void
    {
        if ($pengajuan->user_id === $approver->id) {
            throw new DomainException('Anti self-approval: pengaju tidak boleh meng-approve pengajuan sendiri.');
        }

        if (!$approver->isActive()) {
            throw new DomainException('Akun approver tidak aktif.');
        }

        $nextRole = $this->getNextApprovalRole($pengajuan);
        $roleCode = $approver->role_code;

        $isAllowed = match ($nextRole) {
            ApprovalPengajuan::ROLE_KASARANA => $roleCode === 'kepala_sarana',
            ApprovalPengajuan::ROLE_BENDAHARA => $roleCode === 'bendahara',
            ApprovalPengajuan::ROLE_KEPSEK => $roleCode === 'kepala_sekolah',
            default => false,
        };

        if (!$isAllowed) {
            throw new DomainException('Role user tidak sesuai tahap approval saat ini.');
        }
    }
}
