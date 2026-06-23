# 📋 Dokumentasi Sistem SARPRAS - Analisis Senior Designer

## 🎯 Executive Summary

Sistem **Sarpras v1** adalah platform manajemen sarana dan prasarana yang mengotomasi:

- **Pelaporan kerusakan aset** dari end-user (guru/staff)
- **Validasi bertingkat** dengan approval chain 3-level (Kepala Sarana → Bendahara → Kepala Sekolah)
- **Proposal creation** yang bisa manual atau auto-triggered dari laporan kerusakan
- **Realisasi aset** (perawatan/penggantian/pengadaan) oleh admin
- **Master data** (Gedung, Ruangan, Kategori, User, Role)
- **Notification system** via WhatsApp untuk stakeholder awareness
- **Reporting & Analytics** untuk decision making

---

## 🏗️ System Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                     SARPRAS ECOSYSTEM                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌─────────────┐  ┌──────────────┐  ┌──────────────┐             │
│  │   User      │  │   Business   │  │   Admin      │             │
│  │  Interface  │  │   Logic      │  │  Dashboard   │             │
│  └─────────────┘  └──────────────┘  └──────────────┘             │
│         ↓               ↓                   ↓                      │
│  ┌──────────────────────────────────────────────────────┐         │
│  │             Core Service Layer                       │         │
│  ├──────────────────────────────────────────────────────┤         │
│  │ • ApprovalPengajuanService                          │         │
│  │ • WhatsAppNotificationService                       │         │
│  │ • PengajuanController (CRUD + Approval)            │         │
│  │ • KerusakanController (Report + Validate)          │         │
│  └──────────────────────────────────────────────────────┘         │
│         ↓                                                          │
│  ┌──────────────────────────────────────────────────────┐         │
│  │          Data Model Layer (Eloquent ORM)            │         │
│  ├──────────────────────────────────────────────────────┤         │
│  │ • Aset (Asset Inventory)                            │         │
│  │ • Pengajuan (Proposal)                              │         │
│  │ • ApprovalPengajuan (Approval Chain)                │         │
│  │ • RiwayatKondisiAset (Damage History)              │         │
│  │ • Perawatan/Penggantian (Realization)              │         │
│  │ • User/Role (Access Control)                        │         │
│  │ • Notifikasi (Notification Queue)                   │         │
│  └──────────────────────────────────────────────────────┘         │
│         ↓                                                          │
│  ┌──────────────────────────────────────────────────────┐         │
│  │            Database (MySQL/PostgreSQL)              │         │
│  │  [All tables as defined in migrations]              │         │
│  └──────────────────────────────────────────────────────┘         │
│         ↓                                                          │
│  ┌──────────────────────────────────────────────────────┐         │
│  │          External Services & Storage                │         │
│  ├──────────────────────────────────────────────────────┤         │
│  │ • WhatsApp API (via Service)                        │         │
│  │ • File Storage (Photo/Document)                     │         │
│  │ • Email System (User Notifications)                 │         │
│  └──────────────────────────────────────────────────────┘         │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

---

## 👥 Stakeholder & Role Analysis

### 1. **Guru / Staff Umum** 👨‍🏫

**Tanggung Jawab**: Mendeteksi dan melaporkan kerusakan aset

| Aksi                   | Proses                                             | Status Output |
| ---------------------- | -------------------------------------------------- | ------------- |
| Scan QR Aset           | Find Asset → Lapor Kerusakan                       | DILAPORKAN    |
| Input Pengajuan Manual | Create Pengajuan (Perawatan/Penggantian/Pengadaan) | DIAJUKAN      |

**Authorization**: Read-only asset info, Create damage report, View own submission

---

### 2. **Kepala Sarana** 👔

**Tanggung Jawab**: Validasi kerusakan dan approval teknis pengajuan

| Aksi                 | Proses                          | Status Output        |
| -------------------- | ------------------------------- | -------------------- |
| Validasi Laporan     | Review Laporan → Approve/Reject | DIVALIDASI / DITOLAK |
| Estimate & Recommend | Input estimasi biaya & timeline | Set dalam Pengajuan  |
| Approve Pengajuan    | Level 1 Approval                | DISETUJUI_KASARANA   |

**Authorization**: Validate damage, Create/Edit Pengajuan, First-level approval

---

### 3. **Bendahara** 💰

**Tanggung Jawab**: Review anggaran dan approval finansial

| Aksi           | Proses                             | Status Output                 |
| -------------- | ---------------------------------- | ----------------------------- |
| Review Budget  | Check estimasi vs available budget | Review only                   |
| Approve/Reject | Level 2 Approval                   | DISETUJUI_BENDAHARA / DITOLAK |

**Authorization**: View financial data, Second-level approval, No create access

---

### 4. **Kepala Sekolah** 📋

