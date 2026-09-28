<?php
// public/index.php
session_start();
require_once __DIR__ . '/../config/db.php';

$_SESSION['csrf'] ??= bin2hex(random_bytes(32));

// Ambil seluruh data produk
$stmt = $pdo->query("SELECT id, name, category, price, stock, image FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();

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
  <title>Katalog Produk - Product Manager</title>
  <link rel="stylesheet" href="assets/style.css?v=<?= time() ?>">
</head>
<body>

  <div class="container">
    <div class="header">
      <div>
        <h1 class="title">Katalog Produk</h1>
        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.92rem;">Kelola data stok dan harga produk Anda</p>
      </div>
      <a href="create.php" class="btn btn-primary">+ Tambah Produk</a>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-success">
        <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <div class="products-grid">
      <?php if (empty($products)): ?>
        <p style="color: #64748b;">Belum ada produk yang tersimpan. Klik "+ Tambah Produk" untuk membuat produk pertama.</p>
      <?php else: ?>
        <?php foreach ($products as $p): ?>
          <div class="card">
            
            <!-- Box Gambar -->
            <div class="card-image-box">
              <?php if (!empty($p['image']) && file_exists(__DIR__ . '/uploads/' . $p['image'])): ?>
                <img src="uploads/<?= htmlspecialchars($p['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>" class="card-image">
              <?php else: ?>
                <div class="no-image">
                  <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                  </svg>
                  <span>Tanpa Gambar</span>
                </div>
              <?php endif; ?>
            </div>

            <!-- Konten Kartu -->
            <div class="card-body">
              <span class="card-category"><?= htmlspecialchars($p['category'], ENT_QUOTES, 'UTF-8') ?></span>
              <h3 class="card-title" title="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>
              </h3>
              <p class="card-price">Rp <?= number_format($p['price'], 0, ',', '.') ?></p>
              
              <div class="card-meta">
                <span>Status Stok:</span>
                <span class="stock-indicator <?= ((int)$p['stock'] <= 5) ? 'stock-low' : '' ?>">
                  <span class="stock-dot"></span>
                  <?= (int)$p['stock'] ?> unit
                </span>
              </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="card-actions">
              <a href="edit.php?id=<?= (int)$p['id'] ?>" class="btn btn-edit">Edit</a>
              <form method="POST" action="delete.php" onsubmit="return confirm('Apakah kamu yakin ingin menghapus produk ini?');" style="margin: 0; flex: 1; display: flex;">
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn btn-danger" style="width: 100%;">Hapus</button>
              </form>
            </div>

          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</body>
</html>