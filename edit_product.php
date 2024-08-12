<?php
// Sertakan file konfigurasi database
require_once 'includes/config.php';

// Tangkap data yang dikirimkan dari form edit produk
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $code = $_POST['code'];

    // Tangkap file gambar yang diunggah (jika ada)
    $image = $_FILES['image']['name'];
    $image_temp = $_FILES['image']['tmp_name'];
    $image_folder = 'assets/images/';

    // Jika gambar baru diunggah, pindahkan ke folder uploads dan update query
    if (!empty($image)) {
        move_uploaded_file($image_temp, $image_folder . $image);
        $query_update_product = "UPDATE products SET product_name = :product_name, price = :price, code = :code, image = :image WHERE product_id = :product_id";
        $stmt_update_product = $pdo->prepare($query_update_product);
        $stmt_update_product->bindParam(':image', $image, PDO::PARAM_STR);
    } else {
        // Jika tidak ada gambar baru, gunakan query tanpa update kolom image
        $query_update_product = "UPDATE products SET product_name = :product_name, price = :price, code = :code WHERE product_id = :product_id";
        $stmt_update_product = $pdo->prepare($query_update_product);
    }

    $stmt_update_product->bindParam(':product_name', $product_name, PDO::PARAM_STR);
    $stmt_update_product->bindParam(':price', $price, PDO::PARAM_INT);
    $stmt_update_product->bindParam(':code', $code, PDO::PARAM_STR);
    $stmt_update_product->bindParam(':product_id', $product_id, PDO::PARAM_INT);

    if ($stmt_update_product->execute()) {
        // Redirect kembali ke halaman product setelah berhasil mengedit product
        header("Location: product.php");
        exit();
    } else {
        $error = "Terjadi kesalahan, silakan coba lagi";
        echo $error;
    }
}
?>
