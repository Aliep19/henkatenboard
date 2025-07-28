<?php
session_start();
require_once '../../konfigurasi/konfig.php';

// Nonaktifkan caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (!isset($_SESSION['log']) || $_SESSION['log'] !== 'True' || (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    echo json_encode(['status' => 'expired']);
} else {
    echo json_encode(['status' => 'active']);
}
?>