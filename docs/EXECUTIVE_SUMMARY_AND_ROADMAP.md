# 🎯 SARPRAS System - Executive Summary & Implementation Roadmap

**Prepared by**: Senior System Designer  
**Date**: June 2026  
**Audience**: School Leadership, IT Department, End Users

---

## 📊 System Overview in 30 Seconds

**SARPRAS** adalah sistem manajemen sarana dan prasarana berbasis web yang mengotomasi siklus hidup aset sekolah:

```
Guru Lapor Kerusakan
    ↓
Kepala Sarana Validasi
    ↓
Bendahara Review Budget
    ↓
Kepala Sekolah Approve
    ↓
Admin Realisasi (Perbaiki/Ganti)
    ↓
✅ Aset Terupdate & Terawat
```

**Benefit**:

- 🎯 Transparansi: Real-time status untuk semua stakeholder
- ⚡ Efisiensi: Approval otomatis via notifikasi WhatsApp
- 📊 Audit Trail: Semua aktivitas tercatat (compliance)
- 🛡️ Kontroled: Multi-level approval mencegah penyalahgunaan dana

---

## 🎬 11 Proses Utama Sistem

| #   | Proses                    | Owner                                        | Key Benefit                            |
| --- | ------------------------- | -------------------------------------------- | -------------------------------------- |
| 1️⃣  | **Kerusakan → Realisasi** | Guru + KaSarana + Bendahara + KepSek + Admin | Full workflow automation               |
| 2️⃣  | **Pengajuan Manual**      | User Manapun                                 | Self-service proposal creation         |
| 3️⃣  | **Approval Bertingkat**   | 3 Role (KaSarana/Bendahara/KepSek)           | Proper oversight & segregation of duty |
| 4️⃣  | **Realisasi Admin**       | Admin                                        | Central tracking for completion        |
| 5️⃣  | **QR Code Hub**           | All User                                     | Fast asset lookup & action             |
| 6️⃣  | **Inventaris Aset**       | Admin                                        | Centralized asset database             |
| 7️⃣  | **Master Data**           | Admin                                        | Maintain Gedung/Ruangan/Kategori       |
| 8️⃣  | **User Management**       | Admin                                        | Access control & role assignment       |
| 9️⃣  | **Notifikasi**            | System                                       | Automated stakeholder communication    |
| 🔟  | **Reporting**             | All User                                     | Analytics & decision support           |

---

## 👥 5 Role Stakeholder

### 1. 👨‍🏫 **Guru / Staff** (Pelapor)

**Tanggung jawab**: Mendeteksi dan melaporkan kerusakan  
**Akses**:

- ✅ Scan QR aset untuk quick access
- ✅ Create damage report dengan foto
- ✅ Create pengajuan manual untuk perawatan/pengadaan
- ✅ View notifikasi status pengajuan

**Time**: 5 menit per laporan

---

### 2. 👔 **Kepala Sarana** (Validator)

**Tanggung jawab**: Validasi kerusakan dan approve teknis  
**Akses**:

- ✅ Dashboard kerusakan menunggu validasi
- ✅ Review foto + deskripsi → Approve/Reject
- ✅ Input estimasi biaya & target realisasi
- ✅ Level 1 approval untuk pengajuan

**Time**: 1-2 hari per laporan

---

### 3. 💰 **Bendahara** (Budget Controller)

**Tanggung jawab**: Review anggaran dan approve finansial  
**Akses**:

- ✅ Dashboard pengajuan menunggu approval
- ✅ View estimasi biaya vs available budget
- ✅ Level 2 approval untuk pengajuan

**Time**: 1-2 hari per pengajuan

---

### 4. 📋 **Kepala Sekolah** (Final Approver)

**Tanggung jawab**: Persetujuan final dan policy decision  
**Akses**:

- ✅ Executive dashboard dengan KPI & trend
- ✅ Level 3 final approval
- ✅ View full approval chain history

**Time**: 1 hari per pengajuan

---

### 5. ⚙️ **Admin** (Operator)

**Tanggung jawab**: Realisasi dan system administration  
**Akses**:

- ✅ Realisasi aset (input hasil perbaikan/ganti)
- ✅ CRUD aset & inventory management
- ✅ Batch import assets dari CSV/Excel
- ✅ Manajemen user & role
- ✅ Master data (Gedung/Ruangan/Kategori)

**Time**: 1-2 jam per realisasi + admin overhead

---

## 🔄 Critical Business Workflows

### Workflow A: Kerusakan Terdeteksi → Selesai (25 hari typical)

