# BPMN Profesional Sistem SARPRAS SMKNIS

Versi: 2.0  
Tanggal: Juni 2026  
Status: As-Is, sesuai alur aplikasi saat ini  
Format: BPMN-style swimlane menggunakan Mermaid agar mudah dibaca di Markdown

Dokumen ini memetakan proses bisnis utama Sistem Manajemen Sarana Prasarana
SMKNIS berdasarkan route, controller, model status, dan role yang ada di sistem.

---

## 1. Aktor dan Tanggung Jawab

| Aktor | Tanggung Jawab Utama | Modul Terkait |
| --- | --- | --- |
| Guru/Staf | Scan QR, lapor kerusakan, buat pengajuan, pantau status | Scan QR, Kerusakan, Pengajuan, Notifikasi |
| Kepala Sarana | Validasi kerusakan, approval teknis, monitoring aset dan proses | Kerusakan, Pengajuan, Aset, Laporan |
| Bendahara | Review seluruh pengajuan, approval anggaran, verifikasi keuangan | Pengajuan, Laporan, Notifikasi |
| Kepala Sekolah | Approval final dan validasi khusus laporan dari Kepala Sarana | Pengajuan, Kerusakan, Laporan |
| Admin | Kelola aset, master data, user, QR, realisasi perawatan/penggantian | Inventaris, Master Data, User, Realisasi |
| Sistem | Validasi data, update status, simpan riwayat, kirim notifikasi | Semua modul |

---

## 2. Peta Modul Sistem

```mermaid
flowchart TB
    User[User sesuai role]
    Auth[Autentikasi dan Role Middleware]
    Dashboard[Dashboard Role]
    Scan[Scan QR Action Hub]
    Kerusakan[Laporan dan Validasi Kerusakan]
    Pengajuan[Pengajuan dan Approval Bertingkat]
    Realisasi[Realisasi Admin]
    Verifikasi[Verifikasi Teknis dan Keuangan]
    Inventaris[Inventaris Aset]
    Master[Master Data]
    UserMgmt[Manajemen User]
    Notifikasi[Notifikasi In-App dan WhatsApp]
    Laporan[Pelaporan dan Export]

    User --> Auth --> Dashboard
    Dashboard --> Scan
    Dashboard --> Kerusakan
    Dashboard --> Pengajuan
    Dashboard --> Inventaris
    Dashboard --> Master
    Dashboard --> UserMgmt
    Dashboard --> Laporan
    Scan --> Kerusakan
    Kerusakan --> Pengajuan
    Pengajuan --> Realisasi
    Realisasi --> Verifikasi
    Kerusakan --> Notifikasi
    Pengajuan --> Notifikasi
    Realisasi --> Notifikasi
    Verifikasi --> Notifikasi
    Inventaris --> Laporan
    Pengajuan --> Laporan
    Kerusakan --> Laporan
```

---

## 3. BPMN Utama: Kerusakan Aset sampai Selesai

Alur ini digunakan ketika user melaporkan kerusakan aset. Validasi kerusakan akan
membuat pengajuan otomatis untuk `PERAWATAN` atau `PENGGANTIAN`.

Catatan penting:

- Jika pelapor adalah Kepala Sarana, validator kerusakan adalah Kepala Sekolah.
- Jika pelapor selain Kepala Sarana, validator kerusakan adalah Kepala Sarana.
- Setelah validasi kerusakan oleh Kepala Sarana, pengajuan otomatis langsung masuk status `DISETUJUI_KASARANA`, sehingga tahap berikutnya adalah Bendahara.
- Pengajuan selesai setelah realisasi admin dan, jika diperlukan, verifikasi teknis/keuangan.

