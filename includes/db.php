<?php
// Konfigurasi koneksi & fungsi dasar
session_start();
const DB_HOST = 'localhost';
const DB_NAME = 'db_peminjaman_alat';
const DB_USER = 'root';
const DB_PASS = '';
const BASE_URL = '/nabila_xiirpl2/peminjaman_alat';

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}

function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function require_role(array $roles): void
{
    if (empty($_SESSION['user']) || !in_array($_SESSION['user']['role'], $roles, true)) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function catat_log(string $aktivitas): void
{
    $uid = $_SESSION['user']['id_user'] ?? null;
    db()->prepare('INSERT INTO log_aktivitas (id_user, aktivitas) VALUES (?, ?)')->execute([$uid, $aktivitas]);
}

function flash(?string $msg = null, string $type = 'success'): ?array
{
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $type]; return null; }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function csrf_token(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
}

function csrf_check(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Token tidak valid');
    }
}

function db_message(PDOException $ex): string
{
    // Pesan SIGNAL (SQLSTATE 45000) aman ditampilkan; error lain disamarkan
    return $ex->getCode() === '45000' ? ($ex->errorInfo[2] ?? 'Gagal') : 'Terjadi kesalahan pada database';
}
