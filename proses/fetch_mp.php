<?php
require_once '../konfigurasi/konfig.php'; // Pastikan koneksi $conn3 sudah ada
header('Content-Type: application/json');

// Aktifkan logging untuk debugging
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/php_errors.log'); // Sesuaikan path log

try {
    // Periksa apakah id_bagian dikirimkan
    if (!isset($_POST['id_bagian'])) {
        error_log("Error: id_bagian parameter is required.");
        echo json_encode(["error" => "id_bagian parameter is required."]);
        exit;
    }

    $id_bagian = intval($_POST['id_bagian']);
    error_log("id_bagian received: $id_bagian");

    // Query untuk mengambil data manpower berdasarkan workstation_id
    $query = "
        SELECT  
            karyawan.npk, 
            karyawan.name, 
            MAX(qualifications.process_id) AS qual_process_id,
            MAX(qualifications.value) AS qualification_value,
            MAX(process.id) AS proc_id,
            MAX(process.s_process_id) AS s_process,
            MAX(process.min_skill) AS process_min_skill
        FROM 
            karyawan
        LEFT JOIN 
            karyawan_workstation 
        ON 
            karyawan_workstation.npk = karyawan.npk
        LEFT JOIN 
            qualifications 
        ON 
            qualifications.npk = karyawan_workstation.npk
        LEFT JOIN 
            process 
        ON 
            process.id = qualifications.process_id
        WHERE 
            karyawan_workstation.workstation_id = ?
        GROUP BY 
            karyawan.npk, karyawan.name
    ";

    // Siapkan statement
    $stmt = $conn3->prepare($query);
    if (!$stmt) {
        error_log("Failed to prepare statement: " . $conn3->error);
        echo json_encode(["error" => "Failed to prepare statement: " . $conn3->error]);
        exit;
    }

    // Bind parameter dan eksekusi query
    $stmt->bind_param("i", $id_bagian);
    if (!$stmt->execute()) {
        error_log("Query execution failed: " . $stmt->error);
        echo json_encode(["error" => "Query execution failed: " . $stmt->error]);
        exit;
    }

    $result = $stmt->get_result();
    $manpower = [];

    // Ambil hasil query
    while ($row = $result->fetch_assoc()) {
        $manpower[] = [
            'npk' => $row['npk'],
            'name' => $row['name'],
            'process_id' => $row['qual_process_id'] ?? $row['proc_id'] ?? null,
            'qualification_value' => $row['qualification_value'] ?? 0,
            's_process' => $row['s_process'] ?? '',
            'process_min_skill' => $row['process_min_skill'] ?? 0
        ];
    }

    // Log hasil query
    error_log("Manpower Data: " . json_encode($manpower));

    // Kembalikan hasil dalam format JSON
    if (empty($manpower)) {
        error_log("No manpower data found for id_bagian: $id_bagian");
        echo json_encode(["error" => "Tidak ada data manpower untuk bagian ini"]);
    } else {
        echo json_encode($manpower);
    }

    $stmt->close();
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    echo json_encode(["error" => $e->getMessage()]);
}

$conn3->close();
?>