```mermaid
flowchart TD
    Start((Start: aset rusak))

    subgraph Pelapor["Lane: Pelapor"]
        A1[Scan QR atau pilih aset manual]
        A2[Isi tingkat kerusakan, deskripsi, dan foto]
        A3[Kirim laporan kerusakan]
    end

    subgraph Sistem1["Lane: Sistem"]
        B1{Ada laporan aktif untuk aset?}
        B2[Tolak input duplikat]
        B3[Simpan riwayat kondisi: DILAPORKAN]
        B4[Kirim notifikasi ke validator]
    end

    subgraph Validator["Lane: Kepala Sarana / Kepala Sekolah"]
        C1[Review laporan dan foto kerusakan]
        C2{Laporan valid?}
        C3[Isi catatan penolakan]
        C4[Tentukan tingkat kerusakan]
        C5[Pilih rekomendasi: PERAWATAN atau PENGGANTIAN]
        C6[Isi estimasi biaya]
        C7[Validasi kerusakan]
    end

    subgraph Sistem2["Lane: Sistem"]
        D1[Update riwayat: DIVALIDASI]
        D2[Update kondisi aset]
        D3[Buat pengajuan otomatis]
        D4[Set status pengajuan: DISETUJUI_KASARANA]
        D5[Buat approval Kepala Sarana jika validator Kepala Sarana]
        D6[Notifikasi Bendahara]
        D7[Set riwayat: DITOLAK]
        D8[Notifikasi pelapor]
    end

    subgraph Bendahara["Lane: Bendahara"]
        E1[Review pengajuan dan estimasi biaya]
        E2{Anggaran disetujui?}
        E3[Approve anggaran]
        E4[Tolak dengan catatan]
    end

    subgraph Sistem3["Lane: Sistem"]
        F1[Set status: DISETUJUI_BENDAHARA]
        F2[Notifikasi Kepala Sekolah]
        F3[Set status: DITOLAK]
        F4[Notifikasi pengaju]
    end

    subgraph Kepsek["Lane: Kepala Sekolah"]
        G1[Review final pengajuan]
        G2{Disetujui final?}
        G3[Approve final]
        G4[Tolak dengan catatan]
    end

    subgraph Sistem4["Lane: Sistem"]
        H1[Set status: DIPROSES]
        H2[Notifikasi Admin untuk realisasi]
        H3[Set status: DITOLAK]
        H4[Notifikasi pengaju]
    end

    subgraph Admin["Lane: Admin"]
        I1[Buka antrean realisasi]
        I2{Jenis pengajuan?}
        I3[Input realisasi perawatan]
        I4[Input realisasi penggantian dan aset baru]
        I5[Simpan realisasi]
    end

    subgraph Sistem5["Lane: Sistem"]
        J1[Simpan data perawatan/penggantian]
        J2[Set status: SELESAI]
        J3[Update kondisi aset: BAIK]
        J4[Sinkronkan status kerusakan: SELESAI]
        J5[Notifikasi selesai]
    end

    EndOk((End: selesai))
    EndDup((End: duplikat))
    EndReject1((End: laporan ditolak))
    EndReject2((End: pengajuan ditolak))

    Start --> A1 --> A2 --> A3 --> B1
    B1 -->|Ya| B2 --> EndDup
    B1 -->|Tidak| B3 --> B4 --> C1 --> C2
    C2 -->|Tidak| C3 --> D7 --> D8 --> EndReject1
    C2 -->|Ya| C4 --> C5 --> C6 --> C7 --> D1 --> D2 --> D3 --> D4 --> D5 --> D6
    D6 --> E1 --> E2
    E2 -->|Tidak| E4 --> F3 --> F4 --> EndReject2
    E2 -->|Ya| E3 --> F1 --> F2 --> G1 --> G2
    G2 -->|Tidak| G4 --> H3 --> H4 --> EndReject2
    G2 -->|Ya| G3 --> H1 --> H2 --> I1 --> I2
    I2 -->|PERAWATAN| I3 --> I5
    I2 -->|PENGGANTIAN| I4 --> I5
    I5 --> J1 --> J2 --> J3 --> J4 --> J5 --> EndOk
```

---

## 4. BPMN: Pengajuan Manual

