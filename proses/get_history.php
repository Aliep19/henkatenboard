<?php
require_once '../konfigurasi/konfig.php'; // Koneksi ke $conn (henkaten) dan $conn3 (skillmap_db)
header('Content-Type: application/json');

// Default response
$response = ['status' => 'error', 'message' => 'Gagal mengambil data', 'data' => []];

if (isset($_POST['id_line'])) {
    $id_line = mysqli_real_escape_string($conn, $_POST['id_line']);
    $start_date = isset($_POST['start_date']) ? mysqli_real_escape_string($conn, $_POST['start_date']) : null;
    $end_date = isset($_POST['end_date']) ? mysqli_real_escape_string($conn, $_POST['end_date']) : null;

    // Default to last week (Monday to Sunday) if no date filter is provided
    $date_condition = ($start_date && $end_date) ? 
        "AND h.date BETWEEN '$start_date' AND '$end_date'" : 
        "AND YEARWEEK(h.date, 1) = YEARWEEK(DATE_SUB(CURDATE(), INTERVAL 1 WEEK), 1)";

    $query = "
        SELECT 
            h.date,
            h.id_shifft AS shift,
            h.to_date,
            p.name AS process_name,
            mp.man_power,
            k.name AS employee_name
        FROM henkaten.hkt_form h
        LEFT JOIN henkaten.mp_procees mp ON h.id_hkt = mp.id_hkt
        LEFT JOIN skillmap_db.process p ON mp.id_proses = p.id
        LEFT JOIN skillmap_db.karyawan k ON mp.man_power = k.npk
        WHERE h.id_line = '$id_line'
            $date_condition
        ORDER BY h.date DESC, p.name ASC
    ";

    $result = mysqli_query($conn, $query);

    if ($result) {
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = [
                'date' => $row['date'],
                'to_date' => $row['to_date'],
                'process_name' => $row['process_name'],
                'shift' => $row['shift'],
                'man_power' => $row['man_power'],
                'employee_name' => $row['employee_name'] ?? 'N/A'
            ];
        }
        $response = [
            'status' => 'success',
            'data' => $data
        ];
    } else {
        $response['message'] = 'Query gagal: ' . mysqli_error($conn);
    }
} else {
    $response['message'] = 'ID Line tidak diberikan';
}

echo json_encode($response);
?>