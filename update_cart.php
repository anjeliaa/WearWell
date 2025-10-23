<?php
include 'backend/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = (int)$_POST['cart_id'];
    $size = mysqli_real_escape_string($conn, $_POST['size']);
    $qty = (int)$_POST['quantity'];

    $stmt = $conn->prepare("UPDATE cart SET size=?, quantity=? WHERE id=?");
    $stmt->bind_param("sii", $size, $qty, $cart_id);
    $stmt->execute();
    $stmt->close();

    header("Location: dashboard_user.php");
    exit;
}
