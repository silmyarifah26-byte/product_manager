<?php
// public/index.php
session_start();
require_once __DIR__ . '/../config/db.php';

// Siapkan CSRF token di session untuk keamanan delete
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));

// Ambil data produk (ORDER BY id DESC agar produk terbaru tampil paling atas)
$stmt = $pdo->query("SELECT id, name, category, price, stock FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();

// Tangkap notifikasi pesan status dari redirect PRG
$status = $_GET['status'] ?? '';
$message = '';
if ($status === 'created') $message = 'Produk baru berhasil ditambahkan!';
if ($status === 'updated') $message = 'Data produk berhasil diperbarui!';
if ($status === 'deleted') $message = 'Produk berhasil dihapus!';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Produk - Product Manager</title>
  
  <!-- Pemanggilan CSS dengan trik anti-cache agar desain langsung muncul -->
  <link rel="stylesheet" href="assets/style.css?v=<?= time() ?>">
</head>
<body>

  <div class="container">
    <div class="header">
      <h1 class="title">Product Manager</h1>
      <a href="create.php" class="btn btn-primary">+ Tambah Produk</a>
    </div>

    <!-- Alert Notifikasi Status -->
    <?php if ($message): ?>
      <div class="alert alert-success">
        <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <!-- Grid Flexbox Kartu Produk -->
    <div class="products-grid">
      <?php if (empty($products)): ?>
        <p>Belum ada produk yang tersimpan. Klik "+ Tambah Produk" untuk mengisi.</p>
      <?php else: ?>
        <?php foreach ($products as $p): ?>
          <div class="card">
            <div>
              <span class="card-badge"><?= htmlspecialchars($p['category'], ENT_QUOTES, 'UTF-8') ?></span>
              <h3 class="card-title"><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></h3>
              <p class="card-price">Rp <?= number_format($p['price'], 0, ',', '.') ?></p>
              <p class="card-stock">Stok: <?= (int)$p['stock'] ?> unit</p>
            </div>
            
            <div class="card-actions">
              <a href="edit.php?id=<?= (int)$p['id'] ?>" class="btn btn-edit">Edit</a>
              
              <!-- Form Hapus POST + CSRF Protection -->
              <form method="POST" action="delete.php" onsubmit="return confirm('Apakah kamu yakin ingin menghapus produk ini?');" style="margin: 0;">
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn btn-danger">Hapus</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</body>
</html>