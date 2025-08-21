<?php
require_once '../konfigurasi/konfig.php'; // Mengatur $conn (henkaten) dan $conn3 (skillmap_db)

header('Content-Type: application/json');

// Validasi input
if (!isset($_POST['id_hkt']) || !is_numeric($_POST['id_hkt'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parameter id_hkt diperlukan dan harus numerik.']);
    exit;
}

try {
    $id_hkt = (int) $_POST['id_hkt'];

    // Query untuk mengambil data dari hkt_form
    $query = "SELECT foreman, foreman_2, line_guide, line_guide2, date FROM hkt_form WHERE id_hkt = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id_hkt);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Fungsi untuk mendapatkan nama karyawan berdasarkan NPK
        $getEmployeeName = function($npk) use ($conn3) {
            if (empty($npk)) {
                return null;
            }

            // Tambahkan "0" di depan NPK hingga panjang 5 digit (misalnya, 1502 jadi 01502)
            $npk_padded = str_pad($npk, 5, '0', STR_PAD_LEFT);
            $query = "SELECT name FROM karyawan WHERE npk = ?";
            $stmt = $conn3->prepare($query);
            $stmt->bind_param('s', $npk_padded);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            return $row ? ['npk' => $npk_padded, 'name' => $row['name']] : null;
        };

        // Siapkan data foreman dan line guide
        $support_data = [];
        $roles = ['foreman' => 'Foreman', 'foreman_2' => 'Foreman 2', 'line_guide' => 'Line Guide', 'line_guide2' => 'Line Guide 2'];
        foreach ($roles as $key => $role) {
            if ($employee = $getEmployeeName($row[$key])) {
                $support_data[] = [
                    'role' => $role,
                    'npk' => $employee['npk'],
                    'name' => $employee['name']
                ];
            }
        }

        // Siapkan response
        $response = [
            'id_hkt' => $id_hkt,
            'tanggal' => $row['date'],
            'support' => $support_data
        ];

        echo json_encode($response);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Data tidak ditemukan untuk id_hkt: ' . $id_hkt]);
    }

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
$conn3->close();
?>