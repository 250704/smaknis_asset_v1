# 🔗 System Integration & Process Interconnection Map

---

## 1️⃣ Proses Ecosystem Map (Menunjukkan Relasi Antar Proses)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                       SARPRAS SYSTEM PROCESSES                              │
└─────────────────────────────────────────────────────────────────────────────┘

                             ┌─────────────────────┐
                             │  7️⃣ MASTER DATA    │
                             │  (Gedung, Ruangan) │
                             │   CRUD by Admin     │
                             └────────┬────────────┘
                                      │ creates reference
                                      ▼
┌────────────────────────────────────────────────────────────────┐
│ 6️⃣ INVENTARIS ASET (CRUD by Admin)                            │
│ • Assign to Ruangan                                            │
│ • Assign Category                                              │
│ • Link to Master Data                                          │
└────────────────┬──────────────────┬─────────────────────────────┘
                 │ records          │ updates condition
                 ▼                  ▼
         ┌───────────────┐    ┌─────────────────────────┐
         │ 2️⃣ PENGAJUAN  │◄───┤ 1️⃣ KERUSAKAN→VALIDASI  │
         │ MANUAL        │    │ (Auto-create proposal) │
         │ (Create by    │    └─────────────────────────┘
         │  any user)    │
         └───────┬───────┘
                 │ status: DIAJUKAN
                 ▼
         ┌─────────────────────────────────────────┐
         │ 3️⃣ APPROVAL BERTINGKAT                 │
         │ (3-level approval process)              │
         │ - KaSarana                              │
         │ - Bendahara                             │
         │ - Kepala Sekolah                        │
         └─────────┬───────────────────────────────┘
                   │ status: DIPROSES
                   ▼
         ┌─────────────────────────────────────────┐
         │ 4️⃣ REALISASI ADMIN                     │
         │ Input hasil perbaikan/penggantian       │
         │ Upload foto sebelum-sesudah             │
         │ Update kondisi aset = BAIK              │
         └─────────────────────────────────────────┘
                   │ status: SELESAI
                   ▼
         ┌─────────────────────────────────────────┐
         │ 🔔 9️⃣ NOTIFIKASI                       │
         │ Triggered at each status change         │
         │ WhatsApp to relevant stakeholder        │
         └─────────────────────────────────────────┘

         ┌─────────────────────────────────────────┐
         │ 5️⃣ SCAN QR HUB                         │
         │ Central action dispatcher               │
         │ Role-based menu redirect                │
         └─────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ 8️⃣ USER MANAGEMENT (Admin)                                     │
│ Manage users & roles for all processes                          │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ 🔟 REPORTING & EXPORT (All Users)                              │
│ Analytics for all processes                                    │
└─────────────────────────────────────────────────────────────────┘
```

---

## 2️⃣ Data Flow Across Entities

```
┌──────────────────────────────────────────────────────────────────────────────┐
│                          DATA ENTITY FLOW                                    │
└──────────────────────────────────────────────────────────────────────────────┘

USER (Role-based)
  ├─ Guru → Can create RiwayatKondisiAset + Pengajuan (manual)
  ├─ KaSarana → Can validate RiwayatKondisiAset + approve Pengajuan L1
  ├─ Bendahara → Can approve Pengajuan L2
  ├─ KepSek → Can approve Pengajuan L3
  └─ Admin → Can create Realisasi + manage Aset, Gedung, Ruangan

ASET (Asset Master)
  ├─ Created/Updated by: Admin (via 6️⃣ Inventaris Aset)
  ├─ Referenced by: Pengajuan (aset_id FK)
  ├─ Referenced by: RiwayatKondisiAset (aset_id FK)
  ├─ Referenced by: Perawatan/Penggantian (via Pengajuan)
  └─ Updated condition by: System (during validation/realization)

GEDUNG (Building Master)
  ├─ Created/Updated by: Admin (via 7️⃣ Master Data)
  ├─ Referenced by: Ruangan (gedung_id FK)
  └─ Referenced by: Aset (via Ruangan)

RUANGAN (Room Master)
  ├─ Created/Updated by: Admin (via 7️⃣ Master Data)
  ├─ References: Gedung (gedung_id FK)
  ├─ Referenced by: Aset (ruangan_id FK)
  └─ Referenced by: DetailPengadaan (ruangan_id FK)

