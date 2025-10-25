<?php
include 'backend/config.php';

// Pastikan hanya seller
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];
$message = '';

if(isset($_POST['submit'])){
    $name = $conn->real_escape_string($_POST['name']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);

    // Upload image
    $image = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/'.$image);
    }

    $sql = "INSERT INTO products (seller_id, name, price, stock, image) 
            VALUES ($seller_id, '$name', $price, $stock, '$image')";
    if($conn->query($sql)){
        $message = "Product added successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Product | WearWell</title>
<link rel="stylesheet" href="style.css">
<style>
body { font-family: 'Spartan', sans-serif; padding:20px; background:#f5f5f5; }
form { background:#fff; padding:20px; border-radius:10px; max-width:500px; margin:auto; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
input, label { display:block; width:100%; margin-bottom:15px; }
input[type="submit"] { width:auto; background:#088178; color:#fff; border:none; padding:10px 20px; border-radius:5px; cursor:pointer; }
.message { margin-bottom:15px; color:green; }
</style>
</head>
<body>
<h2>Add New Product</h2>
<?php if($message) echo "<div class='message'>$message</div>"; ?>
<form method="POST" enctype="multipart/form-data">
    <label>Product Name</label>
    <input type="text" name="name" required>

    <label>Price</label>
    <input type="number" name="price" step="0.01" required>

    <label>Stock</label>
    <input type="number" name="stock" required>

    <label>Image</label>
    <input type="file" name="image" accept="image/*">

    <input type="submit" name="submit" value="Add Product">
</form>
</body>
</html>
