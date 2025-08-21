<?php
session_start();
require_once '../konfigurasi/konfig.php';


// Check if user is logged in and has golongan 4
if (!isset($_SESSION['log']) || $_SESSION['log'] !== 'True' || !isset($_SESSION['golongan']) || $_SESSION['golongan'] != 4) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Get POST data
$id_perubahan = isset($_POST['id_perubahan']) ? intval($_POST['id_perubahan']) : 0;
$checked = isset($_POST['checked']) ? intval($_POST['checked']) : null;

if ($id_perubahan <= 0 || !in_array($checked, [0, 1])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
    exit;
}

// Update checked status in perubahan table
$query = "UPDATE perubahan SET checked = ? WHERE id_perubahan = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $checked, $id_perubahan);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    echo json_encode(['status' => 'success', 'message' => 'Status updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
}

$conn->close();
?>