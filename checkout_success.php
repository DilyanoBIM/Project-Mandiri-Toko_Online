<?php
session_start();
require_once 'includes/config.php';
require_once 'fpdf/fpdf.php'; // Sesuaikan path dengan lokasi FPDF

// Cek apakah ada user yang sedang login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil data user yang sedang login
$user_id = $_SESSION['user_id'];

// Query untuk mengambil data order terbaru berdasarkan user_id
$query_order = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY order_id DESC LIMIT 1";
$stmt_order = $pdo->prepare($query_order);
$stmt_order->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_order->execute();
$order = $stmt_order->fetch(PDO::FETCH_ASSOC);

// Query untuk mengambil data detail order berdasarkan order_id, termasuk subtotal
$query_order_details = "SELECT order_details.*, products.product_name, (products.price * 0.9) AS discounted_price, (order_details.quantity * (products.price * 0.9)) AS subtotal
                        FROM order_details
                        INNER JOIN products ON order_details.product_id = products.product_id
                        WHERE order_id = :order_id";
$stmt_order_details = $pdo->prepare($query_order_details);
$stmt_order_details->bindParam(':order_id', $order['order_id'], PDO::PARAM_INT);
$stmt_order_details->execute();
$order_details = $stmt_order_details->fetchAll(PDO::FETCH_ASSOC);

// Create new PDF document
$pdf = new FPDF();
$pdf->AddPage();

// Set font
$pdf->SetFont('Arial', 'B', 12);

// Set title
$pdf->Cell(0, 10, 'Order Details', 0, 1, 'C');

// Line break
$pdf->Ln(10);

// Set content
$pdf->SetFont('Arial', '', 10);

$pdf->Cell(30, 10, 'Product Name', 1, 0, 'C');
$pdf->Cell(30, 10, 'Quantity', 1, 0, 'C');
$pdf->Cell(30, 10, 'Price', 1, 0, 'C');
$pdf->Cell(30, 10, 'Subtotal', 1, 1, 'C');

foreach ($order_details as $order_item) {
    $pdf->Cell(30, 10, $order_item['product_name'], 1, 0, 'L');
    $pdf->Cell(30, 10, $order_item['quantity'], 1, 0, 'C');
    $pdf->Cell(30, 10, 'Rp' . number_format($order_item['discounted_price'], 2), 1, 0, 'R');
    $pdf->Cell(30, 10, 'Rp' . number_format($order_item['subtotal'], 2), 1, 1, 'R');
}

$pdf->Cell(120, 10, '', 0, 1); // Line break

$pdf->Cell(120, 10, 'Total Amount: Rp' . number_format($order['total_amount'], 2), 0, 1);

// Output PDF as download
$pdf->Output('Order_Details.pdf', 'D');

// Exit script
exit();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Success</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2, h3, h4 {
            color: black;
            margin-bottom: 20px;
            text-align: left;
        }

        table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: #fff;
        }

        tbody tr:hover {
            background-color: #f1f1f1;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

    </style>
<body>
    <div class="container mt-5">
        <h2>Checkout Success</h2>
        <p>Your order has been successfully processed.</p>

        <h3>Order Details</h3>
        <table class="table mt-3">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php foreach ($order_details as $order_item) : ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo $order_item['product_name']; ?></td>
                        <td><?php echo $order_item['quantity']; ?></td>
                        <td>Rp<?php echo number_format($order_item['discounted_price'], 2); ?></td>
                        <td>Rp<?php echo number_format($order_item['subtotal'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h4>Total Amount: Rp<?php echo number_format($order['total_amount'], 2); ?></h4>
        <a href="customer_dashboard.php" class="btn btn-primary">Back to Dashboard</a> <!-- Tambahkan tombol ke halaman dashboard -->
    </div>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
