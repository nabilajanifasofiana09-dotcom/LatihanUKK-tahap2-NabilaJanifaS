<?php
require_once __DIR__ . '/layout.php';

/**
 * Halaman CRUD generik.
 * $c: title, table, pk, fields[name => [label, type, options?]], sql (SELECT list), log
 * type: text|password|number|date|textarea|select (options: array atau SQL "v,l")
 */
function crud_page(array $c): void
{
    $pdo = db();
    $fields = $c['fields'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        try {
            if (($_POST['aksi'] ?? '') === 'hapus') {
                $pdo->prepare("DELETE FROM {$c['table']} WHERE {$c['pk']} = ?")->execute([(int)$_POST['id']]);
                catat_log("Hapus {$c['title']} #" . (int)$_POST['id']);
                flash('Data dihapus');
            } else {
                $vals = []; $cols = [];
                foreach ($fields as $n => $f) {
                    $v = trim($_POST[$n] ?? '');
                    if ($f[1] === 'password') {
                        if ($v === '') continue;
                        $v = password_hash($v, PASSWORD_DEFAULT);
                    }
                    $cols[] = $n; $vals[] = $v === '' ? null : $v;
                }
                $id = (int)($_POST['id'] ?? 0);
                if ($id) {
                    $set = implode(', ', array_map(fn($x) => "$x = ?", $cols));
                    $pdo->prepare("UPDATE {$c['table']} SET $set WHERE {$c['pk']} = ?")->execute([...$vals, $id]);
                    catat_log("Ubah {$c['title']} #$id");
                } else {
                    $ph = implode(',', array_fill(0, count($cols), '?'));
                    $pdo->prepare("INSERT INTO {$c['table']} (" . implode(',', $cols) . ") VALUES ($ph)")->execute($vals);
                    catat_log("Tambah {$c['title']}");
                }
                flash('Data disimpan');
            }
        } catch (PDOException $ex) {
            flash(db_message($ex), 'error');
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    $edit = null;
    if (isset($_GET['edit'])) {
        $st = $pdo->prepare("SELECT * FROM {$c['table']} WHERE {$c['pk']} = ?");
        $st->execute([(int)$_GET['edit']]);
        $edit = $st->fetch() ?: null;
    }
    $page = max(1, (int)($_GET['p'] ?? 1));
    $per = 10;
    $total = (int)$pdo->query("SELECT COUNT(*) FROM {$c['table']}")->fetchColumn();
    $rows = $pdo->query($c['sql'] . ' LIMIT ' . $per . ' OFFSET ' . (($page - 1) * $per))->fetchAll();

    page_header('Kelola ' . $c['title']);
    echo '<form method="post"><input type="hidden" name="csrf" value="' . csrf_token() . '">';
    echo '<input type="hidden" name="id" value="' . e($edit[$c['pk']] ?? '') . '">';
    foreach ($fields as $n => [$label, $type]) {
        $opt = $fields[$n][2] ?? null;
        $val = $type === 'password' ? '' : ($edit[$n] ?? '');
        echo '<label>' . e($label) . '</label>';
        if ($type === 'select') {
            $list = is_array($opt) ? $opt : $pdo->query($opt)->fetchAll(PDO::FETCH_KEY_PAIR);
            echo '<select name="' . $n . '" required>';
            foreach ($list as $k => $l) echo '<option value="' . e($k) . '"' . ((string)$k === (string)$val ? ' selected' : '') . '>' . e($l) . '</option>';
            echo '</select>';
        } elseif ($type === 'textarea') {
            echo '<textarea name="' . $n . '">' . e($val) . '</textarea>';
        } else {
            $req = ($type === 'password' && $edit) ? '' : 'required';
            if ($type === 'textarea') $req = '';
            echo '<input type="' . $type . '" name="' . $n . '" value="' . e($val) . '" ' . $req . ($type === 'number' ? ' min="0"' : '') . '>';
        }
    }
    echo '<p><button>' . ($edit ? 'Perbarui' : 'Tambah') . '</button> ' . ($edit ? '<a href="' . e($_SERVER['PHP_SELF']) . '">Batal</a>' : '') . '</p></form>';

    echo '<table><tr>';
    if ($rows) foreach (array_keys($rows[0]) as $h) echo '<th>' . e($h) . '</th>';
    echo '<th>Aksi</th></tr>';
    foreach ($rows as $r) {
        echo '<tr>';
        foreach ($r as $v) echo '<td>' . e($v) . '</td>';
        $id = $r[$c['pk']];
        echo '<td><a href="?edit=' . (int)$id . '">Edit</a> <form method="post" style="display:inline" onsubmit="return confirm(\'Hapus data?\')">'
            . '<input type="hidden" name="csrf" value="' . csrf_token() . '"><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="' . (int)$id . '">'
            . '<button class="btn-danger">Hapus</button></form></td></tr>';
    }
    echo '</table><div class="pager">';
    for ($i = 1; $i <= max(1, ceil($total / $per)); $i++) echo '<a href="?p=' . $i . '">' . $i . '</a>';
    echo '</div>';
    page_footer();
}
