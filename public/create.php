<?php
// public/create.php
session_start();
require_once __DIR__ . '/../config/db.php';

$errors = [];
$name     = '';
$category = 'Umum';
$price    = '';
$stock    = '0';

// Periksa apakah request dikirim melalui method POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Normalisasi input (trim spasi)
    $name     = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    if ($category === '') {
        $category = 'Umum';
    }

    // 2. Validasi tipe & rentang nilai
    $priceRaw = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $stockRaw = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);

    if (mb_strlen($name) < 3) {
        $errors['name'] = 'Nama produk minimal 3 karakter.';
    }

    if ($priceRaw === false || $priceRaw <= 0) {
        $errors['price'] = 'Harga harus berupa angka lebih besar dari 0.';
    } else {
        $price = $priceRaw;
    }

    if ($stockRaw === false || $stockRaw < 0) {
        $errors['stock'] = 'Stok tidak boleh bernilai negatif.';
    } else {
        $stock = $stockRaw;
    }

    // 3. Cek keunikan nama produk (mencegah duplikasi nama di database)
    if (empty($errors)) {
        $checkStmt = $pdo->prepare("SELECT id FROM products WHERE name = :name LIMIT 1");
        $checkStmt->execute(['name' => $name]);
        if ($checkStmt->fetch()) {
            $errors['name'] = 'Nama produk sudah terdaftar, gunakan nama lain.';
        }
    }

    // 4. Jika validasi lolos, simpan ke database dengan Prepared Statement
    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO products (name, category, price, stock) VALUES (:name, :category, :price, :stock)"
        );
        $stmt->execute([
            'name'     => $name,
            'category' => $category,
            'price'    => $price,
            'stock'    => $stock,
        ]);

        // Pola Post-Redirect-Get (PRG) untuk mencegah submit ganda saat refresh
        header("Location: index.php?status=created");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Produk - Product Manager</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>

  <div class="container">
    <div class="header">
      <h1 class="title">Tambah Produk Baru</h1>
      <a href="index.php" class="btn btn-secondary">&larr; Kembali</a>
    </div>

    <div class="form-box">
      <form action="create.php" method="POST" novalidate>
        
        <div class="form-group">
          <label for="name">Nama Produk *</label>
          <input 
            type="text" 
            id="name" 
            name="name" 
            value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" 
            required
          >
          <?php if (isset($errors['name'])): ?>
            <span class="error-text"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="category">Kategori</label>
          <input 
            type="text" 
            id="category" 
            name="category" 
            value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>"
            placeholder="Contoh: Makanan, Minuman, Elektronik"
          >
        </div>

        <div class="form-group">
          <label for="price">Harga (Rp) *</label>
          <input 
            type="number" 
            id="price" 
            name="price" 
            step="0.01" 
            value="<?= htmlspecialchars((string)$price, ENT_QUOTES, 'UTF-8') ?>" 
            required
          >
          <?php if (isset($errors['price'])): ?>
            <span class="error-text"><?= htmlspecialchars($errors['price'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="stock">Jumlah Stok *</label>
          <input 
            type="number" 
            id="stock" 
            name="stock" 
            value="<?= htmlspecialchars((string)$stock, ENT_QUOTES, 'UTF-8') ?>" 
            required
          >
          <?php if (isset($errors['stock'])): ?>
            <span class="error-text"><?= htmlspecialchars($errors['stock'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Simpan Produk</button>
          <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>

      </form>
    </div>
  </div>

</body>
</html>