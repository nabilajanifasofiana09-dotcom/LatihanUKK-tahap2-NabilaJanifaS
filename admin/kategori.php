<?php
require_once __DIR__ . '/../includes/crud.php';
require_role(['admin']);
crud_page([
    'title' => 'Kategori', 'table' => 'kategori', 'pk' => 'id_kategori',
    'sql' => 'SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori',
    'fields' => ['nama_kategori' => ['Nama Kategori', 'text']],
]);
