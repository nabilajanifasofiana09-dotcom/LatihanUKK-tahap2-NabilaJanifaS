<?php
require_once __DIR__ . '/../includes/layout.php';
require_role(['petugas']);
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)$_POST['id'];
    try {
        if ($_POST['aksi'] === 'setuju') {
            $pdo->prepare('CALL sp_setujui_peminjaman(?, ?)')->execute([$id, $_SESSION['user']['id_user']]);
            catat_log("Setujui peminjaman #$id");
            flash('Peminjaman disetujui');
        } else {
            $pdo->prepare("UPDATE peminjaman SET status = 'ditolak', id_petugas = ? WHERE id_peminjaman = ? AND status = 'menunggu'")->execute([$_SESSION['user']['id_user'], $id]);
            catat_log("Tolak peminjaman #$id");
            flash('Peminjaman ditolak');
        }
    } catch (PDOException $ex) { flash(db_message($ex), 'error'); }
    header('Location: persetujuan.php'); exit;
}
$rows = $pdo->query("SELECT id_peminjaman, peminjam, nama_alat, jumlah, tgl_pinjam, tgl_kembali_rencana FROM v_peminjaman WHERE status = 'menunggu' ORDER BY id_peminjaman LIMIT 50")->fetchAll();
page_header('Persetujuan Peminjaman');
echo '<table><tr><th>ID</th><th>Peminjam</th><th>Alat</th><th>Jml</th><th>Pinjam</th><th>Rencana Kembali</th><th>Aksi</th></tr>';
foreach ($rows as $r) {
    echo '<tr>'; foreach ($r as $v) echo '<td>' . e($v) . '</td>';
    echo '<td><form method="post" style="display:inline"><input type="hidden" name="csrf" value="' . csrf_token() . '"><input type="hidden" name="id" value="' . (int)$r['id_peminjaman'] . '">'
        . '<button name="aksi" value="setuju">Setujui</button> <button class="btn-danger" name="aksi" value="tolak">Tolak</button></form></td></tr>';
}
echo $rows ? '' : '<tr><td colspan="7">Tidak ada pengajuan</td></tr>';
echo '</table>'; page_footer();
