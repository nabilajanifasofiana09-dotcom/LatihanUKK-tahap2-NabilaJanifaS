# Aplikasi Peminjaman Alat

PHP native (PDO) + MySQL/MariaDB. Tiga level: admin, petugas, peminjam.

## Instalasi
1. Pasang XAMPP/Laragon (PHP 8.1+, MySQL).
2. Salin folder ini ke `htdocs/peminjaman_alat`.
3. Impor `database/peminjaman_alat.sql` lewat phpMyAdmin atau `mysql -u root < database/peminjaman_alat.sql`.
4. Sesuaikan kredensial di `includes/db.php` bila perlu.
5. Buka `http://localhost/peminjaman_alat/`.

## Akun uji (password: `password`)
| Username | Role |
|---|---|
| admin | admin |
| petugas | petugas |
| peminjam | peminjam |

## Struktur
- `admin/`, `petugas/`, `peminjam/`: halaman per level
- `includes/`: koneksi, layout, CRUD generik
- `database/`: skrip SQL (tabel, view, function, trigger, procedure, data awal)
- `docs/`: ERD, deskripsi program, dokumentasi fungsi, debugging, pengujian, laporan