KATEGORI_ASET (Category Master)
  ├─ Created/Updated by: Admin (via 7️⃣ Master Data)
  ├─ Referenced by: Aset (kategori_id FK)
  └─ Referenced by: DetailPengadaan (kategori_id FK)

RIWAYAT_KONDISI_ASET (Damage Report)
  ├─ Created by: Guru/Staff (via 1️⃣ Kerusakan Laporan)
  ├─ Validated by: KaSarana (set validated_by + validated_at)
  ├─ Triggers: Auto-create Pengajuan (if valid)
  ├─ Triggers: Notification (9️⃣) to KaSarana
  └─ Updated by: System (status = SELESAI after Realisasi)

PENGAJUAN (Proposal)
  ├─ Created by: Guru/Staff/Admin (manual via 2️⃣) OR System (auto from 1️⃣)
  ├─ Status progression: DIAJUKAN → ... → SELESAI or DITOLAK
  ├─ Referenced by: ApprovalPengajuan (pengajuan_id FK)
  ├─ Referenced by: Perawatan/Penggantian (pengajuan_id FK)
  ├─ Referenced by: DetailPengadaan (pengajuan_id FK)
  └─ Triggers: Notification (9️⃣) at each status change

APPROVAL_PENGAJUAN (Approval Record)
  ├─ Created by: System (one record per role per pengajuan)
  ├─ Filled by: KaSarana (L1) → Bendahara (L2) → KepSek (L3)
  ├─ Status: DISETUJUI or DITOLAK
  ├─ Records: approver_id, approved_at, catatan
  └─ Triggers: Notification (9️⃣) when approval done

PERAWATAN / PENGGANTIAN (Realization)
  ├─ Created by: Admin (via 4️⃣ Realisasi)
  ├─ Links to: Pengajuan (pengajuan_id FK unique)
  ├─ Records: photos, cost, date, notes
  ├─ Triggers: Update Aset condition to BAIK
  ├─ Triggers: Update RiwayatKondisiAset status = SELESAI
  └─ Triggers: Notification (9️⃣) "Selesai"

DETAIL_PENGADAAN (Procurement Detail)
  ├─ Created by: System or User (for PENGADAAN type pengajuan)
  ├─ Multiple items per Pengajuan
  ├─ Specifies: item name, quantity, category, ruangan, spec, price
  └─ No direct realization (just proposal detail)

NOTIFIKASI (Notification Queue)
  ├─ Created by: System (event-driven)
  ├─ Sent via: WhatsApp API (async)
  ├─ Stored in: User Inbox
  ├─ Status: UNREAD → READ
  └─ Cleanup: De-duplicate + archive old

LOG_AKTIVITAS (Audit Trail)
  ├─ Recorded by: System (all CRUD operations)
  ├─ Captures: user, entity, action, timestamp
  └─ Used by: Admin for compliance & audit
```

---

## 3️⃣ Stakeholder Interaction Matrix

```
┌─────────────────┬──────────────┬─────────────┬──────────────┬───────────────┐
│ ROLE            │ READS        │ CREATES     │ UPDATES      │ APPROVES      │
├─────────────────┼──────────────┼─────────────┼──────────────┼───────────────┤
│ GURU            │ • Aset info  │ • RiwayatK. │ • Pengajuan  │ None          │
│ (Pelapor)       │ • Own proj.  │ • Pengajuan │   (own)      │               │
│                 │ • Notif.     │             │              │               │
├─────────────────┼──────────────┼─────────────┼──────────────┼───────────────┤
│ KEPALA SARANA   │ • All Aset   │ • Pengajuan │ • RiwayatK.  │ • Pengajuan   │
│ (Validator)     │ • Riwayat    │ • Approval  │   (validate) │   (L1)        │
│                 │ • Notif.     │             │ • Pengajuan  │               │
│                 │              │             │   (recommend)│               │
├─────────────────┼──────────────┼─────────────┼──────────────┼───────────────┤
│ BENDAHARA       │ • Pengajuan  │ • Approval  │ None         │ • Pengajuan   │
│ (Budget)        │ • Finance    │             │              │   (L2)        │
│                 │ • Notif.     │             │              │               │
├─────────────────┼──────────────┼─────────────┼──────────────┼───────────────┤
│ KEPALA SEKOLAH  │ • All        │ • Approval  │ None         │ • Pengajuan   │
│ (Executive)     │ • Dashboard  │             │              │   (L3 Final)  │
│                 │ • Notif.     │             │              │               │
├─────────────────┼──────────────┼─────────────┼──────────────┼───────────────┤
│ ADMIN           │ • All        │ • Aset      │ • Aset       │ None          │
│ (Operator)      │ • Realisasi  │ • Gedung    │ • Gedung     │               │
│                 │ • User       │ • Ruangan   │ • Ruangan    │               │
│                 │ • Kategori   │ • User      │ • User       │               │
│                 │ • Perawatan  │ • Realisasi │ • Realisasi  │               │
└─────────────────┴──────────────┴─────────────┴──────────────┴───────────────┘
```

---

## 4️⃣ System Status & Notification Timeline Example

```
Timeline of a Typical Damage Report Journey:

