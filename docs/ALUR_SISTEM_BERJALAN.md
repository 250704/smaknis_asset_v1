# ALUR SISTEM YANG SUDAH BERJALAN

## Sistem Pengajuan Sarpras SMAKNIS

> Dokumen ini menjelaskan alur sistem yang **sudah berjalan** di kode saat ini,  
> berdasarkan analisis langsung dari `PengajuanController`, `KerusakanController`, routes, dan database schema.

---

## DAFTAR ROLE

| No  | Role           | Kode             |
| --- | -------------- | ---------------- |
| 1   | Guru           | `guru`           |
| 2   | Admin          | `admin`          |
| 3   | Kepala Sarana  | `kepala_sarana`  |
| 4   | Bendahara      | `bendahara`      |
| 5   | Kepala Sekolah | `kepala_sekolah` |

---

## STATUS PENGAJUAN (Urutan Lifecycle)

```
DIAJUKAN → DISETUJUI_KASARANA → DISETUJUI_BENDAHARA → DIPROSES → SELESAI
                                                                ↘ DITOLAK (bisa di tahap mana saja)
```

**Catatan:** Status `MENUNGGU_VERIFIKASI_TEKNIS` dan `MENUNGGU_VERIFIKASI_KEUANGAN` ada di schema tapi **saat ini di-skip** — realisasi perawatan/penggantian langsung ke `SELESAI`.

---

## ALUR 1: PENGADAAN (Buat Manual — Semua Role)

### Siapa yang bisa: Guru, Admin, Kepala Sarana, Bendahara, Kepala Sekolah

```
╔══════════════════════════════════════════════════════════════════════════╗
║  ALUR PENGADAAN — Sarana Baru                                          ║
╠══════════════════════════════════════════════════════════════════════════╣
║                                                                        ║
║  ① [Semua Role] Buat Pengajuan PENGADAAN                               ║
║     │  Form: guru.pengajuan.create                                     ║
║     │  View: guru/pengajuan/create.blade.php                           ║
║     │  Jenis yang tersedia: PENGADAAN saja                             ║
║     │                                                                  ║
║     │  Input:                                                          ║
║     │  • Judul pengajuan                                               ║
║     │  • Deskripsi                                                     ║
║     │  • Estimasi biaya (opsional, auto-hitung dari items)             ║
║     │  • Target realisasi (opsional)                                   ║
║     │  • Lampiran (max 5 file, opsional)                               ║
║     │  • Items pengadaan (min 1):                                      ║
║     │    - Nama sarana rencana                                         ║
║     │    - Kategori                                                    ║
║     │    - Ruangan tujuan                                              ║
║     │    - Jumlah                                                      ║
║     │    - Spesifikasi (opsional)                                      ║
║     │    - Estimasi harga satuan (opsional)                            ║
║     │                                                                  ║
║     ▼                                                                  ║
║  Status: DIAJUKAN                                                      ║
║     │                                                                  ║
║  ② [Kepala Sarana] Review & Approval Teknis                            ║
║     │  Route: kepala_sarana.pengajuan.approve                          ║
║     │  Validasi: status harus DIAJUKAN                                 ║
║     │  Tidak bisa approve pengajuan milik sendiri                      ║
║     │  • Setuju → DISETUJUI_KASARANA                                   ║
║     │  • Tolak → DITOLAK (harus isi catatan)                           ║
║     │                                                                  ║
║     ▼                                                                  ║
║  ③ [Bendahara] Review & Approval Anggaran                             ║
║     │  Route: bendahara.pengajuan.approve                              ║
║     │  Validasi: status harus DISETUJUI_KASARANA                       ║
║     │  Tidak bisa approve pengajuan milik sendiri                      ║
║     │  • Setuju → DISETUJUI_BENDAHARA                                  ║
║     │  • Tolak → DITOLAK (harus isi catatan)                           ║
║     │                                                                  ║
║     ▼                                                                  ║
║  ④ [Kepala Sekolah] Approval Final                                     ║
║     │  Route: kepala_sekolah.pengajuan.approve                         ║
║     │  Validasi: status harus DISETUJUI_BENDAHARA                      ║
║     │  Tidak bisa approve pengajuan milik sendiri                      ║
║     │  • Setuju → DIPROSES                                             ║
║     │  • Tolak → DITOLAK (harus isi catatan)                           ║
║     │                                                                  ║
║     ▼                                                                  ║
║  ⑤ [Admin] Realisasi Pengadaan                                        ║
║     │  Route: admin.realisasi.pengadaan                                ║
║     │  (Detail implementasi realisasi pengadaan)                       ║
║     │  → Status: SELESAI                                               ║
║     │                                                                  ║
║     ▼                                                                  ║
║  ✅ SELESAI                                                             ║
║                                                                        ║
╚══════════════════════════════════════════════════════════════════════════╝
```

