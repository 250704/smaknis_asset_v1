# BPMN Profesional Sistem Sarpras (As-Is)

Dokumen ini adalah BPMN **as-is** (kondisi sistem saat ini di kode), disusun dengan gaya profesional untuk kebutuhan laporan/skripsi.

## Konvensi
- `Start Event` / `End Event` = awal/akhir proses.
- `Task` = aktivitas oleh aktor/sistem.
- `Exclusive Gateway (XOR)` = keputusan ya/tidak.
- `Lane` = pemisahan tanggung jawab aktor.

## 1) BPMN Inti: Siklus Kerusakan -> Pengajuan -> Approval -> Realisasi

```mermaid
flowchart LR
  %% Lanes
  subgraph L1[Lane: Pelapor (Guru/Admin/Kepala Sarana/Bendahara/Kepala Sekolah)]
    A((Start Event)) --> B[Task: Input laporan kerusakan]
  end

  subgraph L2[Lane: Sistem]
    B --> C{XOR: Ada laporan aktif untuk sarana?}
    C -- Ya --> C1[End Event: Laporan ditolak sistem]
    C -- Tidak --> D[Task: Simpan riwayat status DILAPORKAN]
    D --> E{XOR: Pelapor adalah Kepala Sarana?}
  end

  subgraph L3[Lane: Validator]
    E -- Ya --> F1[Task: Kepala Sekolah validasi]
    E -- Tidak --> F2[Task: Kepala Sarana validasi]
    F1 --> G{XOR: Aksi TOLAK?}
    F2 --> G
    G -- Ya --> H[Task: Set riwayat DITOLAK + catatan]
    G -- Tidak --> I[Task: Input rekomendasi dan estimasi]
  end

  subgraph L2b[Lane: Sistem]
    I --> J[Task: Set riwayat DIVALIDASI]
    J --> K[Task: Update kondisi sarana sesuai tingkat kerusakan]
    K --> L[Task: Auto-create pengajuan]
    L --> M[Task: Set status pengajuan DISETUJUI_KASARANA]
    M --> N{XOR: Validator = Kepala Sarana?}
    N -- Ya --> O[Task: Simpan approval KASARANA]
    N -- Tidak --> P[Task: Lewati approval KASARANA]
    O --> Q[Task: Kirim notifikasi ke Bendahara]
    P --> Q
  end

  subgraph L4[Lane: Bendahara]
    Q --> R[Task: Review pengajuan]
    R --> S{XOR: Approve?}
    S -- Tidak --> T[Task: Tolak pengajuan]
    S -- Ya --> U[Task: Approve anggaran]
  end

  subgraph L2c[Lane: Sistem]
    U --> V[Task: Set status DISETUJUI_BENDAHARA]
    T --> T1[Task: Set status DITOLAK]
  end

  subgraph L5[Lane: Kepala Sekolah]
    V --> W[Task: Approval final]
    W --> X{XOR: Approve?}
    X -- Tidak --> Y[Task: Tolak pengajuan]
    X -- Ya --> Z[Task: Setujui final]
  end

  subgraph L2d[Lane: Sistem]
    Z --> AA[Task: Set status DIPROSES]
    Y --> Y1[Task: Set status DITOLAK]
  end

  subgraph L6[Lane: Admin]
    AA --> AB[Task: Input realisasi perawatan/penggantian]
  end

  subgraph L2e[Lane: Sistem]
    AB --> AC[Task: Simpan realisasi]
    AC --> AD[Task: Set status pengajuan SELESAI]
    AD --> AE[Task: Update kondisi sarana BAIK]
    AE --> AF[Task: Sync riwayat kerusakan SELESAI]
    AF --> AG((End Event: Proses selesai))
    H --> AH((End Event: Laporan kerusakan ditolak))
    T1 --> AI((End Event: Pengajuan ditolak di bendahara))
    Y1 --> AJ((End Event: Pengajuan ditolak di kepala sekolah))
  end
```

## 2) BPMN Pengajuan Manual (Tanpa Berasal dari Kerusakan)

```mermaid
flowchart LR
  subgraph P1[Lane: Pengaju]
    A((Start Event)) --> B[Task: Buka form pengajuan]
    B --> C[Task: Isi data pengajuan]
    C --> D{XOR: Jenis pengajuan}
    D -- PENGADAAN --> E[Task: Isi item pengadaan]
    D -- PENGGANTIAN --> F[Task: Pilih sarana lama]
  end

  subgraph P2[Lane: Sistem]
    E --> G[Task: Validasi input]
    F --> G
    G --> H{XOR: Valid?}
    H -- Tidak --> I((End Event: Kembali perbaiki form))
    H -- Ya --> J[Task: Simpan pengajuan status DIAJUKAN]
    J --> K[Task: Notifikasi ke Kepala Sarana]
    K --> L((End Event))
  end
```

## 3) BPMN Approval Bertingkat (Formal)

```mermaid
flowchart LR
  A((Start Event: DIAJUKAN)) --> B[Task: Approval Kepala Sarana]
  B --> C{XOR: Approve?}
  C -- Tidak --> D[Task: Set DITOLAK + catatan]
  C -- Ya --> E[Task: Set DISETUJUI_KASARANA]
  E --> F[Task: Approval Bendahara]
  F --> G{XOR: Approve?}
  G -- Tidak --> D
  G -- Ya --> H[Task: Set DISETUJUI_BENDAHARA]
  H --> I[Task: Approval Kepala Sekolah]
  I --> J{XOR: Approve?}
  J -- Tidak --> D
  J -- Ya --> K[Task: Set DIPROSES]
  D --> L((End Event: Ditolak))
  K --> M((End Event: Masuk antrean realisasi admin))
```

## 4) BPMN Realisasi Admin (As-Is Kode)

