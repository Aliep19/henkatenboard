<?php
require_once '../konfigurasi/konfig.php'; // Adjust the path to your database configuration

if (isset($_POST['npk'])) {
    $npk = $_POST['npk'];

    // Query to fetch skills from qualifications table, joining with process table to get process names and max skill
    $query = "
        SELECT q.process_id, p.name AS process_name, q.value AS skill_value, p.min_skill AS max_skill
        FROM qualifications q
        INNER JOIN process p ON q.process_id = p.id
        WHERE q.npk = ?
        ORDER BY p.name
    ";

    $stmt = $conn3->prepare($query);
    if ($stmt === false) {
        echo json_encode(['status' => 'error', 'message' => 'Query preparation failed: ' . $conn3->error]);
        exit;
    }

    $stmt->bind_param("s", $npk);
    $stmt->execute();
    $result = $stmt->get_result();

    $skills = [];
    while ($row = $result->fetch_assoc()) {
        $skills[] = [
            'process_id' => $row['process_id'],
            'process_name' => $row['process_name'],
            'skill_value' => $row['skill_value'],
            'max_skill' => $row['max_skill'] // Include max_skill in the response
        ];
    }

    $stmt->close();

    // Return the skills data as JSON
    echo json_encode(['status' => 'success', 'skills' => $skills]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'NPK not provided']);
}
?>