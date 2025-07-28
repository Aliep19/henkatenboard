<?php
require_once '../konfigurasi/konfig.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $workstation_id = $_POST['workstation_id'];
    $s_process_id = $_POST['s_process_id'];
    $name = $_POST['name'];
    $min_skill = $_POST['min_skill'];
    $status = $_POST['status'];

    $query = "INSERT INTO process (workstation_id, s_process_id, name, min_skill, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn3->prepare($query);
    $stmt->bind_param("iisis", $workstation_id, $s_process_id, $name, $min_skill, $status);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan proses.']);
    }

    $stmt->close();
    $conn3->close();
}
?>