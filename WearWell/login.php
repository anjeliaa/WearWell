<?php
ob_start();
include 'backend/config.php';

// Jika user sudah login, redirect otomatis ke dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'seller') {
        header("Location: dashboard_seller.php");
    } else {
        header("Location: dashboard_user.php");
    }
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Cek user berdasarkan email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Bandingkan password hash
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // === MULAI MERGE CART SESSION KE DATABASE ===
            if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $item) {
                    $pid = (int)$item['product_id'];
                    $size = mysqli_real_escape_string($conn, $item['size']);
                    $qty = (int)$item['quantity'];

                    // Cek apakah sudah ada di database
                    $check = mysqli_query($conn, "SELECT * FROM cart WHERE user_id='{$user['id']}' AND product_id='$pid' AND size='$size'");
                    if (mysqli_num_rows($check) > 0) {
                        mysqli_query($conn, "UPDATE cart SET quantity = quantity + $qty WHERE user_id='{$user['id']}' AND product_id='$pid' AND size='$size'");
                    } else {
                        mysqli_query($conn, "INSERT INTO cart (user_id, product_id, size, quantity) VALUES ('{$user['id']}', '$pid', '$size', $qty)");
                    }
                }

                // Update session cart dengan data terbaru dari database
                $_SESSION['cart'] = [];
                $result_cart = mysqli_query($conn, "SELECT * FROM cart WHERE user_id='{$user['id']}'");
                while ($row = mysqli_fetch_assoc($result_cart)) {
                    $_SESSION['cart'][] = $row;
                }
            }
            // === SELESAI MERGE CART ===

            // Redirect sesuai role
            if ($user['role'] === 'seller') {
                header("Location: dashboard_seller.php");
            } else {
                header("Location: dashboard_user.php");
            }
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak ditemukan!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | WearWell</title>
    <link rel="icon" href="img/logo.png">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Header -->
<section id="header">
    <div class="nav-left">
        <p class="logo-text">WearWell</p>
    </div>
    <div class="nav-center">
        <ul id="navbar">
            <li><a href="index.php">Home</a></li>
            <li><a href="shop.php">Shop</a></li>
            <li><a href="blog.html">Blog</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="contact.html">Contact</a></li>
        </ul>
    </div>
    <div class="nav-right">
        <a href="cart.php" id="lg-bag"><i class="far fa-shopping-bag"></i></a>
        <a href="login.php" id="lg-user" class="active"><i class="far fa-user"></i></a>
    </div>
</section>

<!-- Login Section -->
<section id="login-page" class="section-p1">
    <div class="login-container">
        <h2>Login to Your Account</h2>
        <p>Welcome back! Please enter your details.</p>

        <?php if (!empty($error)): ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form action="" method="POST" class="login-form">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>

            <button type="submit" class="normal">Login</button>

            <div class="login-links">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
                <p><a href="#">Forgot password?</a></p>
            </div>
        </form>
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
        <p>&copy; Copyright by <span>WearWell</span> All Rights Reserved 2025, Indonesia, Kota Bengkulu.</p>
    </div>
</footer>

</body>
</html>
