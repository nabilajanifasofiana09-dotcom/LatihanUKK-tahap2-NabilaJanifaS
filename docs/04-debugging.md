# 4. Debugging

| Masalah yang mungkin | Penyebab | Penanganan |
|---|---|---|
| Halaman putih / error koneksi | Kredensial salah di `includes/db.php` atau database belum diimpor | Cek DB_USER/DB_PASS, impor ulang SQL |
| CSS tidak muncul / redirect salah | `BASE_URL` tidak sama dengan nama folder | Ubah `BASE_URL` di `includes/db.php` |
| Impor SQL gagal di function/trigger | Klien tidak mendukung `DELIMITER` | Impor lewat phpMyAdmin tab SQL atau klien `mysql` |
| "Token tidak valid" | Session kedaluwarsa | Muat ulang halaman, login lagi |
| Login gagal untuk akun seed | Hash diubah | Pakai password `password` atau buat user baru dari admin |

Cara debug: aktifkan `error_reporting(E_ALL)` saat pengembangan, cek log Apache/PHP, dan uji prosedur langsung: `CALL sp_ajukan_peminjaman(3,1,1,CURDATE() + INTERVAL 3 DAY);`
