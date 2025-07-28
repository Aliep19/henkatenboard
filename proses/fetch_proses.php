<?php
require_once '../konfigurasi/konfig.php';

header('Content-Type: application/json');

try {
    if (isset($_POST['id_proses'])) {
        $id_proses = intval($_POST['id_proses']);

        $query = "SELECT id, name FROM process WHERE id = ?";
        $stmt = $conn3->prepare($query);

        if ($stmt) {
            $stmt->bind_param("i", $id_proses);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo json_encode(['nama_proses' => $row['name']]);
            } else {
                echo json_encode(['error' => 'Data not found']);
            }
        } else {
            echo json_encode(['error' => 'Failed to prepare statement.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['error' => 'id_proses parameter is required.']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn3->close();
