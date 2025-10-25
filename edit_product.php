<?php
include 'backend/config.php';

// Pastikan hanya seller
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];
$message = '';

if(!isset($_GET['id'])){
    header("Location: manage_products.php");
    exit;
}

$id = intval($_GET['id']);

// Ambil data produk
$res = $conn->query("SELECT * FROM products WHERE id=$id AND seller_id=$seller_id");
if($res->num_rows == 0){
    header("Location: manage_products.php");
    exit;
}
$product = $res->fetch_assoc();

// Update produk
if(isset($_POST['submit'])){
    $name = $conn->real_escape_string($_POST['name']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $image = $product['image']; // default gambar lama

    // Upload gambar baru
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        // Hapus gambar lama
        if($image && file_exists('uploads/'.$image)){
            unlink('uploads/'.$image);
        }
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/'.$image);
    }

    $sql = "UPDATE products SET name='$name', price=$price, stock=$stock, image='$image' WHERE id=$id AND seller_id=$seller_id";
    if($conn->query($sql)){
        $message = "Product updated successfully!";
        // refresh data
        $product = $conn->query("SELECT * FROM products WHERE id=$id AND seller_id=$seller_id")->fetch_assoc();
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Product | WearWell</title>
<link rel="stylesheet" href="style.css">
<style>
body { font-family: 'Spartan', sans-serif; padding:20px; background:#f5f5f5; }
form { background:#fff; padding:20px; border-radius:10px; max-width:500px; margin:auto; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
input, label { display:block; width:100%; margin-bottom:15px; }
input[type="submit"] { width:auto; background:#088178; color:#fff; border:none; padding:10px 20px; border-radius:5px; cursor:pointer; }
img { max-width:150px; margin-bottom:15px; display:block; }
.message { margin-bottom:15px; color:green; }
</style>
</head>
<body>
<h2>Edit Product</h2>
<?php if($message) echo "<div class='message'>$message</div>"; ?>
<form method="POST" enctype="multipart/form-data">
    <label>Product Name</label>
    <input type="text" name="name" required value="<?= htmlspecialchars($product['name']) ?>">

    <label>Price</label>
    <input type="number" name="price" step="0.01" required value="<?= $product['price'] ?>">

    <label>Stock</label>
    <input type="number" name="stock" required value="<?= $product['stock'] ?>">

    <label>Image</label>
    <?php if($product['image'] && file_exists('uploads/'.$product['image'])): ?>
        <img src="uploads/<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>">
    <?php endif; ?>
    <input type="file" name="image" accept="image/*">

    <input type="submit" name="submit" value="Update Product">
</form>
</body>
</html>