**Tanggung Jawab**: Persetujuan final dan policy decision

| Aksi           | Proses                | Status Output              |
| -------------- | --------------------- | -------------------------- |
| Final Review   | Review approval chain | Review only                |
| Approve/Reject | Level 3 Approval      | DISETUJUI_KEPSEK / DITOLAK |

**Authorization**: Final approval, Executive dashboard, No create/edit access

---

### 5. **Admin System** ⚙️

**Tanggung Jawab**: Realisasi, inventory management, system administration

| Aksi            | Proses                                       | Status Output |
| --------------- | -------------------------------------------- | ------------- |
| Input Realisasi | Terima pengajuan DIPROSES → Fill realization | SELESAI       |
| Kelola Aset     | CRUD inventory dengan batch import           | Updated       |
| User Management | Manage users, roles, permissions             | Updated       |
| Master Data     | Manage Gedung, Ruangan, Kategori             | Updated       |
| Monitoring      | View logs, system health                     | Analytics     |

**Authorization**: Full system access (Administrator)

---

## 📊 Data Flow & State Machine

### Pengajuan Status Flow

```
┌──────────────┐
│   DIAJUKAN   │  ◄─── Initial state (manual atau auto from validation)
└───────┬──────┘
        │
        ▼
   ┌─────────────────────────────────────────┐
   │  Kepala Sarana Level Approval           │
   │  ├─ APPROVE → DISETUJUI_KASARANA       │
   │  └─ REJECT  → DITOLAK (END)            │
   └────────────┬────────────────────────────┘
                │
                ▼ (if DISETUJUI_KASARANA)
   ┌─────────────────────────────────────────┐
   │  Bendahara Level Approval               │
   │  ├─ APPROVE → DISETUJUI_BENDAHARA      │
   │  └─ REJECT  → DITOLAK (END)            │
   └────────────┬────────────────────────────┘
                │
                ▼ (if DISETUJUI_BENDAHARA)
   ┌─────────────────────────────────────────┐
   │  Kepala Sekolah Level Approval          │
   │  ├─ APPROVE → DISETUJUI_KEPSEK         │
   │  │           ↓                           │
   │  │           DIPROSES                   │
   │  └─ REJECT  → DITOLAK (END)            │
   └────────────┬────────────────────────────┘
                │
                ▼ (if DIPROSES)
   ┌─────────────────────────────────────────┐
   │  Admin Realization                      │
   │  ├─ Fill Realization → SELESAI (END)   │
   │  └─ Update Asset Condition              │
   └─────────────────────────────────────────┘

⚠️ Special Status (Optional Path):
   • MENUNGGU_VERIFIKASI_TEKNIS
   • MENUNGGU_VERIFIKASI_KEUANGAN
   (Currently defined in code but not actively used in main flow)
```

---

## 🔄 Core Workflows

### Workflow 1: Kerusakan → Validasi → Pengajuan → Approval → Realisasi

```
TIME ────────────────────────────────────────────────────────────────────►

 T0: Guru melaporkan kerusakan
     ├─ [Riwayat Kondisi Aset] Status = DILAPORKAN
     └─ [Notification] → Kepala Sarana

 T1: Kepala Sarana validasi
     ├─ [Riwayat Kondisi Aset] Status = DIVALIDASI
     ├─ [Pengajuan] AUTO-CREATE dengan status DIAJUKAN
     └─ [Notification] → Bendahara

 T2: Bendahara review
     ├─ [Approval Pengajuan] KASARANA approved
     ├─ [Pengajuan] Status = DISETUJUI_BENDAHARA
     └─ [Notification] → Kepala Sekolah

 T3: Kepala Sekolah approve
     ├─ [Approval Pengajuan] KEPSEK approved
     ├─ [Pengajuan] Status = DIPROSES
     └─ [Notification] → Admin

 T4: Admin input realisasi
     ├─ [Perawatan/Penggantian] Record created
     ├─ [Pengajuan] Status = SELESAI
     ├─ [Aset] Kondisi = BAIK
     ├─ [Riwayat Kondisi Aset] Status = SELESAI
     └─ [Notification] → All Stakeholder: "Selesai"

Duration: Variable (1-30 hari typical)
```

---

## 🔐 Access Control Matrix

