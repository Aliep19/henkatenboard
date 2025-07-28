<?php

header('Content-Type: application/json');
require_once '../konfigurasi/konfig.php';

// Mengambil input date dan id_shift
$date = isset($_POST['date']) ? date("Y-m-d", strtotime($_POST['date'])) : '';
$id_shift = isset($_POST['id_shift']) ? $_POST['id_shift'] : '';

// Validasi input
if (empty($date) || empty($id_shift)) {
    echo json_encode(array(
        "status" => "error",
        "message" => "Tanggal atau shift tidak boleh kosong"
    ));
    exit;
}

// Query database untuk mencari data dalam range date dan to_date
$sql = "SELECT * FROM hkt_form WHERE ? BETWEEN date AND to_date AND id_shifft = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $date, $id_shift);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Ambil hanya ID pertama dari hasil query
    $row = $result->fetch_assoc();
    $id_hkt = $row['id_hkt'];

    // Redirect dengan ID HKT
    $redirect_url = "http://localhost/henkaten/admin.php?status=success&id_hkt=" . urlencode($id_hkt);
    echo json_encode(array(
        "status" => "success",
        "message" => "Data ditemukan, redirecting...",
        "redirect_url" => $redirect_url
    ));
} else {
    echo json_encode(array(
        "status" => "fail",
        "message" => "Data tidak ada"
    ));
}

$stmt->close();
$conn->close();
