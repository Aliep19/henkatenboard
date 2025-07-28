<?php
require_once '../konfigurasi/konfig.php';

header('Content-Type: application/json');

// Validasi input
if (!isset($_POST['id_bagian'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID Bagian tidak valid']);
    exit;
}

$id_bagian = $_POST['id_bagian'];
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';

try {
    // Query untuk mendapatkan MP Repair yang sudah tersimpan
    $query = "SELECT mr.npk, k.name, mr.startdate, mr.enddate, w.name as workstation_name
              FROM henkaten.mp_repair mr
              JOIN skillmap_db.karyawan k ON mr.npk = k.npk
              LEFT JOIN skillmap_db.workstations w ON mr.id_bagian = w.id
              WHERE mr.id_bagian = ? 
              AND mr.startdate <= ? 
              AND mr.enddate >= ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $id_bagian, $end_date, $start_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'count' => count($data),
        'data' => $data
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>