[Day 0, 09:00] Guru finds broken desk
                ├─ Scan QR code on desk
                ├─ Select "Lapor Kerusakan"
                ├─ Take photo of damage
                ├─ Fill form: Tingkat = BERAT
                ├─ Submit
                └─ System: RiwayatKondisiAset created (DILAPORKAN)
                           Pengajuan auto-reserved (but not created yet)
                           Aset condition = BERAT

                ✉️ NOTIFICATION → KaSarana:
                   "Laporan kerusakan baru: Meja Kelas 3A, Tingkat: BERAT"

[Day 1, 10:30] KaSarana reviews report
                ├─ Open notifikasi link
                ├─ View damage photos
                ├─ Decide: "Perlu Penggantian"
                ├─ Fill: Estimasi = Rp 500.000
                ├─ Fill: Target Realisasi = 2026-06-22
                ├─ Click "APPROVE"
                └─ System: RiwayatKondisiAset.status = DIVALIDASI
                           Pengajuan auto-created (DIAJUKAN)
                           ApprovalPengajuan created (KASARANA)
                           Aset condition remains BERAT

                ✉️ NOTIFICATION → Bendahara:
                   "Pengajuan Penggantian Aset: Meja Kelas 3A, Est: Rp 500.000"

[Day 1, 14:00] Bendahara reviews proposal
                ├─ Open notifikasi link
                ├─ Check estimated cost vs budget
                ├─ Decision: Approved (budget available)
                ├─ Click "SETUJU"
                └─ System: Pengajuan.status = DISETUJUI_BENDAHARA
                           ApprovalPengajuan created (BENDAHARA, DISETUJUI)

                ✉️ NOTIFICATION → KepSek:
                   "Pengajuan Penggantian: Meja Kelas 3A, Disetujui Bendahara"

[Day 2, 10:00] KepSek reviews final approval
                ├─ Open notifikasi link
                ├─ View full approval chain
                ├─ Decision: Approved
                ├─ Click "SETUJU FINAL"
                └─ System: Pengajuan.status = DISETUJUI_KEPSEK
                           Pengajuan.status → DIPROSES
                           ApprovalPengajuan created (KEPSEK, DISETUJUI)

                ✉️ NOTIFICATION → Admin:
                   "Realisasi Siap: Meja Kelas 3A, Mulai tangani segera"

[Day 3-14]     Admin processes realization
                ├─ Source new desk / repair vendor
                ├─ On Day 12: Receive/complete replacement
                ├─ Take photo of new desk
                ├─ Fill realization form:
                │  ├─ Tanggal Penggantian = 2026-06-14
                │  ├─ Foto Aset Baru
                │  ├─ Biaya Realisasi = Rp 480.000 (actual)
                │  └─ Keterangan = "Penggantian sesuai invoice"
                ├─ Click "SIMPAN REALISASI"
                └─ System: Penggantian record created
                           Pengajuan.status = SELESAI
                           Aset.kondisi = BAIK
                           RiwayatKondisiAset.status = SELESAI
                           All approval records finalized

                ✉️ NOTIFICATION → All Stakeholders:
                   "Realisasi Selesai: Meja Kelas 3A, Kondisi: BAIK,
                    Biaya Aktual: Rp 480.000"

[Day 15+]      System maintains audit trail
                ├─ All activity logged
                ├─ Aset now shows: KONDISI=BAIK, RIWAYAT with full timeline
                ├─ Can export for reporting & analysis
                └─ 📊 Dashboard shows: Duration=15 hari, Cost Variance=-4%

