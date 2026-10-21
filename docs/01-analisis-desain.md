# 1. Analisis Kebutuhan dan Desain

## Metode
Waterfall sederhana: analisis kebutuhan, desain, implementasi, pengujian, dokumentasi.

## Analisis Kebutuhan
| Fitur | Admin | Petugas | Peminjam |
|---|:-:|:-:|:-:|
| Login / Logout | v | v | v |
| CRUD user, alat, kategori, peminjaman, pengembalian | v | | |
| Log aktivitas | v | | |
| Menyetujui peminjaman, memantau pengembalian, mencetak laporan | | v | |
| Melihat daftar alat, mengajukan peminjaman, mengembalikan alat | | | v |

Aturan bisnis: stok berkurang saat peminjaman disetujui dan bertambah saat dikembalikan. Denda Rp2.000 per hari keterlambatan.

## Struktur Data (tipe data lengkap ada di `database/peminjaman_alat.sql`)
| Tabel | Kolom utama |
|---|---|
| users | id_user (PK), nama, username (UNIQUE), password (hash), role ENUM |
| kategori | id_kategori (PK), nama_kategori |
| alat | id_alat (PK), id_kategori (FK), nama_alat, stok, kondisi, deskripsi |
| peminjaman | id_peminjaman (PK), id_user (FK), id_alat (FK), id_petugas (FK), jumlah, tgl_pinjam, tgl_kembali_rencana, status |
| pengembalian | id_pengembalian (PK), id_peminjaman (FK, UNIQUE), tgl_kembali, keterlambatan_hari, denda |
| log_aktivitas | id_log (PK), id_user, aktivitas, waktu |

## ERD (Mermaid; salin ke https://mermaid.live atau draw.io lalu ekspor PNG/JPG)
```mermaid
erDiagram
  USERS ||--o{ PEMINJAMAN : mengajukan
  USERS ||--o{ PEMINJAMAN : menyetujui
  USERS ||--o{ LOG_AKTIVITAS : mencatat
  KATEGORI ||--o{ ALAT : mengelompokkan
  ALAT ||--o{ PEMINJAMAN : dipinjam
  PEMINJAMAN ||--o| PENGEMBALIAN : dikembalikan
  USERS { int id_user PK
    string nama
    string username
    string password
    enum role }
  KATEGORI { int id_kategori PK
    string nama_kategori }
  ALAT { int id_alat PK
    int id_kategori FK
    string nama_alat
    int stok
    enum kondisi }
  PEMINJAMAN { int id_peminjaman PK
    int id_user FK
    int id_alat FK
    int id_petugas FK
    int jumlah
    date tgl_pinjam
    date tgl_kembali_rencana
    enum status }
  PENGEMBALIAN { int id_pengembalian PK
    int id_peminjaman FK
    date tgl_kembali
    int keterlambatan_hari
    decimal denda }
  LOG_AKTIVITAS { int id_log PK
    int id_user
    string aktivitas
    timestamp waktu }
```

## Flowchart
### Login
```mermaid
flowchart TD
  A([Mulai]) --> B[/Input username & password/]
  B --> C{User ada dan password cocok?}
  C -- Tidak --> D[Tampilkan notifikasi gagal] --> B
  C -- Ya --> E[Buat session, catat log] --> F{Role?}
  F -- admin --> G[Halaman admin]
  F -- petugas --> H[Halaman petugas]
  F -- peminjam --> I[Halaman peminjam]
```
### Peminjaman Alat
```mermaid
flowchart TD
  A([Mulai]) --> B[/Peminjam pilih alat, jumlah, tgl kembali/]
  B --> C{Jumlah > 0, tanggal valid, stok cukup?}
  C -- Tidak --> D[ROLLBACK, pesan error] --> Z([Selesai])
  C -- Ya --> E[Simpan status menunggu, COMMIT]
  E --> F{Petugas menyetujui?}
  F -- Tolak --> G[Status ditolak] --> Z
  F -- Setuju --> H{Stok masih cukup?}
  H -- Tidak --> D
  H -- Ya --> I[Stok dikurangi, status disetujui, COMMIT] --> Z
```
### Pengembalian dan Denda
```mermaid
flowchart TD
  A([Mulai]) --> B[/Peminjam pilih peminjaman/]
  B --> C{Status disetujui dan milik user?}
  C -- Tidak --> D[ROLLBACK, pesan error] --> Z([Selesai])
  C -- Ya --> E[Hitung terlambat = tgl_kembali - rencana]
  E --> F{Terlambat > 0?}
  F -- Ya --> G[denda = hari x 2000]
  F -- Tidak --> H[denda = 0]
  G --> I[Simpan pengembalian, status dikembalikan, stok ditambah, COMMIT]
  H --> I --> Z
```
Pseudocode pengembalian:
```
BEGIN TRANSACTION
ambil peminjaman WHERE id=? AND user=? FOR UPDATE
JIKA status != 'disetujui' MAKA ROLLBACK; error
terlambat = MAX(tgl_kembali - tgl_rencana, 0)
denda = terlambat * 2000
INSERT pengembalian; UPDATE peminjaman status; UPDATE alat stok += jumlah
COMMIT
```
