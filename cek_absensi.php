<?php
require_once 'konfigurasi/konfig.php'; // $conn = henkaten, $conn2 = lembur, $conn3 = skillmap_db
date_default_timezone_set('Asia/Jakarta');

$today = date('Y-m-d');
$now = new DateTime();
$log = "";

$q = mysqli_query($conn, "SELECT * FROM hkt_form WHERE '$today' BETWEEN date AND to_date");
if (!$q) {
    $log .= "[⛔] Query hkt_form gagal: " . mysqli_error($conn) . "\n";
    echo $log;
    exit;
}

while ($hkt = mysqli_fetch_assoc($q)) {
    $id_hkt = $hkt['id_hkt'];
    $id_line = $hkt['id_line'];
    $id_shift = $hkt['id_shifft'];

    // Ambil nama shift dan jam mulai
    $shift_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT shift, jam_kerja FROM shift WHERE id_shift = $id_shift"));
    if (!$shift_data) {
        $log .= "[⛔] Shift tidak ditemukan untuk ID: $id_shift (HKT: $id_hkt)\n";
        continue;
    }
    $shift_name = $shift_data['shift'];
    $jam_mulai = explode(" - ", $shift_data['jam_kerja'])[0];
    $startTime = new DateTime("{$today} {$jam_mulai}");
    $minutesPassed = ($now->getTimestamp() - $startTime->getTimestamp()) / 60;

    if ($minutesPassed < 30) continue;

    // Cek absensi
    $absen_result = mysqli_query($conn, 
        
        "SELECT COUNT(*) AS total 
        FROM perubahan 
        WHERE id_shift = $id_shift 
        AND tanggal = '$today' 
        AND id_proses IN (
            SELECT id 
            FROM mp_procees 
            WHERE id_hkt = $id_hkt
        )
        "

        );
    $absensiAda = $absen_result && mysqli_fetch_assoc($absen_result)['total'] > 0;

    // Ambil nama line
    $line_name = '';
    $line_result = mysqli_query($conn3, "SELECT name FROM sub_workstations WHERE id = $id_line");
    if ($line_result && mysqli_num_rows($line_result) > 0) {
        $line_name = mysqli_fetch_assoc($line_result)['name'];
    } else {
        $line_name = "Line $id_line"; // fallback
    }

    if (!$absensiAda) {
        // Ambil nama dept
        $dept = '';
        $dept_stmt = $conn3->prepare("
            SELECT d.dept_name 
            FROM sub_workstations sw 
            JOIN workstations w ON sw.workstation_id = w.id 
            JOIN department d ON w.dept_id = d.id 
            WHERE sw.id = ?
        ");
        $dept_stmt->bind_param("i", $id_line);
        $dept_stmt->execute();
        $dept_result = $dept_stmt->get_result();
        if ($dept_result && $dept_result->num_rows > 0) {
            $dept = $dept_result->fetch_assoc()['dept_name'];
        } else {
            $log .= "[⛔] Dept tidak ditemukan untuk line: $line_name\n";
            continue;
        }

        // Tentukan notifikasi
        if ($minutesPassed >= 60 && $minutesPassed < 90) {
            $tipe = 'peringatan_absensi';
            $pesan = "⚠ Belum ada absensi di Line $line_name $shift_name. Hubungi foreman untuk segera melakukan absensi sebelum ditutup.";
            $golongan = 4;
        } elseif ($minutesPassed >= 30 && $minutesPassed < 60) {
            $tipe = 'alert_absensi';
            $pesan = "🔔 Foreman harap segera input absensi untuk Line $line_name $shift_name. Waktu absensi akan ditutup dalam 60 menit.";
            $golongan = 3;
        } else {
            continue;
        }

        // Cek apakah sudah pernah dikirim
        $cekLog = mysqli_query($conn, "SELECT id FROM notification_log WHERE id_hkt = $id_hkt AND notification_type = '$tipe'");
        if ($cekLog && mysqli_num_rows($cekLog) > 0) {
            $log .= "[ℹ️] Notif '$tipe' sudah dikirim untuk Line $line_name (Shift $shift_name)\n";
            continue;
        }

        // Ambil user WA
        $stmt_wa = $conn->prepare("
            SELECT DISTINCT o.no_hp 
            FROM lembur.ct_users u 
            JOIN otp o ON u.npk = o.npk 
            WHERE u.dept = ?  AND u.golongan = ?
        ");
        $stmt_wa->bind_param("si", $dept, $golongan);
        $stmt_wa->execute();
        $result_wa = $stmt_wa->get_result();

        if ($result_wa && $result_wa->num_rows > 0) {
            while ($row = $result_wa->fetch_assoc()) {
                $no_wa = $row['no_hp'];
                $stmt_ins = $conn->prepare("INSERT INTO notification_log (no_wa, message, created_at, id_hkt, notification_type) VALUES (?, ?, NOW(), ?, ?)");
                $stmt_ins->bind_param("ssis", $no_wa, $pesan, $id_hkt, $tipe);
                if ($stmt_ins->execute()) {
                    $log .= "[✅] Notif '$tipe' dikirim ke $no_wa → $line_name (Shift $shift_name)\n";
                } else {
                    $log .= "[❌] Gagal insert notif untuk $no_wa\n";
                }
                $stmt_ins->close();
            }
        } else {
            $log .= "[⚠️] Tidak ada user golongan $golongan di dept $dept ($line_name)\n";
        }
        $stmt_wa->close();
    } else {
        $log .= "[✅] Absensi SUDAH dilakukan untuk Line $line_name  $shift_name\n";
    }
}
$log .= "[✔] Selesai cek pada " . date('H:i:s') . "\n";
echo nl2br($log);


$log .= "[✔] Cek selesai pada " . date('H:i:s') . "\n";
echo nl2br($log); // log tampil ke browser
?>