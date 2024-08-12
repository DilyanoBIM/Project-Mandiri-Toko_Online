<?php
// Sertakan file konfigurasi database
require_once 'includes/config.php';

// Mulai sesi
session_start();

// Tangkap data yang dikirimkan dari formulir Order
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $user_id = $_SESSION['user_id'];

    // Periksa apakah produk sudah ada di keranjang pengguna
    $query_check_cart = "SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id";
    $stmt_check_cart = $pdo->prepare($query_check_cart);
    $stmt_check_cart->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_check_cart->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt_check_cart->execute();
    $cart_item = $stmt_check_cart->fetch(PDO::FETCH_ASSOC);

    if ($cart_item) {
        // Produk sudah ada di keranjang, lakukan update jumlah barang (quantity)
        $new_quantity = $cart_item['quantity'] + $quantity;
        $query_update_cart = "UPDATE cart SET quantity = :new_quantity WHERE user_id = :user_id AND product_id = :product_id";
        $stmt_update_cart = $pdo->prepare($query_update_cart);
        $stmt_update_cart->bindParam(':new_quantity', $new_quantity, PDO::PARAM_INT);
        $stmt_update_cart->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_update_cart->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt_update_cart->execute();
    } else {
        // Produk belum ada di keranjang, lakukan insert data produk ke dalam keranjang
        $query_insert_cart = "INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)";
        $stmt_insert_cart = $pdo->prepare($query_insert_cart);
        $stmt_insert_cart->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_insert_cart->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt_insert_cart->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt_insert_cart->execute();
    }

    // Redirect kembali ke halaman product_damoza setelah berhasil menambahkan ke keranjang
    header("Location: product_damoza.php");
    exit();
}
?>