Alur ini digunakan saat user membuat pengajuan tanpa melalui laporan kerusakan.
Sesuai controller saat ini, jenis pengajuan manual yang aktif adalah `PENGADAAN`
dan `PENGGANTIAN`.

```mermaid
flowchart TD
    Start((Start: user buat pengajuan))

    subgraph Pengaju["Lane: Pengaju"]
        A1[Buka form pengajuan]
        A2[Pilih jenis: PENGADAAN atau PENGGANTIAN]
        A3{Jenis pengajuan}
        A4[Isi item pengadaan: nama, kategori, ruangan, jumlah, estimasi]
        A5[Pilih aset lama untuk penggantian]
        A6[Isi judul, deskripsi, estimasi, target, lampiran]
        A7[Kirim pengajuan]
    end

    subgraph Sistem["Lane: Sistem"]
        B1[Validasi input dan lampiran]
        B2{Data valid?}
        B3[Tampilkan error form]
        B4{Untuk penggantian: ada pengajuan aktif aset ini?}
        B5[Tolak karena aset masih punya pengajuan aktif]
        B6[Simpan pengajuan]
        B7[Simpan detail pengadaan jika jenis PENGADAAN]
        B8[Set status: DIAJUKAN]
        B9[Notifikasi Kepala Sarana]
    end

    EndOk((End: menunggu approval Kepala Sarana))
    EndFix((End: perbaiki data))

    Start --> A1 --> A2 --> A3
    A3 -->|PENGADAAN| A4 --> A6
    A3 -->|PENGGANTIAN| A5 --> A6
    A6 --> A7 --> B1 --> B2
    B2 -->|Tidak| B3 --> EndFix
    B2 -->|Ya| B4
    B4 -->|Ya| B5 --> EndFix
    B4 -->|Tidak / bukan penggantian| B6 --> B7 --> B8 --> B9 --> EndOk
```

---

## 5. BPMN: Approval Bertingkat Pengajuan

Status aktual yang dipakai sistem:

1. `DIAJUKAN`
2. `DISETUJUI_KASARANA`
3. `DISETUJUI_BENDAHARA`
4. `DIPROSES`
5. `MENUNGGU_VERIFIKASI_TEKNIS`
6. `MENUNGGU_VERIFIKASI_KEUANGAN`
7. `SELESAI`
8. `DITOLAK`

```mermaid
flowchart TD
    Start((Start: status DIAJUKAN))

    subgraph Kasarana["Lane: Kepala Sarana"]
        A1[Review detail pengajuan]
        A2{Setujui teknis?}
        A3[Approve]
        A4[Tolak dengan catatan]
    end

    subgraph Sistem1["Lane: Sistem"]
        B1[Buat approval KASARANA: DISETUJUI]
        B2[Set status: DISETUJUI_KASARANA]
        B3[Notifikasi Bendahara]
        B4[Buat approval KASARANA: DITOLAK]
        B5[Set status: DITOLAK]
    end

    subgraph Bendahara["Lane: Bendahara"]
        C1[Review anggaran]
        C2{Setujui anggaran?}
        C3[Approve]
        C4[Tolak dengan catatan]
    end

    subgraph Sistem2["Lane: Sistem"]
        D1[Buat approval BENDAHARA: DISETUJUI]
        D2[Set status: DISETUJUI_BENDAHARA]
        D3[Notifikasi Kepala Sekolah]
        D4[Buat approval BENDAHARA: DITOLAK]
        D5[Set status: DITOLAK]
    end

    subgraph Kepsek["Lane: Kepala Sekolah"]
        E1[Review final]
        E2{Setujui final?}
        E3[Approve]
        E4[Tolak dengan catatan]
    end

    subgraph Sistem3["Lane: Sistem"]
        F1[Buat approval KEPSEK: DISETUJUI]
        F2[Set status: DIPROSES]
        F3[Notifikasi Admin]
        F4[Buat approval KEPSEK: DITOLAK]
        F5[Set status: DITOLAK]
    end

    EndReady((End: siap realisasi))
    EndReject((End: ditolak))

    Start --> A1 --> A2
    A2 -->|Tidak| A4 --> B4 --> B5 --> EndReject
    A2 -->|Ya| A3 --> B1 --> B2 --> B3 --> C1 --> C2
    C2 -->|Tidak| C4 --> D4 --> D5 --> EndReject
    C2 -->|Ya| C3 --> D1 --> D2 --> D3 --> E1 --> E2
    E2 -->|Tidak| E4 --> F4 --> F5 --> EndReject
    E2 -->|Ya| E3 --> F1 --> F2 --> F3 --> EndReady
```