TOTAL CYCLE TIME: 15 days
APPROVAL STAGES: 3 (KaSarana → Bendahara → KepSek)
NOTIFICATIONS: 5 (to different roles at critical points)
AUDIT RECORDS: Complete (all updates timestamped + actor recorded)
```

---

## 5️⃣ Critical Data Validation & Business Rules

```
RULE SET 1: RIWAYAT_KONDISI_ASET (Damage Report)
├─ Only 1 ACTIVE report per asset (prevent duplicates)
├─ Only AKTIF aset can be reported
├─ tingkat_kerusakan must be: RINGAN | BERAT | TIDAK_LAYAK
├─ foto_kerusakan is MANDATORY
├─ Only KaSarana can validate (set validated_by)
├─ Once validated → auto-create pengajuan
└─ Status progression: DILAPORKAN → DIVALIDASI → SELESAI

RULE SET 2: PENGAJUAN (Proposal)
├─ jenis_pengajuan must be: PERAWATAN | PENGGANTIAN | PENGADAAN
├─ estimasi_biaya must be > 0
├─ target_realisasi must be future date
├─ status_pengajuan progression is UNIDIRECTIONAL:
│  DIAJUKAN → DISETUJUI_KASARANA → DISETUJUI_BENDAHARA
│           → DISETUJUI_KEPSEK → DIPROSES → SELESAI
│  OR DITOLAK at any level (TERMINAL state)
├─ Cannot modify after SELESAI or DITOLAK
├─ For PENGADAAN: DetailPengadaan count must be > 0
└─ aset_id can be NULL (for PENGADAAN type)

RULE SET 3: APPROVAL_PENGAJUAN (Approval Record)
├─ Must follow order: KASARANA → BENDAHARA → KEPSEK
├─ Cannot skip approval level
├─ status must be: DISETUJUI | DITOLAK
├─ catatan is MANDATORY if DITOLAK
├─ Each role creates exactly 1 approval record per pengajuan
├─ approved_at auto-recorded on save
└─ No modification after created (audit immutability)

RULE SET 4: PERAWATAN / PENGGANTIAN (Realization)
├─ Only created when Pengajuan.status = DIPROSES
├─ pengajuan_id must be UNIQUE (one realization per proposal)
├─ foto_sebelum & foto_sesudah MANDATORY for Perawatan
├─ foto_aset_baru MANDATORY for Penggantian
├─ biaya_realisasi must be > 0
├─ Upon save: Aset.kondisi auto-set to BAIK
├─ Upon save: Pengajuan.status auto-set to SELESAI
├─ Upon save: RiwayatKondisiAset.status auto-set to SELESAI
└─ No modification allowed after created (realization immutable)

RULE SET 5: ASET (Asset Master)
├─ kode_aset is UNIQUE (system-generated)
├─ nama_aset cannot be empty
├─ Only AKTIF aset can be reported/proposed
├─ Cannot DELETE if has active pengajuan/kerusakan/perawatan
│  → SOFT DELETE (set status = NONAKTIF)
├─ Can hard-delete only if no relasi
├─ kondisi_terkini can only be updated by System (not manual)
│  → Updated during validation → BERAT/RINGAN
│  → Updated during realization → BAIK
└─ kategori_id & ruangan_id are required & immutable

RULE SET 6: USER & ROLE
├─ Each user must have exactly 1 role
├─ Email must be UNIQUE per user
├─ Admin CANNOT set own status to NONAKTIF
├─ Admin CANNOT change own role to non-admin
├─ Only Admin can manage users
├─ Role defines access control:
│  ├─ Guru: Read Aset + Create RiwayatK + Create Pengajuan
│  ├─ KaSarana: Validate RiwayatK + L1 Approval
│  ├─ Bendahara: L2 Approval (budget review)
│  ├─ KepSek: L3 Approval (final)
│  └─ Admin: Full access
└─ Status NONAKTIF = no access to system
```

---

## 6️⃣ Integration Points & External Systems

```
┌─────────────────────────────────────────────────────────────┐
│              SARPRAS SYSTEM INTEGRATIONS                    │
└─────────────────────────────────────────────────────────────┘

  SARPRAS CORE
      ↓ ↓ ↓
      │ │ └─────────────→ 📱 WHATSAPP API
      │ │                   (Send notifications)
      │ │                   Bi-directional messaging possible
      │ │                   (Future: receive replies)
      │ │
      │ └──────────────→ 📧 EMAIL SERVICE
      │                   (Backup notifications)
      │                   (Digest reports)
      │
      └────────────────→ 💾 FILE STORAGE
                            (Photos for damage reports)
                            (Photos for realization)
                            (Batch import CSV files)
                            (Export reports as PDF/Excel)

  FUTURE INTEGRATIONS (To-Be):
      │
      ├─→ 📱 MOBILE APP
      │    (Native app for damage reporting)
      │    (Real-time sync with backend)
      │
      ├─→ 🔗 API GATEWAY
      │    (Third-party vendor system integration)
      │    (Budget planning system)
      │    (Accounting system)
      │
      ├─→ 🗺️ LOCATION SERVICES
      │    (GPS for asset location)
      │    (Geofencing for authorization)
      │
      └─→ 🤖 AI SERVICES
           (Image analysis for damage severity)
           (Predictive maintenance scheduling)
           (Anomaly detection for fraud)
