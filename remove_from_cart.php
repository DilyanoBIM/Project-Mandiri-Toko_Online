<?php
session_start();
require_once 'includes/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cart_id'])) {
    $cart_id = $_POST['cart_id'];
    $user_id = $_SESSION['user_id'];

    // Query untuk menghapus produk dari keranjang belanja
    $query_delete_cart = "DELETE FROM cart WHERE cart_id = :cart_id AND user_id = :user_id";
    $stmt_delete_cart = $pdo->prepare($query_delete_cart);
    $stmt_delete_cart->bindParam(':cart_id', $cart_id, PDO::PARAM_INT);
    $stmt_delete_cart->bindParam(':user_id', $user_id, PDO::PARAM_INT);

    if ($stmt_delete_cart->execute()) {
        header("Location: cart.php");
        exit();
    } else {
        echo "Gagal menghapus produk dari keranjang";
    }
} else {
    echo "Permintaan tidak valid";
}
?>