---

## 6. BPMN: Realisasi dan Verifikasi Akhir

Admin merealisasikan pengajuan `PERAWATAN` atau `PENGGANTIAN`. Setelah realisasi,
sistem menyimpan data realisasi dan mengarah ke penyelesaian. Sistem juga
menyediakan tahap verifikasi teknis dan keuangan untuk status yang memerlukannya.

```mermaid
flowchart TD
    Start((Start: status DIPROSES))

    subgraph Admin["Lane: Admin"]
        A1[Buka daftar realisasi]
        A2[Pilih pengajuan]
        A3{Jenis pengajuan}
        A4[Isi data perawatan: tanggal, teknisi, vendor, biaya, foto, catatan]
        A5[Isi data penggantian: aset lama, aset baru, vendor, biaya, foto, status]
        A6[Simpan realisasi]
    end

    subgraph Sistem1["Lane: Sistem"]
        B1[Validasi data realisasi]
        B2{Valid?}
        B3[Tampilkan error]
        B4[Simpan perawatan atau penggantian]
        B5[Set status pengajuan: SELESAI]
        B6[Update kondisi aset menjadi BAIK]
        B7[Sinkronkan riwayat kerusakan menjadi SELESAI]
        B8[Notifikasi pengaju dan pihak terkait]
    end

    subgraph Kasarana["Lane: Kepala Sarana"]
        C1[Verifikasi teknis jika status MENUNGGU_VERIFIKASI_TEKNIS]
        C2{Butuh verifikasi keuangan?}
    end

    subgraph Bendahara["Lane: Bendahara"]
        D1[Verifikasi keuangan jika status MENUNGGU_VERIFIKASI_KEUANGAN]
    end

    subgraph Sistem2["Lane: Sistem"]
        E1[Set status: MENUNGGU_VERIFIKASI_KEUANGAN]
        E2[Set status: SELESAI]
        E3[Update kondisi aset: BAIK]
        E4[Notifikasi selesai]
    end

    EndOk((End: pengajuan selesai))
    EndFix((End: perbaiki data))

    Start --> A1 --> A2 --> A3
    A3 -->|PERAWATAN| A4 --> A6
    A3 -->|PENGGANTIAN| A5 --> A6
    A6 --> B1 --> B2
    B2 -->|Tidak| B3 --> EndFix
    B2 -->|Ya| B4 --> B5 --> B6 --> B7 --> B8 --> EndOk

    C1 --> C2
    C2 -->|Ya| E1 --> D1 --> E2 --> E3 --> E4 --> EndOk
    C2 -->|Tidak| E2 --> E3 --> E4 --> EndOk
```

---

## 7. BPMN: Scan QR Action Hub

Scan QR menjadi titik awal cepat untuk mencari aset dan mengarahkan user ke aksi
sesuai role.

