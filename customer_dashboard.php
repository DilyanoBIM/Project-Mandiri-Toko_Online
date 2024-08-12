<?php
session_start();
require_once 'includes/config.php';
// Periksa apakah pengguna sudah login dan memiliki peran customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    // Jika tidak, arahkan kembali ke halaman login
    header("Location: login.php");
    exit();
}
$cart_count = 0;

// Periksa apakah user_id tersedia dalam session
if (isset($_SESSION['user_id'])) {
    // Query untuk menghitung jumlah item di keranjang berdasarkan user_id
    $query_cart_count = "SELECT COUNT(*) AS cart_count FROM cart WHERE user_id = :user_id";
    $stmt_cart_count = $pdo->prepare($query_cart_count);
    $stmt_cart_count->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt_cart_count->execute();
    $cart_count = $stmt_cart_count->fetchColumn();
}
// Ambil informasi pengguna dari sesi
$user_id = $_SESSION['user_id'];

// Sertakan file konfigurasi database


// Query untuk mendapatkan informasi pengguna dari database
$query = "SELECT * FROM users WHERE user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Tentukan judul dashboard sesuai dengan peran pengguna
$dashboard_title = '';
if ($user_id == 1) {
    $dashboard_title = 'Dashboard for Customer 1';
} elseif ($user_id == 2) {
    $dashboard_title = 'Dashboard for Customer 2';
} else {
    $dashboard_title = 'General Customer Dashboard';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
    body {
        background-color: #f8f9fa;
        font-family: 'Roboto', sans-serif;
        color: #333;
    }

    .container {
        max-width: 920px;
        margin: 50px auto;
        padding: 20px;
        border-radius: 8px;
        background-color: #fff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .btn-custom {
        background-color: #007bff;
        border-color: #007bff;
        color: #fff;
        transition: background-color 0.3s ease;
    }

    .btn-custom:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }

    h2 {
        margin-bottom: 30px;
        color: #007bff;
        text-align: center;
        font-size: 36px;
        letter-spacing: 1px;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
    }

    h3 {
        margin-bottom: 20px;
        color: #555;
        font-size: 24px;
        text-align: center;
    }

    p {
        margin-bottom: 20px;
        line-height: 1.6;
    }

    .content-box {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .content-box:hover {
        transform: scale(1.05);
    }
    </style>
</head>

<body>
    <nav class="container navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Damoza Toys</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="customer_dashboard.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="product_damoza.php" class="nav-link">Product Damoza</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="badge bg-danger" id="cart-count"><?php echo $cart_count; ?></span>

                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="btn btn-danger">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


    <div class="container">

        <h2>Welcome, <?php echo $user['username']; ?> (Customer)</h2>

        <div class="content-box">
            <h3><?php echo $dashboard_title; ?></h3>
            <?php if ($dashboard_title === 'Dashboard for Customer 1'): ?>
            <p>Content specific to Customer 1...</p>
            <?php elseif ($dashboard_title === 'Dashboard for Customer 2'): ?>
            <p>Content specific to Customer 2...</p>
            <?php else: ?>
            <p>General content for other customers...</p>
            <?php endif; ?>
        </div>
        <br>

    </div>

    <!-- Bootstrap JS dan dependencies -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>