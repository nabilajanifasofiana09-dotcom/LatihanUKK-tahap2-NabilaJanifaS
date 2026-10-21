<?php
require_once __DIR__ . '/../includes/layout.php';
require_role(['petugas']);
$dari = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');
$st = db()->prepare('SELECT id_peminjaman, peminjam, nama_alat, jumlah, tgl_pinjam, tgl_kembali_rencana, status, tgl_kembali, denda FROM v_peminjaman WHERE tgl_pinjam BETWEEN ? AND ? ORDER BY tgl_pinjam LIMIT 500');
$st->execute([$dari, $sampai]);
$rows = $st->fetchAll();
page_header('Laporan Peminjaman');
echo '<form method="get" class="no-print">Dari <input type="date" name="dari" value="' . e($dari) . '"> Sampai <input type="date" name="sampai" value="' . e($sampai) . '"> <button>Tampilkan</button> <button type="button" onclick="window.print()">Cetak</button></form>';
echo '<p>Periode: ' . e($dari) . ' s/d ' . e($sampai) . '</p><table><tr><th>ID</th><th>Peminjam</th><th>Alat</th><th>Jml</th><th>Pinjam</th><th>Rencana</th><th>Status</th><th>Kembali</th><th>Denda</th></tr>';
$sum = 0;
foreach ($rows as $r) { $sum += (float)$r['denda']; echo '<tr>'; foreach ($r as $v) echo '<td>' . e($v) . '</td>'; echo '</tr>'; }
echo '</table><p><strong>Total denda: Rp ' . number_format($sum, 0, ',', '.') . '</strong></p>'; page_footer();
