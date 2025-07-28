<?php
session_start();
require '../konfigurasi/konfig.php'; // Adjust path to your database config

header('Content-Type: application/json');

// Check if required session variables exist
if (!isset($_SESSION['npk']) || !isset($_SESSION['otp_code'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired. Please login again.']);
    exit();
}

// Get input OTP from form
$otp_input = $_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'] . $_POST['otp5'] . $_POST['otp6'];
$npk = mysqli_real_escape_string($conn, $_SESSION['npk']);
$stored_otp = $_SESSION['otp_code'];
$redirect_url = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : 'home.php';

// Verify OTP
if ($otp_input === $stored_otp) {
    // Check OTP in database for additional validation
    $sql = "SELECT otp FROM otp WHERE npk = '$npk' AND `use` = '2'";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        if ($row['otp'] === $otp_input) {
            // OTP is correct, set logged-in session
            $_SESSION['log'] = 'True';
            $_SESSION['otp_verified'] = true;
            $_SESSION['just_logged_in'] = true;

            // Fetch user's full name
            $query_nama = "SELECT full_name FROM ct_users WHERE npk = '$npk'";
            $result_nama = mysqli_query($conn2, $query_nama);
            $nama = 'User';
            if ($result_nama && mysqli_num_rows($result_nama) > 0) {
                $row_nama = mysqli_fetch_assoc($result_nama);
                $nama = $row_nama['full_name'];
            }
            $_SESSION['full_name'] = $nama;

            // Update OTP status in database
            $sql_update = "UPDATE otp SET `use` = '1' WHERE npk = '$npk' AND otp = '$otp_input'";
            mysqli_query($conn, $sql_update);

            // Clear OTP session data
            unset($_SESSION['otp_code']);
            unset($_SESSION['otp_sent_time']);

            echo json_encode(['status' => 'success', 'redirect_url' => $redirect_url]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid OTP code.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'OTP not found or already used.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid OTP code.']);
}

mysqli_close($conn);
mysqli_close($conn2);
?>