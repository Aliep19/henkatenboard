<?php
require_once '../konfigurasi/konfig.php'; // Mengubah path untuk koneksi database

// Cek apakah id_bagian dikirim melalui POST
if (isset($_POST['id_bagian'])) {
    $id_bagian = $_POST['id_bagian'];

    // Query join untuk mengambil data dari sub_workstations berdasarkan workstation_id
    $query = "
        SELECT sub_workstations.id, sub_workstations.name 
        FROM sub_workstations
        INNER JOIN workstations ON sub_workstations.workstation_id = workstations.id
        WHERE sub_workstations.workstation_id = ?
    ";
    
    // Prepare statement untuk menghindari SQL Injection
    $stmt = $conn3->prepare($query);
    $stmt->bind_param("i", $id_bagian); // Bind parameter id_bagian
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Cek apakah hasil query memiliki data
    if ($result->num_rows > 0) {
        $lines = array();
        // Ambil setiap baris data
        while ($row = $result->fetch_assoc()) {
            $lines[] = $row;
        }
        // Kirim data sebagai JSON kembali ke AJAX
        echo json_encode($lines);
    } else {
        // Jika tidak ada data yang ditemukan
        echo json_encode([]);
    }
} else {
    echo json_encode(["error" => "ID bagian tidak diterima"]);
}
?>
