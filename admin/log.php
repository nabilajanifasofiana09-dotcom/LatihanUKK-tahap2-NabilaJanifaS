<?php
require_once __DIR__ . '/../includes/layout.php';
require_role(['admin']);
$page = max(1, (int)($_GET['p'] ?? 1));
$rows = db()->query('SELECT l.waktu, COALESCE(u.username, "sistem") AS pengguna, l.aktivitas FROM log_aktivitas l LEFT JOIN users u ON u.id_user = l.id_user ORDER BY l.id_log DESC LIMIT 20 OFFSET ' . (($page - 1) * 20))->fetchAll();
page_header('Log Aktivitas');
echo '<table><tr><th>Waktu</th><th>Pengguna</th><th>Aktivitas</th></tr>';
foreach ($rows as $r) echo '<tr><td>' . e($r['waktu']) . '</td><td>' . e($r['pengguna']) . '</td><td>' . e($r['aktivitas']) . '</td></tr>';
echo '</table><a href="?p=' . ($page + 1) . '">Berikutnya</a>';
page_footer();
