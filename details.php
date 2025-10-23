<?php
include 'backend/config.php';

// 🔹 Tambah ke cart jika tombol diklik
if (isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $product_id = (int)($_POST['product_id'] ?? 0);
    $size = $_POST['size'] ?? 'M';
    $quantity = (int)($_POST['quantity'] ?? 1);

    if ($product_id > 0 && $size != '') {
        // Cek apakah sudah ada di cart
        $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id=? AND product_id=? AND size=?");
        $stmt->bind_param("iis", $user_id, $product_id, $size);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $stmt_update = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id=? AND product_id=? AND size=?");
            $stmt_update->bind_param("iiis", $quantity, $user_id, $product_id, $size);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO cart (user_id, product_id, size, quantity) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("iisi", $user_id, $product_id, $size, $quantity);
            $stmt_insert->execute();
            $stmt_insert->close();
        }

        $stmt->close();
    }

    // 🔹 Setelah add to cart, redirect ke dashboard
    header("Location: dashboard_user.php");
    exit;
}

// 🔹 Ambil ID produk dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: shop_user.php");
    exit;
}

$id = (int) $_GET['id'];
$query = "
    SELECT p.*, c.name AS category_name 
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.id = $id
";
$result = mysqli_query($conn, $query);
$product = mysqli_fetch_assoc($result);

// Jika produk tidak ditemukan
if (!$product) {
    header("Location: shop_user.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($product['name']) ?> | WearWell</title>
<link rel="icon" href="img/logo.png">
<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
<link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Header -->
<section id="header">
    <div class="nav-left"><p class="logo-text">WearWell</p></div>
    <div class="navbar">
        <div class="menu">
            <a href="dashboard_user.php"><i class="fas fa-home"></i>Home</a>
            <a class="active" href="shop_user.php"><i class="fas fa-store"></i>Shop</a>
            <a href="dikemas.php"><i class="fas fa-box"></i>Dikemas</a>
            <a href="dikirim.php"><i class="fas fa-truck"></i>Dikirim</a>
            <form action="logout.php" method="POST" class="logout-form">
                <button type="submit" class="btn btn-logout">Logout</button>
            </form>
        </div>
    </div>
    <div id="mobile">
        <a href="cart.php"><i class="far fa-shopping-bag"></i></a>
        <i id="bar" class="fas fa-outdent"></i>
    </div>
</section>

<section id="prodetails" class="section-p1">
    <div class="single-pro-image">
        <img src="uploads/<?= htmlspecialchars($product['image']) ?>" width="100%" id="MainImg" alt="<?= htmlspecialchars($product['name']) ?>">
    </div>

    <div class="single-pro-details">
        <h6>Home / <?= htmlspecialchars($product['category_name']) ?></h6>
        <h4><?= htmlspecialchars($product['name']) ?></h4>
        <h2>Rp<?= number_format($product['price'], 0, ',', '.') ?></h2>
        <!-- 🔹 Form Add to Cart -->
        <form action="" method="post">
            <select name="size" required>
                <option value="">Pilih Ukuran</option>
                <option value="XXL">XXL</option>
                <option value="XL">XL</option>
                <option value="L">L</option>
                <option value="M">M</option>
                <option value="S">S</option>
            </select>

            <input type="number" name="quantity" value="1" min="1" required>
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <button type="submit" name="add_to_cart" class="normal">Add To Cart</button>
        </form>

        <h4>Product Details</h4>
        <span>
            <?= !empty($product['description']) ? nl2br(htmlspecialchars($product['description'])) : 'Deskripsi produk belum tersedia.' ?>
        </span>
    </div>
</section>

<!-- Footer -->
<footer class="section-p1">
    <div class="col">
        <img class="logo" src="img/logo.png" alt="" height="50">
        <h4>Contact</h4>
        <p><strong>Address:</strong> Mayor Salim Batu Bara Street, Kota Bengkulu</p>
        <p><strong>Phone:</strong> +62895422212679</p>
        <p><strong>Hours:</strong> 08.00-21.00. Sen-Sab</p>
        <div class="follow">
            <h4>Follow Us</h4>
            <div class="icon">
                <i class="fab fa-facebook"></i>
                <i class="fab fa-twitter"></i>
                <i class="fab fa-instagram"></i>
                <i class="fab fa-pinterest"></i>
                <i class="fab fa-youtube"></i>
            </div>
        </div>
    </div>

    <div class="col">
        <h4>About</h4>
        <a href="#">About Us</a>
        <a href="#">Delivery Information</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms & Conditions</a>
        <a href="#">Contact Us</a>
    </div>

    <div class="col">
        <h4>My Account</h4>
        <a href="#">Sign In</a>
        <a href="#">View Cart</a>
        <a href="#">My Wishlist</a>
        <a href="#">Track My Order</a>
        <a href="#">Help</a>
    </div>

    <div class="col install">
        <h4>Install App</h4>
        <p>From App Store or Google Play</p>
        <div class="row">
            <img src="img/pay/app.jpg" alt="">
            <img src="img/pay/play.jpg" alt="">
        </div>
        <p>Secured Payment Gateways</p>
        <img src="img/pay/pay.png" alt="">
    </div>

    <div class="copyright">
        <p>&copy; Copyright by <span>Kelompok 2</span> All Rights Reserved 2025, Indonesia, Kota Bengkulu.</p>
    </div>
</footer>
</body>
</html>