```mermaid
flowchart TD
    Start((Start: buka scan QR))

    subgraph User["Lane: User"]
        A1[Scan QR atau input kode aset]
        A2[Pilih aset dari hasil pencarian]
        A3[Pilih aksi]
    end

    subgraph Sistem["Lane: Sistem"]
        B1[Normalisasi kode aset]
        B2{Format kode valid?}
        B3[Tampilkan pesan format tidak valid]
        B4[Cari aset exact atau partial match]
        B5{Aset ditemukan?}
        B6[Tampilkan detail aset dan histori ringkas]
        B7[Tampilkan aksi sesuai role]
        B8[Catat log aktivitas SCAN_QR]
        B9[Redirect ke modul tujuan]
    end

    subgraph Aksi["Lane: Aksi Role"]
        C1[Guru: lapor kerusakan / usulan mutasi]
        C2[Kepala Sarana: lapor kerusakan / validasi / approval]
        C3[Bendahara: lapor kerusakan / review pengajuan]
        C4[Kepala Sekolah: lapor kerusakan / approval final]
        C5[Admin: lapor kerusakan / lihat histori / usulan mutasi]
    end

    EndOk((End: masuk modul aksi))
    EndNo((End: aset tidak ditemukan))
    EndInvalid((End: kode tidak valid))

    Start --> A1 --> B1 --> B2
    B2 -->|Tidak| B3 --> EndInvalid
    B2 -->|Ya| B4 --> B5
    B5 -->|Tidak| EndNo
    B5 -->|Ya| B6 --> B7 --> A2 --> A3 --> B8
    B8 --> C1 --> B9
    B8 --> C2 --> B9
    B8 --> C3 --> B9
    B8 --> C4 --> B9
    B8 --> C5 --> B9
    B9 --> EndOk
```

---

## 8. BPMN: Manajemen Inventaris Aset

Modul ini hanya untuk Admin. Data aset dapat dibuat satuan, batch per ruangan,
batch per kategori, import massal, edit, arsip, atau hapus permanen.

```mermaid
flowchart TD
    Start((Start: Admin buka inventaris))

    subgraph Admin["Lane: Admin"]
        A1[Pilih operasi aset]
        A2{Operasi}
        A3[Tambah aset satuan]
        A4[Tambah aset per ruangan]
        A5[Tambah aset per kategori]
        A6[Import massal]
        A7[Edit aset]
        A8[Hapus aset]
    end

    subgraph Sistem["Lane: Sistem"]
        B1[Validasi input aset]
        B2{Valid?}
        B3[Tampilkan error]
        B4[Generate kode aset otomatis]
        B5[Generate nama aset berurutan]
        B6[Simpan atau update aset]
        B7{Hapus: aset punya relasi proses?}
        B8[Soft delete atau arsipkan aset]
        B9[Force delete aset dan hapus foto]
        B10[Tampilkan pesan hasil operasi]
    end

    EndOk((End: data aset diperbarui))
    EndFix((End: perbaiki data))

    Start --> A1 --> A2
    A2 -->|Satuan| A3 --> B1
    A2 -->|Per ruangan| A4 --> B1
    A2 -->|Per kategori| A5 --> B1
    A2 -->|Import massal| A6 --> B1
    A2 -->|Edit| A7 --> B1
    A2 -->|Hapus| A8 --> B7
    B1 --> B2
    B2 -->|Tidak| B3 --> EndFix
    B2 -->|Ya| B4 --> B5 --> B6 --> B10 --> EndOk
    B7 -->|Ya| B8 --> B10 --> EndOk
    B7 -->|Tidak| B9 --> B10 --> EndOk
```

---

## 9. BPMN: Master Data dan Manajemen User

Master data meliputi gedung, ruangan, dan kategori aset. Manajemen user meliputi
tambah user, update user, hapus user, dan reset password.

