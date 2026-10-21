<?php
require_once __DIR__ . '/../includes/crud.php';
require_role(['admin']);
crud_page([
    'title' => 'Alat', 'table' => 'alat', 'pk' => 'id_alat',
    'sql' => 'SELECT a.id_alat, k.nama_kategori, a.nama_alat, a.stok, a.kondisi FROM alat a JOIN kategori k ON k.id_kategori = a.id_kategori ORDER BY a.id_alat DESC',
    'fields' => [
        'id_kategori' => ['Kategori', 'select', 'SELECT id_kategori, nama_kategori FROM kategori'],
        'nama_alat' => ['Nama Alat', 'text'], 'stok' => ['Stok', 'number'],
        'kondisi' => ['Kondisi', 'select', ['baik' => 'baik', 'rusak ringan' => 'rusak ringan', 'rusak berat' => 'rusak berat']],
        'deskripsi' => ['Deskripsi', 'textarea'],
    ],
]);
