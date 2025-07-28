<?php
require_once '../../konfigurasi/konfig.php';
$response = ['success' => false, 'message' => 'Unknown error'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $editId = $_POST['editId'] ?? '';
    $tanggal = $_POST['tanggal'];
    $shift = $_POST['shift'];
    $proses = $_POST['proses'];
    $mesin = $_POST['mesin'];
    $kode = $_POST['kode'];
    $alasan = $_POST['alasan'];
    $sebelum = $_POST['sebelum'];
    $saatini = $_POST['saatini'];
    $gambar = '';

    // Handle file upload
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $upload = $_FILES['gambar'];
        $ext = pathinfo($upload['name'], PATHINFO_EXTENSION);
        $filename = uniqid('img_') . '.' . $ext;
        $uploadDir = '../../Uploads/';
        $uploadPath = $uploadDir . $filename;

        // Validate file size (2MB limit) and type
        $allowedTypes = ['image/jpeg', 'image/png'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($upload['size'] > $maxSize) {
            echo json_encode(['success' => false, 'message' => 'File size exceeds 2MB limit']);
            exit;
        }
        if (!in_array($upload['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type']);
            exit;
        }

        if (move_uploaded_file($upload['tmp_name'], $uploadPath)) {
            $gambar = $filename;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
            exit;
        }
    }

    if ($editId) {
        // Update existing record
        $sql = "UPDATE machine SET tanggal='$tanggal', shift='$shift', proses='$proses', mesin='$mesin',  kode='$kode', alasan='$alasan', sebelum='$sebelum', saatini='$saatini'";
        if ($gambar) {
            $sql .= ", gambar='$gambar'";
            // Delete old image if exists
            $result = $conn->query("SELECT gambar FROM machine WHERE id='$editId'");
            if ($result->num_rows > 0) {
                $oldImage = $result->fetch_assoc()['gambar'];
                if ($oldImage && file_exists('../../Uploads/' . $oldImage)) {
                    unlink('../../Uploads/' . $oldImage);
                }
            }
        }
        $sql .= " WHERE id='$editId'";
    } else {
        // Insert new record
        $sql = "INSERT INTO machine (tanggal, shift, proses, mesin, kode, alasan, sebelum, saatini, gambar) VALUES ('$tanggal', '$shift', '$proses','$mesin', '$kode', '$alasan', '$sebelum', '$saatini', '$gambar')";
    }

    if ($conn->query($sql) === TRUE) {
        $response = ['success' => true];
    } else {
        $response = ['success' => false, 'message' => 'Database error: ' . $conn->error];
    }
}

$conn->close();
echo json_encode($response);
?>