<?php
require_once '../konfigurasi/konfig.php'; // Pastikan file ini mengatur $conn (henkaten) dan $conn3 (skillmap_db)

header('Content-Type: application/json');

try {
    if (isset($_POST['id_hkt'])) {
        $id_hkt = intval($_POST['id_hkt']);

        if ($id_hkt <= 0) {
            echo json_encode(["error" => "ID HKT tidak valid."]);
            exit;
        }

        // Query utama dengan tambahan kolom status dari tabel process
        $query = "SELECT mp.id, mp.id_hkt, mp.id_proses, mp.man_power, mp.absen, mp.mp_pengganti, 
                         p.name AS proses_name, p.min_skill, p.status AS process_status
                  FROM mp_procees mp
                  LEFT JOIN skillmap_db.process p ON mp.id_proses = p.id
                  WHERE mp.id_hkt = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            echo json_encode(["error" => "Gagal menyiapkan statement untuk mp_procees."]);
            exit;
        }

        $stmt->bind_param("i", $id_hkt);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];

        while ($row = $result->fetch_assoc()) {
            // Skip jika kombinasi proses + man_power sudah ada (hindari duplikat)
            $unique_key = $row['id_proses'] . '_' . $row['man_power'];
            if (isset($data[$unique_key])) {
                continue;
            }

            // Ambil nama karyawan dari skillmap_db.karyawan
            $query_karyawan = "SELECT name FROM karyawan WHERE npk = ?";
            $stmt_karyawan = $conn3->prepare($query_karyawan);

            if (!$stmt_karyawan) {
                echo json_encode(["error" => "Gagal menyiapkan statement untuk karyawan."]);
                exit;
            }

            $stmt_karyawan->bind_param("s", $row['man_power']);
            $stmt_karyawan->execute();
            $result_karyawan = $stmt_karyawan->get_result();

            $man_power_name = $row['man_power'];
            if ($row_karyawan = $result_karyawan->fetch_assoc()) {
                $man_power_name = $row_karyawan['name'];
            }

            $stmt_karyawan->close();

            // Ambil nilai kualifikasi
            $query_qual = "SELECT value AS qualification_value 
                           FROM qualifications 
                           WHERE npk = ? AND process_id = ?";
            $stmt_qual = $conn3->prepare($query_qual);

            if (!$stmt_qual) {
                echo json_encode(["error" => "Gagal menyiapkan statement untuk qualifications."]);
                exit;
            }

            $stmt_qual->bind_param("si", $row['man_power'], $row['id_proses']);
            $stmt_qual->execute();
            $result_qual = $stmt_qual->get_result();

            $qualification_value = 0;
            if ($row_qual = $result_qual->fetch_assoc()) {
                $qualification_value = $row_qual['qualification_value'];
            }

            $stmt_qual->close();

            // Masukkan ke array data tanpa duplikat
            $data[$unique_key] = [
                'id' => $row['id_proses'],
                'id_hkt' => $row['id_hkt'],
                'npk' => $row['man_power'],
                'man_power' => $man_power_name,
                'proses_name' => $row['proses_name'] ?? 'Unknown',
                'min_skill' => $row['min_skill'] ?? 0,
                'status' => $row['absen'],
                'process_status' => $row['process_status'], // Tambahkan status dari tabel process
                'qualification_value' => $qualification_value,
                'mp_pengganti' => $row['mp_pengganti'] ?? null
            ];
        }

        $stmt->close();

        if (empty($data)) {
            echo json_encode(["error" => "Data tidak ditemukan untuk id_hkt: " . $id_hkt]);
        } else {
            echo json_encode(array_values($data)); // Buang key agar jadi array numerik
        }
    } else {
        echo json_encode(["error" => "Parameter id_hkt diperlukan."]);
    }
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}

$conn->close();
$conn3->close();
?>