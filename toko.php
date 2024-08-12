<?php
// Sertakan file konfigurasi database
require_once 'includes/config.php';

// Query untuk mengambil daftar pelanggan dengan role customer
$query_customers = "SELECT * FROM users WHERE role = 'customer'";
$stmt_customers = $pdo->query($query_customers);
$customers = $stmt_customers->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko</title>
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

        .table th,
        .table td {
            text-align: center;
            vertical-align: middle;
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
    </style>
</head>

<body>
    <div class="container">
        <h2 class="page-title">Toko</h2>
        <a href="admin_dashboard.php" class="btn btn-secondary btn-back">Kembali ke Dashboard</a>
        <table class="table table-bordered mt-3">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Nama Customer</th>
                    <th scope="col">Jumlah Order</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php foreach ($customers as $customer) : ?>
                    <?php
                    // Query untuk menghitung jumlah order berdasarkan user_id
                    $query_order_count = "SELECT COUNT(*) as order_count FROM orders WHERE user_id = :user_id";
                    $stmt_order_count = $pdo->prepare($query_order_count);
                    $stmt_order_count->bindParam(':user_id', $customer['user_id'], PDO::PARAM_INT);
                    $stmt_order_count->execute();
                    $order_count = $stmt_order_count->fetchColumn();
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo $customer['username']; ?></td>
                        <td><?php echo $order_count; ?></td>
                        <td><a href="customer_orders.php?username=<?php echo $customer['username']; ?>" class="btn btn-primary btn-custom">Lihat Order</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS dan dependencies -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>

