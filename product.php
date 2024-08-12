<?php
// Sertakan file konfigurasi database
require_once 'includes/config.php';

// Variabel untuk menyimpan pesan kesalahan dan sukses
$error = '';
$success = '';

// Tangkap data yang dikirimkan dari form tambah atau edit produk
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $code = $_POST['code'];
    $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null;

    // Cek apakah kode produk sudah ada dalam database
    $query_check_code = "SELECT COUNT(*) AS code_count FROM products WHERE code = :code" . ($product_id ? " AND product_id != :product_id" : "");
    $stmt_check_code = $pdo->prepare($query_check_code);
    $stmt_check_code->bindParam(':code', $code, PDO::PARAM_STR);
    if ($product_id) {
        $stmt_check_code->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    }
    $stmt_check_code->execute();
    $code_count = $stmt_check_code->fetchColumn();

    // Cek apakah nama produk sudah ada dalam database
    $query_check_name = "SELECT COUNT(*) AS name_count FROM products WHERE product_name = :product_name" . ($product_id ? " AND product_id != :product_id" : "");
    $stmt_check_name = $pdo->prepare($query_check_name);
    $stmt_check_name->bindParam(':product_name', $product_name, PDO::PARAM_STR);
    if ($product_id) {
        $stmt_check_name->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    }
    $stmt_check_name->execute();
    $name_count = $stmt_check_name->fetchColumn();

    if ($code_count > 0) {
        $error = "Kode produk sudah ada dalam database.";
    } elseif ($name_count > 0) {
        $error = "Nama produk sudah ada dalam database.";
    } else {
        // Tangkap file gambar yang diunggah
        $image = $_FILES['image']['name'];
        $image_temp = $_FILES['image']['tmp_name'];
        $image_folder = 'assets/images/';

        if ($product_id) {
            // Edit produk
            if (!empty($image)) {
                move_uploaded_file($image_temp, $image_folder . $image);
                $query_update_product = "UPDATE products SET product_name = :product_name, price = :price, image = :image, code = :code WHERE product_id = :product_id";
                $stmt_update_product = $pdo->prepare($query_update_product);
                $stmt_update_product->bindParam(':image', $image, PDO::PARAM_STR);
            } else {
                $query_update_product = "UPDATE products SET product_name = :product_name, price = :price, code = :code WHERE product_id = :product_id";
                $stmt_update_product = $pdo->prepare($query_update_product);
            }
            $stmt_update_product->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt_update_product->bindParam(':product_name', $product_name, PDO::PARAM_STR);
            $stmt_update_product->bindParam(':price', $price, PDO::PARAM_INT);
            $stmt_update_product->bindParam(':code', $code, PDO::PARAM_STR);

            if ($stmt_update_product->execute()) {
                $success = "Produk berhasil diperbarui.";
            } else {
                $error = "Terjadi kesalahan, silakan coba lagi.";
            }
        } else {
            // Tambah produk baru
            if (move_uploaded_file($image_temp, $image_folder . $image)) {
                $query_insert_product = "INSERT INTO products (product_name, price, image, code) VALUES (:product_name, :price, :image, :code)";
                $stmt_insert_product = $pdo->prepare($query_insert_product);
                $stmt_insert_product->bindParam(':product_name', $product_name, PDO::PARAM_STR);
                $stmt_insert_product->bindParam(':price', $price, PDO::PARAM_INT);
                $stmt_insert_product->bindParam(':image', $image, PDO::PARAM_STR);
                $stmt_insert_product->bindParam(':code', $code, PDO::PARAM_STR);

                if ($stmt_insert_product->execute()) {
                    header("Location: product.php");
                    exit();
                } else {
                    $error = "Terjadi kesalahan, silakan coba lagi.";
                }
            } else {
                $error = "Gagal mengunggah gambar.";
            }
        }
    }
}

