<?php
ob_start();
include 'backend/config.php';

// ===== Helper =====
function clamp_qty($n, $min = 1, $max = 9999) {
    $n = (int)$n;
    if ($n < $min) $n = $min;
    if ($n > $max) $n = $max;
    return $n;
}
function esc($conn, $str) { return mysqli_real_escape_string($conn, (string)$str); }
function load_cart_from_db($conn, $user_id) {
    $_SESSION['cart'] = [];
    $user_id = (int)$user_id;
    $res = mysqli_query($conn, "SELECT product_id, size, quantity FROM cart WHERE user_id = {$user_id}");
    while ($row = mysqli_fetch_assoc($res)) {
        $_SESSION['cart'][] = [
            'product_id' => (int)$row['product_id'],
            'size'       => (string)$row['size'],
            'quantity'   => (int)$row['quantity'],
        ];
    }
}

// Buat cart session jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Source of truth: login → tarik dari DB
if (isset($_SESSION['user_id'])) {
    load_cart_from_db($conn, $_SESSION['user_id']);
}

// Add to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $size       = (string)($_POST['size'] ?? 'M');
    $quantity   = clamp_qty($_POST['quantity'] ?? 1);

    if (isset($_SESSION['user_id'])) {
        $user_id = (int)$_SESSION['user_id'];

        $stmt = mysqli_prepare($conn, "SELECT quantity FROM cart WHERE user_id=? AND product_id=? AND size=?");
        mysqli_stmt_bind_param($stmt, "iis", $user_id, $product_id, $size);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $exists = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_free_result($stmt);
        mysqli_stmt_close($stmt);

        if ($exists) {
            $stmt = mysqli_prepare($conn,
                "UPDATE cart SET quantity = LEAST(quantity + ?, 9999)
                 WHERE user_id=? AND product_id=? AND size=?"
            );
            mysqli_stmt_bind_param($stmt, "iiis", $quantity, $user_id, $product_id, $size);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            $stmt = mysqli_prepare($conn,
                "INSERT INTO cart (user_id, product_id, size, quantity) VALUES (?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, "iisi", $user_id, $product_id, $size, $quantity);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        load_cart_from_db($conn, $user_id);
    } else {
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $product_id && $item['size'] === $size) {
                $item['quantity'] = clamp_qty($item['quantity'] + $quantity);
                $found = true;
                break;
            }
        }
        unset($item);

        if (!$found) {
            $_SESSION['cart'][] = [
                'product_id' => $product_id,
                'size'       => $size,
                'quantity'   => $quantity
            ];
        }
    }

    header("Location: cart.php");
    exit;
}

