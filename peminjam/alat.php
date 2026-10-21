<?php
require_once __DIR__ . '/../includes/layout.php';
require_role(['peminjam']);
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        $pdo->prepare('CALL sp_ajukan_peminjaman(?, ?, ?, ?)')->execute([$_SESSION['user']['id_user'], (int)$_POST['id_alat'], (int)$_POST['jumlah'], $_POST['tgl_kembali']]);
        catat_log('Ajukan peminjaman alat #' . (int)$_POST['id_alat']);
        flash('Pengajuan terkirim, menunggu persetujuan petugas');
    } catch (PDOException $ex) { flash(db_message($ex), 'error'); }
    header('Location: alat.php'); exit;
}
$q = trim($_GET['q'] ?? '');
$st = $pdo->prepare('SELECT a.id_alat, a.nama_alat, k.nama_kategori, a.stok, a.kondisi FROM alat a JOIN kategori k ON k.id_kategori = a.id_kategori WHERE a.nama_alat LIKE ? ORDER BY a.nama_alat LIMIT 50');
$st->execute(['%' . $q . '%']);
page_header('Daftar Alat');
echo '<form method="get">Cari <input name="q" value="' . e($q) . '"> <button>Cari</button></form><table><tr><th>Alat</th><th>Kategori</th><th>Stok</th><th>Kondisi</th><th>Ajukan Pinjam</th></tr>';
foreach ($st as $r) {
    echo '<tr><td>' . e($r['nama_alat']) . '</td><td>' . e($r['nama_kategori']) . '</td><td>' . e($r['stok']) . '</td><td>' . e($r['kondisi']) . '</td><td>';
    if ($r['stok'] > 0) echo '<form method="post"><input type="hidden" name="csrf" value="' . csrf_token() . '"><input type="hidden" name="id_alat" value="' . (int)$r['id_alat'] . '">'
        . '<input type="number" name="jumlah" min="1" max="' . (int)$r['stok'] . '" value="1" style="width:60px" required> <input type="date" name="tgl_kembali" min="' . date('Y-m-d') . '" required> <button>Ajukan</button></form>';
    else echo 'Habis';
    echo '</td></tr>';
}
echo '</table>'; page_footer();
