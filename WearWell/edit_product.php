<?php
include 'backend/config.php';

// Cek login dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$message = '';

// Ambil ID produk dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_products.php");
    exit;
}
$product_id = intval($_GET['id']);

// Ambil data produk (hapus pembatas seller_id)
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    header("Location: manage_products.php");
    exit;
}
$product = $result->fetch_assoc();
$stmt->close();

// Handle update produk
if (isset($_POST['update_product'])) {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $image = $product['image']; // Default gambar lama

    // Upload gambar baru jika ada
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        // Hapus gambar lama jika ada
        if ($image && file_exists($target_dir . $image)) {
            unlink($target_dir . $image);
        }
        $image = basename($_FILES['image']['name']);
        $target_file = $target_dir . $image;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $message = "Failed to upload image!";
        }
    }

    // Update database (hapus pembatas seller_id)
    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, stock = ?, image = ? WHERE id = ?");
    $stmt->bind_param("sdssi", $name, $price, $stock, $image, $product_id);

    if ($stmt->execute()) {
        $message = "Product updated successfully!";
        // Refresh data produk
        $stmt->close();
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
    } else {
        $message = "Error updating product: " . $conn->error;
    }
    $stmt->close();

    // Redirect untuk menghindari duplikasi saat refresh
    header("Location: edit_product.php?id=$product_id&success=1");
    exit;
}

// Pesan sukses dari redirect
if (isset($_GET['success'])) {
    $message = "Product updated successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Product | WearWell</title>
<link rel="stylesheet" href="style.css">
<style>
body { font-family: 'Spartan', sans-serif; background:#f5f5f5; margin:0; padding:0; }
.navbar { display:flex; justify-content:space-between; align-items:center; background:#fff; padding:10px 20px; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
.navbar a { text-decoration:none; color:#0f0f0f; margin-left:15px; font-weight:500; }
.navbar a:hover { color:#f74943; }
.navbar a.active { color:#f74943; font-weight:700; }
.logo { font-size:24px; font-weight:700; color:#f74943; }
.container { padding:20px; }
.card { background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 5px rgba(0,0,0,0.1); margin-bottom:20px; }
.product-form { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:20px; }
.product-form input { flex:1; min-width:200px; padding:10px; border:1px solid #ddd; border-radius:5px; }
.product-form button { padding:10px 20px; background:#f74943; color:#fff; border:none; border-radius:5px; cursor:pointer; }
.product-form button:hover { background:#e74c3c; }
.preview-img { margin-top:10px; max-width:100px; max-height:100px; border:1px solid #ddd; border-radius:5px; }
.message { color:green; font-weight:600; margin-bottom:15px; }
</style>
</head>
<body>
<div class="navbar">
    <div class="logo">WearWell</div>
    <div>
        <a href="dashboard_seller.php">Home</a>
        <a href="manage_products.php" class="active">Manage Product</a>
        <a href="#">Orders</a>
        <a href="#">Reports</a>
    </div>
    <div>
        <span><?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php" class="btn btn-delete">Logout</a>
    </div>
</div>

<section class="container">
    <h2>Edit Product</h2>
    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" class="product-form">
        <input type="text" name="name" placeholder="Product Name" value="<?= htmlspecialchars($product['name']) ?>" required>
        <input type="number" step="0.01" name="price" placeholder="Price" value="<?= htmlspecialchars($product['price']) ?>" required>
        <input type="number" name="stock" placeholder="Stock" value="<?= htmlspecialchars($product['stock']) ?>" required>
        <input type="file" name="image" accept="image/*" id="imageInput">
        <img id="imagePreview" class="preview-img" src="uploads/<?= htmlspecialchars($product['image']) ?>" alt="Current Image" style="display:<?= $product['image'] ? 'block' : 'none' ?>;">
        <button type="submit" name="update_product">Update Product</button>
    </form>
</section>

<script>
const imageInput = document.getElementById('imageInput');
const imagePreview = document.getElementById('imagePreview');
imageInput.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = e => {
            imagePreview.src = e.target.result;
            imagePreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        imagePreview.src = 'uploads/<?= htmlspecialchars($product['image']) ?>';
        imagePreview.style.display = '<?= $product['image'] ? 'block' : 'none' ?>';
    }
});
</script>
</body>
</html>
