<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once '../konfigurasi/konfig.php';

header('Content-Type: application/json'); // Ensure JSON output

if (isset($_POST['id_line'])) {
    $sub_workstation_id = $_POST['id_line'];

    // Step 1: Get the role ID for guide (role == 1)
    $role_query = "SELECT id FROM roles WHERE id = 1";
    $role_result = mysqli_query($conn3, $role_query);
    if (!$role_result) {
        echo json_encode(['error' => 'Role query failed: ' . mysqli_error($conn3)]);
        exit;
    }

    $role_data = mysqli_fetch_assoc($role_result);
    $guide_role_id = $role_data['id'];

    // Step 2: Fetch line_guide data from karyawan and filter by sub_workstation
    $query = "
        SELECT karyawan.npk, karyawan.name
        FROM karyawan
        INNER JOIN karyawan_workstation ON karyawan.npk = karyawan_workstation.npk
        WHERE karyawan.role = ? AND karyawan_workstation.workstation_id = ?
    ";

    $stmt = $conn3->prepare($query);
    if (!$stmt) {
        echo json_encode(['error' => 'Prepare statement failed: ' . $conn3->error]);
        exit;
    }

    $stmt->bind_param("ii", $guide_role_id, $sub_workstation_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $guide = [];
    while ($row = $result->fetch_assoc()) {
        $guide[] = $row;
    }

    echo json_encode($guide);
} else {
    echo json_encode(['error' => 'No id_line provided']);
}
?>