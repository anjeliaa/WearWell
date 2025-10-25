<?php
include 'backend/config.php';

// Jika form dikirim
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Validasi password
    if ($password !== $confirm) {
        $error = "Password dan konfirmasi tidak cocok!";
    } else {
        // Cek apakah email sudah terdaftar
        $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error = "Email sudah digunakan!";
        } else {
            // Hash password
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Role default: user
            $role = 'user';

            // Simpan ke database
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $hashed, $role);

            if ($stmt->execute()) {
                // Simpan session user
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;

                // Redirect ke dashboard user
                header("Location: dashboard_user.php");
                exit;
            } else {
                $error = "Terjadi kesalahan saat menyimpan data.";
            }

            $stmt->close();
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | WearWell</title>
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
        <a href="register.php" id="lg-user" class="active"><i class="far fa-user"></i></a>
    </div>
</section>

<!-- Register Section -->
<section id="register-page" class="section-p1">
    <div class="login-container">
        <h2>Create an Account</h2>
        <p>Join WearWell and start shopping today!</p>

        <?php if (isset($error)): ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form action="" method="POST" class="login-form">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required>

            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Create a password" required>

            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>

            <button type="submit" class="normal">Register</button>

            <div class="login-links">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </form>
    </div>
</section>

<footer class="section-p1">
    <div class="copyright">
        <p>&copy; Copyright by <span>Kelompok 2</span> All Rights Reserved 2025, Indonesia, Kota Bengkulu.</p>
    </div>
</footer>

</body>
</html>