```mermaid
flowchart LR
  A((Start Event: DIPROSES)) --> B{XOR: Jenis}
  B -- PERAWATAN --> C[Task: Isi form realisasi perawatan]
  B -- PENGGANTIAN --> D[Task: Isi form realisasi penggantian]
  C --> E[Task: Simpan data realisasi]
  D --> E
  E --> F[Task: Set status pengajuan SELESAI]
  F --> G[Task: Update kondisi sarana BAIK]
  G --> H[Task: Sync riwayat kerusakan SELESAI]
  H --> I((End Event))
```

## 5) BPMN Scan QR Action Hub

```mermaid
flowchart LR
  A((Start Event)) --> B[Task: User scan/input kode sarana]
  B --> C{XOR: Kode valid dan sarana ditemukan?}
  C -- Tidak --> D((End Event: Tampilkan error/hasil kosong))
  C -- Ya --> E[Task: Tampilkan detail sarana + action berbasis role]
  E --> F{XOR: Role user}
  F -- Guru --> G[Task: Redirect ke Lapor Kerusakan / Usulan Mutasi]
  F -- Kepala Sarana --> H[Task: Redirect ke Validasi Kerusakan / Approval]
  F -- Bendahara --> I[Task: Redirect ke Review Pengajuan]
  F -- Kepala Sekolah --> J[Task: Redirect ke Approval Final]
  F -- Admin --> K[Task: Redirect ke Lapor Kerusakan / Histori / Mutasi]
  G --> L((End Event))
  H --> L
  I --> L
  J --> L
  K --> L
```

## 6) BPMN Inventaris Sarana (Admin)

```mermaid
flowchart LR
  A((Start Event)) --> B{XOR: Mode kelola sarana}
  B -- Create Unit --> C[Task: Input 1 sarana]
  B -- Create Batch --> D[Task: Input per ruangan/per kategori/import]
  B -- Edit --> E[Task: Ubah data sarana]
  B -- Delete --> F[Task: Hapus sarana]

  C --> G[Task: Generate kode_sarana + nama_sarana]
  D --> G
  G --> H[Task: Simpan sarana]
  E --> I[Task: Simpan perubahan]
  F --> J{XOR: Sarana punya relasi proses?}
  J -- Ya --> K[Task: Soft delete (arsip)]
  J -- Tidak --> L[Task: Hard delete + hapus foto]

  H --> M((End Event))
  I --> M
  K --> M
  L --> M
```

## 7) BPMN Master Data (Gedung, Ruangan, Kategori)

```mermaid
flowchart LR
  A((Start Event)) --> B{XOR: Entitas master}
  B -- Gedung --> C[Task: CRUD Gedung]
  B -- Ruangan --> D[Task: CRUD Ruangan + generate kode_ruangan]
  B -- Kategori --> E[Task: CRUD Kategori]
  C --> F{XOR: Boleh hapus?}
  D --> G{XOR: Boleh hapus?}
  E --> H{XOR: Boleh hapus?}
  F -- Tidak --> I[Task: Tolak hapus]
  F -- Ya --> J[Task: Hapus]
  G -- Tidak --> I
  G -- Ya --> J
  H -- Tidak --> I
  H -- Ya --> J
  I --> K((End Event))
  J --> K
```

## 8) BPMN Manajemen User (Admin)

```mermaid
flowchart LR
  A((Start Event)) --> B{XOR: Aksi user management}
  B -- Tambah --> C[Task: Input data user + role + status + password]
  B -- Ubah --> D[Task: Update profil/role/status]
  B -- Reset Password --> E[Task: Input password baru]
  C --> F[Task: Simpan user]
  D --> G{XOR: Target adalah admin login sendiri?}
  G -- Ya dan ubah ke NONAKTIF/non-admin --> H[Task: Tolak perubahan]
  G -- Lainnya --> I[Task: Simpan perubahan]
  E --> J[Task: Simpan hash password baru]
  F --> K((End Event))
  H --> K
  I --> K
  J --> K
```

## 9) BPMN Notifikasi

```mermaid
flowchart LR
  A((Start Event)) --> B[Task: Event proses memicu notifikasi]
  B --> C[Task: Sistem kirim notifikasi ke role/user tujuan]
  C --> D[Task: User buka inbox notifikasi]
  D --> E[Task: Sistem mark as read + rapikan thread duplikat]
  E --> F{XOR: User klik item?}
  F -- Ya --> G[Task: Redirect ke halaman proses terkait]
  F -- Tidak --> H[Task: Tetap di inbox]
  G --> I((End Event))
  H --> I
```

## 10) BPMN Pelaporan dan Export

```mermaid
flowchart LR
  A((Start Event)) --> B[Task: Role berizin buka modul laporan]
  B --> C[Task: Set filter periode/lokasi/kategori/status/keyword]
  C --> D[Task: Sistem hitung KPI + finance + tren]
  D --> E{XOR: Output}
  E -- Dashboard --> F[Task: Tampilkan ringkasan dan tabel]
  E -- Export Excel --> G[Task: Generate file XLSX multi-sheet]
  E -- Export PDF --> H[Task: Render view PDF]
  F --> I((End Event))
  G --> I
  H --> I
```

---

## Catatan Profesional (As-Is vs To-Be)
- Pada kode saat ini, setelah realisasi admin, status langsung `SELESAI`.
- Node status `MENUNGGU_VERIFIKASI_TEKNIS/KEUANGAN` tetap ada di sistem, namun dipakai bila alur verifikasi diaktifkan pada proses tertentu.
- Jika kamu butuh versi **To-Be** (misalnya verifikasi teknis/keuangan wajib sebelum selesai), saya bisa buatkan paket BPMN terpisah `AS-IS` dan `TO-BE`.

