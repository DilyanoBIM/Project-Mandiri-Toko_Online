<?php
session_start();
require_once 'includes/config.php';

// Tangkap data yang dikirimkan jika jumlah barang diubah
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cart_id = $_POST['cart_id'];
    $quantity = $_POST['quantity'];

    // Query untuk memperbarui jumlah barang di keranjang
    $query_update_cart = "UPDATE cart SET quantity = :quantity WHERE cart_id = :cart_id";
    $stmt_update_cart = $pdo->prepare($query_update_cart);
    $stmt_update_cart->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $stmt_update_cart->bindParam(':cart_id', $cart_id, PDO::PARAM_INT);
    $stmt_update_cart->execute();

    // Redirect kembali ke halaman cart.php setelah berhasil memperbarui keranjang
    $_SESSION['message'] = "Cart updated successfully.";
    header("Location: cart.php");
    exit();
}

// Query untuk mengambil daftar barang di keranjang pengguna
$query_cart_items = "SELECT cart.cart_id, products.product_id, products.product_name, products.price, cart.quantity, products.image
                    FROM cart
                    INNER JOIN products ON cart.product_id = products.product_id
                    WHERE cart.user_id = :user_id";
$stmt_cart_items = $pdo->prepare($query_cart_items);
$stmt_cart_items->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt_cart_items->execute();
$cart_items = $stmt_cart_items->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/fontawesome/css/all.min.css" rel="stylesheet">
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
    }

    .container {
        max-width: 1000px;
        margin: 50px auto;
        padding: 20px;
        border-radius: 8px;
        background-color: #fff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    h2 {
        color: #007bff;
        margin-bottom: 20px;
        text-align: center;
    }

    .btn-secondary {
        margin-bottom: 20px;
    }

    .table th,
    .table td {
        vertical-align: middle;
    }

    .img-thumbnail {
        max-width: 80px;
        height: auto;
    }

    .form-control {
        width: 80px;
        display: inline-block;
        margin-bottom: 0;
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }

    .btn-primary:focus {
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.5);
    }

    .total-price {
        margin-top: 20px;
        font-size: 1.2rem;
        font-weight: bold;
        text-align: right;
    }

    .checkout-btn {
        margin-top: 20px;
        float: right;
    }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2>Cart</h2>
        <a href="javascript:goBack();" class="btn btn-secondary">Back</a>

        <?php if (isset($_SESSION['message'])) : ?>
        <div class="alert alert-warning mt-3" role="alert">
            <?php
                echo $_SESSION['message']; 
                unset($_SESSION['message']); // Hapus pesan setelah ditampilkan
                ?>
        </div>
        <?php endif; ?>

        <table class="table mt-3">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Image</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php $total_price = 0; ?>
                <!-- Variabel untuk menyimpan total harga -->
                <?php foreach ($cart_items as $cart_item) : ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo $cart_item['product_name']; ?></td>
                    <td>
                        <?php
                            $original_price = $cart_item['price']; // Harga asli
                            $discounted_price = $original_price * 0.9; // Harga setelah diskon 10%
                            echo 'Rp' . number_format($original_price, 2);
                            ?>
                    </td>
                    <td>10% => <?php echo 'Rp' . number_format($discounted_price, 2); ?></td>
                    <td>
                        <!-- Form untuk mengubah jumlah barang secara langsung -->
                        <form action="cart.php" method="POST">
                            <input type="hidden" name="cart_id" value="<?php echo $cart_item['cart_id']; ?>">
                            <input type="number" name="quantity" value="<?php echo $cart_item['quantity']; ?>" min="1"
                                onchange="this.form.submit()" class="form-control mr-sm-2" required>
                        </form>
                    </td>
                    <td>
                        <?php
                            $subtotal = $discounted_price * $cart_item['quantity'];
                            echo 'Rp' . number_format($subtotal, 2);
                            ?>
                    </td>
                    <td><img src="assets/images/<?php echo $cart_item['image']; ?>" alt="Product Image"
                            class="img-thumbnail"></td>
                    <td>
                        <form action="remove_from_cart.php" method="POST">
                            <input type="hidden" name="cart_id" value="<?php echo $cart_item['cart_id']; ?>">
                            <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php $total_price += $subtotal; ?>
                <!-- Tambahkan harga setiap item ke total harga -->
                <?php endforeach; ?>
            </tbody>
        </table>

        <h4>Total Price: Rp<?php echo number_format($total_price, 2); ?></h4> <!-- Tampilkan total harga -->

        <?php if (!empty($cart_items)) : ?>
        <!-- Form checkout -->
        <form action="checkout_process.php" method="POST" onsubmit="return confirmCheckout()">
            <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">
            <button type="submit" name="checkout" class="btn btn-primary">Checkout</button>
        </form>
        <?php endif; ?>
    </div>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
    function confirmCheckout() {
        return confirm("Yakin ingin checkout? Tidak ada perubahan?");
    }

    function goBack() {
        history.back();
    }
    </script>
</body>

</html>