```mermaid
flowchart TD
    Start((Start: Admin buka administrasi))

    subgraph Admin["Lane: Admin"]
        A1[Pilih modul administrasi]
        A2{Modul}
        A3[Kelola gedung]
        A4[Kelola ruangan]
        A5[Kelola kategori aset]
        A6[Kelola user]
        A7[Pilih aksi: tambah, edit, hapus, reset password]
    end

    subgraph Sistem["Lane: Sistem"]
        B1[Validasi input dan hak akses]
        B2{Valid?}
        B3[Tampilkan error]
        B4{Aksi hapus punya relasi aktif?}
        B5[Tolak hapus data yang masih dipakai]
        B6[Simpan perubahan]
        B7[Hash password jika reset password]
        B8[Tampilkan pesan hasil operasi]
    end

    EndOk((End: administrasi selesai))
    EndFix((End: perbaiki data))
    EndReject((End: operasi ditolak))

    Start --> A1 --> A2
    A2 -->|Gedung| A3 --> A7
    A2 -->|Ruangan| A4 --> A7
    A2 -->|Kategori| A5 --> A7
    A2 -->|User| A6 --> A7
    A7 --> B1 --> B2
    B2 -->|Tidak| B3 --> EndFix
    B2 -->|Ya| B4
    B4 -->|Ya| B5 --> EndReject
    B4 -->|Tidak / bukan hapus| B6 --> B7 --> B8 --> EndOk
```

---

## 10. BPMN: Notifikasi

Notifikasi dibuat dari perubahan penting seperti laporan kerusakan, validasi,
approval, penolakan, realisasi, dan verifikasi. Sistem menyimpan notifikasi di
database dan mengirim WhatsApp ke user tujuan.

```mermaid
flowchart TD
    Start((Start: event proses terjadi))

    subgraph Sistem["Lane: Sistem"]
        A1[Identifikasi event]
        A2[Tentukan target role atau target user]
        A3[Susun judul, isi, dan URL]
        A4{Ada notifikasi thread yang sama?}
        A5[Update notifikasi lama dan set unread]
        A6[Buat notifikasi baru]
        A7[Kirim WhatsApp ke user tujuan]
    end

    subgraph User["Lane: User"]
        B1[Melihat badge notifikasi]
        B2[Membuka notifikasi]
        B3[Klik URL menuju halaman terkait]
    end

    subgraph Sistem2["Lane: Sistem"]
        C1[Mark as read]
        C2[Mark all as read jika user memilih baca semua]
    end

    End((End: notifikasi ditangani))

    Start --> A1 --> A2 --> A3 --> A4
    A4 -->|Ya| A5 --> A7
    A4 -->|Tidak| A6 --> A7
    A7 --> B1 --> B2 --> B3 --> C1 --> End
    B2 --> C2 --> End
```

---

## 11. BPMN: Pelaporan dan Export

Modul laporan tersedia untuk Admin, Kepala Sarana, Bendahara, dan Kepala Sekolah.
Guru tidak memiliki akses laporan.

```mermaid
flowchart TD
    Start((Start: user buka laporan))

    subgraph User["Lane: User Terotorisasi"]
        A1[Pilih filter periode, gedung, ruangan, kategori, status, keyword]
        A2[Pilih output]
        A3{Output}
    end

    subgraph Sistem["Lane: Sistem"]
        B1[Validasi role laporan]
        B2{Role diizinkan?}
        B3[Tolak akses]
        B4[Query aset, pengajuan, kerusakan, perawatan, penggantian]
        B5[Hitung KPI]
        B6[Hitung estimasi dan realisasi biaya]
        B7[Susun data tren]
    end

    subgraph Output["Lane: Output"]
        C1[Tampilkan dashboard laporan]
        C2[Generate Excel multi sheet]
        C3[Render PDF view]
    end

    EndOk((End: laporan tersedia))
    EndReject((End: akses ditolak))

    Start --> B1 --> B2
    B2 -->|Tidak| B3 --> EndReject
    B2 -->|Ya| A1 --> A2 --> A3
    A3 -->|Dashboard| B4 --> B5 --> B6 --> B7 --> C1 --> EndOk
    A3 -->|Excel| B4 --> B5 --> B6 --> B7 --> C2 --> EndOk
    A3 -->|PDF| B4 --> B5 --> B6 --> B7 --> C3 --> EndOk
```

---

## 12. Aturan Bisnis Penting

### 12.1 Status Pengajuan

