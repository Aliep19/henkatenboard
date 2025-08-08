<?php
session_start();

// Ambil input dari form
$npk = $_POST['npk'] ?? '';
$password = $_POST['password'] ?? '';
$userCaptcha = strtoupper(trim($_POST['captcha'] ?? ''));
$expectedCaptcha = $_SESSION['captcha_code'] ?? '';

// Validasi CAPTCHA
if ($userCaptcha !== $expectedCaptcha) {
    echo "Captcha salah. <a href='../login.php'>Kembali</a>";
    exit;
}

// Validasi login (contoh statis)
if ($npk === 'admin' && $password === '123456') {
    echo "Login berhasil. Selamat datang, $npk!";
    // simpan ke session, redirect, dll
    // $_SESSION['user'] = $npk;
    // header('Location: dashboard.php');
} else {
    echo "NPK atau password salah. <a href='../login.php'>Coba lagi</a>";
}
