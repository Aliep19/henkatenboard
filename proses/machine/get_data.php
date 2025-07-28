<?php
require_once '../../konfigurasi/konfig.php';

$id = $_GET['id'];
$sql = "SELECT * FROM machine WHERE id='$id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'message' => 'Data not found']);
}

$conn->close();
?>