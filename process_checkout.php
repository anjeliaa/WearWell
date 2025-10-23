<?php
include 'backend/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data dari form checkout
$fullname = $_POST['fullname'] ?? '';
$address  = $_POST['address'] ?? '';
$card_number = $_POST['card_number'] ?? '';
$expiry = $_POST['expiry'] ?? '';
$cvv = $_POST['cvv'] ?? '';

// Ambil cart user dari database
$stmtCart = $conn->prepare("SELECT c.product_id, c.quantity, c.size, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id=?");
$stmtCart->bind_param("i", $user_id);
$stmtCart->execute();
$cart_result = $stmtCart->get_result();
$cart_items = $cart_result->fetch_all(MYSQLI_ASSOC);
$stmtCart->close();

// Jika cart kosong, redirect kembali
if (empty($cart_items)) {
    header("Location: dashboard_user.php");
    exit;
}

// Hitung total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Simpan ke table orders
$stmtOrder = $conn->prepare("INSERT INTO orders (id_users, total, status) VALUES (?, ?, 'Pending')");
$stmtOrder->bind_param("id", $user_id, $total);
$stmtOrder->execute();
$order_id = $stmtOrder->insert_id;
$stmtOrder->close();

// Simpan tiap item ke order_items
foreach ($cart_items as $item) {
    $pid = (int)$item['product_id'];
    $qty = (int)$item['quantity'];
    $price = $item['price'];

    $stmtItem = $conn->prepare("INSERT INTO order_items (id_orders, id_products, quantity, price) VALUES (?, ?, ?, ?)");
    $stmtItem->bind_param("iiid", $order_id, $pid, $qty, $price);
    $stmtItem->execute();
    $stmtItem->close();
}

// Hapus cart user di database
$conn->query("DELETE FROM cart WHERE user_id='$user_id'");

// Redirect ke dikemas.php untuk melihat receipt
header("Location: dikemas.php");
exit;
?>