```
Fitur                    | Guru | KaSarana | Bendahara | KepSek | Admin
─────────────────────────|------|----------|-----------|--------|──────
Lapor Kerusakan         |  ✅  |    ✅    |    ✅     |   ❌   |  ✅
Validasi Kerusakan      |  ❌  |    ✅    |    ❌     |   ❌   |  ❌
Create Pengajuan        |  ✅  |    ✅    |    ✅     |   ❌   |  ✅
Edit Pengajuan          |  ✅  |    ✅    |    ❌     |   ❌   |  ✅
KaSarana Approval       |  ❌  |    ✅    |    ❌     |   ❌   |  ❌
Bendahara Approval      |  ❌  |    ❌    |    ✅     |   ❌   |  ❌
KepSek Approval         |  ❌  |    ❌    |    ❌     |   ✅   |  ❌
Input Realisasi         |  ❌  |    ❌    |    ❌     |   ❌   |  ✅
Manage Aset             |  ❌  |    ❌    |    ❌     |   ❌   |  ✅
Manage User             |  ❌  |    ❌    |    ❌     |   ❌   |  ✅
View Report             |  ✅  |    ✅    |    ✅     |   ✅   |  ✅
Scan QR                 |  ✅  |    ✅    |    ✅     |   ✅   |  ✅
```

---

## 💾 Database Schema Overview

### Critical Tables:

**aset** (Asset Master)

```sql
├─ kode_aset (Unique identifier)
├─ nama_aset
├─ kategori_id (FK)
├─ ruangan_id (FK)
├─ kondisi_terkini (BAIK|RINGAN|BERAT|TIDAK_LAYAK)
├─ status_aset (AKTIF|NONAKTIF)
├─ tahun_perolehan
├─ harga_perolehan
└─ foto_aset
```

**riwayat_kondisi_aset** (Damage Report)

```sql
├─ aset_id (FK)
├─ user_id (Reporter, FK)
├─ tingkat_kerusakan (RINGAN|BERAT|TIDAK_LAYAK)
├─ deskripsi
├─ foto_kerusakan
├─ status (DILAPORKAN|DIVALIDASI|DITINDAKLANJUTI)
├─ validated_by (Validator, FK)
└─ validated_at
```

**pengajuan** (Proposal)

```sql
├─ aset_id (FK, nullable for procurement)
├─ user_id (Proposer, FK)
├─ jenis_pengajuan (PERAWATAN|PENGGANTIAN|PENGADAAN)
├─ judul_pengajuan
├─ deskripsi
├─ estimasi_biaya
├─ target_realisasi
├─ status_pengajuan (See status flow above)
└─ lampiran (JSON array)
```

**approval_pengajuan** (Approval Record)

```sql
├─ pengajuan_id (FK)
├─ approver_id (User, FK)
├─ role_approval (KASARANA|BENDAHARA|KEPSEK|KASARANA_VERIFIKASI|BENDAHARA_VERIFIKASI)
├─ status (DISETUJUI|DITOLAK)
├─ catatan
└─ approved_at
```

**perawatan** (Maintenance Realization)

```sql
├─ pengajuan_id (Unique, FK)
├─ tanggal_perawatan
├─ foto_sebelum
├─ foto_sesudah
├─ biaya_realisasi
└─ keterangan
```

**penggantian** (Replacement Realization)

```sql
├─ pengajuan_id (Unique, FK)
├─ aset_lama_id (FK)
├─ aset_baru_id (FK, nullable until completion)
├─ tanggal_penggantian
├─ foto_aset_baru
├─ biaya_realisasi
└─ keterangan
```

---

## 📡 Notification System

### Trigger Events:

| Event             | Triggered By | To Role   | Message Template                                        |
| ----------------- | ------------ | --------- | ------------------------------------------------------- |
| Laporan Baru      | Guru         | KaSarana  | "Laporan kerusakan baru: [AsetName], Tingkat: [Level]"  |
| Validasi Selesai  | KaSarana     | Bendahara | "Pengajuan baru menunggu persetujuan: [JudulPengajuan]" |
| KaSarana Approve  | KaSarana     | Bendahara | "Disetujui Kepala Sarana: [JudulPengajuan]"             |
| KaSarana Reject   | KaSarana     | Pengaju   | "Ditolak Kepala Sarana: [Catatan]"                      |
| Bendahara Approve | Bendahara    | KepSek    | "Disetujui Bendahara: [JudulPengajuan]"                 |
| Bendahara Reject  | Bendahara    | Pengaju   | "Ditolak Bendahara: [Catatan]"                          |
| KepSek Approve    | KepSek       | Admin     | "Persetujuan Final: [JudulPengajuan], Mulai Realisasi"  |
| KepSek Reject     | KepSek       | Pengaju   | "Ditolak Kepala Sekolah: [Catatan]"                     |
| Realisasi Selesai | Admin        | All       | "Realisasi Selesai: [JudulPengajuan]"                   |

### Notification Delivery:

- **Channel**: WhatsApp (Primary), Email (Secondary)
- **Queue**: Async job queue (Maintenance pattern)
- **De-duplication**: Group duplicate notifications in user inbox
- **Read Status**: Mark as read when user clicks notification

---

## 🎯 Key Business Rules

### 1. **Kerusakan Report Rules:**

