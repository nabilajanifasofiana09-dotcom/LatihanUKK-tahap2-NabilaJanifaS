<?php
require_once __DIR__ . '/../includes/layout.php';
require_role(['petugas']);
$rows = db()->query("SELECT id_peminjaman, peminjam, nama_alat, jumlah, tgl_kembali_rencana, status, tgl_kembali, denda FROM v_peminjaman WHERE status IN ('disetujui','dikembalikan') ORDER BY id_peminjaman DESC LIMIT 50")->fetchAll();
page_header('Pemantauan Pengembalian');
echo '<table><tr><th>ID</th><th>Peminjam</th><th>Alat</th><th>Jml</th><th>Rencana Kembali</th><th>Status</th><th>Tgl Kembali</th><th>Denda</th></tr>';
foreach ($rows as $r) {
    $late = $r['status'] === 'disetujui' && $r['tgl_kembali_rencana'] < date('Y-m-d');
    echo '<tr' . ($late ? ' class="late"' : '') . '>'; foreach ($r as $v) echo '<td>' . e($v) . '</td>'; echo '</tr>';
}
echo '</table><p>Baris merah = belum dikembalikan dan melewati batas waktu.</p>'; page_footer();
