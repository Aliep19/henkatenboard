<?php
header('Content-Type: application/json');
require_once '../konfigurasi/konfig.php';

// Shift determination logic from workhours.php
date_default_timezone_set('Asia/Jakarta');
$time = date('H:i:s');

if ($time >= '22:30:00' || $time <= '05:59:59') {
    $shift = 1; // Shift malam
} elseif ($time >= '06:00:00' && $time <= '14:29:59') {
    $shift = 2; // Shift pagi
} elseif ($time >= '14:30:00' && $time <= '22:29:59') {
    $shift = 3; // Shift sore
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $line = isset($_POST['line']) ? intval($_POST['line']) : 0;
    $date = isset($_POST['date']) ? $_POST['date'] : '';

    if ($line <= 0 || empty($date)) {
        $response['message'] = 'Parameter tidak valid.';
        echo json_encode($response);
        exit;
    }

    // Query to check for hkt_form record within the date range
    $sql = "SELECT id_hkt FROM hkt_form WHERE id_line = ? AND id_shifft = ? AND ? BETWEEN date AND to_date";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $response['message'] = 'Gagal menyiapkan query.';
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param("iis", $line, $shift, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response['success'] = true;
        $response['id_hkt'] = $row['id_hkt'];
    } else {
        $response['message'] = 'Data HKT tidak ditemukan.';
    }

    $stmt->close();
    $conn->close();
} else {
    $response['message'] = 'Metode request tidak valid.';
}

echo json_encode($response);
?>  