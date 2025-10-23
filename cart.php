<?php
ob_start();
include 'backend/config.php';

// Buat cart session jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Jika user login, sinkronkan session cart dengan database
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Ambil cart dari database
    $result = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id'");
    $db_cart = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $db_cart[$row['product_id'].'_'.$row['size']] = $row; // key unik
    }

    // Merge session cart ke database
    foreach ($_SESSION['cart'] as $item) {
        $key = $item['product_id'].'_'.$item['size'];
        $pid = (int)$item['product_id'];
        $size = mysqli_real_escape_string($conn, $item['size']);
        $qty = (int)$item['quantity'];

        if (isset($db_cart[$key])) {
            mysqli_query($conn, "UPDATE cart SET quantity = quantity + $qty 
                                 WHERE user_id='$user_id' AND product_id='$pid' AND size='$size'");
        } else {
            mysqli_query($conn, "INSERT INTO cart (user_id, product_id, size, quantity) 
                                 VALUES ('$user_id', '$pid', '$size', $qty)");
        }
    }

    // Update session cart dengan data terbaru dari database
    $_SESSION['cart'] = [];
    $result = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id'");
    while ($row = mysqli_fetch_assoc($result)) {
        $_SESSION['cart'][] = $row;
    }
}

// Tambah produk ke cart
if (isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $size = $_POST['size'] ?? 'M';
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['product_id'] == $product_id && $item['size'] == $size) {
            $item['quantity'] += $quantity;
            $found = true;
            break;
        }
    }
    unset($item);

    if (!$found) {
        $_SESSION['cart'][] = [
            'product_id' => $product_id,
            'size' => $size,
            'quantity' => $quantity
        ];
    }

    if (isset($_SESSION['user_id'])) {
        $check = mysqli_query($conn, "SELECT * FROM cart WHERE user_id='$user_id' AND product_id='$product_id' AND size='$size'");
        if (mysqli_num_rows($check) > 0) {
            mysqli_query($conn, "UPDATE cart SET quantity = quantity + $quantity 
                                 WHERE user_id='$user_id' AND product_id='$product_id' AND size='$size'");
        } else {
            mysqli_query($conn, "INSERT INTO cart (user_id, product_id, size, quantity) 
                                 VALUES ('$user_id', '$product_id', '$size', $quantity)");
        }
    }

    header("Location: cart.php");
    exit;
}

// Aksi tambah / kurang / hapus
if (isset($_POST['action'])) {
    $index = $_POST['index'];

    if ($_POST['action'] == 'increase') {
        $_SESSION['cart'][$index]['quantity'] += 1;
    } elseif ($_POST['action'] == 'decrease') {
        if ($_SESSION['cart'][$index]['quantity'] > 1) {
            $_SESSION['cart'][$index]['quantity'] -= 1;
        } else {
            unset($_SESSION['cart'][$index]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }
    } elseif ($_POST['action'] == 'remove') {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }

    // Update database hanya jika user login
    if (isset($_SESSION['user_id'])) {
        // Hapus seluruh cart user
        mysqli_query($conn, "DELETE FROM cart WHERE user_id='$user_id'");
        // Insert ulang dari session cart
        foreach ($_SESSION['cart'] as $item) {
            $pid = (int)$item['product_id'];
            $size = mysqli_real_escape_string($conn, $item['size']);
            $qty = (int)$item['quantity'];
            mysqli_query($conn, "INSERT INTO cart (user_id, product_id, size, quantity) 
                                 VALUES ('$user_id', '$pid', '$size', '$qty')");
        }
    }

    header("Location: cart.php");
    exit;
}

// Ambil detail produk dari database + hitung total
$cart_items = [];
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $pid = (int)$item['product_id'];
    $qty = (int)$item['quantity'];
    $size = $item['size'];
    $p = mysqli_query($conn, "SELECT * FROM products WHERE id = '$pid'");
    $product = mysqli_fetch_assoc($p);
    if ($product) {
        $product['quantity'] = $qty;
        $product['size'] = $size;
        $product['subtotal'] = $product['price'] * $qty;
        $cart_items[] = $product;
        $total += $product['subtotal'];
    }
}

$checkout_link = isset($_SESSION['user_id']) ? 'dashboard.php' : 'login.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>WearWell - Cart</title>
<link rel="icon" href="img/logo.png">
<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
<link rel="stylesheet" href="style.css">
<style>
table { width:100%; border-collapse: collapse; margin-top:20px; }
th, td { border:1px solid #ccc; padding:10px; text-align:center; }
img { width:80px; }
.btn { padding:5px 10px; margin:0 2px; cursor:pointer; }
#cart-total { margin-top:20px; text-align:right; }
</style>
</head>
<body>

<section id="header">
    <div class="nav-left"><p class="logo-text">WearWell</p></div>
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
        <a class="active" href="cart.php" id="lg-bag"><i class="far fa-shopping-bag"></i></a>
        <a href="login.php" id="lg-user"><i class="far fa-user"></i></a>
    </div>
</section>

<section id="page-header" class="about-header">
    <h2>#Cart</h2>
</section>

<section id="cart" class="section-p1">
    <table>
        <thead>
            <tr>
                <td>Gambar</td>
                <td>Produk</td>
                <td>Size</td>
                <td>Harga</td>
                <td>Jumlah</td>
                <td>Subtotal</td>
                <td>Aksi</td>
            </tr>
        </thead>
        <tbody>
        <?php if(!empty($cart_items)): ?>
            <?php foreach($cart_items as $index => $item): ?>
                <tr>
                    <td>
                        <img src="<?= (strpos($item['image'], 'http') === 0 ? htmlspecialchars($item['image']) : 'uploads/'.htmlspecialchars($item['image'])) ?>" 
                             alt="<?= htmlspecialchars($item['name']) ?>">
                    </td>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= htmlspecialchars($item['size']) ?></td>
                    <td>Rp <?= number_format($item['price'],0,',','.') ?></td>
                    <td>
                        <?= $item['quantity'] ?>
                        <form style="display:inline;" method="post">
                            <input type="hidden" name="index" value="<?= $index ?>">
                            <button type="submit" name="action" value="increase" class="btn">+</button>
                            <button type="submit" name="action" value="decrease" class="btn">-</button>
                        </form>
                    </td>
                    <td>Rp <?= number_format($item['subtotal'],0,',','.') ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="index" value="<?= $index ?>">
                            <button type="submit" name="action" value="remove" class="btn">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="5"><strong>Total</strong></td>
                <td colspan="2"><strong>Rp <?= number_format($total,0,',','.') ?></strong></td>
            </tr>
        <?php else: ?>
            <tr>
                <td colspan="7" style="text-align:center;">Keranjang kosong</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <div id="cart-total">
        <a href="shop.php" class="btn">Lanjut Belanja</a>
        <?php if(!empty($cart_items)): ?>
        <a href="login.php" class="btn">Checkout</a>
        <?php endif; ?>
    </div>
</section>

</body>
</html>
