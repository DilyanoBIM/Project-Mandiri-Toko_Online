<?php
// Konfigurasi database
define('DB_HOST', 'localhost'); // Host database (biasanya localhost)
define('DB_USER', 'root'); // Nama pengguna database
define('DB_PASS', ''); // Kata sandi database
define('DB_NAME', 'damoza_toys'); // Nama database

// Fungsi untuk membersihkan input
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
function cleanInputForDB($pdo, $data) {
    $data = cleanInput($data); // Gunakan fungsi cleanInput yang sudah dibuat sebelumnya
    $data = $pdo->quote($data); // Gunakan metode quote() dari objek PDO untuk menghindari SQL Injection
    return $data;
}
function cleanInputForDisplay($data) {
    $data = cleanInput($data); // Gunakan fungsi cleanInput yang sudah dibuat sebelumnya
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); // Gunakan htmlspecialchars untuk mencegah XSS
    return $data;
}

// Koneksi ke database MySQL menggunakan PDO
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    // Atur mode error dan pengecualian PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>