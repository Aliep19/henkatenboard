<?php
session_start();
require_once '../konfigurasi/konfig.php';

// Pemeriksaan sesi
if (!isset($_SESSION['log']) || $_SESSION['log'] !== 'True') {
    header("Location: ../login.php");
    exit();
}

$upload_error = "";
$upload_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['layout_image']) && isset($_POST['line_id'])) {
    $line_id = $_POST['line_id'];
    
    // Query untuk mengambil nama line
    $query_line_name = "SELECT name FROM sub_workstations WHERE id = ?";
    $stmt_line_name = mysqli_prepare($conn3, $query_line_name);
    mysqli_stmt_bind_param($stmt_line_name, "i", $line_id);
    mysqli_stmt_execute($stmt_line_name);
    $result_line_name = mysqli_stmt_get_result($stmt_line_name);

    if ($row_line_name = mysqli_fetch_assoc($result_line_name)) {
        $line_name = strtolower(str_replace(' ', '_', $row_line_name['name']));
        $target_dir = "../assets/img/line_layouts/";
        
        // Pastikan direktori ada
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $target_file = $target_dir . "line_$line_name.png";
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($_FILES["layout_image"]["name"], PATHINFO_EXTENSION));

        // Validasi gambar
        $check = getimagesize($_FILES["layout_image"]["tmp_name"]);
        if ($check !== false) {
            $width = $check[0];
            $height = $check[1];
            if ($width < 600 || $width > 1500 || $height < 300 || $height > 800) {
                $upload_error = "Ukuran gambar tidak sesuai. Lebar harus antara 600-1500px dan tinggi antara 300-800px. Ukuran saat ini: {$width}x{$height}px.";
                $uploadOk = 0;
            }
        } else {
            $upload_error = "File bukan gambar.";
            $uploadOk = 0;
        }

        // Validasi jenis file
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            $upload_error .= " Hanya file JPG, JPEG, dan PNG yang diperbolehkan.";
            $uploadOk = 0;
        }

        // Validasi ukuran file (5MB)
        if ($_FILES["layout_image"]["size"] > 5000000) {
            $upload_error .= " Ukuran file terlalu besar. Maksimum 5MB.";
            $uploadOk = 0;
        }

        // Jika validasi lolos, upload file
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["layout_image"]["tmp_name"], $target_file)) {
                $upload_success = true;
                $upload_error = "Gambar berhasil diunggah untuk line $line_name.";
            } else {
                $upload_error = "Terjadi kesalahan saat mengunggah file.";
            }
        }
    } else {
        $upload_error = "Line tidak ditemukan.";
    }
    mysqli_stmt_close($stmt_line_name);
}

// Kirim respons sebagai JSON untuk AJAX
header('Content-Type: application/json');
echo json_encode([
    'success' => $upload_success,
    'message' => $upload_error
]);
?>