---

## ALUR 2: PERAWATAN & PENGGANTIAN (Otomatis dari Kerusakan)

### Siapa yang memulai: Guru atau Kepala Sarana (lapor kerusakan)

```
╔══════════════════════════════════════════════════════════════════════════╗
║  ALUR PERAWATAN/PENGGANTIAN — Dari Laporan Kerusakan                   ║
╠══════════════════════════════════════════════════════════════════════════╣
║                                                                        ║
║  ① [Guru/Kepala Sarana] Lapor Kerusakan via Scan QR                    ║
║     │  Route: guru.kerusakan.create / kepala_sarana.kerusakan.create   ║
║     │  Controller: KerusakanController@create / @store                 ║
║     │                                                                  ║
║     │  Input:                                                          ║
║     │  • kode_sarana (dari scan QR)                                    ║
║     │  • Tingkat kerusakan: RINGAN / BERAT / TIDAK_LAYAK               ║
║     │  • Deskripsi kerusakan                                           ║
║     │  • Foto kerusakan (wajib, max 4MB)                               ║
║     │                                                                  ║
║     │  Simpan ke: tabel riwayat_kondisi_sarana                         ║
║     │                                                                  ║
║     ▼                                                                  ║
║  Status Kerusakan: DILAPORKAN                                          ║
║     │                                                                  ║
║     │  ┌─────────────────────────────────────────────────────────┐      ║
║     │  │ PERCABANGAN VALIDATOR:                                  │      ║
║     │  │                                                         │      ║
║     │  │ • Jika pelapor = GURU                                   │      ║
║     │  │   → Validator = KEPALA SARANA                            │      ║
║     │  │                                                         │      ║
║     │  │ • Jika pelapor = KEPALA SARANA                           │      ║
║     │  │   → Validator = BENDAHARA                                │      ║
║     │  │   (Kepala Sarana tidak bisa validasi laporannya sendiri) │      ║
║     │  └─────────────────────────────────────────────────────────┘      ║
║     │                                                                  ║
║     ▼                                                                  ║
║  ② [Kepala Sarana / Bendahara] Validasi Kerusakan                      ║
║     │  Route: kepala_sarana.kerusakan.validate                         ║
║     │  Controller: KerusakanController@validateKerusakan               ║
║     │                                                                  ║
║     │  Opsi:                                                           ║
║     │  ┌──────────────┐    ┌──────────────────────────────────────┐     ║
║     │  │ TOLAK         │    │ VALIDASI                             │     ║
║     │  │ • Catatan     │    │ • Tingkat kerusakan (update)         │     ║
║     │  │ → DITOLAK     │    │ • Rekomendasi: PERAWATAN/PENGGANTIAN│     ║
║     │  │ (selesai)     │    │ • Estimasi biaya                    │     ║
║     │  └──────────────┘    │ • Catatan (opsional)                 │     ║
║     │                      └──────────────────────────────────────┘     ║
║     │                                                                  ║
║     │  Jika VALIDASI:                                                  ║
║     │  • Update riwayat_kondisi_sarana → status: DIVALIDASI            ║
║     │  • Update kondisi_terkini pada sarana                            ║
║     │  • Cek apakah sudah ada pengajuan aktif untuk sarana ini         ║
║     │    (jika ada → tolak dengan error)                               ║
║     │                                                                  ║
║     │  ══════════════════════════════════════════════════                ║
║     │  ║ OTOMATIS BUAT PENGAJUAN PERAWATAN/PENGGANTIAN ║               ║
║     │  ══════════════════════════════════════════════════                ║
║     │                                                                  ║
║     │  Data pengajuan otomatis:                                        ║
║     │  • sarana_id = sarana yang dilaporkan                            ║
║     │  • user_id = validator (yang memvalidasi)                        ║
║     │  • judul = "Perawatan/Penggantian Sarana {kode_sarana}"         ║
║     │  • jenis_pengajuan = PERAWATAN atau PENGGANTIAN                  ║
║     │  • deskripsi = dari laporan kerusakan                            ║
║     │  • estimasi_biaya = dari input validasi                          ║
║     │                                                                  ║
║     │  ┌─────────────────────────────────────────────────────────┐      ║
║     │  │ STATUS AWAL PENGAJUAN:                                  │      ║
║     │  │                                                         │      ║
║     │  │ • Validator = Kepala Sarana                              │      ║
║     │  │   → Status: DISETUJUI_KASARANA (skip approval kepsar)   │      ║
║     │  │   → Record approval kepsar otomatis dibuat              │      ║
║     │  │                                                         │      ║
║     │  │ • Validator = Bendahara (laporan dari kepsar)            │      ║
║     │  │   → Status: DISETUJUI_BENDAHARA (skip approval bendhr)  │      ║
║     │  │   → Record approval bendahara otomatis dibuat           │      ║
║     │  └─────────────────────────────────────────────────────────┘      ║
║     │                                                                  ║
║     ▼                                                                  ║
║                                                                        ║
║  ═══════════════════════════════════════════════════════════            ║
║  Lanjut ke APPROVAL CHAIN (sesuai status awal):                        ║
║  ═══════════════════════════════════════════════════════════            ║
║                                                                        ║
║  SKENARIO A: Pelapor = Guru, Validator = Kepala Sarana                 ║
║  ─────────────────────────────────────────────────────                  ║
║  Status awal: DISETUJUI_KASARANA                                       ║
║     │                                                                  ║
║  ③a [Bendahara] Approval Anggaran                                      ║
║     │  • Setuju → DISETUJUI_BENDAHARA                                  ║
║     │  • Tolak → DITOLAK                                               ║
║     ▼                                                                  ║
║  ④a [Kepala Sekolah] Approval Final                                    ║
║     │  • Setuju → DIPROSES                                             ║
║     │  • Tolak → DITOLAK                                               ║
║     ▼                                                                  ║
║  ⑤a [Admin] Realisasi                                                  ║
║     │  → SELESAI                                                       ║
║                                                                        ║
║  SKENARIO B: Pelapor = Kepala Sarana, Validator = Bendahara            ║
║  ─────────────────────────────────────────────────────                  ║
║  Status awal: DISETUJUI_BENDAHARA                                      ║
║     │                                                                  ║
║  ③b [Kepala Sekolah] Approval Final                                    ║
║     │  • Setuju → DIPROSES                                             ║
║     │  • Tolak → DITOLAK                                               ║
║     ▼                                                                  ║
║  ④b [Admin] Realisasi                                                  ║
║     │  → SELESAI                                                       ║
║                                                                        ║
╚══════════════════════════════════════════════════════════════════════════╝
```

