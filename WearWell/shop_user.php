<?php
include 'backend/config.php';

// 🔹 Cek user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 🔹 Proses Add to Cart
if (isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $size = $_POST['size'] ?? 'M';
    $quantity = (int)($_POST['quantity'] ?? 1);

    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id=? AND product_id=? AND size=?");
    $stmt->bind_param("iis", $user_id, $product_id, $size);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $stmt_update = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id=? AND product_id=? AND size=?");
        $stmt_update->bind_param("iiis", $quantity, $user_id, $product_id, $size);
        $stmt_update->execute();
        $stmt_update->close();
    } else {
        $stmt_insert = $conn->prepare("INSERT INTO cart (user_id, product_id, size, quantity) VALUES (?, ?, ?, ?)");
        $stmt_insert->bind_param("iisi", $user_id, $product_id, $size, $quantity);
        $stmt_insert->execute();
        $stmt_insert->close();
    }

    $stmt->close();
    header("Location: shop_user.php");
    exit;
}

// 🔹 Ambil kategori yang dipilih
$selected_category = isset($_GET['category']) ? $_GET['category'] : '';

// 🔹 Query produk
if ($selected_category) {
    $query = "
        SELECT p.*, c.name AS category_name 
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE c.name = '$selected_category'
        ORDER BY p.id DESC
    ";
} else {
    $query = "
        SELECT p.*, c.name AS category_name 
        FROM products p
        JOIN categories c ON p.category_id = c.id
        ORDER BY p.id DESC
    ";
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shop | WearWell</title>
<link rel="icon" href="img/logo.png">
<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
<link rel="stylesheet" href="style.css">

</head>
<body>

<div class="navbar">
    <span class="logo">WearWell</span>
    <div class="menu">
        <a href="dashboard_user.php"><i class="fas fa-home"></i> Home</a>
        <a class="active" href="shop_user.php"><i class="fas fa-store"></i> Shop</a>
        <a href="dikemas.php"><i class="fas fa-box"></i> Dikemas</a>
        <a href="dikirim.php"><i class="fas fa-truck"></i> Dikirim</a>
        <form action="logout.php" method="POST" style="display:inline;">
            <button type="submit" class="btn btn-logout">Logout</button>
        </form>
    </div>
</div>

<section id="page-header">
    <h2>#stayhome</h2>
    <p>Save more with coupons & up to 50% off!</p>
</section>

<!-- 🔹 Filter Kategori -->
<section id="category-filter" class="section-p1" style="text-align:center; margin-bottom: 30px;">
    <a href="shop_user.php" class="btn-category <?= $selected_category == '' ? 'active' : '' ?>">Semua</a>
    <a href="shop_user.php?category=Jacket" class="btn-category <?= $selected_category == 'Jacket' ? 'active' : '' ?>">Jacket</a>
    <a href="shop_user.php?category=Kaos" class="btn-category <?= $selected_category == 'Kaos' ? 'active' : '' ?>">Kaos</a>
    <a href="shop_user.php?category=Sweater" class="btn-category <?= $selected_category == 'Sweater' ? 'active' : '' ?>">Sweater</a>
    <a href="shop_user.php?category=Celana" class="btn-category <?= $selected_category == 'Celana' ? 'active' : '' ?>">Celana</a>
    <a href="shop_user.php?category=Jersey" class="btn-category <?= $selected_category == 'Jersey' ? 'active' : '' ?>">Jersey</a>
</section>

<section id="product1" class="section-p1">
    <div class="pro-container">
        <?php if($result && mysqli_num_rows($result) > 0): ?>
            <?php while($product = $result->fetch_assoc()): ?>
                <div class="pro">
                    <a href="details.php?id=<?= $product['id'] ?>">
                        <img src="uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    </a>
                    <div class="des">
                        <span><?= htmlspecialchars($product['category_name']) ?></span>
                        <h5><?= htmlspecialchars($product['name']) ?></h5>
                        <h4>Rp<?= number_format($product['price'], 0, ',', '.') ?></h4>
                    </div>
                    <form action="shop_user.php" method="post">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="size" value="M">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" name="add_to_cart" class="cart-btn">
                            <i class="fal fa-shopping-cart cart"></i>
                        </button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;">Produk tidak ditemukan untuk kategori ini.</p>
        <?php endif; ?>
    </div>
</section>

</body>
</html>
