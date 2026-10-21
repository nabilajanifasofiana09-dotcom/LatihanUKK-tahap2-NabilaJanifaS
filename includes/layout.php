<?php
require_once __DIR__ . '/db.php';

function page_header(string $title): void
{
    $u = $_SESSION['user'] ?? null;
    $menu = [
        'admin' => ['admin/users.php' => 'User', 'admin/kategori.php' => 'Kategori', 'admin/alat.php' => 'Alat',
                    'admin/peminjaman.php' => 'Peminjaman', 'admin/pengembalian.php' => 'Pengembalian', 'admin/log.php' => 'Log'],
        'petugas' => ['petugas/persetujuan.php' => 'Persetujuan', 'petugas/pemantauan.php' => 'Pemantauan', 'petugas/laporan.php' => 'Laporan'],
        'peminjam' => ['peminjam/alat.php' => 'Daftar Alat', 'peminjam/pengembalian.php' => 'Peminjaman Saya'],
    ];
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . e($title) . ' - Peminjaman Alat</title><link rel="stylesheet" href="' . BASE_URL . '/assets/style.css"></head><body>';
    if ($u) {
        echo '<nav><strong>Peminjaman Alat</strong>';
        foreach ($menu[$u['role']] as $href => $label) echo '<a href="' . BASE_URL . '/' . $href . '">' . $label . '</a>';
        echo '<span class="right">' . e($u['nama']) . ' (' . e($u['role']) . ') <a href="' . BASE_URL . '/logout.php">Logout</a></span></nav>';
    }
    echo '<main><h2>' . e($title) . '</h2>';
    if ($f = flash()) echo '<div class="alert ' . e($f[1]) . '">' . e($f[0]) . '</div>';
}

function page_footer(): void { echo '</main></body></html>'; }