---

## DETAIL REALISASI (Admin)

### Realisasi Perawatan

```
Route: admin.realisasi.perawatan.store
Controller: PengajuanController@realisasiPerawatan
Hanya role: admin

Input:
• Tanggal perawatan (wajib)
• Biaya realisasi (wajib)
• Keterangan (wajib)
• Nama teknisi (wajib)
• Kontak teknisi (opsional)
• Nama vendor (wajib)
• Kontak vendor (opsional)
• Foto sesudah (wajib, image max 4MB)
• Foto bukti (opsional, image max 4MB)

Efek:
• Simpan data ke tabel perawatan
• Status pengajuan → SELESAI (langsung, tanpa verifikasi)
• Kondisi sarana → BAIK
• Riwayat kerusakan → SELESAI
```

### Realisasi Penggantian

```
Route: admin.realisasi.penggantian.store
Controller: PengajuanController@realisasiPenggantian
Hanya role: admin

Input:
• Tanggal penggantian (wajib)
• Biaya realisasi (wajib)
• Keterangan (wajib)
• Nama teknisi (wajib)
• Kontak teknisi (opsional)
• Nama vendor (wajib)
• Kontak vendor (opsional)
• Kode sarana baru (opsional)
• Foto sarana baru (opsional, image max 4MB)
• Foto bukti (opsional, image max 4MB)

Efek:
• Simpan data ke tabel penggantian
• Status pengajuan → SELESAI (langsung, tanpa verifikasi)
• Kondisi sarana lama → BAIK
• Jika ada sarana baru → kondisi BAIK
• Riwayat kerusakan → SELESAI
```