// Increase / decrease / remove
if (isset($_POST['action'])) {
    $index = (int)($_POST['index'] ?? -1);

    if (isset($_SESSION['cart'][$index])) {
        $pid  = (int)$_SESSION['cart'][$index]['product_id'];
        $size = (string)$_SESSION['cart'][$index]['size'];

        if ($_POST['action'] === 'increase') {
            $_SESSION['cart'][$index]['quantity'] = clamp_qty($_SESSION['cart'][$index]['quantity'] + 1);

            if (isset($_SESSION['user_id'])) {
                $user_id = (int)$_SESSION['user_id'];
                $newq = $_SESSION['cart'][$index]['quantity'];
                $stmt = mysqli_prepare($conn,
                    "UPDATE cart SET quantity=? WHERE user_id=? AND product_id=? AND size=?"
                );
                mysqli_stmt_bind_param($stmt, "iiis", $newq, $user_id, $pid, $size);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }

        } elseif ($_POST['action'] === 'decrease') {
            $newq = clamp_qty($_SESSION['cart'][$index]['quantity'] - 1);
            if ($newq < 1) {
                if (isset($_SESSION['user_id'])) {
                    $user_id = (int)$_SESSION['user_id'];
                    $stmt = mysqli_prepare($conn,
                        "DELETE FROM cart WHERE user_id=? AND product_id=? AND size=?"
                    );
                    mysqli_stmt_bind_param($stmt, "iis", $user_id, $pid, $size);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
                unset($_SESSION['cart'][$index]);
                $_SESSION['cart'] = array_values($_SESSION['cart']);
            } else {
                $_SESSION['cart'][$index]['quantity'] = $newq;
                if (isset($_SESSION['user_id'])) {
                    $user_id = (int)$_SESSION['user_id'];
                    $stmt = mysqli_prepare($conn,
                        "UPDATE cart SET quantity=? WHERE user_id=? AND product_id=? AND size=?"
                    );
                    mysqli_stmt_bind_param($stmt, "iiis", $newq, $user_id, $pid, $size);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }

        } elseif ($_POST['action'] === 'remove') {
            if (isset($_SESSION['user_id'])) {
                $user_id = (int)$_SESSION['user_id'];
                $stmt = mysqli_prepare($conn,
                    "DELETE FROM cart WHERE user_id=? AND product_id=? AND size=?"
                );
                mysqli_stmt_bind_param($stmt, "iis", $user_id, $pid, $size);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            unset($_SESSION['cart'][$index]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }
    }

    header("Location: cart.php");
    exit;
}

// Ambil detail produk + total
$cart_items = [];
$total = 0;
foreach ($_SESSION['cart'] as $it) {
    $pid = (int)$it['product_id'];
    $qty = clamp_qty($it['quantity']);
    $size = esc($conn, $it['size']);
    $p = mysqli_query($conn, "SELECT * FROM products WHERE id = {$pid}");
    $product = mysqli_fetch_assoc($p);
    if ($product) {
        $product['quantity'] = $qty;
        $product['size']     = $size;
        $product['subtotal'] = ((float)$product['price']) * $qty;
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
    <div class="table-wrap">
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
                  <tr class="row-reveal">
                      <td>
                          <img src="<?= (strpos($item['image'], 'http') === 0 ? htmlspecialchars($item['image']) : 'uploads/'.htmlspecialchars($item['image'])) ?>" 
                               alt="<?= htmlspecialchars($item['name']) ?>">
                      </td>
                      <td style="text-align:left">
                        <div style="font-weight:700"><?= htmlspecialchars($item['name']) ?></div>
                        <div style="font-size:12px;color:var(--muted)">Kode: #<?= (int)$item['id'] ?></div>
                      </td>
                      <td><span style="display:inline-block;background:#f1f5f9;border:1px solid var(--border);padding:4px 10px;border-radius:999px;font-weight:600"><?= htmlspecialchars($item['size']) ?></span></td>
                      <td>Rp <?= number_format($item['price'],0,',','.') ?></td>
                      <td>
                          <div style="display:inline-flex;gap:6px;align-items:center">
                            <form style="display:inline;" method="post">
                                <input type="hidden" name="index" value="<?= (int)$index ?>">
                                <button type="submit" name="action" value="decrease" class="btn" aria-label="Kurangi">-</button>
                            </form>
                            <div style="min-width:48px;padding:6px 10px;border:1px solid var(--border);border-radius:10px;background:#fff;font-weight:700">
                              <?= (int)$item['quantity'] ?>
                            </div>
                            <form style="display:inline;" method="post">
                                <input type="hidden" name="index" value="<?= (int)$index ?>">
                                <button type="submit" name="action" value="increase" class="btn" aria-label="Tambah">+</button>
                            </form>
                          </div>
                      </td>
                      <td>Rp <?= number_format($item['subtotal'],0,',','.') ?></td>
                      <td>
                          <form method="post">
                              <input type="hidden" name="index" value="<?= (int)$index ?>">
                              <button type="submit" name="action" value="remove" class="btn">Hapus</button>
                          </form>
                      </td>
                  </tr>
              <?php endforeach; ?>
              <tr>
                  <td colspan="5" style="text-align:right"><strong>Total</strong></td>
                  <td colspan="2"><strong>Rp <?= number_format($total,0,',','.') ?></strong></td>
              </tr>
          <?php else: ?>
              <tr>
                  <td colspan="7" style="text-align:center;">Keranjang kosong</td>
              </tr>
          <?php endif; ?>
          </tbody>
      </table>
    </div>

    <div id="cart-total">
        <a href="shop.php" class="btn">Lanjut Belanja</a>
        <?php if(!empty($cart_items)): ?>
        <a href="<?= htmlspecialchars($checkout_link) ?>" class="btn">Checkout</a>
        <?php endif; ?>
    </div>
</section>

<footer class="section-p1">
    <div class="col">
        <img class="logo" src="img/logo.png" alt="" height="50">
        <h4>Contact</h4>
        <p><strong>Address:</strong> Mayor Salim Batu Bara Street, Kota Bengkulu</p>
        <p><strong>Phone:</strong> +62895422212679</p>
        <p><strong>Hours:</strong> 08.00–21.00, Sen–Sab</p>
        <div class="follow">
            <h4>Follow Us</h4>
            <div class="icon">
                <i class="fab fa-facebook"></i>
                <i class="fab fa-twitter"></i>
                <i class="fab fa-instagram"></i>
                <i class="fab fa-pinterest"></i>
                <i class="fab fa-youtube"></i>
            </div>
        </div>
    </div>

    <div class="col">
        <h4>About</h4>
        <a href="#">About Us</a>
        <a href="#">Delivery Information</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms & Conditions</a>
        <a href="#">Contact Us</a>
    </div>

    <div class="col">
        <h4>My Account</h4>
        <a href="login.php">Sign In</a>
        <a href="cart.php">View Cart</a>
        <a href="#">My Wishlist</a>
        <a href="#">Track My Order</a>
        <a href="#">Help</a>
    </div>

    <div class="col install">
        <h4>Install App</h4>
        <p>From App Store or Google Play</p>
        <div class="row">
            <img src="img/pay/app.jpg" alt="">
            <img src="img/pay/play.jpg" alt="">
        </div>
        <p>Secured Payment Gateways</p>
        <img src="img/pay/pay.png" alt="">
    </div>

    <div class="copyright">
        <p>&copy; Copyright by <span>WearWell</span> All Rights Reserved 2025, Indonesia, Kota Bengkulu.</p>
    </div>
</footer>

<script>
// Animasi reveal baris tabel (ringan, non-intrusif)
const rows = document.querySelectorAll('tr.row-reveal');
if ('IntersectionObserver' in window) {
  const io = new IntersectionObserver((entries)=>{
    entries.forEach(e=>{ if(e.isIntersecting){ e.target.classList.add('show'); io.unobserve(e.target); } });
  }, {threshold:.1});
  rows.forEach(r=> io.observe(r));
} else {
  rows.forEach(r=> r.classList.add('show'));
}
</script>

</body>
</html>
