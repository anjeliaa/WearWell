<?php
include 'backend/config.php';

// Ambil kategori yang dipilih (jika ada)
$selected_category = isset($_GET['category']) ? $_GET['category'] : '';

// Query dasar untuk ambil semua produk + join nama kategori
if ($selected_category) {
    $query = "
        SELECT p.*, c.name AS category_name 
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE c.name = '$selected_category'
        ORDER BY p.id DESC
    ";
} else {
    $query = "
        SELECT p.*, c.name AS category_name 
        FROM products p
        JOIN categories c ON p.category_id = c.id
        ORDER BY p.id DESC
    ";
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WearWell</title>
    <link rel="icon" href="img/logo.png">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <link rel="stylesheet" href="style.css">
</head>
<body>

<section id="header">
    <div class="nav-left">
        <p class="logo-text">WearWell</p>
    </div>

    <div class="nav-center">
        <ul id="navbar">
            <li><a href="index.php">Home</a></li>
            <li><a class="active" href="shop.php">Shop</a></li>
            <li><a href="blog.html">Blog</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="contact.html">Contact</a></li>
        </ul>
    </div>

    <div class="nav-right">
        <a href="cart.php" id="lg-bag"><i class="far fa-shopping-bag"></i></a>
        <a href="login.php" id="lg-user"><i class="far fa-user"></i></a>
    </div>

    <div id="mobile">
        <a href="cart.php"><i class="far fa-shopping-bag"></i></a>
        <i id="bar" class="fas fa-outdent"></i>
    </div>
</section>

<section id="page-header">
    <h2>#stayhome</h2>
    <p>Save more with coupons & up to 50% off!</p>
</section>

<!-- Filter tombol kategori -->
<section id="category-filter" class="section-p1" style="text-align:center; margin-bottom: 30px;">
    <a href="shop.php" class="btn-category <?= $selected_category == '' ? 'active' : '' ?>">Semua</a>
    <a href="shop.php?category=Jacket" class="btn-category <?= $selected_category == 'Jacket' ? 'active' : '' ?>">Jacket</a>
    <a href="shop.php?category=Kaos" class="btn-category <?= $selected_category == 'Kaos' ? 'active' : '' ?>">Kaos</a>
    <a href="shop.php?category=Sweater" class="btn-category <?= $selected_category == 'Sweater' ? 'active' : '' ?>">Sweater</a>
    <a href="shop.php?category=Celana" class="btn-category <?= $selected_category == 'Celana' ? 'active' : '' ?>">Celana</a>
    <a href="shop.php?category=Jersey" class="btn-category <?= $selected_category == 'Jersey' ? 'active' : '' ?>">Jersey</a>
</section>

<section id="product1" class="section-p1">
    <div class="pro-container">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while($product = $result->fetch_assoc()): ?>
                <div class="pro">
    <a href="detail.php?id=<?= $product['id'] ?>">
        <img src="uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
    </a>
    <div class="des">
        <span><?= htmlspecialchars($product['category_name']) ?></span>
        <h5><?= htmlspecialchars($product['name']) ?></h5>
        <div class="star">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
        </div>
        <h4>Rp<?= number_format($product['price'], 0, ',', '.') ?></h4>
    </div>
    <form action="cart.php" method="post">
        <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['name']) ?>">
        <input type="hidden" name="price" value="<?= $product['price'] ?>">
        <input type="hidden" name="image" value="uploads/<?= htmlspecialchars($product['image']) ?>">
        <button type="submit" name="add_to_cart" class="cart-btn">
            <i class="fal fa-shopping-cart cart"></i>
        </button>
    </form>
</div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;">Produk tidak ditemukan untuk kategori ini.</p>
        <?php endif; ?>
    </div>
</section>

<section id="pagination" class="section-p1">
    <a href="#">1</a>
    <a href="#">2</a>
    <a href="#"><i class="fal fa-long-arrow-alt-right"></i></a>
</section>

<footer class="section-p1">
    <div class="col">
        <img class="logo" src="img/logo.png" alt="" height="50">
        <h4>Contact</h4>
        <p><strong>Address:</strong> Mayor Salim Batu Bara Street, Kota Bengkulu</p>
        <p><strong>Phone:</strong> +62895422212679</p>
        <p><strong>Hours:</strong> 08.00-21.00. Sen-Sab</p>
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
        <p>&copy; Copyright by <span>WearWell</span> All Rights Reserved 2025, Indonesia, Kota Bengkulu.</p>
    </div>
</footer>

<script src="script.js"></script>

</body>
</html>