### Realisasi Pengadaan

```
Route: admin.realisasi.pengadaan
Controller: PengajuanController (implementasi detail pengadaan)
Hanya role: admin
```

---

## FITUR VERIFIKASI (ADA DI KODE, TAPI SAAT INI DI-SKIP)

```
Verifikasi Teknis (Kepala Sarana):
• Route: kepala_sarana.pengajuan.verifikasi-teknis
• Aktif jika status = MENUNGGU_VERIFIKASI_TEKNIS
• Lanjut ke MENUNGGU_VERIFIKASI_KEUANGAN atau SELESAI

Verifikasi Keuangan (Bendahara):
• Route: bendahara.pengajuan.verifikasi-keuangan
• Aktif jika status = MENUNGGU_VERIFIKASI_KEUANGAN
• → SELESAI

CATATAN: Saat ini realisasi perawatan & penggantian langsung
set status SELESAI tanpa melalui verifikasi teknis/keuangan.
```

---

## RINGKASAN AKSES PER ROLE

### 👨‍🏫 GURU

| Fitur                     | Akses |
| ------------------------- | ----- |
| Buat pengajuan PENGADAAN  | ✅    |
| Lapor kerusakan (scan QR) | ✅    |
| Lihat pengajuan sendiri   | ✅    |
| Approval                  | ❌    |
| Realisasi                 | ❌    |
| Lihat semua pengajuan     | ❌    |

### 🔧 KEPALA SARANA

| Fitur                                | Akses                                    |
| ------------------------------------ | ---------------------------------------- |
| Buat pengajuan PENGADAAN             | ✅                                       |
| Lapor kerusakan (scan QR)            | ✅                                       |
| Validasi kerusakan (dari guru)       | ✅                                       |
| Approval pengajuan (tahap 1)         | ✅ Status: DIAJUKAN → DISETUJUI_KASARANA |
| Review semua pengajuan               | ✅                                       |
| Lihat pengajuan sendiri              | ✅                                       |
| Verifikasi teknis (ada tapi di-skip) | ✅                                       |
| Realisasi                            | ❌                                       |

### 💰 BENDAHARA

