<?php
// public/edit.php
session_start();
require_once __DIR__ . '/../config/db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: index.php");
    exit;
}

$errors = [];
$name     = $product['name'];
$category = $product['category'];
$price    = $product['price'];
$stock    = $product['stock'];
$image    = $product['image'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    if ($category === '') {
        $category = 'Umum';
    }

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

    if (empty($errors)) {
        $checkStmt = $pdo->prepare("SELECT id FROM products WHERE name = :name AND id != :id LIMIT 1");
        $checkStmt->execute(['name' => $name, 'id' => $id]);
        if ($checkStmt->fetch()) {
            $errors['name'] = 'Nama produk sudah digunakan oleh produk lain.';
        }
    }

    // Proses upload jika ada file baru yang diunggah
    $imageName = $image;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if (!in_array($file['type'], $allowedTypes)) {
            $errors['image'] = 'Format gambar harus berupa JPG, PNG, atau WEBP.';
        } elseif ($file['size'] > $maxSize) {
            $errors['image'] = 'Ukuran gambar maksimal 2MB.';
        } elseif ($file['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newImageName = uniqid('prod_', true) . '.' . $ext;
            $uploadDir = __DIR__ . '/uploads/';

            if (move_uploaded_file($file['tmp_name'], $uploadDir . $newImageName)) {
                // Hapus file gambar lama jika ada
                if (!empty($image) && file_exists($uploadDir . $image)) {
                    unlink($uploadDir . $image);
                }
                $imageName = $newImageName;
            } else {
                $errors['image'] = 'Gagal menyimpan gambar baru.';
            }
        }
    }

    if (empty($errors)) {
        $updateStmt = $pdo->prepare(
            "UPDATE products 
             SET name = :name, category = :category, price = :price, stock = :stock, image = :image 
             WHERE id = :id"
        );
        $updateStmt->execute([
            'name'     => $name,
            'category' => $category,
            'price'    => $price,
            'stock'    => $stock,
            'image'    => $imageName,
            'id'       => $id,
        ]);

        header("Location: index.php?status=updated");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Produk - Product Manager</title>
  <link rel="stylesheet" href="assets/style.css?v=<?= time() ?>">
</head>
<body>

  <div class="container">
    <div class="header">
      <h1 class="title">Edit Produk</h1>
      <a href="index.php" class="btn btn-secondary">&larr; Kembali</a>
    </div>

    <div class="form-box">
      <!-- Wajib enctype multipart/form-data -->
      <form action="edit.php?id=<?= (int)$id ?>" method="POST" enctype="multipart/form-data" novalidate>
        
        <div class="form-group">
          <label for="name">Nama Produk *</label>
          <input type="text" id="name" name="name" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" required>
          <?php if (isset($errors['name'])): ?>
            <span class="error-text"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="category">Kategori</label>
          <input type="text" id="category" name="category" value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
          <label for="price">Harga (Rp) *</label>
          <input type="number" id="price" name="price" step="0.01" value="<?= htmlspecialchars((string)$price, ENT_QUOTES, 'UTF-8') ?>" required>
          <?php if (isset($errors['price'])): ?>
            <span class="error-text"><?= htmlspecialchars($errors['price'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="stock">Jumlah Stok *</label>
          <input type="number" id="stock" name="stock" value="<?= htmlspecialchars((string)$stock, ENT_QUOTES, 'UTF-8') ?>" required>
          <?php if (isset($errors['stock'])): ?>
            <span class="error-text"><?= htmlspecialchars($errors['stock'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="image">Ganti / Unggah Foto Produk (Maks 2MB)</label>
          <?php if (!empty($image) && file_exists(__DIR__ . '/uploads/' . $image)): ?>
            <div style="margin-bottom: 10px;">
              <small style="color: #64748b;">Foto Saat Ini:</small><br>
              <img src="uploads/<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>" alt="Preview" style="width: 80px; height: 80px; object-fit: cover; border-radius: 6px; margin-top: 5px; border: 1px solid #cbd5e1;">
            </div>
          <?php endif; ?>
          <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
          <?php if (isset($errors['image'])): ?>
            <span class="error-text"><?= htmlspecialchars($errors['image'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
          <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>

      </form>
    </div>
  </div>

</body>
</html>