<?php
include 'backend/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = (int)$_POST['cart_id'];

    $stmt = $conn->prepare("DELETE FROM cart WHERE id=?");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $stmt->close();

    header("Location: dashboard_user.php");
    exit;
}
