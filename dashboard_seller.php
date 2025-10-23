<?php
include 'backend/config.php';

// Pastikan hanya seller yang bisa masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

// Total produk semua seller
$total_products = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];

// Total order item (semua order)
$total_orders = $conn->query("SELECT COUNT(*) as total FROM order_items")->fetch_assoc()['total'];

// Orders pending (semua)
$orders_pending = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status='Pending'")->fetch_assoc()['total'];

// Revenue hari ini (semua order)
$revenue_today = $conn->query("
    SELECT SUM(oi.quantity * oi.price) as total
    FROM order_items oi
    JOIN orders o ON oi.id_orders = o.id_orders
    WHERE DATE(o.created_at) = CURDATE()
")->fetch_assoc()['total'] ?? 0;

// Ambil data produk untuk Manage Product (semua)
$products = $conn->query("SELECT p.*, u.username AS seller_name FROM products p JOIN users u ON p.seller_id=u.id");

// Ambil data orders (semua order)
$orders = $conn->query("
    SELECT o.id_orders, u.username AS customer, oi.quantity, oi.price, o.status, p.name AS product_name
    FROM order_items oi
    JOIN orders o ON oi.id_orders = o.id_orders
    JOIN users u ON o.id_users = u.id
    JOIN products p ON oi.id_products = p.id
    ORDER BY o.id_orders DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Seller Dashboard | WearWell</title>
<link rel="stylesheet" href="style.css">
<style>

</style>
</head>
<body>

<div class="navbar">
    <div class="logo">WearWell</div>
    <div>
        <a href="dashboard_seller.php" onclick="showTab('home'); return false;">Home</a>
        <a href="#" onclick="showTab('products'); return false;">Manage Product</a>
        <a href="#" onclick="showTab('orders'); return false;">Orders</a>
        <a href="#" onclick="showTab('reports'); return false;">Reports</a>
    </div>
    <div>
        <span><?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php" class="btn btn-delete">Logout</a>
    </div>
</div>

<div class="container">
    <!-- Home -->
    <div id="home" class="tab-content">
        <div class="card">
            <h3>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h3>
            <p>Total Products: <?= $total_products ?></p>
            <p>Total Orders: <?= $total_orders ?></p>
            <p>Orders Pending: <?= $orders_pending ?></p>
            <p>Revenue Today: Rp<?= number_format($revenue_today,2,',','.') ?></p>
        </div>
    </div>

    <!-- Manage Product -->
    <div id="products" class="tab-content hidden">
        <div class="card">
            <h3>Manage Products <a href="add_product.php" class="btn btn-add">Add New</a></h3>
            <table>
                <tr><th>ID</th><th>Name</th><th>Price</th><th>Stock</th><th>Seller</th><th>Image</th><th>Action</th></tr>
                <?php while($p = $products->fetch_assoc()): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td>Rp<?= number_format($p['price'],2,',','.') ?></td>
                    <td><?= $p['stock'] ?></td>
                    <td><?= htmlspecialchars($p['seller_name']) ?></td>
                    <td>
                        <?php if($p['image'] && file_exists('uploads/'.$p['image'])): ?>
                            <img src="uploads/<?= $p['image'] ?>" alt="<?= htmlspecialchars($p['name']) ?>" width="50">
                        <?php else: ?>-
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn btn-edit">Edit</a>
                        <a href="delete_product.php?id=<?= $p['id'] ?>" class="btn btn-delete">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

    <!-- Orders -->
    <div id="orders" class="tab-content hidden">
        <div class="card">
            <h3>Orders</h3>
            <table>
                <tr><th>Order ID</th><th>Customer</th><th>Product</th><th>Quantity</th><th>Price</th><th>Status</th><th>Action</th></tr>
                <?php while($o = $orders->fetch_assoc()): ?>
                <tr>
                    <td><?= $o['id_orders'] ?></td>
                    <td><?= htmlspecialchars($o['customer']) ?></td>
                    <td><?= htmlspecialchars($o['product_name']) ?></td>
                    <td><?= $o['quantity'] ?></td>
                    <td>Rp<?= number_format($o['price'],2,',','.') ?></td>
                    <td><?= $o['status'] ?></td>
                    <td>
                        <?php if($o['status'] !== 'Completed' && $o['status'] !== 'Canceled'): ?>
                            <form action="update_order.php" method="GET">
                                <input type="hidden" name="id_orders" value="<?= $o['id_orders'] ?>">
                                <select name="status">
                                    <option value="Pending" <?= $o['status']=='Pending'?'selected':'' ?>>Pending</option>
                                    <option value="Processing" <?= $o['status']=='Processing'?'selected':'' ?>>Processing</option>
                                    <option value="Shipped" <?= $o['status']=='Shipped'?'selected':'' ?>>Shipped</option>
                                    <option value="Completed" <?= $o['status']=='Completed'?'selected':'' ?>>Completed</option>
                                    <option value="Canceled" <?= $o['status']=='Canceled'?'selected':'' ?>>Canceled</option>
                                </select>
                                <button type="submit" class="btn btn-update">Update</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

    <!-- Reports -->
    <div id="reports" class="tab-content hidden">
        <div class="card">
            <h3>Reports</h3>
            <p>Coming soon: sales stats, revenue charts, best-selling products.</p>
        </div>
    </div>
</div>

<script>
function showTab(tabId){
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    const tab = document.getElementById(tabId);
    if(tab) tab.classList.remove('hidden');

    // Navbar active
    document.querySelectorAll('.navbar a').forEach(a => a.classList.remove('active'));
    const activeLink = document.querySelector('.navbar a[onclick*="'+tabId+'"]');
    if(activeLink) activeLink.classList.add('active');
}

document.addEventListener('DOMContentLoaded', function(){
    const hash = window.location.hash.substring(1);
    showTab(hash ? hash : 'home');
});
</script>

</body>
</html>
