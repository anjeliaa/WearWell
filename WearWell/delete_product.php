<?php
include 'backend/config.php';

// Pastikan hanya seller
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

if(isset($_GET['id'])){
    $id = intval($_GET['id']);

    // Ambil nama gambar dulu
    $res = $conn->query("SELECT image FROM products WHERE id=$id AND seller_id=$seller_id");
    if($res->num_rows){
        $img = $res->fetch_assoc()['image'];
        if($img && file_exists('uploads/'.$img)){
            unlink('uploads/'.$img); // hapus file gambar
        }

        // Hapus produk
        $conn->query("DELETE FROM products WHERE id=$id AND seller_id=$seller_id");
    }
}

header("Location: manage_products.php");
exit;
?>
