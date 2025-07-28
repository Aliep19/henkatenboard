<?php
require_once '../../konfigurasi/konfig.php';

$id = isset($_POST['id']) ? $_POST['id'] : null;
if ($id === null) {
    echo json_encode(['success' => false, 'message' => 'ID not provided']);
    $conn->close();
    exit;
}

// Ambil nama file gambar
$stmt = $conn->prepare("SELECT gambar FROM machine WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $image = $result->fetch_assoc()['gambar'];
    $filePath = '../../uploads/' . $image; // <-- path relatif sudah benar

    // Hapus file jika ada
    if ($image && file_exists($filePath)) {
        if (!unlink($filePath)) {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus file gambar']);
            $stmt->close();
            $conn->close();
            exit;
        }
    }
}
$stmt->close();

// Hapus data dari database
$stmt = $conn->prepare("DELETE FROM machine WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
