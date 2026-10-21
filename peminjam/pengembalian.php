<?php
require_once __DIR__ . '/../includes/layout.php';
require_role(['peminjam']);
$pdo = db(); $uid = $_SESSION['user']['id_user'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        $pdo->prepare('CALL sp_kembalikan_alat(?, ?)')->execute([(int)$_POST['id'], $uid]);
        catat_log('Kembalikan peminjaman #' . (int)$_POST['id']);
        flash('Alat berhasil dikembalikan');
    } catch (PDOException $ex) { flash(db_message($ex), 'error'); }
    header('Location: pengembalian.php'); exit;
}
$st = $pdo->prepare('SELECT p.id_peminjaman, v.nama_alat, v.jumlah, v.tgl_kembali_rencana, v.status, v.tgl_kembali, v.denda FROM peminjaman p JOIN v_peminjaman v ON v.id_peminjaman = p.id_peminjaman WHERE p.id_user = ? ORDER BY p.id_peminjaman DESC LIMIT 50');
$st->execute([$uid]);
page_header('Peminjaman Saya');
echo '<table><tr><th>ID</th><th>Alat</th><th>Jml</th><th>Rencana Kembali</th><th>Status</th><th>Tgl Kembali</th><th>Denda</th><th>Aksi</th></tr>';
foreach ($st as $r) {
    $id = $r['id_peminjaman']; unset($r['id_peminjaman']);
    echo '<tr><td>' . (int)$id . '</td>'; foreach ($r as $v) echo '<td>' . e($v) . '</td>';
    echo '<td>' . ($r['status'] === 'disetujui' ? '<form method="post"><input type="hidden" name="csrf" value="' . csrf_token() . '"><input type="hidden" name="id" value="' . (int)$id . '"><button>Kembalikan</button></form>' : '-') . '</td></tr>';
}
echo '</table><p>Denda keterlambatan: Rp2.000 per hari.</p>'; page_footer();
