<?php
session_start();
require_once '../../konfigurasi/konfig.php';

// Perbarui waktu aktivitas terakhir
if (isset($_SESSION['log']) && $_SESSION['log'] === 'True') {
    $_SESSION['last_activity'] = time();
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Sesi tidak valid']);
}
?>