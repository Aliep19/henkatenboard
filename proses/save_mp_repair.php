<?php
// Disable error output to browser
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');

// Clean any potential output buffer
while (ob_get_level()) {
    ob_end_clean();
}

// Set headers for JSON response
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

try {
    // Include configuration file for database connections
    require_once '../konfigurasi/konfig.php';

    // Get raw input data
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate input
    if (empty($input['mp_repairs']) || empty($input['startdate']) || empty($input['enddate']) || empty($input['id_bagian']) || empty($input['id_line'])) {
        throw new Exception('Data MP Repair, startdate, enddate, id_bagian, atau id_line tidak lengkap');
    }

    // Ensure mp_repairs is an array
    $mp_repairs = (array)$input['mp_repairs'];
    $startdate = $input['startdate'];
    $enddate = $input['enddate'];
    $id_bagian = intval($input['id_bagian']);
    $id_line = intval($input['id_line']);

    // Validate date format
    $start_date = DateTime::createFromFormat('Y-m-d', $startdate);
    $end_date = DateTime::createFromFormat('Y-m-d', $enddate);
    if (!$start_date || !$end_date) {
        throw new Exception('Format tanggal tidak valid');
    }
    if ($start_date > $end_date) {
        throw new Exception('Tanggal mulai harus sebelum tanggal akhir');
    }

    // Validate NPKs against planned manpower (sama seperti kode awal)
    $stmt_check = $conn3->prepare("
        SELECT k.npk
        FROM skillmap_db.karyawan k
        INNER JOIN skillmap_db.karyawan_workstation kw ON k.npk = kw.npk
        WHERE kw.workstation_id = ?
        AND k.npk IN (
            SELECT mp.man_power
            FROM henkaten.mp_procees mp
            INNER JOIN henkaten.hkt_form hf ON mp.id_hkt = hf.id_hkt
            WHERE hf.id_line = ?
            AND (
                (hf.date <= ? AND hf.to_date >= ?) OR
                (hf.date <= ? AND hf.to_date >= ?) OR
                (hf.date >= ? AND hf.to_date <= ?)
            )
        )
        OR k.npk IN (
            SELECT mr.npk
            FROM henkaten.mp_repair mr
            WHERE (
                (mr.startdate <= ? AND mr.enddate >= ?) OR
                (mr.startdate <= ? AND mr.enddate >= ?) OR
                (mr.startdate >= ? AND mr.enddate <= ?)
            )
        )
    ");
    if (!$stmt_check) {
        throw new Exception('Prepare check failed: ' . $conn3->error);
    }

    $stmt_check->bind_param(
        "iissssssssssss",
        $id_bagian,
        $id_line,
        $enddate,
        $startdate,
        $startdate,
        $enddate,
        $startdate,
        $enddate,
        $enddate,
        $startdate,
        $startdate,
        $enddate,
        $startdate,
        $enddate
    );

    if (!$stmt_check->execute()) {
        throw new Exception('Check execute failed: ' . $stmt_check->error);
    }

    $result = $stmt_check->get_result();
    $planned_npks = [];
    while ($row = $result->fetch_assoc()) {
        $planned_npks[] = $row['npk'];
    }
    $stmt_check->close();

    // Check if any selected NPKs are planned
    $invalid_npks = array_intersect($mp_repairs, $planned_npks);
    if (!empty($invalid_npks)) {
        throw new Exception('Manpower dengan NPK ' . implode(', ', $invalid_npks) . ' sudah direncanakan atau ditugaskan sebagai MP Repair');
    }

    // Start transaction
    $conn->begin_transaction();


    // Save new MP Repair data
    $stmt_insert = $conn->prepare("INSERT INTO henkaten.mp_repair (npk, startdate, enddate, id_bagian) VALUES (?, ?, ?, ?)");
    if (!$stmt_insert) {
        throw new Exception('Prepare insert failed: ' . $conn->error);
    }

    foreach ($mp_repairs as $npk) {
        if (empty($npk)) {
            continue;
        }

        // Validate NPK exists in karyawan and workstation
        $stmt_validate = $conn3->prepare("
            SELECT k.npk
            FROM skillmap_db.karyawan k
            INNER JOIN skillmap_db.karyawan_workstation kw ON k.npk = kw.npk
            WHERE k.npk = ?
        ");
        if (!$stmt_validate) {
            throw new Exception('Prepare validate failed: ' . $conn3->error);
        }

        $stmt_validate->bind_param("s", $npk);
        if (!$stmt_validate->execute()) {
            throw new Exception('Validate execute failed: ' . $stmt_validate->error);
        }

        $result = $stmt_validate->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("NPK $npk tidak valid atau tidak terdaftar di workstation ini");
        }
        $stmt_validate->close();

        // Insert MP Repair
        $stmt_insert->bind_param("sssi", $npk, $startdate, $enddate, $id_bagian);
        if (!$stmt_insert->execute()) {
            throw new Exception('Insert execute failed: ' . $stmt_insert->error);
        }
    }
    $stmt_insert->close();

    // Commit transaction
    $conn->commit();

    // Send success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Data MP Repair (' . count($mp_repairs) . ' item) berhasil disimpan'
    ]);

} catch (Exception $e) {
    // Rollback transaction if an error occurred
    if (isset($conn) && $conn->connect_errno === 0) {
        $conn->rollback();
    }

    // Log error
    error_log('Error in save_mp_repair.php: ' . $e->getMessage());

    // Send error response
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

// Close connections
if (isset($conn) && $conn->connect_errno === 0) {
    $conn->close();
}
if (isset($conn3) && $conn3->connect_errno === 0) {
    $conn3->close();
}

// Ensure script ends here
exit();
?>