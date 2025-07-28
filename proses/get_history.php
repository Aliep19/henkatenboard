<?php
require_once '../konfigurasi/konfig.php'; // Koneksi ke $conn (henkaten) dan $conn3 (skillmap_db)
header('Content-Type: application/json');

// Default response
$response = ['status' => 'error', 'message' => 'Gagal mengambil data', 'data' => []];

if (isset($_POST['id_line'])) {
    $id_line = mysqli_real_escape_string($conn, $_POST['id_line']);

    // Ambil minggu lalu (Minggu sebelumnya dari hari Senin sampai Minggu)
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
            AND YEARWEEK(h.date, 1)
        ORDER BY h.date DESC, p.name ASC
    ";

            // AND YEARWEEK(h.date, 1) = YEARWEEK(CURDATE(), 1)
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
