<?php
require_once __DIR__ . '/../includes/crud.php';
require_role(['admin']);
crud_page([
    'title' => 'Peminjaman', 'table' => 'peminjaman', 'pk' => 'id_peminjaman',
    'sql' => 'SELECT id_peminjaman, peminjam, nama_alat, jumlah, tgl_pinjam, tgl_kembali_rencana, status FROM v_peminjaman ORDER BY id_peminjaman DESC',
    'fields' => [
        'id_user' => ['Peminjam', 'select', "SELECT id_user, nama FROM users WHERE role = 'peminjam'"],
        'id_alat' => ['Alat', 'select', 'SELECT id_alat, nama_alat FROM alat'],
        'jumlah' => ['Jumlah', 'number'], 'tgl_pinjam' => ['Tanggal Pinjam', 'date'],
        'tgl_kembali_rencana' => ['Rencana Kembali', 'date'],
        'status' => ['Status', 'select', ['menunggu' => 'menunggu', 'disetujui' => 'disetujui', 'ditolak' => 'ditolak', 'dikembalikan' => 'dikembalikan']],
    ],
]);