// Tangani penghapusan produk
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $product_id = $_POST['product_id'];

    try {
        $pdo->beginTransaction();

        // Hapus entri terkait dari tabel order_details
        $query_delete_order_details = "DELETE FROM order_details WHERE product_id = :product_id";
        $stmt_delete_order_details = $pdo->prepare($query_delete_order_details);
        $stmt_delete_order_details->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt_delete_order_details->execute();

        // Hapus entri terkait dari tabel cart
        $query_delete_cart = "DELETE FROM cart WHERE product_id = :product_id";
        $stmt_delete_cart = $pdo->prepare($query_delete_cart);
        $stmt_delete_cart->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt_delete_cart->execute();

        // Hapus produk dari tabel products
        $query_delete_product = "DELETE FROM products WHERE product_id = :product_id";
        $stmt_delete_product = $pdo->prepare($query_delete_product);
        $stmt_delete_product->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt_delete_product->execute();

        $pdo->commit();
        $success = "Produk berhasil dihapus.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}


// Query untuk mengambil daftar produk dari database
$query = "SELECT * FROM products";
$stmt = $pdo->query($query);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Page</title>
    <!-- Bootstrap CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/product.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

</head>

<body>
    <div class="container mt-5">
        <h2 class="mb-3">Product Page</h2>
        <a href="admin_dashboard.php" class="btn btn-primary mb-3">Back to Dashboard</a>
        <!-- Tombol Tambah Product -->
        <a href="#" id="addProductBtn" class="btn btn-success mb-3">Tambah Product</a>
        <div class="row mb-3">
            <div class="col-md-6">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="GET" class="form-inline">
                    <label for="search" class="mr-2">Search by Code:</label>
                    <input type="text" id="search" name="search" class="form-control mr-sm-2" placeholder="Enter code"
                        autocomplete="off"><br>
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
        </div>
        <?php
        // Variabel untuk menyimpan query pencarian
        $search_query = '';

        // Tangkap data yang dikirimkan dari form search
        if (isset($_GET['search'])) {
            $search = $_GET['search'];
            // Buat query pencarian berdasarkan 'code'
            $search_query = " WHERE code LIKE '%$search%'";
        }

        // Query untuk mengambil daftar produk dari database berdasarkan pencarian
        $query = "SELECT * FROM products" . $search_query;
        $stmt = $pdo->query($query);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <!-- Form Tambah Product (awalnya disembunyikan) -->
        <div id="addProductForm" style="display: none;">
            <div class="card">
                <div class="card-header">
                    Tambah Product Baru
                </div>
                <div class="card-body">
                    <?php
                    // Tampilkan pesan error jika ada
                    if (!empty($error)) {
                        echo '<div class="alert alert-danger" role="alert">' . $error . '</div>';
                    }
                    if (!empty($success)) {
                        echo '<div class="alert alert-success" role="alert">' . $success . '</div>';
                    }
                    ?>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST"
                        enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="product_name">Product Name:</label>
                            <input type="text" id="product_name" name="product_name" class="form-control" required>
                        </div><br>
                        <div class="form-group">
                            <label for="code">Code:</label>
                            <input type="text" id="code" name="code" class="form-control" required>
                        </div><br>
                        <div class="form-group">
                            <label for="price">Price:</label>
                            <input type="number" id="price" name="price" min="0" class="form-control" required>
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="image">Image:</label>
                            <input type="file" id="image" name="image" class="form-control-file" required>
                        </div><br>
                        <input type="hidden" name="product_id" id="product_id">
                        <button type="submit" name="submit" class="btn btn-primary">Tambah Product</button>
                    </form>
                </div>
            </div>
        </div>
        <br>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Product Name</th>
                    <th>Code</th>
                    <th>Price</th>
                    <th>Image</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php foreach ($products as $product) : ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo $product['product_name']; ?></td>
                    <td><?php echo $product['code']; ?></td>
                    <td>Rp<?php echo number_format($product['price'], 2); ?></td>
                    <td><img src="<?php echo 'assets/images/' . $product['image']; ?>" alt="Product Image"
                            class="image-thumbnail"></td>
                    <td>
                        <a href="#" class="btn btn-warning btn-edit" data-id="<?php echo $product['product_id']; ?>"
                            data-name="<?php echo $product['product_name']; ?>"
                            data-code="<?php echo $product['code']; ?>" data-price="<?php echo $product['price']; ?>"
                            data-image="<?php echo $product['image']; ?>">Edit</a>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST"
                            class="d-inline">
                            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                            <button type="submit" name="delete" class="btn btn-danger"
                                onclick="return confirm('Anda yakin ingin menghapus produk ini?');">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Script untuk menampilkan/menyembunyikan form dan mengisi form edit -->
        <script src="assets/js/bootstrap.bundle.min.js"></script>
        <script>
        document.getElementById('addProductBtn').addEventListener('click', function() {
            var form = document.getElementById('addProductForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            document.getElementById('product_id').value = '';
            document.querySelector('form').reset();
            document.querySelector('form button[type=submit]').innerText = 'Tambah Product';
        });

        document.querySelectorAll('.btn-edit').forEach(function(button) {
            button.addEventListener('click', function() {
                var form = document.getElementById('addProductForm');
                form.style.display = 'block';
                document.getElementById('product_id').value = this.getAttribute('data-id');
                document.getElementById('product_name').value = this.getAttribute('data-name');
                document.getElementById('code').value = this.getAttribute('data-code');
                document.getElementById('price').value = this.getAttribute('data-price');
                document.querySelector('form button[type=submit]').innerText = 'Edit Product';
            });
        });
        </script>
    </div>
</body>

</html>