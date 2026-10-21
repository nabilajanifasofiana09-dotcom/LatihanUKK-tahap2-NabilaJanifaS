# 3. Pengujian

Kolom "Hasil Aktual" dan "Screenshot" diisi saat pengujian dijalankan di komputer Anda (simpan tangkapan layar di `docs/screenshots/`).

| No | Skenario | Kasus / Langkah | Hasil yang Diharapkan | Hasil Aktual | Screenshot |
|---|---|---|---|---|---|
| 1 | Login user | Username `admin`, password salah | Gagal, muncul notifikasi, tetap di halaman login | | uji1a.png |
| 1b | Login user | `admin` / `password` | Berhasil, masuk halaman admin | | uji1b.png |
| 2 | Tambah alat | Admin isi form Alat valid | Data tersimpan, muncul di tabel, log "Alat baru" tercatat | | uji2.png |
| 3 | Pinjam alat | Peminjam ajukan 1 Bor Listrik, lalu petugas setujui | Status menunggu lalu disetujui, stok berkurang 1 | | uji3.png |
| 3b | Pinjam alat | Ajukan jumlah melebihi stok | Gagal, pesan "Stok alat tidak mencukupi" | | uji3b.png |
| 4 | Kembalikan alat | Peminjam klik Kembalikan tepat waktu | Status dikembalikan, stok naik, denda 0 | | uji4a.png |
| 4b | Kembalikan alat (denda) | Ubah tgl_kembali_rencana 3 hari lalu (admin), lalu kembalikan | Keterlambatan 3 hari, denda Rp6.000 | | uji4b.png |
| 5 | Cek privilege | Login peminjam, buka `/admin/users.php` | Dialihkan ke login, akses ditolak | | uji5.png |
