<?php
require_once '../konfigurasi/konfig.php';
header('Content-Type: application/json');
ob_clean();

try {
    /** ================= HELPER ================= */
    function json($data) {
        echo json_encode($data); exit;
    }

    function getEmployeeName($conn3, $npk) {
        $stmt = $conn3->prepare("SELECT name FROM karyawan WHERE npk = ?");
        if (!$stmt) return $npk;
        $stmt->bind_param("s", $npk);
        $stmt->execute();
        $name = $stmt->get_result()->fetch_assoc()['name'] ?? $npk;
        $stmt->close();
        return $name;
    }

    /** ================= FETCH DATA ================= */
    function fetchPerubahanData($conn, $conn3, $id_hkt) {
        $sql = "SELECT p.id_perubahan, p.id_proses, p.mp_awal, p.reason, p.mp_pengganti, p.tanggal, p.id_shift,
                       pr.name AS proses_name, pr.min_skill, pr.status AS process_status,
                       q.value AS qualification_value
                FROM perubahan p
                LEFT JOIN skillmap_db.process pr ON p.id_proses = pr.id
                LEFT JOIN skillmap_db.qualifications q ON p.mp_awal = q.npk AND p.id_proses = q.process_id
                LEFT JOIN mp_procees mp ON p.id_proses = mp.id_proses AND mp.id_hkt = ?
                WHERE mp.id_hkt = ? AND p.tanggal = CURDATE()";

        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception("Prepare gagal: " . $conn->error);
        $stmt->bind_param("ii", $id_hkt, $id_hkt);
        $stmt->execute();
        $res = $stmt->get_result();

        $data = [];
        while ($r = $res->fetch_assoc()) {
            $id = $r['id_perubahan'];
            if (isset($data[$id])) continue;

            $data[$id] = [
                'id_perubahan'       => $id,
                'id_proses'          => $r['id_proses'],
                'proses_name'        => $r['proses_name'] ?? 'Unknown',
                'mp_awal'            => getEmployeeName($conn3, $r['mp_awal']),
                'npk_awal'           => $r['mp_awal'],
                'reason'             => $r['reason'],
                'mp_pengganti'       => $r['mp_pengganti'] ? getEmployeeName($conn3, $r['mp_pengganti']) . " ({$r['mp_pengganti']})" : null,
                'npk_pengganti'      => $r['mp_pengganti'],
                'tanggal'            => $r['tanggal'],
                'id_shift'           => $r['id_shift'],
                'process_status'     => $r['process_status'],
                'min_skill'          => $r['min_skill'],
                'qualification_value'=> $r['qualification_value'] ?? 0
            ];
        }
        $stmt->close();
        return array_values($data);
    }

    /** ================= UPDATE DATA ================= */
    function updateReason($conn, $id, $reason) {
        $stmt = $conn->prepare("UPDATE perubahan SET reason = ? WHERE id_perubahan = ?");
        if (!$stmt) return false;
        $stmt->bind_param("ii", $reason, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    function updateMultiple($conn, $updates) {
        $success = 0;
        foreach ($updates as $u) {
            $id = intval($u['id_perubahan']);
            $reason = intval($u['reason']);
            if ($id > 0 && in_array($reason, [0,1,2,3,4,5])) {
                if (updateReason($conn, $id, $reason)) $success++;
            }
        }
        return $success;
    }

    /** ================= MAIN ================= */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? null;

        if ($action === 'update_reason') {
            $id = intval($_POST['id_perubahan']);
            $reason = intval($_POST['reason']);
            if ($id <= 0 || !in_array($reason, [0,1,2,3,4,5])) json(["error"=>"Parameter tidak valid."]);
            json(updateReason($conn, $id, $reason) 
                ? ["success"=>"Reason berhasil diperbarui."] 
                : ["error"=>"Gagal memperbarui reason."]);
        }

        if ($action === 'update_multiple') {
            $updates = $_POST['updates'] ?? [];
            if (!is_array($updates)) json(["error"=>"Data update tidak valid."]);
            $ok = updateMultiple($conn, $updates);
            json([
                "success"=>"Berhasil memperbarui $ok dari ".count($updates)." data.",
                "updated"=>$ok,
                "total"=>count($updates)
            ]);
        }
    }

    // Ambil data by id_hkt
    $id_hkt = intval($_POST['id_hkt'] ?? 0);
    if ($id_hkt <= 0) json(["error"=>"Parameter id_hkt diperlukan."]);

    $data = fetchPerubahanData($conn, $conn3, $id_hkt);
if (empty($data)) {
    $stmt = $conn->prepare("SELECT COUNT(*) as c FROM perubahan 
                            WHERE id_proses IN (SELECT id_proses FROM mp_procees WHERE id_hkt = ?) 
                            AND tanggal = CURDATE()");
    $stmt->bind_param("i", $id_hkt);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['c'];
    $stmt->close();

    json(["error" => "Belum ada absensi untuk line ini di tanggal ini"]);
}


    json($data);

} catch (Exception $e) {
    json(["error"=>$e->getMessage()]);
}

$conn->close();
$conn3->close();
