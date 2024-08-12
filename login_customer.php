<?php
// Mulai sesi
session_start();

// Cek apakah pengguna sudah login, jika ya langsung arahkan ke halaman dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin_dashboard.php");
        exit();
    } elseif ($_SESSION['role'] == 'customer') {
        header("Location: customer_dashboard.php");
        exit();
    }
}

// Sertakan file konfigurasi database
require_once 'includes/config.php';

// Variabel untuk menyimpan pesan kesalahan
$error = '';

// Tangkap data yang dikirimkan dari formulir login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query untuk mencari pengguna berdasarkan username dan peran customer
    $query = "SELECT * FROM users WHERE username = :username AND role = 'customer'";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    // Jika pengguna ditemukan
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // Verifikasi password
        if (password_verify($password, $row['password'])) {
            // Simpan informasi pengguna ke sesi
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];

            // Redirect ke halaman dashboard customer setelah login berhasil
            header("Location: customer_dashboard.php");
            exit();
        } else {
            $error = "Password salah";
        }
    } else {
        $error = "Username atau peran tidak cocok";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Customer</title>
    <!-- Bootstrap CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            overflow: hidden;
            margin: 0;
        }
        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.8);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <div class="login-container mt-5">
        <h2 class="text-center">Login - Customer</h2>
        <br><br>
        <?php
        // Tampilkan pesan error jika login gagal
        if (!empty($error)) {
            echo '<div class="alert alert-danger">' . $error . '</div>';
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" class="form-control " autocomplete="off" required>
            </div>
            <br>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <br>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        <p class="mt-3">Belum punya akun? <a href="register.php">Daftar disini</a></p>
    </div>

    <!-- Bootstrap JS dan dependencies -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
