<?php
require_once '../konfigurasi/konfig.php';
header('Content-Type: application/json');

try {
    if (!isset($_POST['id_hkt']) || !isset($_POST['id_bagian']) || !isset($_POST['id_shift']) || !isset($_POST['tanggal'])) {
        throw new Exception("Parameter id_hkt, id_bagian, id_shift, dan tanggal diperlukan.");
    }

    $id_hkt = intval($_POST['id_hkt']);
    $id_bagian = intval($_POST['id_bagian']); // This is workstation_id from workstations table
    $id_shift = intval($_POST['id_shift']);
    $tanggal = $_POST['tanggal'];

    // Validate HKT form
    $query_hkt = "SELECT date, id_line FROM hkt_form WHERE id_hkt = ?";
    $stmt_hkt = $conn->prepare($query_hkt);
    $stmt_hkt->bind_param("i", $id_hkt);
    $stmt_hkt->execute();
    $result_hkt = $stmt_hkt->get_result();

    if ($result_hkt->num_rows === 0) {
        throw new Exception("HKT form tidak ditemukan");
    }

    $hkt_data = $result_hkt->fetch_assoc();
    $tanggal_hkt = $hkt_data['date'];
    $id_line = $hkt_data['id_line'];
    $stmt_hkt->close();

    // Fetch employees from all sub-workstations under the specified workstation
    $query_workstation = "
        SELECT DISTINCT k.npk, k.name
        FROM skillmap_db.karyawan k
        INNER JOIN skillmap_db.karyawan_workstation kw ON k.npk = kw.npk
        INNER JOIN skillmap_db.sub_workstations sw ON kw.workstation_id = sw.id
        WHERE sw.workstation_id = ?
    ";
    $stmt_workstation = $conn3->prepare($query_workstation);
    if (!$stmt_workstation) {
        throw new Exception("Gagal menyiapkan query workstation: " . $conn3->error);
    }
    $stmt_workstation->bind_param("i", $id_bagian);
    $stmt_workstation->execute();
    $result_workstation = $stmt_workstation->get_result();

    $npk_list = [];
    while ($row = $result_workstation->fetch_assoc()) {
        $npk_list[] = $row['npk'];
    }
    $stmt_workstation->close();

    if (empty($npk_list)) {
        echo json_encode(["error" => "Tidak ada manpower di workstation ini"]);
        exit;
    }

    // Fetch employee details and qualifications for all MPs in the workstation
    $placeholders = implode(',', array_fill(0, count($npk_list), '?'));
    $query_karyawan = "
        SELECT 
            k.npk, 
            k.name,
            q.process_id,
            q.value AS qualification_value,
            p.id AS proc_id,
            p.s_process_id AS s_process,
            p.min_skill AS process_min_skill
        FROM 
            skillmap_db.karyawan k
        LEFT JOIN 
            skillmap_db.qualifications q ON k.npk = q.npk
        LEFT JOIN 
            skillmap_db.process p ON p.id = q.process_id AND p.s_process_id = ?
        WHERE 
            k.npk IN ($placeholders)
    ";

    $stmt_karyawan = $conn3->prepare($query_karyawan);
    if (!$stmt_karyawan) {
        throw new Exception("Gagal menyiapkan query karyawan: " . $conn3->error);
    }

    $types = 's' . str_repeat('s', count($npk_list));
    $params = array_merge([$id_line], $npk_list);
    $stmt_karyawan->bind_param($types, ...$params);

    if (!$stmt_karyawan->execute()) {
        throw new Exception("Eksekusi query karyawan gagal: " . $stmt_karyawan->error);
    }

    $result_karyawan = $stmt_karyawan->get_result();
    $mp_repair = [];
    $current_npk = null;
    $current_mp = null;

    while ($row = $result_karyawan->fetch_assoc()) {
        if ($row['npk'] !== $current_npk) {
            if ($current_mp) {
                $mp_repair[] = $current_mp;
            }
            $current_npk = $row['npk'];
            $current_mp = [
                'npk' => $row['npk'],
                'name' => $row['name'],
                'qualifications' => []
            ];
        }
        if ($row['process_id']) {
            $current_mp['qualifications'][] = [
                'process_id' => $row['process_id'],
                'qualification_value' => $row['qualification_value'] ?? 0,
                'process_min_skill' => $row['process_min_skill'] ?? 0
            ];
        }
    }
    if ($current_mp) {
        $mp_repair[] = $current_mp;
    }

    $stmt_karyawan->close();

    if (empty($mp_repair)) {
        echo json_encode(["error" => "Tidak ada data karyawan yang sesuai"]);
    } else {
        echo json_encode($mp_repair);
    }

} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    echo json_encode(["error" => $e->getMessage()]);
}

$conn->close();
$conn3->close();
?>