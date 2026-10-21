# 2. Deskripsi Program dan Dokumentasi Modul

## Modul Login (`login.php`)
- Input: username, password, token CSRF
- Proses: cari user dengan prepared statement, `password_verify`, `session_regenerate_id`, catat log, arahkan sesuai role
- Output: redirect ke halaman role, atau notifikasi "Username atau password salah"

## Modul Peminjaman (`peminjam/alat.php`, `petugas/persetujuan.php`)
- Input: id_alat, jumlah, tgl_kembali (peminjam); id_peminjaman dan aksi setuju/tolak (petugas)
- Proses: `sp_ajukan_peminjaman` memvalidasi dan menyimpan status menunggu; `sp_setujui_peminjaman` mengunci baris, mengurangi stok, mengubah status
- Output: pesan sukses/gagal, status peminjaman berubah

## Modul Pengembalian (`peminjam/pengembalian.php`)
- Input: id_peminjaman
- Proses: `sp_kembalikan_alat`; trigger `trg_pengembalian_bi` menghitung keterlambatan dan denda lewat `fn_hitung_denda`
- Output: status dikembalikan, stok bertambah, denda tercatat

## Modul CRUD Admin (`includes/crud.php`, `admin/*.php`)
- Input: data form per tabel
- Proses: validasi CSRF, INSERT/UPDATE/DELETE prepared statement, pencatatan log, pagination 10 baris
- Output: tabel data dan pesan hasil operasi

## Modul Laporan (`petugas/laporan.php`)
- Input: rentang tanggal
- Proses: query `v_peminjaman` dengan BETWEEN dan LIMIT 500, total denda
- Output: tabel laporan, tombol cetak

## Dokumentasi Fungsi / Prosedur / Trigger
| Nama | Jenis | Input | Output / Efek |
|---|---|---|---|
| `db()` | fungsi PHP | - | koneksi PDO tunggal |
| `e($s)` | fungsi PHP | string | string aman HTML |
| `require_role($roles)` | fungsi PHP | daftar role | redirect ke login bila tidak berhak |
| `catat_log($aktivitas)` | fungsi PHP | string | baris baru di log_aktivitas |
| `csrf_token()` / `csrf_check()` | fungsi PHP | - / POST | token / hentikan bila tidak valid |
| `db_message($ex)` | fungsi PHP | PDOException | pesan aman untuk pengguna |
| `crud_page($c)` | fungsi PHP | konfigurasi tabel | halaman CRUD lengkap |
| `fn_hitung_denda(rencana, kembali)` | function SQL | 2 tanggal | denda DECIMAL |
| `sp_ajukan_peminjaman` | procedure | user, alat, jumlah, rencana | baris peminjaman menunggu |
| `sp_setujui_peminjaman` | procedure | id, petugas | stok turun, status disetujui |
| `sp_kembalikan_alat` | procedure | id, user | pengembalian, stok naik |
| `trg_pengembalian_bi` | trigger | baris baru | isi keterlambatan dan denda |
| `trg_alat_ai` | trigger | baris baru | log alat baru |
| `v_peminjaman` | view | - | JOIN 5 tabel untuk laporan |
