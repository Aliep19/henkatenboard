<?php
require_once '../konfigurasi/konfig.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $workstation_id = $_POST['workstation_id'];

    $query = "SELECT id, name FROM sub_workstations WHERE workstation_id = ?";
    $stmt = $conn3->prepare($query);
    $stmt->bind_param("i", $workstation_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $sub_workstations = [];
    while ($row = $result->fetch_assoc()) {
        $sub_workstations[] = $row;
    }

    echo json_encode($sub_workstations);
    $stmt->close();
    $conn3->close();
}
?>