| Fitur                                  | Akses                                               |
| -------------------------------------- | --------------------------------------------------- |
| Buat pengajuan PENGADAAN               | ✅                                                  |
| Validasi kerusakan (dari kepsar)       | ✅                                                  |
| Approval pengajuan (tahap 2)           | ✅ Status: DISETUJUI_KASARANA → DISETUJUI_BENDAHARA |
| Review semua pengajuan                 | ✅                                                  |
| Lihat pengajuan sendiri                | ✅                                                  |
| Verifikasi keuangan (ada tapi di-skip) | ✅                                                  |
| Lapor kerusakan                        | ❌                                                  |
| Realisasi                              | ❌                                                  |

### 🏫 KEPALA SEKOLAH

| Fitur                                | Akses                                     |
| ------------------------------------ | ----------------------------------------- |
| Buat pengajuan PENGADAAN             | ✅                                        |
| Approval pengajuan (tahap 3 - final) | ✅ Status: DISETUJUI_BENDAHARA → DIPROSES |
| Review kerusakan dari kepsar         | ✅ (view only)                            |
| Lihat pengajuan sendiri              | ✅                                        |
| Lapor kerusakan                      | ❌                                        |
| Validasi kerusakan                   | ❌                                        |
| Realisasi                            | ❌                                        |

### 👤 ADMIN

| Fitur                    | Akses |
| ------------------------ | ----- |
| Buat pengajuan PENGADAAN | ✅    |
| Lihat semua pengajuan    | ✅    |
| Lihat pengajuan sendiri  | ✅    |
| Realisasi perawatan      | ✅    |
| Realisasi penggantian    | ✅    |
| Realisasi pengadaan      | ✅    |
| Approval                 | ❌    |
| Lapor kerusakan          | ❌    |
| Validasi kerusakan       | ❌    |

---

## DIAGRAM ALUR KESELURUHAN

```
                    ┌───────────────┐
                    │   SCAN QR     │
                    │  (Guru/Kepsar)│
                    └───────┬───────┘
                            │
                ┌───────────┴───────────┐
                │                       │
                ▼                       ▼
        ┌──────────────┐      ┌──────────────────┐
        │ Lapor         │      │ Buat Pengajuan    │
        │ Kerusakan     │      │ PENGADAAN         │
        │               │      │ (semua role bisa)  │
        └──────┬───────┘      └────────┬──────────┘
               │                        │
               ▼                        │
        ┌──────────────┐               │
        │ Status:       │               │
        │ DILAPORKAN    │               │
        └──────┬───────┘               │
               │                        │
               ▼                        │
        ┌──────────────┐               │
        │ Validasi      │               │
        │ Kerusakan     │               │
        │ (Kepsar/      │               │
        │  Bendahara)   │               │
        └──────┬───────┘               │
               │                        │
               │ Auto-create            │
               │ Pengajuan              │
               │ PERAWATAN/             │
               │ PENGGANTIAN            │
               │                        │
               ▼                        ▼
        ┌──────────────────────────────────────┐
        │         APPROVAL CHAIN                │
        │                                      │
        │  DIAJUKAN                            │
        │     ↓ (Kepala Sarana)                 │
        │  DISETUJUI_KASARANA ←─── atau mulai  │
        │     ↓ (Bendahara)        dari sini   │
        │  DISETUJUI_BENDAHARA ←── atau sini   │
        │     ↓ (Kepala Sekolah)               │
        │  DIPROSES                            │
        └──────────────┬───────────────────────┘
                       │
                       ▼
                ┌──────────────┐
                │  REALISASI   │
                │  (Admin)     │
                │              │
                │ Perawatan /  │
                │ Penggantian /│
                │ Pengadaan    │
                └──────┬───────┘
                       │
                       ▼
                ┌──────────────┐
                │   SELESAI    │
                └──────────────┘
```

---

## NOTIFIKASI

Setiap perubahan status menghasilkan notifikasi tracking yang di-broadcast ke user terkait melalui:

1. **Notifikasi in-app** (tabel `notifikasi`)
2. **WhatsApp** (via `WhatsAppNotificationService`)

---

_Dokumen ini di-generate berdasarkan analisis kode pada 12 Juli 2026_