- ✅ One active damage report per asset (prevent duplicate)
- ✅ Damage level auto-updates asset condition
- ✅ Only Kepala Sarana can validate damage report
- ✅ Validation auto-creates proposal (DIAJUKAN status)

### 2. **Pengajuan Rules:**

- ✅ Status progression is unidirectional (DIAJUKAN → ... → SELESAI)
- ✅ Rejection at any level ends proposal (status = DITOLAK)
- ✅ Cannot revert from SELESAI or DITOLAK
- ✅ For PENGADAAN: Detail_Pengadaan items must be > 0
- ✅ Target realisasi must be future date

### 3. **Approval Chain Rules:**

- ✅ Must follow order: KaSarana → Bendahara → KepSek
- ✅ Cannot skip approval level
- ✅ Each approval role creates separate ApprovalPengajuan record
- ✅ Approval timestamp auto-recorded
- ✅ Rejection message is mandatory

### 4. **Realization Rules:**

- ✅ Only one realization per proposal (Perawatan XOR Penggantian)
- ✅ Realization only allowed when status = DIPROSES
- ✅ Upon realization, asset condition auto-set to BAIK
- ✅ Damage report auto-marked as SELESAI
- ✅ Photo evidence mandatory (before & after for maintenance, new asset for replacement)

### 5. **Asset Management Rules:**

- ✅ Asset code (kode_aset) is auto-generated and UNIQUE
- ✅ Asset cannot be deleted if has active pengajuan/kerusakan
- ✅ Soft delete (archive) if has relasi; hard delete otherwise
- ✅ Condition update only by system (during validation/realization)
- ✅ Only AKTIF assets can be reported/proposed

### 6. **User & Role Rules:**

- ✅ Each user must have exactly 1 role
- ✅ Admin cannot modify their own status to NONAKTIF
- ✅ Admin cannot change their own role to non-admin
- ✅ Only admin can manage users
- ✅ Email must be unique per user

---

## 🚀 Strengths of Current System

✅ **Multi-level Approval Chain**: Ensures proper oversight  
✅ **Auto-proposal Generation**: Reduces manual entry from kerusakan → pengajuan  
✅ **Role-based Access Control**: Clear responsibility segregation  
✅ **Async Notification System**: Keep stakeholders informed in real-time  
✅ **Audit Trail**: All activities logged via RiwayatKondisiAset & ApprovalPengajuan  
✅ **Flexible Proposal Types**: Supports Perawatan, Penggantian, Pengadaan  
✅ **QR-based Quick Access**: Reduces kerusakan entry friction  
✅ **Batch Import**: Admin can bulk-create assets efficiently

---

## 🔧 Potential Improvements (To-Be)

⚠️ **Optional Verifikasi Teknis/Keuangan**: Reserved status exist but not implemented  
→ _Could add intermediate verification step before final approval_

⚠️ **Mutasi Aset**: Mentioned in UI but not core BPMN  
→ _Could add asset transfer workflow between ruangan/gedung_

⚠️ **Penggantian Aset Storage**: penggantian table has aset_baru_id nullable  
→ _Could be created earlier (during proposal) vs later (during realization)_

⚠️ **Approval Override/Escalation**: No mechanism to escalate if stuck  
→ _Could add escalation workflow after X days pending_

⚠️ **Budget Planning**: Bendahara approval is per-proposal, not budget-pool based  
→ _Could add annual budget allocation & tracking system_

---

## 📝 Recommendations

### Short-term (Phase 1):

1. ✅ Implement all 10 BPMN diagrams as-is
2. ✅ Full training for all stakeholder roles
3. ✅ Comprehensive user documentation
4. ✅ UAT (User Acceptance Testing) for 2 weeks

### Medium-term (Phase 2):

1. Add **Mutasi Aset** workflow diagram
2. Implement **Escalation Policy** (auto-escalate after 7 days pending approval)
3. Add **Budget Planning Module** with annual allocation
4. Integrate **Mobile App** for quick damage reporting

### Long-term (Phase 3):

1. Add **Preventive Maintenance** scheduling
2. Implement **Asset Depreciation** tracking
3. AI-based **Damage Severity Prediction** from photos
4. Multi-site **Federation** (if expanding beyond single school)

---

## 📚 Documentation Deliverables

- ✅ BPMN Diagrams (10 scenarios)
- ✅ Database Schema (ER Model)
- ✅ Access Control Matrix
- ✅ Business Rules Catalog
- ✅ Status Flow Diagrams
- ✅ Role Responsibility Matrix
- ✅ Notification Trigger Mapping
- ✅ Risk Assessment & Mitigation

---

**Author**: Senior System Designer  
**Date**: June 2026  
**Document Status**: ✅ FINAL - Ready for Development & Implementation  
**Next Step**: Stakeholder Review & Approval → Development Sprint Planning
