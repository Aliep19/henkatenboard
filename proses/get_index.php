<?php
require_once '../konfigurasi/konfig.php'; // Pastikan koneksi database benar

if (isset($_GET['id_hkt'])) {
    $id_hkt = intval($_GET['id_hkt']); // Pastikan id_hkt berupa integer untuk keamanan

    $query = "
        SELECT 
            hf.id_line, 
            hf.id_shifft, 
            s.jam_kerja, 
            hf.output_target
        FROM hkt_form hf
        LEFT JOIN shift s ON hf.id_shifft = s.id_shift
        WHERE hf.id_hkt = ?";

    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $id_hkt);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            echo json_encode($data);
        } else {
            echo json_encode(['error' => 'Data not found']);
        }
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Query preparation failed']);
    }
} else {
    echo json_encode(['error' => 'ID HKT not provided']);
}
