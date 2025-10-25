<?php
include 'backend/config.php';

// Ambil ID produk dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: shop.php");
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
    header("Location: shop.php");
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
    <!-- Kiri: Nama Toko -->
    <div class="nav-left">
        <p class="logo-text">WearWell</p>
    </div>

    <!-- Tengah: Menu Navigasi -->
    <div class="nav-center">
        <ul id="navbar">
            <li><a href="index.php">Home</a></li>
            <li><a class="active"href="shop.php">Shop</a></li>
            <li><a href="blog.html">Blog</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="contact.html">Contact</a></li>
        </ul>
    </div>

    <!-- Kanan: Cart & Login/Register -->
    <div class="nav-right">
        <a href="cart.php" id="lg-bag"><i class="far fa-shopping-bag"></i></a>
        <a href="login.php" id="lg-user"><i class="far fa-user"></i></a>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile">
        <a href="cart.html"><i class="far fa-shopping-bag"></i></a>
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
        <form action="cart.php" method="post">
    <select name="size" required>
        <option value="">Pilih Ukuran</option>
        <option value="XXL">XXL</option>
        <option value="XL">XL</option>
        <option value="L">L</option>
        <option value="M">M</option>
        <option value="S">S</option>
    </select>

    <input type="number" name="quantity" value="1" min="1" required>

    <!-- pakai ID produk, bukan nama -->
    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
    <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['name']) ?>">
    <input type="hidden" name="price" value="<?= $product['price'] ?>">
    <input type="hidden" name="image" value="uploads/<?= htmlspecialchars($product['image']) ?>">
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
