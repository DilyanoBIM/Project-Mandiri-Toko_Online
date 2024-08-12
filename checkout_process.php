<?php
session_start();
require_once 'includes/config.php';

// Cek apakah ada user yang sedang login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Query untuk mengecek apakah ada produk di keranjang pengguna
$query_cart_check = "SELECT COUNT(*) AS total_items FROM cart WHERE user_id = :user_id";
$stmt_cart_check = $pdo->prepare($query_cart_check);
$stmt_cart_check->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_cart_check->execute();
$cart_check = $stmt_cart_check->fetch(PDO::FETCH_ASSOC);

if ($cart_check['total_items'] == 0) {
    // Jika keranjang kosong, kembali ke halaman cart dengan pesan peringatan
    $_SESSION['message'] = "Your cart is empty. Please add products to your cart before checking out.";
    header("Location: cart.php");
    exit();
}

// Ambil total harga dari cart.php
$total_amount = $_POST['total_price'];

// Ambil metode pembayaran dari form (misalnya Anda menambahkannya di cart.php)
$payment_method = $_POST['payment_method'] ?? '';

// Insert data ke tabel orders
$query_order = "INSERT INTO orders (user_id, total_amount, payment_method) VALUES (:user_id, :total_amount, :payment_method)";
$stmt_order = $pdo->prepare($query_order);
$stmt_order->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_order->bindParam(':total_amount', $total_amount, PDO::PARAM_STR);
$stmt_order->bindParam(':payment_method', $payment_method, PDO::PARAM_STR);
$stmt_order->execute();
$order_id = $pdo->lastInsertId();

// Insert data ke tabel order_details
$query_cart_items = "SELECT product_id, quantity FROM cart WHERE user_id = :user_id";
$stmt_cart_items = $pdo->prepare($query_cart_items);
$stmt_cart_items->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_cart_items->execute();
$cart_items = $stmt_cart_items->fetchAll(PDO::FETCH_ASSOC);

foreach ($cart_items as $item) {
    $product_id = $item['product_id'];
    $quantity = $item['quantity'];

    // Query untuk mendapatkan harga produk
    $query_product = "SELECT price FROM products WHERE product_id = :product_id";
    $stmt_product = $pdo->prepare($query_product);
    $stmt_product->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt_product->execute();
    $product = $stmt_product->fetch(PDO::FETCH_ASSOC);

    $price = $product['price'] * 0.9; // Harga setelah diskon 10%
    $subtotal = $price * $quantity;

    $query_order_detail = "INSERT INTO order_details (order_id, product_id, quantity, price, subtotal) VALUES (:order_id, :product_id, :quantity, :price, :subtotal)";
    $stmt_order_detail = $pdo->prepare($query_order_detail);
    $stmt_order_detail->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt_order_detail->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt_order_detail->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $stmt_order_detail->bindParam(':price', $price, PDO::PARAM_STR);
    $stmt_order_detail->bindParam(':subtotal', $subtotal, PDO::PARAM_STR);
    $stmt_order_detail->execute();
}

// Hapus data keranjang setelah checkout
$query_delete_cart = "DELETE FROM cart WHERE user_id = :user_id";
$stmt_delete_cart = $pdo->prepare($query_delete_cart);
$stmt_delete_cart->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_delete_cart->execute();

header("Location: checkout_success.php");
exit();
?>
