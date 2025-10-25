<?php
session_start();
session_destroy();

// Redirect ke halaman home setelah logout
header("Location: index.php");
exit;
?>
