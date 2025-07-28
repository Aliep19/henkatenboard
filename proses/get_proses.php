<?php
require_once '../konfigurasi/konfig.php';

if (isset($_POST['id_line'])) {
    $workstation_id = $_POST['id_line'];

    $query = "
        SELECT id, name, status, min_skill 
        FROM process 
        WHERE workstation_id = ?
    ";

    $stmt = $conn3->prepare($query);
    $stmt->bind_param("i", $workstation_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $processes = array();
        while ($row = $result->fetch_assoc()) {
            $processes[] = $row;
        }
        echo json_encode($processes);
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode(["error" => "id_line not received"]);
}

$conn3->close();
?>