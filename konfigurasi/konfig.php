<?php

$servername = "localhost";
$username = "root";
$password = "";
$database = "henkaten";
$database2 = "lembur";
$database3 = "skillmap_db";
$database4 = "isd";

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $database);
$conn2 = new mysqli($servername, $username, $password, $database2);
$conn3 = new mysqli($servername, $username, $password, $database3);
$conn4 = new mysqli($servername, $username, $password, $database4);

// Memeriksa koneksi
if ($conn->connect_error || $conn2->connect_error || $conn3->connect_error || $conn4->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set session timeout (70 detik untuk server)
define('SESSION_TIMEOUT', 3600);

// Periksa apakah sesi telah kedaluwarsa
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    // Hancurkan sesi dan arahkan ke login
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Perbarui waktu aktivitas terakhir
$_SESSION['last_activity'] = time();
?>