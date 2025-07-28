<?php
// Koneksi ke database
require_once '../konfigurasi/konfig.php';

if (isset($_POST['id_proses']) && is_array($_POST['id_proses'])) {
    $id_proses_list = $_POST['id_proses'];

    // Validasi input
    $placeholders = implode(',', array_fill(0, count($id_proses_list), '?'));
    $types = str_repeat('i', count($id_proses_list)); // Semua ID adalah integer

    // Query untuk mendapatkan data proses
    $query = "SELECT id, name, min_skill FROM process WHERE id IN ($placeholders)";
    $stmt = $conn3->prepare($query);

    if ($stmt) {
        $stmt->bind_param($types, ...$id_proses_list);
        $stmt->execute();
        $result = $stmt->get_result();

        $response = [];
        while ($row = $result->fetch_assoc()) {
            $id_proses = $row['id'];

            // Query tambahan untuk mendapatkan semua data dari tabel qualifications berdasarkan process_id
            $query_qualifications = "SELECT npk, value FROM qualifications WHERE process_id = ?";
            $stmt_qualifications = $conn3->prepare($query_qualifications);
            $stmt_qualifications->bind_param("i", $id_proses);
            $stmt_qualifications->execute();
            $result_qualifications = $stmt_qualifications->get_result();

            $qualifications = [];
            while ($qual_row = $result_qualifications->fetch_assoc()) {
                $qualifications[] = [
                    'npk' => $qual_row['npk'],
                    'value' => $qual_row['value']
                ];
            }

            // Simpan hasil ke response
            $response[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'min_skill' => $row['min_skill'],
                'qualifications' => $qualifications // Tambahkan semua data qualifications
            ];
        }

        echo json_encode(['success' => true, 'data' => $response]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
    }

    $stmt->close();
    $conn3->close();
} else {
    echo json_encode(['success' => false, 'message' => 'ID Proses tidak valid']);
}
