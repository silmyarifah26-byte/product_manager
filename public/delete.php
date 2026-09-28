<?php
// public/delete.php
session_start();
require_once __DIR__ . '/../config/db.php';

// Pastikan hanya request POST yang dapat mengeksekusi penghapusan
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode request tidak diizinkan.');
}

// 1. Validasi Token CSRF untuk keamanan dari request berbahaya pihak luar
$userToken = $_POST['csrf'] ?? '';
$sessionToken = $_SESSION['csrf'] ?? '';

if (!hash_equals($sessionToken, $userToken)) {
    http_response_code(403);
    exit('Akses ditolak: Token CSRF tidak valid.');
}

// 2. Validasi ID produk
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id) {
    // 3. Hapus data dengan prepared statement
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute(['id' => $id]);
}

// 4. Redirect kembali ke halaman utama dengan notifikasi sukses
header("Location: index.php?status=deleted");
exit;