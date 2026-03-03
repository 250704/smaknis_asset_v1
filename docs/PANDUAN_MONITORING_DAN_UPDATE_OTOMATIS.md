# Panduan Monitoring Proses Pembuatan Sistem dan Update Otomatis

## Tujuan
Dokumen ini dipakai untuk:
- memantau progres pengembangan sistem sesuai blueprint,
- mendeteksi hambatan lebih cepat,
- memperbarui status proyek secara otomatis dan konsisten.

## File yang Dipakai
- `docs/monitoring/CHECKLIST_PENGEMBANGAN.md`: ceklis manual progres fitur.
- `docs/monitoring/STATUS_OTOMATIS.md`: snapshot otomatis kondisi proyek.
- `scripts/update-progress.ps1`: skrip pembaruan otomatis status.

## Cara Kerja Monitoring
1. Tim update ceklis manual setiap selesai pekerjaan penting.
2. Jalankan skrip update otomatis minimal 1 kali per hari.
3. Commit perubahan dokumen monitoring bersama perubahan kode.

## Aturan Status Ceklis
- `[ ]`: belum mulai.
- `[-]`: sedang dikerjakan / sebagian selesai.
- `[x]`: selesai dan sudah diverifikasi.
- `[!]`: blocked (wajib tulis penyebab di catatan).

## Standar Update Harian
1. Pagi:
- cek item prioritas hari ini pada `CHECKLIST_PENGEMBANGAN.md`.
2. Setelah coding:
- ubah status ceklis sesuai progres nyata.
3. Sore / sebelum push:
- jalankan:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/update-progress.ps1
```

4. Jika perlu include test:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/update-progress.ps1 -RunTests
```

## Apa yang Diupdate Otomatis
Skrip akan memperbarui:
- waktu update terakhir,
- branch aktif,
- ringkasan perubahan git (added/modified/deleted/untracked),
- status migrasi Laravel,
- daftar route dashboard aktif,
- hasil test (jika `-RunTests` dipakai).

## Frekuensi Minimal
- Proyek aktif: 1 kali/hari.
- Menjelang demo/release: setiap selesai batch fitur.

## Opsional: Jadwalkan Otomatis (Windows Task Scheduler)
Contoh command:

```powershell
schtasks /Create /SC DAILY /TN "Sarpras_Update_Progress" /TR "powershell -ExecutionPolicy Bypass -File C:\laragon\www\sarpras_smaknis_v1\scripts\update-progress.ps1" /ST 17:00
```

Jalankan sekali, lalu file `STATUS_OTOMATIS.md` akan diperbarui otomatis setiap hari jam 17:00.
