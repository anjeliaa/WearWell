<?php
include 'backend/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

if(isset($_GET['id_orders']) && isset($_GET['status'])){
    $id_orders = intval($_GET['id_orders']);
    $new_status = $conn->real_escape_string($_GET['status']);

    $valid_status = ['Pending','Processing','Shipped','Completed','Canceled'];
    if(!in_array($new_status, $valid_status)){
        die("Invalid status");
    }

    // Pastikan order terkait produk milik seller
    $conn->query("UPDATE orders SET status='$new_status' WHERE id_orders=$id_orders");

}

// Redirect ke tab Orders
header("Location: dashboard_seller.php#orders");
exit;
?>
