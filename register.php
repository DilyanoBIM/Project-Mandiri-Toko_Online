<?php
// Sertakan file konfigurasi database
require_once 'includes/config.php';

// Tangkap data yang dikirimkan dari formulir registrasi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];

    // Validasi jika password tidak cocok dengan konfirmasi password
    if ($password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok";
    } else {
        // Query untuk mengecek apakah username sudah digunakan
        $query_check_username = "SELECT * FROM users WHERE username = :username";
        $stmt_check_username = $pdo->prepare($query_check_username);
        $stmt_check_username->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt_check_username->execute();

        if ($stmt_check_username->rowCount() > 0) {
            $error = "Username sudah digunakan, silakan coba yang lain";
        } else {
            // Validasi password khusus untuk peran admin
            if ($role === 'admin') {
                // Misalnya, kita memerlukan password minimal 8 karakter dengan setidaknya satu huruf besar dan satu angka
                if (!preg_match('/^(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
                    $error = "Password untuk admin harus minimal 8 karakter dengan setidaknya satu huruf besar dan satu angka";
                }
            }

            if (!isset($error)) {
                // Hash password sebelum dimasukkan ke database
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Query untuk memasukkan data pengguna baru ke dalam database
                $query_register = "INSERT INTO users (fullname, email, phone, username, password, role) VALUES (:fullname, :email, :phone, :username, :password, :role)";
                $stmt_register = $pdo->prepare($query_register);
                $stmt_register->bindParam(':fullname', $fullname, PDO::PARAM_STR);
                $stmt_register->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt_register->bindParam(':phone', $phone, PDO::PARAM_STR);
                $stmt_register->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt_register->bindParam(':password', $hashed_password, PDO::PARAM_STR);
                $stmt_register->bindParam(':role', $role, PDO::PARAM_STR);

                if ($stmt_register->execute()) {
                    // Redirect ke halaman login setelah registrasi berhasil
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Terjadi kesalahan, silakan coba lagi";
                }
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- Bootstrap CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
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

    .register-container {
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
    </style>
</head>

<body>

    <div id="particles-js"></div>
    <div class="register-container">
        <h2 class="text-center">Register</h2>
        <?php
        // Tampilkan pesan error jika ada
        if (isset($error)) {
            echo '<div class="alert alert-danger" role="alert">' . $error . '</div>';
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="mt-3">
            <div class="form-group mt-3">
                <label for="fullname">Nama Lengkap:</label>
                <input type="text" id="fullname" name="fullname" class="form-control" autocomplete="off" required>
            </div>
            <div class="form-group mt-3">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" autocomplete="off" required>
            </div>
            <div class="form-group mt-3">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" class="form-control" autocomplete="off" required>
            </div>
            <div class="form-group mt-3">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" autocomplete="off" required>
            </div>
            <div class="form-group mt-3">
                <label for="confirm_password">Konfirmasi Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                    autocomplete="off" required>
            </div>
            <div class="form-group mt-3">
                <label for="phone">Nomor HP:</label>
                <input type="text" id="phone" name="phone" class="form-control" autocomplete="off" required>
            </div>
            <!-- Role sebagai input hidden dengan nilai customer -->
            <input type="hidden" id="role" name="role" value="customer">
            <button type="submit" class="btn btn-primary btn-block mt-3">Register</button>
        </form>



        <p class="mt-3 text-center">Sudah punya akun? <a href="login.php">Login disini</a></p>
    </div>

    <!-- Bootstrap JS dan dependencies -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
    particlesJS("particles-js", {
        "particles": {
            "number": {
                "value": 80,
                "density": {
                    "enable": true,
                    "value_area": 800
                }
            },
            "color": {
                "value": "#ffffff"
            },
            "shape": {
                "type": "circle",
                "stroke": {
                    "width": 0,
                    "color": "#000000"
                },
                "polygon": {
                    "nb_sides": 5
                },
                "image": {
                    "src": "img/github.svg",
                    "width": 100,
                    "height": 100
                }
            },
            "opacity": {
                "value": 0.5,
                "random": false,
                "anim": {
                    "enable": false,
                    "speed": 1,
                    "opacity_min": 0.1,
                    "sync": false
                }
            },
            "size": {
                "value": 3,
                "random": true,
                "anim": {
                    "enable": false,
                    "speed": 40,
                    "size_min": 0.1,
                    "sync": false
                }
            },
            "line_linked": {
                "enable": true,
                "distance": 150,
                "color": "#ffffff",
                "opacity": 0.4,
                "width": 1
            },
            "move": {
                "enable": true,
                "speed": 6,
                "direction": "none",
                "random": false,
                "straight": false,
                "out_mode": "out",
                "bounce": false,
                "attract": {
                    "enable": false,
                    "rotateX": 600,
                    "rotateY": 1200
                }
            }
        },
        "interactivity": {
            "detect_on": "canvas",
            "events": {
                "onhover": {
                    "enable": true,
                    "mode": "repulse"
                },
                "onclick": {
                    "enable": true,
                    "mode": "push"
                },
                "resize": true
            },
            "modes": {
                "grab": {
                    "distance": 400,
                    "line_linked": {
                        "opacity": 1
                    }
                },
                "bubble": {
                    "distance": 400,
                    "size": 40,
                    "duration": 2,
                    "opacity": 8,
                    "speed": 3
                },
                "repulse": {
                    "distance": 200,
                    "duration": 0.4
                },
                "push": {
                    "particles_nb": 4
                },
                "remove": {
                    "particles_nb": 2
                }
            }
        },
        "retina_detect": true
    });
    </script>
</body>

</html>