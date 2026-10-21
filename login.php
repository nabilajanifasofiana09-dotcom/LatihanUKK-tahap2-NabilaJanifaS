<?php
require_once __DIR__ . '/includes/layout.php';

$home = ['admin' => 'admin/users.php', 'petugas' => 'petugas/persetujuan.php', 'peminjam' => 'peminjam/alat.php'];
if (!empty($_SESSION['user'])) { header('Location: ' . BASE_URL . '/' . $home[$_SESSION['user']['role']]); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $st = db()->prepare('SELECT id_user, nama, username, password, role FROM users WHERE username = ?');
    $st->execute([trim($_POST['username'] ?? '')]);
    $u = $st->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        session_regenerate_id(true);
        unset($u['password']);
        $_SESSION['user'] = $u;
        catat_log('Login berhasil');
        header('Location: ' . BASE_URL . '/' . $home[$u['role']]);
        exit;
    }
    flash('Username atau password salah', 'error');
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}
page_header('Login');
?>
<form method="post">
  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
  <label>Username</label><input name="username" required autofocus>
  <label>Password</label><input type="password" name="password" required>
  <p><button>Login</button></p>
</form>
<?php page_footer();
