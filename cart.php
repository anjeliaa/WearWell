<?php
session_start();
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
<style>
/* ====== Polesan visual tanpa ubah layout ====== */
:root{
  --bg:#f6f7f8;
  --card:#ffffff;
  --text:#0f172a;
  --muted:#64748b;
  --border:#e5e7eb;
  --brand:#ef4444; /* merah lembut ke tombol */
  --brand-2:#111827; /* header #Cart bg gelap */
  --radius:12px;
  --shadow:0 6px 24px rgba(0,0,0,.06);
  --shadow-sm:0 2px 10px rgba(0,0,0,.04);
  --dur:.35s;
  --ease:cubic-bezier(.22,.61,.36,1);
}
@media (prefers-reduced-motion: reduce){
  *{animation-duration: .001ms !important; transition-duration: .001ms !important;}
}

body{background:var(--bg); color:var(--text);}
#page-header.about-header{
  background:linear-gradient(180deg, rgba(17,24,39,.75), rgba(17,24,39,.55)), url('img/banner.png') center/cover no-repeat;
  color:#fff; border-radius: var(--radius);
  margin: 16px auto; padding: 32px 16px; box-shadow: var(--shadow-sm);
}
#page-header h2{letter-spacing:.5px}

/* Kontainer tabel tetap sama, tapi dibungkus shadow & radius */
.section-p1{background:var(--card); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow); padding:18px;}
/* Wrapper responsif untuk tabel */
.table-wrap{overflow-x:auto; border-radius:10px}

/* Tabel */
table { width:100%; border-collapse: collapse; margin-top:8px; font-size:15px; }
thead td{
  font-weight:700; background:#fafafa; position:sticky; top:0; z-index:1;
  color:#111; border-bottom:1px solid var(--border);
}
th, td { border-bottom:1px solid var(--border); padding:12px 10px; text-align:center; }
tbody tr:nth-child(odd){ background:#fff; }
tbody tr:nth-child(even){ background:#fdfdfd; }
tbody tr{ transition: background var(--dur) var(--ease), transform var(--dur) var(--ease); }
tbody tr:hover{ background:#f8fafc; transform:translateY(-1px); }

/* Gambar produk */
img { width:80px; height:auto; border-radius:10px; box-shadow: var(--shadow-sm); transition: transform var(--dur) var(--ease); }
td img:hover{ transform: scale(1.03); }

/* Tombol */
.btn {
  padding:8px 12px; margin:0 2px; cursor:pointer; border-radius:10px; border:1px solid var(--border);
  background:#fff; color:#111; transition: transform .18s var(--ease), box-shadow .18s var(--ease), background .18s, color .18s;
  box-shadow: 0 1px 2px rgba(0,0,0,.04);
}
.btn:hover{ transform: translateY(-1px); box-shadow: 0 6px 16px rgba(0,0,0,.08); }
.btn:active{ transform: translateY(0); box-shadow: 0 2px 8px rgba(0,0,0,.06); }

/* Spesifik untuk tombol tambah/kurang → rasa “control” */
button[name="action"][value="increase"],
button[name="action"][value="decrease"]{
  width:36px; height:32px; line-height: 32px; padding:0;
  display:inline-flex; align-items:center; justify-content:center;
  font-weight:700;
}

/* Tombol Hapus jadi ghost-danger (tanpa ubah markup) */
button[name="action"][value="remove"]{
  background:#fff; color:#b91c1c; border-color:#fecaca;
}
button[name="action"][value="remove"]:hover{
  background:#fee2e2; color:#991b1b; box-shadow: 0 6px 16px rgba(239,68,68,.15);
}

/* CTA bawah */
#cart-total { margin-top:20px; text-align:right; display:flex; gap:8px; justify-content:flex-end; flex-wrap:wrap; }
#cart-total .btn{
  border-color: transparent;
  background: var(--brand); color:#fff; font-weight:600;
}
#cart-total .btn:hover{ filter: brightness(1.05); }
#cart-total .btn:first-child{ background:#111; }

/* Angka total tebal */
tfoot td, tr td strong{ font-weight:800; }

/* Animasi masuk halus untuk isi tabel */
.row-reveal{ opacity:0; transform: translateY(8px); }
.row-reveal.show{ opacity:1; transform: translateY(0); transition: opacity .5s var(--ease), transform .5s var(--ease); }
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