```
[Day 0] Guru lapor kerusakan aset
        ↓ Sistem kirim notif ke KaSarana
[Day 1] KaSarana validasi + estimate
        ↓ Sistem auto-create proposal
[Day 2] Bendahara review budget
        ↓ Approve (if budget OK)
[Day 3] KepSek final approval
        ↓ Sistem notif ke Admin
[Day 4-15] Admin merealisasi (perbaikan/penggantian/pengadaan)
           Upload foto sebelum-sesudah + biaya aktual
        ↓ Sistem update asset condition ke BAIK
[Day 16] ✅ Proses Selesai
         Semua stakeholder dapat notif "Selesai"
         Riwayat aset terupdate
```

**Key Controls**:

- ✅ Cannot skip approval level
- ✅ Cannot modify after SELESAI
- ✅ All steps audited & logged
- ✅ Can reject at any approval stage (go back to originator)

---

### Workflow B: Manual Proposal (5-20 hari typical)

```
[Day 0] User (Guru/Staff/Admin) create pengajuan manual
        Pilih jenis: PERAWATAN / PENGGANTIAN / PENGADAAN
        Fill estimasi & target date
        ↓ Sistem kirim notif ke KaSarana
[Day 1] KaSarana review & approve
[Day 2] Bendahara review budget & approve
[Day 3] KepSek final approval
[Day 4-15] Admin realisasi
[Day 16] ✅ Selesai
```

**Flexibility**: Can originate from any role (unlike workflow A which needs kerusakan)

---

## 📊 Status State Machine

```
START
  ↓
DIAJUKAN (initial state)
  ↓
┌──────────────────────────────────────┐
│ Level 1: Kepala Sarana              │
│ ❌ REJECT → DITOLAK (END)           │
│ ✅ APPROVE → DISETUJUI_KASARANA    │
└──────────────────────────────────────┘
  ↓
┌──────────────────────────────────────┐
│ Level 2: Bendahara                   │
│ ❌ REJECT → DITOLAK (END)           │
│ ✅ APPROVE → DISETUJUI_BENDAHARA   │
└──────────────────────────────────────┘
  ↓
┌──────────────────────────────────────┐
│ Level 3: Kepala Sekolah             │
│ ❌ REJECT → DITOLAK (END)           │
│ ✅ APPROVE → DISETUJUI_KEPSEK      │
└──────────────────────────────────────┘
  ↓
DIPROSES (ready for admin realization)
  ↓
Admin input realization + photos + actual cost
  ↓
SELESAI (final status, immutable)
  ↓
END ✅
```

---

## 🔔 Notification System

System secara **otomatis** mengirim WhatsApp notification ke stakeholder:

| Event             | Penerima       | Aksi              |
| ----------------- | -------------- | ----------------- |
| Laporan baru      | Kepala Sarana  | Validasi hari ini |
| Validasi selesai  | Bendahara      | Review pengajuan  |
| KaSarana approve  | Bendahara      | Continue approval |
| Bendahara approve | Kepala Sekolah | Final review      |
| KepSek approve    | Admin          | Mulai realisasi   |
| Realisasi selesai | All            | Confirmation      |

**No more manual follow-up! System alerts automatically.**

---

## 💾 Data Ownership & CRUD Permissions

```
┌────────────────────┬───────┬────────┬────────┬────────┬────────┐
│ Entity             │ Guru  │ KaS.   │ Bnh.   │ KepSek │ Admin  │
├────────────────────┼───────┼────────┼────────┼────────┼────────┤
│ Laporan Kerusakan  │ C R   │ C R U  │        │        │ C R    │
│ Pengajuan          │ C R U │ C R U  │ R      │        │ C R U  │
│ Approval Level 1   │       │ C R    │        │        │        │
│ Approval Level 2   │       │        │ C R    │        │        │
│ Approval Level 3   │       │        │        │ C R    │        │
│ Realisasi          │       │        │        │        │ C R U  │
│ Aset Inventory     │       │        │        │        │ C R U D│
│ Master Data        │       │        │        │        │ C R U D│
│ User Management    │       │        │        │        │ C R U D│
└────────────────────┴───────┴────────┴────────┴────────┴────────┘
Legend: C=Create, R=Read, U=Update, D=Delete
```

---

## 🎯 Key Success Metrics (KPI)

### Operational KPIs:

- **Average Resolution Time**: From laporan → selesai (target: 20 hari)
- **Approval Cycle Time**: DIAJUKAN → DISETUJUI_KEPSEK (target: 3 hari)
- **Compliance Rate**: % pengajuan with valid approval chain (target: 100%)
- **Asset Utilization**: % aset dalam kondisi BAIK (target: 95%)

### Financial KPIs:

- **Budget Variance**: Actual vs estimated cost (target: < 10% deviation)
- **Cost per Realization**: Total maintenance cost / count (track trend)
- **Budget Utilization**: Actual spend / allocated budget (target: 85-100%)

### User Adoption KPIs:

- **Active Users**: % role-based users accessing system weekly (target: > 80%)
- **On-time Approvals**: % approvals within SLA (target: > 90%)
- **System Uptime**: % operational time (target: 99.5%)

---

## 🚀 Implementation Timeline

### Phase 1: Setup & Training (Week 1-2)

- ✅ System deployment & configuration
- ✅ User accounts & role assignment
- ✅ Training for all 5 roles (online + offline)
- ✅ Soft launch dengan pilot group (10 users)

**Deliverable**: Trained users, system operational

---

### Phase 2: Production Launch (Week 3-4)

- ✅ Full rollout to all users
- ✅ Support helpdesk on standby
- ✅ Daily monitoring & issue fixing
- ✅ Weekly performance review

**Deliverable**: System fully operational with all users

---

### Phase 3: Optimization (Month 2-3)

- ✅ UAT feedback integration
- ✅ Report customization based on user need
- ✅ Process refinement & optimization
- ✅ Admin training on analytics

**Deliverable**: Optimized workflows, actionable dashboards

---

### Phase 4: Enhancement (Month 4+)

- ⭐ Mobile app untuk kerusakan reporting
- ⭐ Predictive maintenance scheduling
- ⭐ Multi-site federation (if expanding)
- ⭐ Advanced analytics & AI recommendations

---

## ⚠️ Risk Assessment & Mitigation

| Risk                      | Impact | Probability | Mitigation                             |
| ------------------------- | ------ | ----------- | -------------------------------------- |
| User resistance to change | High   | Medium      | Early involvement + training + support |
| Data migration issues     | High   | Low         | Dry-run + backup strategy              |
| System downtime           | High   | Low         | Redundancy + monitoring + SLA          |
| Incomplete approval chain | Medium | Medium      | Validation rules + alerts              |
| Budget overrun            | Medium | Medium      | Proper estimation + tracking           |
| Slow adoption             | Medium | Medium      | Change management + incentives         |

---

## 📚 Documentation Provided

1. ✅ **BPMN_PROFESIONAL_COMPLETE.md**: 11 detailed process diagrams
2. ✅ **SYSTEM_ANALYSIS_SENIOR_DESIGNER.md**: Full technical analysis
3. ✅ **This Executive Summary**: Stakeholder-friendly overview

---

## ❓ FAQ

**Q: Bagaimana jika Kepala Sarana reject laporan kerusakan?**  
A: Status laporan = DITOLAK, notif ke guru dengan alasan penolakan, guru bisa submit ulang laporan baru.

**Q: Apakah bisa edit pengajuan setelah submit?**  
A: Ya, selama belum di-approve level 1 (DIAJUKAN status). Setelah di-approve, status immutable.

**Q: Bagaimana tracking anggaran tahunan?**  
A: Bendahara bisa view dashboard keuangan dengan real-time actual vs budget.

**Q: Bisa approve via mobile?**  
A: WhatsApp notification punya quick-action button. Full approval form via web/mobile browser.

**Q: Siapa bisa akses laporan & export data?**  
A: Semua role bisa akses laporan. Export (PDF/Excel) available untuk admin + Kepala Sekolah.

---

## 🎓 Training Materials Needed

- ✅ Role-based user guides (5 documents)
- ✅ Video tutorial for each workflow
- ✅ FAQ & troubleshooting guide
- ✅ System admin manual
- ✅ Approval process SOP

---

## 📞 Support & Contact

- **Technical Lead**: [IT Department]
- **System Admin**: [Designated Admin]
- **Helpdesk**: Available 8am-5pm (weekdays)
- **Email**: [Support email]
- **Emergency**: [On-call number]

---

## ✅ Approval & Sign-Off

**System Design Review**: ******\_\_\_****** (Senior Designer)  
**Technology Review**: ********\_******** (IT Lead)  
**Business Owner Review**: ******\_\_****** (School Principal)  
**Approval for Implementation**: ****\_**** (Decision Maker)

**Date**: ******\_\_\_******

---

## 🎯 Next Steps

1. [ ] Review & discuss this document with stakeholders
2. [ ] Approve BPMN & system design
3. [ ] Assign implementation team
4. [ ] Schedule training sessions
5. [ ] Deploy system to production
6. [ ] Monitor & optimize

---

**Document Version**: 1.0  
**Status**: ✅ FINAL - Ready for Stakeholder Approval  
**Last Updated**: June 2026
