<?php
require_once '../konfigurasi/konfig.php';

if (!isset($_GET['id_hkt']) || intval($_GET['id_hkt']) <= 0) {
    echo json_encode(['error' => 'Invalid ID HKT']);
    exit;
}

$id_hkt = intval($_GET['id_hkt']);

// Query untuk mendapatkan data dari tabel mp_procees berdasarkan id_hkt
$query_process = "SELECT id_proses, man_power FROM mp_procees WHERE id_hkt = ?";
$stmt_process = $conn->prepare($query_process);
$stmt_process->bind_param("i", $id_hkt);
$stmt_process->execute();
$result_process = $stmt_process->get_result();

$data = [];
if ($result_process->num_rows > 0) {
    while ($row = $result_process->fetch_assoc()) {
        $id_proses = $row['id_proses'];
        $npk = $row['man_power'];

        // Query untuk mendapatkan nama dari tabel karyawan berdasarkan npk
        $query_karyawan = "SELECT name FROM karyawan WHERE npk = ?";
        $stmt_karyawan = $conn3->prepare($query_karyawan);
        $stmt_karyawan->bind_param("s", $npk);
        $stmt_karyawan->execute();
        $result_karyawan = $stmt_karyawan->get_result();

        $name = $result_karyawan->num_rows > 0 ? $result_karyawan->fetch_assoc()['name'] : 'Not Found';

        // Tambahkan data ke array
        $data[] = [
            'id_proses' => $id_proses,
            'npk' => $npk,
            'name' => $name
        ];
    }
} else {
    echo json_encode([]);
    exit;
}

header('Content-Type: application/json');
echo json_encode($data);
