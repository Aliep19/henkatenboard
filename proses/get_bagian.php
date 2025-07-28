<?php
// Koneksi ke database
require_once '../konfigurasi/konfig.php';


// Query untuk mengambil data workstations
$query_workstations = "SELECT id, name FROM workstations";
$result_workstations = mysqli_query($conn3, $query_workstations);

// Cek jika query berhasil
$workstations = [];
if ($result_workstations) {
    while ($row = mysqli_fetch_assoc($result_workstations)) {
        $workstations[] = $row;  // Menyimpan data dalam array
    }
} else {
    $workstations = ['error' => 'No data found'];  // Jika query gagal
}

// Mengirim data dalam format JSON
echo json_encode($workstations);
