<?php
require_once __DIR__ . '/includes/db.php';
if (!empty($_SESSION['user'])) catat_log('Logout');
$_SESSION = [];
session_destroy();
header('Location: ' . BASE_URL . '/login.php');
