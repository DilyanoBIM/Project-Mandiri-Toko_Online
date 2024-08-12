<?php
// Sertakan file konfigurasi database
require_once 'includes/config.php';

// Tangkap order_id dari parameter URL
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    // Query untuk mengambil detail pesanan (order_details) berdasarkan order_id
    $query_order_details = "SELECT order_details.product_id, products.product_name, order_details.quantity, order_details.price, products.image
                            FROM order_details
                            INNER JOIN products ON order_details.product_id = products.product_id
                            WHERE order_details.order_id = :order_id";
    $stmt_order_details = $pdo->prepare($query_order_details);
    $stmt_order_details->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt_order_details->execute();
    $order_details = $stmt_order_details->fetchAll(PDO::FETCH_ASSOC);

    // Query untuk mengambil total amount dari pesanan
    $query_total_amount = "SELECT total_amount FROM orders WHERE order_id = :order_id";
    $stmt_total_amount = $pdo->prepare($query_total_amount);
    $stmt_total_amount->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt_total_amount->execute();
    $total_amount = $stmt_total_amount->fetchColumn();
} else {
    // Redirect ke halaman customer_orders.php jika parameter order_id tidak ditemukan
    header("Location: customer_orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
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

        .btn-secondary {
            margin-top: 20px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 20px;
        }

        .table {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .table th {
            background-color: #007bff;
            color: #fff;
            border-color: #007bff;
        }

        .table th,
        .table td,
        .table h4 {
            font-size: 16px;
        }

        .table img {
            max-width: 100px;
            height: auto;
        }

        .table h4 {
            margin-top: 20px;
            color: #333;
            font-weight: bold;
        }

        .btn-back {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2 class="page-title">Order Details</h2>
        <table class="table mt-3">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Image</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php $total_price = 0; ?>
                <?php foreach ($order_details as $order_item) : ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo $order_item['product_name']; ?></td>
                        <td><?php echo $order_item['quantity']; ?></td>
                        <td>Rp<?php echo number_format($order_item['price'], 2); ?></td>
                        <td><img src="assets/images/<?php echo $order_item['image']; ?>" alt="Product Image"></td>
                        <td>Rp<?php echo number_format($order_item['price'] * $order_item['quantity'], 2); ?></td>
                    </tr>
                    <?php $total_price += $order_item['price'] * $order_item['quantity']; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h4 class="mt-3">Total Amount: Rp<?php echo number_format($total_amount, 2); ?></h4>
        <button onclick="window.history.back()" class="btn btn-secondary btn-back">Back to Orders</button>
    </div>

    <!-- Bootstrap JS dan dependencies -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>

