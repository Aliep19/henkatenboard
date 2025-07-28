<?php
require_once '../konfigurasi/konfig.php';

header('Content-Type: application/json');

// Ambil data dari POST
$id_bagian = isset($_POST['id_bagian']) ? $_POST['id_bagian'] : null;
$id_line = isset($_POST['id_line']) ? $_POST['id_line'] : null;
$id_shift = isset($_POST['id_shift']) ? $_POST['id_shift'] : null;
$excelProcesses = isset($_POST['excelProcesses']) ? json_decode($_POST['excelProcesses'], true) : [];

if (!$id_bagian || !$id_line || !$id_shift || empty($excelProcesses)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Data yang diperlukan tidak lengkap.'
    ]);
    exit;
}

try {
    // Ambil data proses dari database untuk line yang dipilih
    $query = "SELECT p.id, p.name, p.min_skill 
              FROM process p 
              WHERE p.workstation_id = ? AND p.line_id = ?";
    $stmt = $conn3->prepare($query);
    $stmt->bind_param("ii", $id_bagian, $id_line);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $dbProcesses = [];
    while ($row = $result->fetch_assoc()) {
        $dbProcesses[] = $row;
    }
    
    // Validasi setiap proses dari Excel
    $validProcesses = [];
    $totalCount = 0;
    $validCount = 0;
    
    foreach ($excelProcesses as $excelProcessName => $excelManpower) {
        $totalCount++;
        
        // Cari proses yang sesuai di database
        $matchedDbProcess = null;
        foreach ($dbProcesses as $dbProcess) {
            // Lakukan pencocokan nama proses (bisa disesuaikan dengan kebutuhan)
            if (strcasecmp(trim($dbProcess['name']), trim($excelProcessName)) === 0) {
                $matchedDbProcess = $dbProcess;
                break;
            }
        }
        
        if ($matchedDbProcess) {
            // Validasi manpower (cek apakah NPK ada di database dan sesuai shift)
            $npk = $excelManpower['npk'];
            $name = $excelManpower['name'];
            
            // Query untuk validasi manpower
            $manpowerQuery = "SELECT m.npk, m.full_name 
                             FROM ct_users m
                             JOIN manpower_assignments ma ON m.npk = ma.employee_npk
                             WHERE m.npk = ? 
                             AND ma.line_id = ?
                             AND ma.shift_id = ?";
            $manpowerStmt = $conn2->prepare($manpowerQuery);
            $manpowerStmt->bind_param("sii", $npk, $id_line, $id_shift);
            $manpowerStmt->execute();
            $manpowerResult = $manpowerStmt->get_result();
            
            if ($manpowerResult->num_rows > 0) {
                $validCount++;
                $validProcesses[] = [
                    'excel_process_name' => $excelProcessName,
                    'excel_npk' => $npk,
                    'excel_name' => $name,
                    'db_process_id' => $matchedDbProcess['id'],
                    'db_process_name' => $matchedDbProcess['name'],
                    'min_skill' => $matchedDbProcess['min_skill']
                ];
            }
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'validProcesses' => $validProcesses,
        'totalCount' => $totalCount,
        'validCount' => $validCount,
        'message' => 'Validasi selesai.'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
?>