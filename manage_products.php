<?php
include 'backend/config.php';

// Cek login dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

// Handle tambah produk
if (isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);

    // Upload gambar
    $image = $_FILES['image']['name'];
    $target_dir = "uploads/";
    if(!is_dir($target_dir)){
        mkdir($target_dir, 0777, true);
    }
    $target_file = $target_dir . basename($image);

    if(move_uploaded_file($_FILES['image']['tmp_name'], $target_file)){
        $stmt = $conn->prepare("INSERT INTO products (seller_id, name, price, stock, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isdis", $seller_id, $name, $price, $stock, $image);
        $stmt->execute();
        $stmt->close();

        // **Redirect agar tidak duplikasi saat refresh**
        header("Location: manage_products.php?success=1");
        exit;
    } else {
        $error = "Failed to upload image!";
    }
}


// Ambil semua produk seller
$result = $conn->prepare("SELECT * FROM products WHERE seller_id = ?");
$result->bind_param("i", $seller_id);
$result->execute();
$products = $result->get_result();
$result->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Products | WearWell</title>
<link rel="stylesheet" href="style.css">
<style>
    body { font-family: 'Spartan', sans-serif; background:#f5f5f5; margin:0; padding:0; }
.navbar { display:flex; justify-content:space-between; align-items:center; background:#fff; padding:10px 20px; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
.navbar a { text-decoration:none; color:#0f0f0f; margin-left:15px; font-weight:500; }
.navbar a:hover { color:#f74943; }
.logo { font-size:24px; font-weight:700; color:#f74943; }
.container { padding:20px; }
.card { background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 5px rgba(0,0,0,0.1); margin-bottom:20px; }
.card h3 { margin-top:0; }
table { width:100%; border-collapse:collapse; background:#fff; border-radius:10px; overflow:hidden; margin-bottom:20px; }
th, td { padding:12px; border-bottom:1px solid #ddd; text-align:left; }
th { background:#088178; color:#fff; }
tr:hover { background:#f1f1f1; }
.btn { padding:8px 12px; border:none; border-radius:5px; cursor:pointer; margin-right:5px; }
.btn-add { background:#088178; color:#fff; }
.btn-edit { background:#f74943; color:#fff; }
.btn-delete { background:#e74c3c; color:#fff; }
.btn-update { background:#f74943; color:#fff; }
.tab-content.hidden { display:none; }
    .preview-img {
        margin-top: 10px;
        max-width: 100px;
        max-height: 100px;
        display: none;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
</style>
</head>
<body>
    <div class="navbar">
    <div class="logo">WearWell</div>
    <div>
        <a href="dashboard_seller.php" onclick="showTab('home')">Home</a>
        <a href="manage_products.php" onclick="showTab('products')">Manage Product</a>
        <a href="#" onclick="showTab('orders')">Orders</a>
        <a href="#" onclick="showTab('reports')">Reports</a>
    </div>
    <div>
        <span><?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php" class="btn btn-delete">Logout</a>
    </div>
</div>
<section class="container">
    <h2>Manage Products</h2>

    <!-- Form Tambah Produk -->
    <form action="" method="POST" enctype="multipart/form-data" class="product-form">
        <input type="text" name="name" placeholder="Product Name" required>
        <input type="number" step="0.01" name="price" placeholder="Price" required>
        <input type="number" name="stock" placeholder="Stock" required>
        <input type="file" name="image" accept="image/*" id="imageInput">
        <img id="imagePreview" class="preview-img" alt="Preview">
        <button type="submit" name="add_product" class="btn btn-update">Add Product</button>
    </form>

    <!-- Tabel Produk -->
    <table class="cart-table">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Image</th>
            <th>Action</th>
        </tr>
        <?php while($product = $products->fetch_assoc()): ?>
        <tr>
            <td><?= $product['id'] ?></td>
            <td><?= htmlspecialchars($product['name']) ?></td>
            <td>$<?= number_format($product['price'], 2) ?></td>
            <td><?= $product['stock'] ?></td>
            <td>
                <?php if($product['image']): ?>
                    <img src="uploads/<?= htmlspecialchars($product['image']) ?>" width="50" alt="Product Image">
                <?php endif; ?>
            </td>
            <td>
                <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn btn-update">Edit</a>
                <a href="?delete=<?= $product['id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</section>

<script>
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');

    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.setAttribute('src', e.target.result);
                imagePreview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            imagePreview.setAttribute('src', '');
            imagePreview.style.display = 'none';
        }
    });
</script>
</body>
</html>
