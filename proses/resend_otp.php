<?php
session_start();
require '../konfigurasi/konfig.php'; // Sesuaikan path ke file konfigurasi database Anda

$response = array();

if (isset($_POST['npk']) && !empty($_POST['npk'])) {
    $npk = mysqli_real_escape_string($conn, $_POST['npk']);

    // Generate OTP baru
    $otp_code = sprintf('%06d', mt_rand(0, 999999));
    $_SESSION['otp_code'] = $otp_code;

    // Ambil nomor telepon dari database
    $sql_no_hp = "SELECT no_hp FROM hp WHERE npk = '$npk'";
    $result_no_hp = mysqli_query($conn4, $sql_no_hp);

    if ($no_hp_row = mysqli_fetch_assoc($result_no_hp)) {
        $no_hp = $no_hp_row['no_hp'];
    } else {
        $no_hp = '';
    }

    // Update OTP di database
    $sql_update = "UPDATE otp SET otp = '$otp_code', send = '2', `use` = '2' WHERE npk = '$npk'";
    if (mysqli_query($conn, $sql_update)) {
        // Di sini Anda bisa menambahkan logika untuk mengirim OTP ke nomor telepon (misalnya via SMS API)
        // Contoh: kirim SMS menggunakan API pihak ketiga (jika ada)

        $response['status'] = 'success';
        $response['message'] = 'OTP baru telah dikirim.';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Gagal memperbarui OTP di database.';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'NPK tidak ditemukan.';
}

// Kembalikan respons dalam format JSON
header('Content-Type: application/json');
echo json_encode($response);
?>