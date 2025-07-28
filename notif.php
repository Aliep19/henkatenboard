<?php
// notif.php
require_once 'konfigurasi/konfig.php';
header('Content-Type: text/html; charset=UTF-8');

// Periksa koneksi database
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Ambil parameter no_wa dari URL
$no_wa = isset($_GET['no_wa']) ? trim($_GET['no_wa']) : null;

// Array untuk menyimpan notifikasi alert untuk logging di console
$alert_notifications = [];

// Query untuk mengambil notifikasi berdasarkan no_wa
if ($no_wa) {
    $query = "SELECT no_wa, message, created_at, notification_type FROM notification_log WHERE no_wa = ? ORDER BY created_at DESC";
    error_log("Menjalankan query: $query dengan no_wa: $no_wa");
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Gagal menyiapkan query: " . $conn->error);
        die("Gagal menyiapkan query: " . $conn->error);
    }
    $stmt->bind_param("s", $no_wa);
    if (!$stmt->execute()) {
        error_log("Gagal menjalankan query: " . $stmt->error);
        die("Gagal menjalankan query: " . $stmt->error);
    }
    $result = $stmt->get_result();
} else {
    $query = "SELECT no_wa, message, created_at, notification_type FROM notification_log ORDER BY created_at DESC";
    error_log("Menjalankan query tanpa no_wa: $query");
    $result = $conn->query($query);
    if ($result === false) {
        error_log("Gagal menjalankan query: " . $conn->error);
        die("Gagal menjalankan query: " . $conn->error);
    }
}

// Kumpulkan notifikasi alert untuk logging
while ($row = $result->fetch_assoc()) {
    if ($row['notification_type'] === 'alert_absensi' || $row['notification_type'] === 'peringatan_absensi') {
        $alert_notifications[] = [
            'no_wa' => $row['no_wa'],
            'message' => $row['message'],
            'created_at' => date('d M Y, H:i', strtotime($row['created_at'])),
            'notification_type' => $row['notification_type']
        ];
    }
}
$result->data_seek(0); // Reset pointer result untuk loop tampilan