```

---

## 7️⃣ Error Handling & Recovery Scenarios

```
ERROR SCENARIO 1: Duplicate Damage Report
├─ Condition: Guru tries to report same asset that has ACTIVE report
├─ System Check: SELECT WHERE aset_id = ? AND status != 'SELESAI'
├─ Result: REJECT with message "Laporan masih aktif untuk aset ini"
└─ Recovery: Show active report status to user

ERROR SCENARIO 2: Invalid Approval Sequence
├─ Condition: Bendahara tries to approve when KASARANA hasn't approved yet
├─ System Check: Verify ApprovalPengajuan for KASARANA exists & DISETUJUI
├─ Result: REJECT with message "Tunggu approval Kepala Sarana"
└─ Recovery: Redirect to view current approval status

ERROR SCENARIO 3: Realization on Wrong Status
├─ Condition: Admin tries to input realization on DIAJUKAN status
├─ System Check: Verify Pengajuan.status == 'DIPROSES'
├─ Result: REJECT with message "Tunggu semua approval selesai"
└─ Recovery: Show approval progress to admin

ERROR SCENARIO 4: Missing Photo on Realization
├─ Condition: Admin submits realization without after-photos
├─ System Check: foto_sesudah NOT NULL
├─ Result: REJECT with message "Foto sesudah kerusakan wajib"
└─ Recovery: Prompt upload photo

ERROR SCENARIO 5: Negative Actual Cost
├─ Condition: Admin enters negative biaya_realisasi
├─ System Check: biaya_realisasi > 0
├─ Result: REJECT with message "Biaya tidak boleh negatif"
└─ Recovery: Correct input

ERROR SCENARIO 6: User Account Locked
├─ Condition: User disabled/NONAKTIF tries to login
├─ System Check: user.status_akun == 'AKTIF'
├─ Result: DENY access with message "Akun tidak aktif"
└─ Recovery: Admin reactivate account or reset password

ERROR SCENARIO 7: WhatsApp Notification Failure
├─ Condition: WhatsApp API timeout or error
├─ System: Log error but don't block process
├─ Recovery: Retry mechanism (exponential backoff)
│           User can also check Inbox notifications
│           Alternative email delivery available

ERROR SCENARIO 8: Database Constraint Violation
├─ Condition: FK reference missing (e.g., ruangan_id invalid)
├─ System: Validation before save
├─ Result: REJECT with message about missing reference
└─ Recovery: User must select valid reference from dropdown
```

---

## 📋 Quick Reference Checklist

**For Implementers:**

- [ ] Create all database tables with constraints
- [ ] Implement role-based access control middleware
- [ ] Build 11 BPMN processes in code logic
- [ ] Set up WhatsApp API integration
- [ ] Create notification queue & async jobs
- [ ] Implement all validation rules
- [ ] Build audit logging system
- [ ] Create test cases for each scenario
- [ ] Train all 5 stakeholder roles
- [ ] Deploy to production with monitoring

**For Users:**

- [ ] Know your role's responsibilities
- [ ] Understand expected response times (1-3 days per level)
- [ ] Check WhatsApp & email regularly for notifications
- [ ] Upload photos with good quality
- [ ] Provide accurate cost estimates
- [ ] Fill all mandatory fields
- [ ] Don't modify data after approval

**For Admins:**

- [ ] Monitor system uptime & performance
- [ ] Backup database daily
- [ ] Review audit logs weekly
- [ ] Manage users & inactive accounts
- [ ] Track KPI & generate reports
- [ ] Resolve escalated issues
- [ ] Plan capacity & resources

---

**Document Version**: 1.0  
**Status**: ✅ FINAL - System Architecture Complete  
**Ready for**: Development & Testing Phase
