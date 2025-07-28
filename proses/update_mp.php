<?php
header('Content-Type: application/json');
require_once '../konfigurasi/konfig.php';

// Ambil data dari request
$data = json_decode(file_get_contents('php://input'), true);

if (is_array($data)) {
    $response = array();

    foreach ($data as $row) {
        $id_hkt = isset($row['id_hkt']) ? $row['id_hkt'] : null;
        $id_proses = isset($row['id_proses']) ? $row['id_proses'] : null;
        $npk_awal = isset($row['npk_awal']) ? $row['npk_awal'] : null;
        $absen = isset($row['absen']) ? $row['absen'] : null;
        $mp_pengganti = isset($row['mp_pengganti']) ? $row['mp_pengganti'] : null;
        $reason_pengganti = isset($row['reason']) ? $row['reason'] : null;
        $id_shift = isset($row['id_shift']) ? $row['id_shift'] : null;
        $tanggal = isset($row['tanggal']) ? $row['tanggal'] : null;

        // Validasi data
        if (empty($id_hkt) || empty($id_proses) || empty($npk_awal) || empty($id_shift) || empty($tanggal)) {
            $response[] = array('error' => 'Data tidak lengkap untuk NPK Awal: ' . ($npk_awal ? $npk_awal : 'null'));
            continue;
        }

        // Cek apakah id_hkt sesuai dengan rentang tanggal di hkt_form
        $query_check_hkt = "SELECT id_hkt, id_line FROM hkt_form WHERE id_hkt = ? AND ? BETWEEN date AND to_date";
        $stmt_check_hkt = $conn->prepare($query_check_hkt);
        $stmt_check_hkt->bind_param("is", $id_hkt, $tanggal);
        $stmt_check_hkt->execute();
        $result_check_hkt = $stmt_check_hkt->get_result();

        if ($result_check_hkt->num_rows == 0) {
            $response[] = array('error' => 'ID HKT tidak valid atau di luar rentang tanggal untuk NPK Awal: ' . $npk_awal);
            $stmt_check_hkt->close();
            continue;
        }
        $hkt_data = $result_check_hkt->fetch_assoc();
        $id_line = $hkt_data['id_line'];
        $stmt_check_hkt->close();

        // Mapping kode absen ke alasan
        $reason_map = [
            0 => 'Tanpa Keterangan',
            1 => 'Izin',
            2 => 'Cuti',
            3 => 'Train',
            4 => 'Sakit',
            5 => 'Hadir'
        ];
        $reason_text = isset($reason_map[$absen]) ? $reason_map[$absen] : 'Tidak Diketahui';

        // Ambil nama proses dan s_process_id dari tabel process di skillmap_db
        $query_process = "SELECT name, min_skill, s_process_id FROM process WHERE id = ?";
        $stmt_process = $conn3->prepare($query_process);
        $stmt_process->bind_param("i", $id_proses);
        $stmt_process->execute();
        $result_process = $stmt_process->get_result();
        $process_name = '';
        $min_skill = 0;
        $s_process_id = null;
        if ($result_process->num_rows > 0) {
            $process_data = $result_process->fetch_assoc();
            $process_name = $process_data['name'];
            $min_skill = $process_data['min_skill'];
            $s_process_id = $process_data['s_process_id'];
        }
        $stmt_process->close();

        // Tentukan tipe proses untuk notifikasi
        $process_type = ($s_process_id && $s_process_id != 0) ? 's-process' : 'proses';

        // Ambil nama MP awal dan pengganti dari tabel karyawan di skillmap_db
        $mp_awal_name = '';
        $mp_pengganti_name = '';
        if ($npk_awal) {
            $query_karyawan = "SELECT name FROM karyawan WHERE npk = ?";
            $stmt_karyawan = $conn3->prepare($query_karyawan);
            $stmt_karyawan->bind_param("s", $npk_awal);
            $stmt_karyawan->execute();
            $result_karyawan = $stmt_karyawan->get_result();
            if ($result_karyawan->num_rows > 0) {
                $mp_awal_name = $result_karyawan->fetch_assoc()['name'];
            }
            $stmt_karyawan->close();
        }
        if ($mp_pengganti) {
            $query_karyawan = "SELECT name FROM karyawan WHERE npk = ?";
            $stmt_karyawan = $conn3->prepare($query_karyawan);
            $stmt_karyawan->bind_param("s", $mp_pengganti);
            $stmt_karyawan->execute();
            $result_karyawan = $stmt_karyawan->get_result();
            if ($result_karyawan->num_rows > 0) {
                $mp_pengganti_name = $result_karyawan->fetch_assoc()['name'];
            }
            $stmt_karyawan->close();
        }

        // Cek kualifikasi MP pengganti
        $qualification_status = 'Tidak ada MP pengganti';
        if ($mp_pengganti) {
            $query_qualification = "SELECT value FROM qualifications WHERE npk = ? AND process_id = ?";
            $stmt_qualification = $conn3->prepare($query_qualification);
            $stmt_qualification->bind_param("si", $mp_pengganti, $id_proses);
            $stmt_qualification->execute();
            $result_qualification = $stmt_qualification->get_result();
            if ($result_qualification->num_rows > 0) {
                $qualification_value = $result_qualification->fetch_assoc()['value'];
                $qualification_status = ($qualification_value >= $min_skill) 
                    ? "Qualified (Skill: $qualification_value)" 
                    : "Tidak Qualified (Skill: $qualification_value)";
            } else {
                $qualification_status = "Tidak Qualified";
            }
            $stmt_qualification->close();
        }

        // Query INSERT INTO untuk tabel perubahan
        $insertQuery = "INSERT INTO perubahan (id_proses, mp_awal, reason, mp_pengganti, id_shift, tanggal) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param('isisis', $id_proses, $npk_awal, $absen, $mp_pengganti, $id_shift, $tanggal);

        $success = false;

        if ($insertStmt->execute()) {
            $response[] = array('success' => 'Data berhasil ditambahkan ke tabel perubahan untuk NPK Awal: ' . $npk_awal);
            $success = true;
        } else {
            $response[] = array('error' => 'Gagal menambahkan data ke tabel perubahan untuk NPK Awal: ' . $npk_awal);
        }
        $insertStmt->close();

        // Cek apakah data sudah ada di tabel mp_procees
        $checkQuery = "SELECT COUNT(*) as count FROM mp_procees WHERE id_hkt = ? AND id_proses = ? AND man_power = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param('iis', $id_hkt, $id_proses, $npk_awal);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $rowCount = $checkResult->fetch_assoc()['count'];
        $checkStmt->close();

        if ($rowCount > 0) {
            // Query UPDATE untuk mengubah data di mp_procees jika data sudah ada
            $updateQuery = "UPDATE mp_procees 
                            SET absen = ?, mp_pengganti = ? 
                            WHERE id_hkt = ? AND id_proses = ? AND man_power = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param('isiis', $absen, $mp_pengganti, $id_hkt, $id_proses, $npk_awal);

            if ($updateStmt->execute()) {
                if ($updateStmt->affected_rows > 0) {
                    $response[] = array('success' => 'Data berhasil diupdate untuk NPK Awal: ' . $npk_awal);
                    $success = true;
                } else {
                    $response[] = array('info' => 'Tidak ada perubahan untuk NPK Awal: ' . $npk_awal);
                }
            } else {
                $response[] = array('error' => 'Gagal mengupdate data untuk NPK Awal: ' . $npk_awal);
            }
            $updateStmt->close();
        } else {
            // Query INSERT untuk menambahkan data ke mp_procees jika data belum ada
            $insertMpQuery = "INSERT INTO mp_procees (id_hkt, id_proses, man_power, absen, mp_pengganti) 
                              VALUES (?, ?, ?, ?, ?)";
            $insertMpStmt = $conn->prepare($insertMpQuery);
            $insertMpStmt->bind_param('iisis', $id_hkt, $id_proses, $npk_awal, $absen, $mp_pengganti);

            if ($insertMpStmt->execute()) {
                $response[] = array('success' => 'Data berhasil ditambahkan ke tabel mp_procees untuk NPK Awal: ' . $npk_awal);
                $success = true;
            } else {
                $response[] = array('error' => 'Gagal menambahkan data ke tabel mp_procees untuk NPK Awal: ' . $npk_awal);
            }
            $insertMpStmt->close();
        }

        // Jika ada MP pengganti dan operasi berhasil, buat notifikasi
        if ($mp_pengganti && $success) {
            // Langkah 1: Ambil departemen berdasarkan id_line
            $query_dept = "SELECT d.dept_name 
                           FROM skillmap_db.sub_workstations sw 
                           JOIN skillmap_db.workstations w ON sw.workstation_id = w.id 
                           JOIN skillmap_db.department d ON w.dept_id = d.id 
                           WHERE sw.id = ?";
            $stmt_dept = $conn3->prepare($query_dept);
            $stmt_dept->bind_param("i", $id_line);
            $stmt_dept->execute();
            $result_dept = $stmt_dept->get_result();
            $dept = '';
            if ($result_dept->num_rows > 0) {
                $dept = $result_dept->fetch_assoc()['dept_name'];
            } else {
                error_log("Departemen tidak ditemukan untuk id_line: $id_line");
            }
            $stmt_dept->close();

            // Langkah 2: Ambil nomor WA dari ct_users (lembur) dan cocokkan dengan otp (henkaten) untuk golongan 3 dan 4 di dept yang sesuai atau QA
            if ($dept) {
                $query_wa = "SELECT DISTINCT o.no_hp 
                             FROM lembur.ct_users u 
                             JOIN henkaten.otp o ON u.npk = o.npk 
                             WHERE u.dept = 'qa' OR u.dept = ? AND u.golongan = 4";
                $stmt_wa = $conn2->prepare($query_wa);
                $stmt_wa->bind_param("s", $dept);
                $stmt_wa->execute();
                $result_wa = $stmt_wa->get_result();

                if ($result_wa->num_rows > 0) {
                    // Format alasan untuk pesan notifikasi
                    $reason_text_pengganti = '';
                    if ($reason_pengganti === 'not_qualified') {
                        $reason_text_pengganti = 'Dipilih karena tidak ada MP qualified';
                    } elseif ($reason_pengganti === 'ojt') {
                        $reason_text_pengganti = 'Sedang Training (OJT)';
                    }

                    $message = "Terjadi perubahan MP di {$process_type} '$process_name' karena '$reason_text'. " .
                               "MP awal: $mp_awal_name ($npk_awal) digantikan oleh $mp_pengganti_name ($mp_pengganti). " .
                               "Status kualifikasi: $qualification_status." .
                               ($reason_text_pengganti ? " Alasan pemilihan: $reason_text_pengganti." : "");

                    while ($row_wa = $result_wa->fetch_assoc()) {
                        $no_wa = $row_wa['no_hp'];

                        $insert_notif_query = "INSERT INTO notification_log (no_wa, message, created_at) VALUES (?, ?, NOW())";
                        $stmt_notif = $conn->prepare($insert_notif_query);
                        $stmt_notif->bind_param("ss", $no_wa, $message);
                        if (!$stmt_notif->execute()) {
                            error_log("Gagal menyimpan notifikasi untuk NPK Awal: $npk_awal ke nomor: $no_wa");
                            $response[] = array('error' => 'Gagal menyimpan notifikasi untuk NPK Awal: ' . $npk_awal . ' ke nomor: ' . $no_wa);
                        }
                        $stmt_notif->close();
                    }
                } else {
                    error_log("Tidak ada pengguna dengan golongan 3 atau 4 ditemukan untuk dept: $dept atau QA");
                    $response[] = array('error' => 'Tidak ada nomor WA untuk departemen QA atau dept: ' . $dept . ' ditemukan untuk NPK Awal: ' . $npk_awal);
                }
                $stmt_wa->close();
            } else {
                $response[] = array('error' => 'Departemen tidak ditemukan untuk id_line: ' . $id_line . ' untuk NPK Awal: ' . $npk_awal);
            }
        }
    }

    echo json_encode($response);
} else {
    echo json_encode(array('error' => 'Invalid data format.'));
}

$conn->close();
$conn2->close();
$conn3->close();
?>