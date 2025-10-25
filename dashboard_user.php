<?php
include 'backend/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'] ?? '';
$role = $_SESSION['role'] ?? '';
$user_id = $_SESSION['user_id'];

$cart_items = [];
$total = 0;

// Ambil cart dari database user (join dengan produk)
$stmt = $conn->prepare("SELECT c.id as cart_id, p.name, p.price, p.image, c.quantity, c.size 
                        FROM cart c 
                        JOIN products p ON c.product_id = p.id 
                        WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $total += $row['price'] * $row['quantity'];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | WearWell</title>
<link rel="icon" href="img/logo.png">
<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
<link rel="stylesheet" href="style.css">
<style>

</style>
<script>
function toggleCheckout() {
    const form = document.getElementById('checkoutForm');
    form.style.display = form.style.display === 'block' ? 'none' : 'block';
}
</script>
</head>
<body>

<div class="navbar">
    <span class="logo">WearWell</span>
    <div class="menu">
        <a class="active" href="dashboard_user.php"><i class="fas fa-home"></i>Home</a>
        <a href="shop_user.php"><i class="fas fa-store"></i>Shop</a>
        <a href="dikemas.php"><i class="fas fa-box"></i>Dikemas</a>
        <a href="dikirim.php"><i class="fas fa-truck"></i>Dikirim</a>
        <form action="logout.php" method="POST" class="logout-form">
            <button type="submit" class="btn btn-logout">Logout</button>
        </form>
    </div>
</div>

<div class="welcome-message container">
    <h2>Welcome, <?= htmlspecialchars($username) ?>!</h2>

<div class="container">
    <h2>Keranjang Kamu</h2>
    <?php if(count($cart_items) > 0): ?>
        <table class="cart-table">
            <tr>
                <th>Gambar</th>
                <th>Produk</th>
                <th>Size</th>
                <th>Quantity</th>
                <th>Harga</th>
                <th>Subtotal</th>
                <th>Aksi</th>
            </tr>
            <?php foreach($cart_items as $item): ?>
            <?php 
                $cart_id = $item['cart_id'];
                $name = $item['name'];
                $price = $item['price'];
                $qty = $item['quantity'];
                $size = $item['size'];
                $img = (strpos($item['image'], 'http') === 0) ? $item['image'] : 'uploads/'.$item['image'];
            ?>
            <tr>
                <td><img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($name) ?>"></td>
                <td><?= htmlspecialchars($name) ?></td>
                <td>
                    <form action="update_cart.php" method="POST" class="cart-update-form">
                        <input type="hidden" name="cart_id" value="<?= htmlspecialchars($cart_id) ?>">
                        <select name="size">
                            <option value="S" <?= $size=='S'?'selected':'' ?>>S</option>
                            <option value="M" <?= $size=='M'?'selected':'' ?>>M</option>
                            <option value="L" <?= $size=='L'?'selected':'' ?>>L</option>
                            <option value="XL" <?= $size=='XL'?'selected':'' ?>>XL</option>
                        </select>
                </td>
                <td><input type="number" name="quantity" value="<?= htmlspecialchars($qty) ?>" min="1"></td>
                <td>Rp <?= number_format($price,0,',','.') ?></td>
                <td>Rp <?= number_format($price * $qty,0,',','.') ?></td>
                <td>
                        <button type="submit" class="btn btn-update">Update</button>
                    </form>
                    <form action="delete_cart.php" method="POST" class="cart-delete-form">
                        <input type="hidden" name="cart_id" value="<?= htmlspecialchars($cart_id) ?>">
                        <button type="submit" class="btn btn-delete">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <div class="total">Total: Rp <?= number_format($total,0,',','.') ?></div>
        <button class="btn-checkout" onclick="toggleCheckout()">Proceed Checkout</button>

        <div id="checkoutForm" class="checkout-form">
            <h3>Payment Details</h3>
            <form action="process_checkout.php" method="POST">
                <input type="text" name="fullname" placeholder="Nama Lengkap" required>
                <input type="text" name="address" placeholder="Alamat Pengiriman" required>
                <input type="text" name="card_number" placeholder="Nomor Kartu" required>
                <input type="text" name="expiry" placeholder="Tanggal Exp (MM/YY)" required>
                <input type="text" name="cvv" placeholder="CVV" required>
                <button type="submit" class="btn-checkout">Pay Now</button>
            </form>
        </div>

    <?php else: ?>
        <p>Keranjang kamu kosong. <a href="shop.php">Belanja Sekarang</a></p>
    <?php endif; ?>
</div>

</body>
</html>
