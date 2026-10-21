# 5. Laporan Evaluasi Singkat

## Fitur yang sudah diimplementasikan
Login/logout 3 level, CRUD user/kategori/alat/peminjaman/pengembalian, log aktivitas, persetujuan dan pemantauan oleh petugas, laporan cetak, pengajuan dan pengembalian oleh peminjam, denda otomatis lewat trigger, transaksi COMMIT/ROLLBACK di stored procedure.

## Status pengujian
Kode ditulis dan disusun tanpa dijalankan pada server MySQL/PHP di lingkungan pembuatnya, jadi seluruh skenario di `03-pengujian.md` perlu dijalankan dan hasilnya diisi sebelum dikumpulkan. Perbaiki bug yang muncul dan catat di bagian bawah.

## Bug / keterbatasan yang diketahui
- Admin dapat mengedit data peminjaman/pengembalian secara langsung tanpa otomatis menyesuaikan stok.
- Belum ada pembatasan percobaan login berulang.
- Belum ada pendaftaran mandiri peminjam (akun dibuat admin).
- Hapus data yang masih dipakai relasi akan ditolak foreign key (pesan umum).

## Rencana pengembangan
Penyesuaian stok otomatis pada edit admin, pembatasan login, ekspor PDF/Excel, notifikasi jatuh tempo, upload foto alat.
