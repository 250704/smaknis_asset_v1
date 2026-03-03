# Checklist Pengembangan Sistem Inventaris

Update status pakai format:
- `[ ]` belum mulai
- `[-]` sedang dikerjakan
- `[x]` selesai
- `[!]` blocked

## A. Fondasi Sistem
- [x] Struktur role 5 level (admin, guru, kepala_sarana, bendahara, kepala_sekolah)
- [x] Skema database inti sesuai blueprint final
- [x] Login tanpa register publik
- [x] Middleware role dan proteksi akun nonaktif
- [x] Sidebar per role sesuai blueprint menu
- [-] Halaman per menu blueprint (kerangka siap, implementasi detail bertahap)

## B. Modul Inventaris
- [x] CRUD gedung
- [x] CRUD ruangan
- [x] CRUD kategori aset
- [x] CRUD aset satuan
- [x] Standarisasi kode lokasi (kode gedung + kode ruangan + lantai) untuk generator kode aset
- [ ] Tambah aset massal
- [x] Cetak/generate QR per unit aset

## C. Modul Scan QR (Action Hub)
- [x] Scan QR menampilkan detail aset
- [x] Scan langsung kamera (desktop/mobile) dari halaman sistem
- [x] Shortcut ajukan perawatan
- [x] Shortcut ajukan penggantian
- [x] Shortcut laporan kerusakan
- [x] Shortcut usulan mutasi lokasi
- [x] Log aktivitas SCAN_QR (audit trail)

## D. Modul Pengajuan
- [ ] Form pengajuan perawatan
- [ ] Form pengajuan penggantian
- [ ] Form pengajuan pengadaan (tanpa aset_id)
- [ ] Riwayat pengajuan per user
- [ ] Monitoring semua pengajuan (admin)

## E. Approval Bertingkat
- [x] Service anti self-approval (fondasi)
- [ ] UI approval Kepala Sarana
- [ ] UI approval Bendahara
- [ ] UI approval Kepala Sekolah
- [ ] Notifikasi antar level approval

## F. Realisasi
- [ ] Realisasi perawatan (foto sebelum/sesudah + biaya)
- [ ] Realisasi penggantian (nonaktif aset lama + aset baru + QR baru)
- [ ] Realisasi pengadaan (input aset baru + assign ruangan)

## G. Pelaporan dan Audit
- [ ] Laporan inventaris (PDF/Excel)
- [ ] Laporan kerusakan (PDF/Excel)
- [ ] Laporan perawatan (PDF/Excel)
- [ ] Laporan penggantian (PDF/Excel)
- [ ] Laporan pengajuan/pengadaan (PDF/Excel)
- [ ] Log aktivitas admin-only

## H. Kualitas dan Release
- [ ] Test feature utama
- [ ] UAT per role
- [ ] Hardening validasi input
- [ ] Finalisasi dokumentasi pengguna

## Catatan Blocker
- Belum ada.
