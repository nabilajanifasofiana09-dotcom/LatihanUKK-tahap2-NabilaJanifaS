<?php
require_once __DIR__ . '/../includes/crud.php';
require_role(['admin']);
crud_page([
    'title' => 'User', 'table' => 'users', 'pk' => 'id_user',
    'sql' => 'SELECT id_user, nama, username, role, created_at FROM users ORDER BY id_user DESC',
    'fields' => [
        'nama' => ['Nama', 'text'], 'username' => ['Username', 'text'], 'password' => ['Password', 'password'],
        'role' => ['Role', 'select', ['admin' => 'admin', 'petugas' => 'petugas', 'peminjam' => 'peminjam']],
    ],
]);
