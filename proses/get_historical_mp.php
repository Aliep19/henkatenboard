<?php
header('Content-Type: text/html; charset=UTF-8');
require_once '../konfigurasi/konfig.php';

// Ambil parameter dari request
$line_id = isset($_POST['line_id']) ? intval($_POST['line_id']) : 0;
$selected_date = isset($_POST['selected_date']) ? $_POST['selected_date'] : date('Y-m-d');

// Validasi input
if (!$line_id || !$selected_date) {
    echo "
    <tr>
        <td colspan='6' class='text-center text-muted fst-italic py-4'>
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

$stmt_history = $conn->prepare($query_history);
if ($stmt_history === false) {
    echo "<tr><td colspan='6' class='text-center text-muted fst-italic py-4'>Error: " . htmlspecialchars($conn->error) . "</td></tr>";
    exit;
}
$stmt_history->bind_param("is", $line_id, $selected_date); // Hanya bind line_id dan selected_date
$stmt_history->execute();
$result_history = $stmt_history->get_result();

if ($result_history && $result_history->num_rows > 0) {
    while ($row = $result_history->fetch_assoc()) {
        // Ambil nama proses dari tabel process di skillmap_db
        $process_name = 'Unknown';
        $query_process = "SELECT name, status FROM process WHERE id = ?"; // Ambil status juga
        $stmt_process = $conn3->prepare($query_process);
        if ($stmt_process === false) {
            echo "<tr><td colspan='6' class='text-center text-muted fst-italic py-4'>Error: " . htmlspecialchars($conn3->error) . "</td></tr>";
            $stmt_history->close();
            exit;
        }
        $stmt_process->bind_param("i", $row['id_proses']);
        $stmt_process->execute();
        $result_process = $stmt_process->get_result();
        if ($result_process && $result_process->num_rows > 0) {
            $process_data = $result_process->fetch_assoc();
            $process_name = $process_data['name'];
            $status = $process_data['status']; // Ambil status untuk pewarnaan
        }
        $stmt_process->close();

        // Ambil nama karyawan untuk mp_awal dari tabel karyawan di skillmap_db
        $mp_awal_name = $row['mp_awal'];
        $query_karyawan = "SELECT name FROM karyawan WHERE npk = ?";
        $stmt_karyawan = $conn3->prepare($query_karyawan);
        if ($stmt_karyawan === false) {
            echo "<tr><td colspan='6' class='text-center text-muted fst-italic py-4'>Error: " . htmlspecialchars($conn3->error) . "</td></tr>";
            $stmt_history->close();
            exit;
        }
        $stmt_karyawan->bind_param("s", $row['mp_awal']);
        $stmt_karyawan->execute();
        $result_karyawan = $stmt_karyawan->get_result();
        if ($result_karyawan && $result_karyawan->num_rows > 0) {
            $karyawan_data = $result_karyawan->fetch_assoc();
            $mp_awal_name = $karyawan_data['name'];
        }
        $stmt_karyawan->close();

        // Ambil nama karyawan untuk mp_pengganti dari tabel karyawan di skillmap_db
        $mp_pengganti_name = $row['mp_pengganti'];
        if ($row['mp_pengganti']) {
            $stmt_karyawan = $conn3->prepare($query_karyawan);
            if ($stmt_karyawan === false) {
                echo "<tr><td colspan='6' class='text-center text-muted fst-italic py-4'>Error: " . htmlspecialchars($conn3->error) . "</td></tr>";
                $stmt_history->close();
                exit;
            }
            $stmt_karyawan->bind_param("s", $row['mp_pengganti']);
            $stmt_karyawan->execute();
            $result_karyawan = $stmt_karyawan->get_result();
            if ($result_karyawan && $result_karyawan->num_rows > 0) {
                $karyawan_data = $result_karyawan->fetch_assoc();
                $mp_pengganti_name = $karyawan_data['name'];
            }
            $stmt_karyawan->close();
        }

        // Konversi reason ke teks yang sesuai
        $reason_text = '';
        switch ($row['reason']) {
            case 0:
                $reason_text = 'Tanpa Keterangan';
                break;
            case 1:
                $reason_text = 'Izin';
                break;
            case 2:
                $reason_text = 'Cuti';
                break;
            case 3:
                $reason_text = 'Train';
                break;
            case 4:
                $reason_text = 'Sakit';
                break;
            default:
                $reason_text = 'Tidak Diketahui';
        }

        // Format nama dan NPK untuk kolom BEFORE dan AFTER
        $before = $mp_awal_name ? htmlspecialchars($mp_awal_name . ' - ' . $row['mp_awal']) : htmlspecialchars($row['mp_awal']);
        $after = $row['mp_pengganti'] ? ($mp_pengganti_name ? htmlspecialchars($mp_pengganti_name . ' - ' . $row['mp_pengganti']) : htmlspecialchars($row['mp_pengganti'])) : '-';

        // Tentukan warna berdasarkan status proses
        $row_color = $status == 1 ? 'table-danger' : ($status == 0 ? 'table-warning' : '');

        echo "
        <tr class='$row_color'>
            <td>" . htmlspecialchars($row['tanggal']) . "</td>
            <td>" . htmlspecialchars($process_name) . "</td>
            <td>" . $before . "</td>
            <td>" . $reason_text . "</td>
            <td>" . $after . "</td>
            <td>" . htmlspecialchars($row['shift']) . "</td>
        </tr>";
    }
} else {
    echo "
    <tr>
        <td colspan='6' class='text-center text-muted fst-italic py-4'>
            Tidak ada data absensi di luar 'Hadir' untuk line dan tanggal yang dipilih
        </td>
    </tr>";
}

$stmt_history->close();
$conn->close();
$conn3->close();
?>