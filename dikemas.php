<?php
include 'backend/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? '';

// Ambil semua order Pending / Processing
$stmt = $conn->prepare("
    SELECT * FROM orders 
    WHERE id_users = ? AND status IN ('Pending','Processing') 
    ORDER BY id_orders DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pesanan Dikemas | WearWell</title>
<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
<link rel="stylesheet" href="style.css" />
<style>

</style>
</head>
<body>

<div class="navbar">
    <span class="logo">WearWell</span>
    <div class="menu">
        <a href="dashboard_user.php" class="nav-item">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="shop_user.php" class="nav-item">
            <i class="fas fa-store"></i>
            <span>Shop</span>
        </a>
        <a href="dikemas.php" class="nav-item active">
            <i class="fas fa-box"></i>
            <span>Dikemas</span>
        </a>
        <a href="dikirim.php" class="nav-item">
            <i class="fas fa-truck"></i>
            <span>Dikirim</span>
        </a>
        <form action="logout.php" method="POST" class="logout-form">
            <button type="submit">Logout</button>
        </form>
    </div>
</div>



<div class="container">
    <h2>Pesanan Sedang Dikemas</h2>

    <?php if(count($orders) > 0): ?>
        <?php foreach($orders as $order): ?>
            <div class="order-section">
                <div class="order-header">
                    <div>
                        <p><strong>ID Pesanan:</strong> <?= $order['id_orders'] ?></p>
                        <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
                        <p><strong>Tanggal:</strong> <?= $order['created_at'] ?></p>
                    </div>
                </div>

                <?php
                $stmtItems = $conn->prepare("
                    SELECT oi.*, p.name, p.image
                    FROM order_items oi
                    JOIN products p ON oi.id_products = p.id
                    WHERE oi.id_orders = ?
                ");
                $stmtItems->bind_param("i", $order['id_orders']);
                $stmtItems->execute();
                $items = $stmtItems->get_result();
                $total = 0;
                ?>

                <table>
                    <thead>
                        <tr>
                            <th>Gambar</th>
                            <th>Nama Produk</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $items->fetch_assoc()):
                            $price = $row['price'] ?? 0;
                            $qty = $row['quantity'] ?? 1;
                            $subtotal = $price * $qty;
                            $total += $subtotal;
                            $img = (isset($row['image']) && file_exists('uploads/'.$row['image'])) ? 'uploads/'.$row['image'] : 'uploads/no-image.png';
                        ?>
                        <tr>
                            <td><img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($row['name']) ?>"></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= $qty ?></td>
                            <td>Rp<?= number_format($price,0,',','.') ?></td>
                            <td>Rp<?= number_format($subtotal,0,',','.') ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div class="total">Total: Rp<?= number_format($total,0,',','.') ?></div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Tidak ada pesanan yang sedang dikemas.</p>
    <?php endif; ?>

    <a href="dashboard_user.php" class="button">Kembali ke Beranda</a>
</div>

</body>
</html>
