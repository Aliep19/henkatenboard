
<?php
header('Content-Type: application/json');
require_once '../konfigurasi/konfig.php';

// Ambil data dari request
$data = json_decode(file_get_contents('php://input'), true);

if (is_array($data)) {
    $response = array();

    foreach ($data as $row) {
        $id_hkt = isset($row['id_hkt']) ? $row['id_hkt'] : null;
        $npk_awal = isset($row['npk_awal']) ? $row['npk_awal'] : null;
        $absen = isset($row['absen']) ? $row['absen'] : null;
        $npk_pengganti = isset($row['npk_pengganti']) ? $row['npk_pengganti'] : null;
        $tanggal = isset($row['tanggal']) ? $row['tanggal'] : null;

        // Validasi data
        if (empty($id_hkt) || empty($npk_awal) || empty($tanggal)) {
            $response[] = array('error' => 'Data tidak lengkap untuk NPK Awal: ' . ($npk_awal ? $npk_awal : 'null'));
            continue;
        }

        // Cek apakah id_hkt sesuai dengan rentang tanggal di hkt_form
        $query_check_hkt = "SELECT id_hkt FROM hkt_form WHERE id_hkt = ? AND ? BETWEEN date AND to_date";
        $stmt_check_hkt = $conn->prepare($query_check_hkt);
        $stmt_check_hkt->bind_param("is", $id_hkt, $tanggal);
        $stmt_check_hkt->execute();
        $result_check_hkt = $stmt_check_hkt->get_result();

        if ($result_check_hkt->num_rows == 0) {
            $response[] = array('error' => 'ID HKT tidak valid atau di luar rentang tanggal untuk NPK Awal: ' . $npk_awal);
            $stmt_check_hkt->close();
            continue;
        }
        $stmt_check_hkt->close();

        // Query INSERT INTO untuk tabel absen_support
        $insertQuery = "INSERT INTO absen_support (id_hkt, npk, absen, pengganti, tanggal) 
                        VALUES (?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param('isiss', $id_hkt, $npk_awal, $absen, $npk_pengganti, $tanggal);

        if ($insertStmt->execute()) {
            $response[] = array('success' => 'Data berhasil ditambahkan ke tabel absen_support untuk NPK Awal: ' . $npk_awal);
        } else {
            $response[] = array('error' => 'Gagal menambahkan data ke tabel absen_support untuk NPK Awal: ' . $npk_awal);
        }
        $insertStmt->close();
    }

    echo json_encode($response);
} else {
    echo json_encode(array('error' => 'Invalid data format.'));
}

$conn->close();
$conn3->close();
?>
