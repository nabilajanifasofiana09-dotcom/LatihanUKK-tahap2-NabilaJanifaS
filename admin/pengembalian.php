<?php
require_once __DIR__ . '/../includes/crud.php';
require_role(['admin']);
crud_page([
    'title' => 'Pengembalian', 'table' => 'pengembalian', 'pk' => 'id_pengembalian',
    'sql' => 'SELECT id_pengembalian, id_peminjaman, tgl_kembali, keterlambatan_hari, denda FROM pengembalian ORDER BY id_pengembalian DESC',
    'fields' => [
        'id_peminjaman' => ['ID Peminjaman', 'number'], 'tgl_kembali' => ['Tanggal Kembali', 'date'],
        'keterlambatan_hari' => ['Keterlambatan (hari)', 'number'], 'denda' => ['Denda (Rp)', 'number'],
    ],
]);
