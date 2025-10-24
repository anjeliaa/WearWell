<?php
session_start();
include 'backend/config.php';

// Pastikan hanya seller yang bisa masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

// Helper kecil: cari path gambar yang ada (uploads/ atau img/)
function product_image_src($filename) {
    if (!$filename) return null;
    $candidates = ['uploads/' . $filename,'img/' . $filename,$filename];
    foreach ($candidates as $p) if (is_file($p)) return $p;
    return null;
}

// ==================== DASHBOARD METRICS (SEMUA) ====================
$total_products = $conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()['total'] ?? 0;
$total_orders   = $conn->query("SELECT COUNT(*) AS total FROM order_items")->fetch_assoc()['total'] ?? 0;
$orders_pending = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status='Pending'")->fetch_assoc()['total'] ?? 0;

$revenue_today = $conn->query("
    SELECT COALESCE(SUM(oi.quantity * oi.price),0) AS total
    FROM order_items oi
    JOIN orders o ON oi.id_orders = o.id_orders
    WHERE DATE(o.created_at) = CURDATE()
")->fetch_assoc()['total'] ?? 0;

// ==================== DATA TABEL (SEMUA) ====================
$products = $conn->query("
    SELECT p.*, u.username AS seller_name
    FROM products p
    JOIN users u ON p.seller_id = u.id
");

$orders = $conn->query("
    SELECT o.id_orders, u.username AS customer, oi.quantity, oi.price, o.status, p.name AS product_name
    FROM order_items oi
    JOIN orders   o ON oi.id_orders  = o.id_orders
    JOIN users    u ON o.id_users    = u.id
    JOIN products p ON oi.id_products = p.id
    ORDER BY o.id_orders DESC
");

// ==================== REPORTS (BY DEFAULT DIFILTER PER SELLER LOGIN) ====================
$sellerId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

// --- FAVORITES (tanpa filter seller, hanya Completed) ---
$favorites = $conn->query("
    SELECT p.id, p.name, SUM(oi.quantity) AS total_sold, SUM(oi.quantity * oi.price) AS revenue
    FROM order_items oi
    JOIN orders   o ON oi.id_orders  = o.id_orders
    JOIN products p ON oi.id_products = p.id
    WHERE o.status='Completed'
    GROUP BY p.id, p.name
    ORDER BY total_sold DESC
    LIMIT 10
");

// --- REVENUE 14 hari (tanpa filter seller, hanya Completed) ---
$rev = $conn->query("
    SELECT DATE(o.created_at) AS d, SUM(oi.quantity * oi.price) AS total
    FROM order_items oi
    JOIN orders   o ON oi.id_orders  = o.id_orders
    JOIN products p ON oi.id_products = p.id
    WHERE o.status='Completed' AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
    GROUP BY DATE(o.created_at)
    ORDER BY d
");

// Siapkan array untuk Chart.js
$rev_labels = []; $rev_data = [];
$period = new DatePeriod(new DateTime(date('Y-m-d', strtotime('-13 days'))), new DateInterval('P1D'), (new DateTime(date('Y-m-d')))->modify('+1 day'));
$rev_map = [];
while ($r = $rev->fetch_assoc()) $rev_map[$r['d']] = (float)$r['total'];
foreach ($period as $dt) { $d = $dt->format('Y-m-d'); $rev_labels[] = $d; $rev_data[] = $rev_map[$d] ?? 0.0; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Seller Dashboard | WearWell</title>
<link rel="stylesheet" href="style.css">
<style>
/* ====== ANIMASI & TRANSITION (tanpa ubah layout) ====== */
:root{
  --dur-fast:.25s; --dur:.45s; --easing:cubic-bezier(.22,.61,.36,1);
}
@media (prefers-reduced-motion: reduce) {
  * { animation-duration: .001ms !important; transition-duration: .001ms !important; }
}

/* base */
.hidden{ display:none !important; }
.navbar{display:flex;justify-content:space-between;align-items:center;padding:12px 16px;background:#111;color:#fff;}
.navbar a{color:#fff;margin:0 8px;text-decoration:none;padding:6px 10px;border-radius:6px;position:relative;transition:transform var(--dur-fast) var(--easing)}
.navbar a.active, .navbar a:hover{background:#222;}
/* underline animatif */
.navbar a::after{
  content:""; position:absolute; left:10px; right:10px; bottom:4px; height:2px; background:#22c55e;
  transform:scaleX(0); transform-origin:left; transition:transform var(--dur) var(--easing); border-radius:2px;
}
.navbar a.active::after, .navbar a:hover::after{ transform:scaleX(1); }

.container{max-width:1100px;margin:20px auto;padding:0 12px;}
.card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,.04);transition:box-shadow var(--dur) var(--easing), transform var(--dur) var(--easing)}
.card:hover{ box-shadow:0 10px 30px rgba(0,0,0,.08); transform:translateY(-2px); }

table{width:100%;border-collapse:collapse;margin-top:8px;border-radius:10px;overflow:hidden}
th,td{border-bottom:1px solid #eee;padding:10px;text-align:left;}
th{background:#fafafa;}
tbody tr{ transition: background-color var(--dur-fast) var(--easing); }
tbody tr:hover{ background:#fafafa; }

.btn{display:inline-block;padding:6px 10px;border-radius:6px;text-decoration:none;border:0;cursor:pointer;transition:transform var(--dur-fast) var(--easing), filter var(--dur-fast) var(--easing)}
.btn:active{ transform:translateY(1px) scale(.98); }
.btn-add{background:#16a34a;color:#fff}
.btn-edit{background:#2563eb;color:#fff}
.btn-delete{background:#dc2626;color:#fff}
.btn-update{background:#0ea5e9;color:#fff}
.btn:hover{ filter:brightness(1.05); }

.logo{font-weight:700;letter-spacing:.5px}
h3{margin-top:0}
canvas{background:#fff;border:1px solid #eee;border-radius:10px}

/* ====== Animasi masuk tab ====== */
@keyframes fadeUp { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
.tab-content.animate-in{ animation:fadeUp var(--dur) var(--easing); }
</style>
</head>
<body>

<div class="navbar">
    <div class="logo">WearWell</div>
    <div>
        <!-- penting: jangan reload halaman -->
        <a href="#" onclick="showTab('home'); return false;">Home</a>
        <a href="#" onclick="showTab('products'); return false;">Manage Product</a>
        <a href="#" onclick="showTab('orders'); return false;">Orders</a>
        <a href="#" onclick="showTab('reports'); return false;">Reports</a>
    </div>
    <div>
        <span><?= htmlspecialchars($_SESSION['username'] ?? 'Seller') ?></span>
        <a href="logout.php" class="btn btn-delete">Logout</a>
    </div>
</div>

<div class="container">
    <!-- Home -->
    <div id="home" class="tab-content">
        <div class="card">
            <h3>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Seller') ?>!</h3>
            <p>Total Products: <?= (int)$total_products ?></p>
            <p>Total Orders: <?= (int)$total_orders ?></p>
            <p>Orders Pending: <?= (int)$orders_pending ?></p>
            <p>Revenue Today: Rp<?= number_format((float)$revenue_today, 2, ',', '.') ?></p>
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
                    <td><?= (int)$p['id'] ?></td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td>Rp<?= number_format((float)$p['price'], 2, ',', '.') ?></td>
                    <td><?= (int)$p['stock'] ?></td>
                    <td><?= htmlspecialchars($p['seller_name']) ?></td>
                    <td>
                        <?php $src = product_image_src($p['image']); ?>
                        <?= $src ? '<img src="'.htmlspecialchars($src).'" alt="'.htmlspecialchars($p['name']).'" width="50" style="border-radius:8px;transition:transform .2s">' : '-' ?>
                    </td>
                    <td>
                        <a href="edit_product.php?id=<?= (int)$p['id'] ?>" class="btn btn-edit">Edit</a>
                        <a href="delete_product.php?id=<?= (int)$p['id'] ?>" class="btn btn-delete" onclick="return confirm('Hapus produk ini?')">Delete</a>
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
                    <td><?= (int)$o['id_orders'] ?></td>
                    <td><?= htmlspecialchars($o['customer']) ?></td>
                    <td><?= htmlspecialchars($o['product_name']) ?></td>
                    <td><?= (int)$o['quantity'] ?></td>
                    <td>Rp<?= number_format((float)$o['price'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($o['status']) ?></td>
                    <td>
                        <?php if($o['status'] !== 'Completed' && $o['status'] !== 'Canceled'): ?>
                            <form action="update_order.php" method="GET" style="display:flex;gap:6px;align-items:center;">
                                <input type="hidden" name="id_orders" value="<?= (int)$o['id_orders'] ?>">
                                <select name="status">
                                    <?php
                                    $statuses = ['Pending','Processing','Shipped','Completed','Canceled'];
                                    foreach ($statuses as $st) {
                                        $sel = ($o['status']===$st) ? 'selected' : '';
                                        echo "<option value=\"{$st}\" {$sel}>{$st}</option>";
                                    }
                                    ?>
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

            <h4 style="margin-top:10px;margin-bottom:8px;">Barang Favorit (Top 10)</h4>
            <table>
                <tr>
                    <th>#</th><th>Product</th><th>Total Terjual</th><th>Pendapatan</th>
                </tr>
                <?php 
                $i = 1; 
                if ($favorites && $favorites->num_rows > 0): 
                    while($f = $favorites->fetch_assoc()): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($f['name']) ?></td>
                        <td><?= (int)$f['total_sold'] ?></td>
                        <td>Rp<?= number_format((float)($f['revenue'] ?? 0), 2, ',', '.') ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="4" style="text-align:center;">Belum ada penjualan.</td></tr>
                <?php endif; ?>
            </table>

            <h4 style="margin-top:20px;margin-bottom:8px;">Chart Pendapatan (14 Hari Terakhir)</h4>
            <div style="height:320px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabId){
    // sembunyikan semua
    document.querySelectorAll('.tab-content').forEach(el => {
        el.classList.add('hidden');
        el.classList.remove('animate-in');
    });

    // tampilkan tab & pastikan animasi kepicu
    const tab = document.getElementById(tabId);
    if(tab){
        tab.classList.remove('hidden');
        void tab.offsetHeight;              // paksa reflow supaya animasi jalan
        tab.classList.add('animate-in');
    }

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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// Data dari PHP
const revLabels = <?= json_encode($rev_labels, JSON_UNESCAPED_UNICODE) ?>;
const revData   = <?= json_encode($rev_data,   JSON_UNESCAPED_UNICODE) ?>;

function formatRupiah(n){ return 'Rp' + Number(n || 0).toLocaleString('id-ID', {minimumFractionDigits: 0}); }

const ctx = document.getElementById('revenueChart');
if (ctx) {
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: revLabels,
      datasets: [{
        label: 'Pendapatan Harian',
        data: revData,
        tension: 0.3,
        fill: false,
        pointRadius: 0,        // biar garis “draw-in” lebih halus
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animations: {
        y: { duration: 900, easing: 'easeOutQuart',
             from: (c)=> (c.type==='data' && c.mode==='default' ? 0 : undefined) },
        tension: { duration: 600, easing: 'linear', from: 0.6, to: 0.3 }
      },
      interaction: { intersect:false, mode:'index' },
      scales: {
        y: {
          ticks: { callback: (v)=> 'Rp' + Number(v).toLocaleString('id-ID') }
        }
      },
      plugins: {
        tooltip: {
          callbacks: { label: (c)=> formatRupiah(c.parsed.y || 0) }
        },
        legend: { display: true }
      }
    }
  });
}
</script>

</body>
</html>