| Status | Makna | Pemilik Aksi Berikutnya |
| --- | --- | --- |
| `DIAJUKAN` | Pengajuan baru menunggu approval teknis | Kepala Sarana |
| `DISETUJUI_KASARANA` | Approval teknis selesai, menunggu anggaran | Bendahara |
| `DISETUJUI_BENDAHARA` | Approval anggaran selesai, menunggu final | Kepala Sekolah |
| `DISETUJUI_KEPSEK` | Status final lama yang masih dikenali sistem | Admin |
| `DIPROSES` | Pengajuan siap/sedang direalisasikan | Admin |
| `MENUNGGU_VERIFIKASI_TEKNIS` | Realisasi perlu verifikasi teknis | Kepala Sarana |
| `MENUNGGU_VERIFIKASI_KEUANGAN` | Realisasi perlu verifikasi biaya | Bendahara |
| `SELESAI` | Proses selesai | Tidak ada |
| `DITOLAK` | Pengajuan ditolak di salah satu approval | Tidak ada |

### 12.2 Status Kerusakan

| Status | Makna | Pemilik Aksi Berikutnya |
| --- | --- | --- |
| `DILAPORKAN` | Laporan baru masuk | Kepala Sarana atau Kepala Sekolah |
| `DIVALIDASI` | Laporan valid dan pengajuan otomatis dibuat | Sistem / Pengajuan |
| `DITINDAKLANJUTI` | Kerusakan sedang ditangani | Admin / Verifikator |
| `SELESAI` | Kerusakan selesai ditangani | Tidak ada |
| `DITOLAK` | Laporan kerusakan ditolak | Tidak ada |

### 12.3 Kondisi Aset

| Kondisi | Makna |
| --- | --- |
| `BAIK` | Aset normal dan dapat digunakan |
| `RINGAN` | Rusak ringan, umumnya direkomendasikan perawatan |
| `BERAT` | Rusak berat, dapat memerlukan penggantian |
| `TIDAK_LAYAK` | Aset tidak layak digunakan |

### 12.4 Aturan Validasi Kunci

| Aturan | Dampak |
| --- | --- |
| Satu aset tidak boleh punya laporan kerusakan aktif ganda | Sistem menolak laporan baru |
| Satu aset tidak boleh punya pengajuan penggantian aktif ganda | Sistem menolak pengajuan baru |
| Approval harus sesuai urutan role | Role yang salah ditolak |
| Pengaju tidak boleh meng-approve pengajuan sendiri | Anti self-approval |
| Aset yang sudah punya relasi proses tidak dihapus permanen | Sistem mengarsipkan aset |
| Laporan hanya bisa diakses role tertentu | Guru tidak bisa membuka modul laporan |

---

## 13. Ringkasan Alur End-to-End

```mermaid
flowchart LR
    A[Scan QR / pilih aset] --> B[Lapor kerusakan atau buat pengajuan]
    B --> C{Sumber proses}
    C -->|Kerusakan| D[Validasi kerusakan]
    D --> E[Pengajuan otomatis]
    C -->|Manual| F[Pengajuan manual]
    E --> G[Approval Bendahara]
    F --> H[Approval Kepala Sarana]
    H --> G
    G --> I[Approval Kepala Sekolah]
    I --> J[Realisasi Admin]
    J --> K[Update aset dan riwayat]
    K --> L[Notifikasi selesai]
    L --> M[Laporan dan export]
```

---

## 14. Rekomendasi Penggunaan Dokumen

1. Gunakan Bab 3 sebagai BPMN utama untuk presentasi sistem.
2. Gunakan Bab 4 dan Bab 5 untuk menjelaskan pengajuan dan approval.
3. Gunakan Bab 6 untuk menjelaskan penyelesaian pekerjaan admin.
4. Gunakan Bab 12 sebagai referensi status ketika membuat SOP atau manual user.
5. Jika diagram akan dimasukkan ke Word/Canva, render Mermaid menjadi gambar PNG/SVG.

