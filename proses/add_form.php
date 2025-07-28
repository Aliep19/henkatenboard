<?php
require_once '../konfigurasi/konfig.php';

// Decode JSON data
$formData = json_decode($_POST['formData'], true);

// Validasi data kosong
if (empty($formData['tanggal']) || empty($formData['tanggal_akhir'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Tanggal dan tanggal akhir wajib diisi."]);
    exit;
}

// Prepare and bind query untuk `hkt_form`
$sql = $conn->prepare("INSERT INTO hkt_form (date, to_date, id_bagian, id_line, id_shifft, output_target, foreman, foreman_2, line_guide, line_guide2) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
if ($sql === false) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Kesalahan dalam menyiapkan query SQL: " . $conn->error]);
    exit;
}

$date = date('Y-m-d', strtotime($formData['tanggal']));
$to_date = date('Y-m-d', strtotime($formData['tanggal_akhir']));
$sql->bind_param(
    "ssiiiiiiii",
    $date,
    $to_date,
    $formData['id_bagian'],
    $formData['id_line'],
    $formData['id_shift'],
    $formData['output_target'],
    $formData['foreman1'],
    $formData['foreman2'],
    $formData['line_guide1'],
    $formData['line_guide2']
);

if ($sql->execute()) {
    $last_id_hkt = $conn->insert_id;
    
    // Array untuk menyimpan notifikasi
    $notifications = [];
    
    // Insert data ke `mp_procees` jika ada man_power
    if (!empty($formData['man_power'])) {
        $mp_sql = $conn->prepare("INSERT INTO mp_procees (id_hkt, id_proses, man_power, absen) VALUES (?, ?, ?, ?)");
        if ($mp_sql === false) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Kesalahan dalam query SQL untuk mp_procees: " . $conn->error]);
            exit;
        }
        
        foreach ($formData['man_power'] as $mp) {
            $id_proses = $mp['process_id'];
            $manpower = $mp['manpower'];
            $reason = isset($mp['reason']) ? $mp['reason'] : ''; // Ambil alasan dari formData
            $absen = 0; // Nilai default untuk absen (0 = hadir)
            $mp_sql->bind_param("iisi", $last_id_hkt, $id_proses, $manpower, $absen);
            if (!$mp_sql->execute()) {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "Gagal menyimpan man_power untuk id_hkt: {$last_id_hkt}, id_proses: {$id_proses}. " . $mp_sql->error]);
                exit;
            }
            
            // Ambil nama proses dan min_skill dari tabel process (skillmap_db)
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
            
            // Ambil nama manpower dari tabel karyawan (skillmap_db)
            $query_karyawan = "SELECT name FROM karyawan WHERE npk = ?";
            $stmt_karyawan = $conn3->prepare($query_karyawan);
            $stmt_karyawan->bind_param("s", $manpower);
            $stmt_karyawan->execute();
            $result_karyawan = $stmt_karyawan->get_result();
            $manpower_name = '';
            if ($result_karyawan->num_rows > 0) {
                $manpower_name = $result_karyawan->fetch_assoc()['name'];
            }
            $stmt_karyawan->close();
            
            // Tentukan tipe proses untuk notifikasi
            $process_type = ($s_process_id && $s_process_id != 0) ? 's-process' : 'proses';
            
            // Cek kualifikasi manpower
            $qualification_status = 'Qualified';
            $qualification_value = 0;
            $reason_text = '';
            $query_qualification = "SELECT value FROM qualifications WHERE npk = ? AND process_id = ?";
            $stmt_qualification = $conn3->prepare($query_qualification);
            $stmt_qualification->bind_param("si", $manpower, $id_proses);
            $stmt_qualification->execute();
            $result_qualification = $stmt_qualification->get_result();
            if ($result_qualification->num_rows > 0) {
                $qualification_value = $result_qualification->fetch_assoc()['value'];
                if ($qualification_value < $min_skill) {
                    // MP underqualified (merah), s-process atau non-s-process
                    $qualification_status = "Tidak Qualified (Skill: $qualification_value, Dibutuhkan: $min_skill)";
                    $reason_text = 'Dipilih karena tidak ada MP qualified';
                    $notifications[] = [
                        'process_name' => $process_name,
                        'process_type' => $process_type,
                        'manpower_name' => $manpower_name,
                        'npk' => $manpower,
                        'qualification_status' => $qualification_status,
                        'reason_text' => $reason_text
                    ];
                }
            } else {
                // MP tanpa kualifikasi (merah), s-process atau non-s-process
                $qualification_status = "Tidak Qualified (Tidak ada kualifikasi)";
                $reason_text = 'Dipilih karena tidak ada MP qualified';
                $notifications[] = [
                    'process_name' => $process_name,
                    'process_type' => $process_type,
                    'manpower_name' => $manpower_name,
                    'npk' => $manpower,
                    'qualification_status' => $qualification_status,
                    'reason_text' => $reason_text
                    ];
            }
            $stmt_qualification->close();
            
            // Cek sertifikasi untuk s-process (hanya untuk MP yang qualified dan kuning)
            if ($s_process_id && $s_process_id != 0 && $qualification_value >= $min_skill) {
                $query_certification = "SELECT npk FROM s_process_certification WHERE npk = ? AND s_process_id = ?";
                $stmt_certification = $conn3->prepare($query_certification);
                $stmt_certification->bind_param("si", $manpower, $s_process_id);
                $stmt_certification->execute();
                $result_certification = $stmt_certification->get_result();
                if ($result_certification->num_rows == 0) {
                    // MP uncertified di s-process (kuning)
                    $reason_text = ($reason === 'not_certified') ? 'Belum mempunyai sertifikasi' : ($reason === 'ojt' ? 'Sedang masa OJT' : 'Belum tersertifikasi');
                    $qualification_status = "Belum tersertifikasi";
                    $notifications[] = [
                        'process_name' => $process_name,
                        'process_type' => $process_type,
                        'manpower_name' => $manpower_name,
                        'npk' => $manpower,
                        'qualification_status' => $qualification_status,
                        'reason_text' => $reason_text
                    ];
                }
                $stmt_certification->close();
            }
        }
        $mp_sql->close();
    }
    
    // Jika ada notifikasi, kirim ke nomor WhatsApp pengguna dengan golongan 3 atau 4 di departemen yang sesuai
    if (!empty($notifications)) {
        // Langkah 1: Ambil departemen berdasarkan id_line
        // id_line merujuk ke sub_workstations.id
        $query_dept = "SELECT d.dept_name 
                       FROM skillmap_db.sub_workstations sw 
                       JOIN skillmap_db.workstations w ON sw.workstation_id = w.id 
                       JOIN skillmap_db.department d ON w.dept_id = d.id 
                       WHERE sw.id = ?";
        $stmt_dept = $conn3->prepare($query_dept);
        $stmt_dept->bind_param("i", $formData['id_line']);
        $stmt_dept->execute();
        $result_dept = $stmt_dept->get_result();
        $dept = '';
        if ($result_dept->num_rows > 0) {
            $dept = $result_dept->fetch_assoc()['dept_name'];
        } else {
            // Catat error jika departemen tidak ditemukan
            error_log("Departemen tidak ditemukan untuk id_line: {$formData['id_line']}");
        }
        $stmt_dept->close();

        // Langkah 2: Ambil nomor WA dari ct_users (lembur) dan cocokkan dengan otp (henkaten) untuk golongan 3 dan 4 di dept yang sesuai
        if ($dept) {
            $query_wa = "SELECT DISTINCT o.no_hp 
                         FROM lembur.ct_users u 
                         JOIN henkaten.otp o ON u.npk = o.npk 
                         WHERE u.dept = 'qa' OR u.dept = ? AND u.golongan IN (3, 4)";
            $stmt_wa = $conn2->prepare($query_wa);
            $stmt_wa->bind_param("s", $dept);
            $stmt_wa->execute();
            $result_wa = $stmt_wa->get_result();
            
            if ($result_wa->num_rows > 0) {
                while ($row_wa = $result_wa->fetch_assoc()) {
                    $no_wa = $row_wa['no_hp'];
                    
                    foreach ($notifications as $notif) {
                        $message = "Ada perubahan MP di {$notif['process_type']} '{$notif['process_name']}' karena manpower tidak memenuhi syarat. " .
                                   "MP: {$notif['manpower_name']} ({$notif['npk']}). " .
                                   "Status kualifikasi: {$notif['qualification_status']}. " .
                                   ($notif['reason_text'] ? "Alasan pemilihan: {$notif['reason_text']}. " : "") .
                                   "Planning ini berdasarkan tanggal mulai: $date dan tanggal berakhir: $to_date.";
                        
                        $insert_notif_query = "INSERT INTO notification_log (no_wa, message, created_at) VALUES (?, ?, NOW())";
                        $stmt_notif = $conn->prepare($insert_notif_query);
                        $stmt_notif->bind_param("ss", $no_wa, $message);
                        if (!$stmt_notif->execute()) {
                            // Catat error tapi lanjutkan proses
                            error_log("Gagal menyimpan notifikasi untuk NPK: {$notif['npk']} ke nomor: $no_wa");
                        }
                        $stmt_notif->close();
                    }
                }
            } else {
                // Catat bahwa tidak ada pengguna yang ditemukan untuk dept ini
                error_log("Tidak ada pengguna dengan golongan 3 atau 4 ditemukan untuk dept: $dept");
            }
            $stmt_wa->close();
        }
    }
    
    echo json_encode(["status" => "success", "message" => "Data berhasil disimpan.", "id_hkt" => $last_id_hkt]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Kesalahan dalam menyimpan data ke hkt_form: " . $sql->error]);
}

$sql->close();
$conn->close();
$conn2->close();
$conn3->close();
?>