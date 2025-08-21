<?php
header('Content-Type: text/html; charset=UTF-8');
require_once '../konfigurasi/konfig.php';

// Helper functions (Laravel-like)
function query($conn, $sql, $params = [], $types = '') {
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return ['error' => $conn->error];
    }
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
}

function getProcessData($conn, $id_proses) {
    $result = query($conn, "SELECT name, status, min_skill FROM process WHERE id = ?", [$id_proses], 'i');
    return $result instanceof mysqli_result && $result->num_rows > 0 ? $result->fetch_assoc() : ['name' => 'Unknown', 'status' => 0, 'min_skill' => 0];
}

function getEmployeeName($conn, $npk) {
    $result = query($conn, "SELECT name FROM karyawan WHERE npk = ?", [$npk], 's');
    return $result instanceof mysqli_result && $result->num_rows > 0 ? $result->fetch_assoc()['name'] : $npk;
}

function getQualification($conn, $npk, $process_id, $min_skill) {
    $result = query($conn, "SELECT value FROM qualifications WHERE npk = ? AND process_id = ?", [$npk, $process_id], 'si');
    if ($result instanceof mysqli_result && $result->num_rows > 0) {
        $value = $result->fetch_assoc()['value'];
        return [
            'status' => $value >= $min_skill ? 'Qualified' : 'Not Qualified',
            'color' => $value >= $min_skill ? 'bg-success' : 'bg-danger'
        ];
    }
    return ['status' => 'Not Qualified', 'color' => 'bg-danger'];
}

// Ambil parameter dari request
$line_id = isset($_POST['line_id']) ? intval($_POST['line_id']) : 0;
$selected_date = isset($_POST['selected_date']) ? $_POST['selected_date'] : date('Y-m-d');

// Validasi input
if (!$line_id || !$selected_date) {
    echo "
    <tr>
        <td colspan='8' class='text-center text-muted fst-italic py-4'>
            Pilih Line dan pastikan Tanggal tersedia
        </td>
    </tr>";
    exit;
}

// Query untuk mengambil data dari tabel perubahan di henkaten, hanya reason != 5, tanpa filter shift
$query_history = "
    SELECT 
        p.tanggal,
        p.id_proses,
        p.mp_awal,
        p.reason,
        p.mp_pengganti,
        p.id_shift,
        p.checked,
        s.shift
    FROM perubahan p
    JOIN hkt_form h ON h.id_hkt = (
        SELECT id_hkt 
        FROM mp_procees 
        WHERE id_proses = p.id_proses 
        LIMIT 1
    )
    JOIN shift s ON p.id_shift = s.id_shift
    WHERE h.id_line = ? 
    AND p.tanggal = ?
    AND p.reason != 5
    ORDER BY p.tanggal DESC, s.shift
";

$result_history = query($conn, $query_history, [$line_id, $selected_date], 'is');
if (is_array($result_history) && isset($result_history['error'])) {
    echo "<tr><td colspan='8' class='text-center text-muted fst-italic py-4'>Error: " . htmlspecialchars($result_history['error']) . "</td></tr>";
    exit;
}

if ($result_history && $result_history->num_rows > 0) {
    while ($row = $result_history->fetch_assoc()) {
    // Ambil nama proses dan min_skill dari tabel process di skillmap_db
    $process_data = getProcessData($conn3, $row['id_proses']);
    $process_name = $process_data['name'];
    $status = $process_data['status'];
    $min_skill = $process_data['min_skill'];

    // Ambil nama karyawan untuk mp_awal
    $mp_awal_name = getEmployeeName($conn3, $row['mp_awal']);

    // Ambil nama karyawan untuk mp_pengganti
    $mp_pengganti_name = $row['mp_pengganti'];
    $qualification_status = '-';
    $qualification_color = '';
    if ($row['mp_pengganti']) {
        $mp_pengganti_name = getEmployeeName($conn3, $row['mp_pengganti']);
        // Ambil kualifikasi mp_pengganti
        $qualification = getQualification($conn3, $row['mp_pengganti'], $row['id_proses'], $min_skill);
        $qualification_status = $qualification['status'];
        $qualification_color = $qualification['color'];
    }

    // Konversi reason ke teks
    $reason_text = '';
    switch ($row['reason']) {
        case 0: $reason_text = 'Tanpa Keterangan'; break;
        case 1: $reason_text = 'Izin'; break;
        case 2: $reason_text = 'Cuti'; break;
        case 3: $reason_text = 'Train'; break;
        case 4: $reason_text = 'Sakit'; break;
        default: $reason_text = 'Tidak Diketahui';
    }

    // Format nama dan NPK untuk kolom BEFORE dan AFTER
    $before = $mp_awal_name ? htmlspecialchars($mp_awal_name . ' - ' . $row['mp_awal']) : htmlspecialchars($row['mp_awal']);
    $after = $row['mp_pengganti'] ? ($mp_pengganti_name ? htmlspecialchars($mp_pengganti_name . ' - ' . $row['mp_pengganti']) : htmlspecialchars($row['mp_pengganti'])) : '-';

    // Tentukan warna berdasarkan status proses
    $row_color = $status == 1 ? 'table-danger' : ($status == 0 ? 'table-warning' : '');

    // Tentukan status checked
    if (is_null($row['checked'])) {
        $checked_status = "<span class='text-muted'>-</span>";
    } elseif ($row['checked'] == 1) {
        $checked_status = "<span class='badge bg-success'>Approved</span>";
    } else {
        $checked_status = "<span class='badge bg-danger'>Not Approved</span>";
    }

    echo "
    <tr class='$row_color'>
        <td>" . htmlspecialchars($row['tanggal']) . "</td>
        <td>" . htmlspecialchars($process_name) . "</td>
        <td>" . $before . "</td>
        <td>" . $reason_text . "</td>
        <td>" . $after . "</td>
        <td><span class='badge $qualification_color'>$qualification_status</span></td>
        <td>" . htmlspecialchars($row['shift']) . "</td>
        <td>" . $checked_status . "</td>
    </tr>";
}

} else {
    echo "
    <tr>
        <td colspan='8' class='text-center text-muted fst-italic py-4'>
            Tidak ada data absensi di luar 'Hadir' untuk line dan tanggal yang dipilih
        </td>
    </tr>";
}

$conn->close();
$conn3->close();
?>
