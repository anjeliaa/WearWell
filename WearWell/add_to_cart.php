<?php
session_start();
include 'backend/config.php';

// Pastikan user login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['add_to_cart'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = (int)$_POST['product_id'];
    $size = $_POST['size'] ?? 'M';
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    // Cek apakah produk sudah ada di cart user
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id=? AND product_id=? AND size=?");
    $stmt->bind_param("iis", $user_id, $product_id, $size);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Jika sudah ada, update quantity
        $stmt_update = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id=? AND product_id=? AND size=?");
        $stmt_update->bind_param("iiis", $quantity, $user_id, $product_id, $size);
        $stmt_update->execute();
        $stmt_update->close();
    } else {
        // Jika belum ada, insert baru
        $stmt_insert = $conn->prepare("INSERT INTO cart (user_id, product_id, size, quantity) VALUES (?, ?, ?, ?)");
        $stmt_insert->bind_param("iisi", $user_id, $product_id, $size, $quantity);
        $stmt_insert->execute();
        $stmt_insert->close();
    }

    $stmt->close();

    // Kembali ke halaman shop_user
    header("Location: shop_user.php");
    exit;
} else {
    header("Location: shop_user.php");
    exit;
}
?>
