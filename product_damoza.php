<?php
session_start();
require_once 'includes/config.php';

// Inisialisasi cart count dengan nilai 0
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

// Variabel untuk menyimpan query pencarian
$search_query = '';

// Tangkap data yang dikirimkan dari form search
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    // Buat query pencarian berdasarkan 'code'
    $search_query = " WHERE code LIKE :search";
}

// Set the number of items per page
$items_per_page = 8;

// Calculate the total number of pages
$stmt_total_items = $pdo->prepare("SELECT COUNT(*) FROM products" . $search_query);
if (isset($search)) {
    $stmt_total_items->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
}
$stmt_total_items->execute();
$total_items = $stmt_total_items->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Calculate the starting index of the current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_index = ($page - 1) * $items_per_page;

// Query untuk mengambil daftar produk dari database berdasarkan pencarian dan halaman
$query = "SELECT product_id, product_name, code, price, image FROM products" . $search_query . " LIMIT :start_index, :items_per_page";
$stmt = $pdo->prepare($query);
if (isset($search)) {
    $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
}
$stmt->bindValue(':start_index', $start_index, PDO::PARAM_INT);
$stmt->bindValue(':items_per_page', $items_per_page, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Pastikan user_id tersedia dalam session
    if (!isset($_SESSION['user_id'])) {
        echo "User session not found.";
        exit(); // Keluar dari script jika user session tidak tersedia
    }

    // Cek apakah produk sudah ada di keranjang
    $query_check_cart = "SELECT COUNT(*) AS cart_count FROM cart WHERE user_id = :user_id AND product_id = :product_id";
    $stmt_check_cart = $pdo->prepare($query_check_cart);
    $stmt_check_cart->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt_check_cart->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt_check_cart->execute();
    $cart_count = $stmt_check_cart->fetchColumn();

    if ($cart_count > 0) {
        // Produk sudah ada di keranjang, tambahkan jumlahnya
        $query_update_cart = "UPDATE cart SET quantity = quantity + :quantity WHERE user_id = :user_id AND product_id = :product_id";
        $stmt_update_cart = $pdo->prepare($query_update_cart);
        $stmt_update_cart->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt_update_cart->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt_update_cart->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt_update_cart->execute();
        $_SESSION['message'] = "Yeay product sudah dimasukkan ke dalam keranjang!";

        // Redirect kembali ke halaman product_damoza setelah berhasil memperbarui keranjang
        header("Location: product_damoza.php");
        exit();
    } else {
        // Produk belum ada di keranjang, tambahkan produk baru
        $query_insert_cart = "INSERT INTO cart (user_id, product_id, quantity, code) VALUES (:user_id, :product_id, :quantity, :code)";
        $stmt_insert_cart = $pdo->prepare($query_insert_cart);
        $stmt_insert_cart->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt_insert_cart->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt_insert_cart->bindParam(':quantity', $quantity, PDO::PARAM_INT);

        // Ambil code dari produk yang diorder
        $query_code = "SELECT code FROM products WHERE product_id = :product_id";
        $stmt_code = $pdo->prepare($query_code);
        $stmt_code->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt_code->execute();
        $code = $stmt_code->fetchColumn();
        $stmt_insert_cart->bindParam(':code', $code, PDO::PARAM_STR);

        if ($stmt_insert_cart->execute()) {
            $_SESSION['message'] = "Yeay product sudah dimasukkan ke dalam keranjang!";
        } else {
            $_SESSION['message'] = "There was an error adding the product to the cart.";
        }
    }

    // Redirect kembali ke halaman product_damoza untuk menghindari pengiriman ulang form
    header("Location: product_damoza.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Damoza</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Custom CSS -->
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


    <div class="container mt-5">
        <h2>Product Damoza</h2>
        <a href="customer_dashboard.php" class="btn btn-secondary">Back</a>
        <!-- Display confirmation message if exists -->
        <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success" id="message">
            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
        <?php endif; ?>
        <div class="row mb-3">
            <div class="col-md-6">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="GET" class="form-inline">
                    <label for="search" class="mr-2">Search by Code:</label>
                    <input type="text" id="search" name="search" class="form-control mr-sm-2" placeholder="Enter code">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
        </div>

        <div class="row">
            <?php foreach ($products as $product) : ?>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card">
                    <img src="assets/images/<?php echo $product['image']; ?>" alt="Product Image" class="card-img-top">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $product['product_name']; ?></h5>
                        <p class="card-text">Code: <?php echo $product['code']; ?></p>
                        <p class="card-text">Price: <?php echo 'Rp' . number_format($product['price'], 2); ?></p>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST"
                            onsubmit="return confirmOrder()">
                            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                            <input type="number" name="quantity" value="1" min="1" class="form-control mb-2" required>
                            <button type="submit" class="btn btn-primary">Order</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                    <a class="page-link"
                        href="?page=<?= max(1, $page - 1) ?><?= isset($search) ? '&search=' . urlencode($search) : '' ?>"
                        tabindex="-1">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                    <a class="page-link"
                        href="?page=<?= $i ?><?= isset($search) ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= $page == $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link"
                        href="?page=<?= min($total_pages, $page + 1) ?><?= isset($search) ? '&search=' . urlencode($search) : '' ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
    function goBack() {
        window.history.back();
    }

    // Panggil fungsi untuk mengatur jumlah produk di keranjang saat halaman dimuat
    updateCartCount(<?php echo $cart_count; ?>);

    // Fungsi untuk mengatur jumlah produk di keranjang
    function updateCartCount(count) {
        // Dapatkan elemen badge menggunakan ID
        let cartBadge = document.getElementById('cart-count');

        // Perbarui teks pada badge dengan jumlah yang diberikan
        cartBadge.textContent = count;
    }

    function confirmOrder() {
        return confirm("Anda Yakin Ingin Menambahkan Product ini?");
    }

    // Hide the message after 3 seconds
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            var message = document.getElementById('message');
            if (message) {
                message.style.display = 'none';
            }
        }, 2000);
    });
    </script>
</body>

</html>