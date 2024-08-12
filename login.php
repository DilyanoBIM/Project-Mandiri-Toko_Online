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

    // Query untuk mencari pengguna berdasarkan username
    $query = "SELECT * FROM users WHERE username = :username";
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

            // Redirect ke halaman dashboard yang sesuai berdasarkan peran (role)
            if ($row['role'] == 'admin') {
                header("Location: admin_dashboard.php");
                exit();
            } elseif ($row['role'] == 'customer') {
                header("Location: customer_dashboard.php");
                exit();
            }
        } else {
            $error = "Password salah";
        }
    } else {
        $error = "Username tidak ditemukan";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

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
        z-index: 10;
    }

    #particles-js {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        z-index: 0;
    }

    .password-input-container {
        position: relative;
    }

    .password-input-container input {
        padding-right: 40px;
        /* space for the icon */
    }

    .show-password-btn {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        background-color: transparent;
        border: none;
        cursor: pointer;
        color: #6c757d;
        transition: color 0.2s ease-in-out;
    }

    .show-password-btn:hover {
        color: #007bff;
    }
    </style>
</head>

<body>
    <div id="particles-js"></div>
    <div class="login-container mt-5">
        <h2 class="text-center">Login</h2>
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
                <input type="text" id="username" name="username" class="form-control" autocomplete="off" required>
            </div>
            <br>
            <div class="form-group">
                <label for="password">Password:</label>
                <div class="password-input-container">
                    <input type="password" id="password" name="password" class="form-control" required>
                    <button type="button" class="show-password-btn" id="show-password-icon">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <br>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <p class="mt-3">Belum punya akun? <a href="register.php">Daftar disini</a></p>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/login_script.js"></script>
</body>

</html>