// Fungsi untuk memformat pesan
function formatProfessionalMessage($message, $notification_type) {
    if ($notification_type === 'alert_absensi' || $notification_type === 'peringatan_absensi') {
        return "<div class='message-content'>" . htmlspecialchars($message) . "</div>";
    }

    $structured = [
        'process' => 'Tidak diketahui',
        'process_type' => 'non s-process',
        'reason' => 'Tidak diketahui',
        'mp_awal' => 'Tidak diketahui',
        'mp_pengganti' => 'Tidak ada',
        'qualification' => 'Tidak ada',
        'reason_pengganti' => 'Tidak ada',
        'source' => 'Unknown',
        'start_date' => 'Tidak diketahui',
        'end_date' => 'Tidak diketahui'
    ];

    $lines = array_filter(array_map('trim', explode('. ', trim($message))), 'strlen');
    foreach ($lines as $line) {
        if (preg_match("/(Ada perubahan MP di|Terjadi perubahan MP di) (s-process|proses) '([^']*)'/", $line, $matches)) {
            $structured['process'] = !empty($matches[3]) ? $matches[3] : 'Tidak diketahui';
            $structured['process_type'] = ($matches[2] === 's-process') ? 's-process' : 'non s-process';
        }
        if (preg_match("/karena '([^']*)'/", $line, $matches)) {
            $structured['reason'] = !empty($matches[1]) ? $matches[1] : 'Tidak diketahui';
        } elseif (preg_match("/karena\s+([^.]*)/", $line, $matches)) {
            $structured['reason'] = !empty(trim($matches[1])) ? trim($matches[1]) : 'Tidak diketahui';
        }
        if (preg_match("/MP awal: ([^()]+) \(([^\)]+)\)/", $line, $matches)) {
            $structured['mp_awal'] = !empty($matches[1]) ? trim($matches[1]) . ' (' . $matches[2] . ')' : 'Tidak diketahui';
        } elseif (preg_match("/MP: ([^()]+) \(([^\)]+)\)/", $line, $matches)) {
            $structured['mp_awal'] = !empty($matches[1]) ? trim($matches[1]) . ' (' . $matches[2] . ')' : 'Tidak diketahui';
        }
        if (preg_match("/digantikan oleh ([^()]+) \(([^\)]+)\)/", $line, $matches)) {
            $structured['mp_pengganti'] = !empty($matches[1]) ? trim($matches[1]) . ' (' . $matches[2] . ')' : 'Tidak ada';
            $structured['source'] = 'Absensi';
        } elseif (preg_match("/digantikan oleh\s*([^.]*)/", $line, $matches)) {
            $structured['mp_pengganti'] = !empty(trim($matches[1])) ? trim($matches[1]) : 'Tidak ada';
            $structured['source'] = 'Absensi';
        }
        if (preg_match("/Status kualifikasi: (.*)/", $line, $matches)) {
            $structured['qualification'] = !empty(trim($matches[1])) ? trim($matches[1]) : 'Tidak ada';
        }
        if (preg_match("/Alasan pemilihan: (.*)/", $line, $matches)) {
            $structured['reason_pengganti'] = !empty(trim($matches[1])) ? trim($matches[1]) : 'Tidak ada';
        } elseif (preg_match("/Manpower telah dipilih dengan alasan: (Belum mempunyai sertifikasi|Sedang masa OJT)/", $line, $matches)) {
            $structured['reason_pengganti'] = !empty($matches[1]) ? $matches[1] : 'Tidak ada';
            $structured['source'] = 'Planning';
        }
        if (preg_match("/Planning ini berdasarkan tanggal mulai: ([^ ]+) dan tanggal berakhir: ([^ ]+)/", $line, $matches)) {
            $structured['start_date'] = !empty($matches[1]) ? $matches[1] : 'Tidak diketahui';
            $structured['end_date'] = !empty($matches[2]) ? $matches[2] : 'Tidak diketahui';
        }
        if (preg_match("/(manpower tidak memenuhi syarat|Belum mempunyai sertifikasi|Sedang masa OJT)/", $line) && $structured['source'] !== 'Absensi') {
            $structured['source'] = 'Planning';
        }
    }

    $html = "<div class='message-content'>";
    $html .= "<strong>Terjadi Perubahan Man Power (" . htmlspecialchars($structured['source']) . ")</strong><br>";
    $html .= "<strong>Proses: </strong> " . htmlspecialchars($structured['process']) . " (" . htmlspecialchars($structured['process_type']) . ")<br>";
    $html .= "<strong>Alasan: </strong> " . htmlspecialchars($structured['reason']) . "<br>";
    $html .= "<strong>MP Awal: </strong> " . htmlspecialchars($structured['mp_awal']) . "<br>";
    if ($structured['source'] === 'Absensi') {
        $html .= "<strong>MP Pengganti: </strong> " . htmlspecialchars($structured['mp_pengganti']) . "<br>";
    }
    $html .= "<strong>Status Kualifikasi: </strong> " . htmlspecialchars($structured['qualification']) . "<br>";
    $html .= "<strong>Alasan Pemilihan: </strong> " . htmlspecialchars($structured['reason_pengganti']) . "<br>";
    if ($structured['source'] === 'Planning') {
        $html .= "<strong>Tanggal Mulai: </strong> " . htmlspecialchars($structured['start_date']) . "<br>";
        $html .= "<strong>Tanggal Berakhir: </strong> " . htmlspecialchars($structured['end_date']) . "<br>";
    }
    $html .= "</div>";

    return $html;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $message_count = isset($result) && $result ? $result->num_rows : 0;
    ?>
    <title>(<?php echo $message_count; ?>) WhatsApp</title>
    <link rel="shortcut icon" href="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/css/notif.css?v=<?php echo time(); ?>">
    <style>
        .message.planning .message-bubble {
            background-color: #e6f3ff;
            border-left: 3px solid #007bff;
        }
        .message.absensi .message-bubble {
            background-color: #e6ffe6;
            border-left: 3px solid #28a745;
        }
        .message.alert_absensi .message-bubble,
        .message.peringatan_absensi .message-bubble {
            background-color: #fff3cd;
            border-left: 3px solid #ffc107;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <h2>Daftar Pesan WhatsApp<?php echo $no_wa ? ' untuk ' . htmlspecialchars($no_wa) : ''; ?></h2>
        </div>
        <div class="chat-body">
            <?php if ($no_wa && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                        $source_class = $row['notification_type'] === 'alert_absensi' ? 'alert_absensi' :
                                       ($row['notification_type'] === 'peringatan_absensi' ? 'peringatan_absensi' : 
                                       (preg_match("/(manpower tidak memenuhi syarat|Belum mempunyai sertifikasi|Sedang masa OJT)/", $row['message']) && !preg_match("/digantikan oleh/", $row['message']) ? 'planning' : 'absensi'));
                    ?>
                    <div class="message received <?php echo htmlspecialchars($source_class); ?>">
                        <div class="message-sender"><?php echo htmlspecialchars($row['no_wa']); ?></div>
                        <div class="message-bubble">
                            <?php echo formatProfessionalMessage($row['message'], $row['notification_type']); ?>
                            <div class="message-time">
                                <?php echo date('d M Y, H:i', strtotime($row['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php elseif ($no_wa && $result->num_rows == 0): ?>
                <div class="no-messages">Tidak ada pesan WhatsApp untuk nomor <?php echo htmlspecialchars($no_wa); ?>.</div>
            <?php elseif (!$no_wa): ?>
                <div class="no-messages">Silakan masukkan nomor WhatsApp di URL (contoh: ?no_wa=+6281234567890).</div>
            <?php else: ?>
                <div class="no-messages">Tidak ada pesan WhatsApp yang tersimpan.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Script untuk logging notifikasi alert ke console browser -->
    <script>
        const alertNotifications = <?php echo json_encode($alert_notifications); ?>;
        if (alertNotifications.length > 0) {
            console.group('Notifikasi Alert Absensi');
            alertNotifications.forEach(notification => {
                console.log('Nomor WA:', notification.no_wa);
                console.log('Jenis Notifikasi:', notification.notification_type === 'alert_absensi' ? 'Alert Absensi (Foreman)' : 'Peringatan Absensi (Golongan 4)');
                console.log('Pesan:', notification.message);
                console.log('Waktu Pengiriman:', notification.created_at);
                console.log('---');
            });
            console.groupEnd();
        } else {
            console.log('Tidak ada notifikasi alert_absensi atau peringatan_absensi untuk ditampilkan.');
        }
    </script>
</body>
</html>

<?php
if ($no_wa) {
    $stmt->close();
}
$conn->close();
?>