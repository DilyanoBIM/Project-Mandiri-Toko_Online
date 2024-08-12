<?php
// Sertakan file konfigurasi database
require_once 'includes/config.php';

// Tangkap nama pengguna (username) dari parameter URL
if (isset($_GET['username'])) {
    $username = $_GET['username'];

    // Query untuk mengambil data pesanan berdasarkan nama pengguna
    $query_orders = "SELECT order_id, order_date FROM orders WHERE user_id = (SELECT user_id FROM users WHERE username = :username)";
    $stmt_orders = $pdo->prepare($query_orders);
    $stmt_orders->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt_orders->execute();
    $orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Redirect ke halaman toko jika parameter username tidak ditemukan
    header("Location: toko.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Orders</title>
    <!-- Bootstrap CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 800px;
            margin-top: 50px;
        }

        .btn-custom {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 8px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 8px;
            transition-duration: 0.4s;
        }

        .btn-custom:hover {
            background-color: #45a049;
        }

        .btn-secondary-custom {
            background-color: #337ab7;
            border-color: #2e6da4;
        }

        .btn-secondary-custom:hover {
            background-color: #286090;
            border-color: #204d74;
        }

        .btn-back {
            margin-top: 20px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 20px;
        }

        .order-item {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .order-item .order-date {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .order-item .btn-view {
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2 class="page-title">Orders for <?php echo $username; ?></h2>
        <a href="toko.php" class="btn btn-secondary btn-back">Back to Customer List</a>
        <div class="mt-3">
            <!-- Tampilkan daftar pesanan -->
            <?php foreach ($orders as $order) : ?>
                <div class="order-item">
                    <div class="order-date">Date: <?php echo $order['order_date']; ?></div>
                    <a href="order_details.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-primary btn-custom btn-view">View Order</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Bootstrap JS dan dependencies -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>

