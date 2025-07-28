<?php
session_start();
require_once '../konfigurasi/konfig.php';

if (!isset($_SESSION['log']) || $_SESSION['log'] !== 'True') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$upload_dir = '../assets/img/uploads/news/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($action === 'fetch') {
    $query = "SELECT id, category, description, filename, uploaded_at FROM news_images ORDER BY uploaded_at DESC";
    $result = mysqli_query($conn, $query);
    $news = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $news[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $news]);
    exit();
}

if ($action === 'insert') {
    if (isset($_FILES['news_image']) && isset($_POST['category']) && isset($_POST['description'])) {
        $category = $_POST['category'];
        $description = $_POST['description'];
        $file = $_FILES['news_image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if ($file['error'] === UPLOAD_ERR_OK) {
            if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = $category . '_' . time() . '.' . $ext;
                $destination = $upload_dir . $filename;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $query = "INSERT INTO news_images (category, description, filename) VALUES (?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, 'sss', $category, $description, $filename);
                    if (mysqli_stmt_execute($stmt)) {
                        echo json_encode(['success' => true, 'message' => 'Gambar dan deskripsi berhasil diunggah']);
                    } else {
                        unlink($destination);
                        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data']);
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Gagal mengunggah gambar']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'File tidak valid atau terlalu besar']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat mengunggah']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    }
    exit();
}

if ($action === 'delete') {
    $id = $_POST['id'] ?? '';
    if ($id) {
        $query = "SELECT filename FROM news_images WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($row) {
            $file_path = $upload_dir . $row['filename'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $query = "DELETE FROM news_images WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $id);
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Gambar berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus data']);
            }
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gambar tidak ditemukan']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    }
    exit();
}

if ($action === 'update') {
    $id = $_POST['id'] ?? '';
    if (isset($_POST['category']) && isset($_POST['description']) && $id) {
        $category = $_POST['category'];
        $description = $_POST['description'];
        $filename = null;

        // Check if a new image is uploaded
        if (isset($_FILES['news_image']) && $_FILES['news_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['news_image'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = $category . '_' . time() . '.' . $ext;
                $destination = $upload_dir . $filename;

                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    echo json_encode(['success' => false, 'message' => 'Gagal mengunggah gambar']);
                    exit();
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'File tidak valid atau terlalu besar']);
                exit();
            }
        }

        // Fetch old filename to delete it if a new image is uploaded
        $query = "SELECT filename FROM news_images WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($row) {
            if ($filename) {
                // Delete old file
                $old_file = $upload_dir . $row['filename'];
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
                // Update with new filename, category, description, and updated_at
                $query = "UPDATE news_images SET category = ?, description = ?, filename = ?, uploaded_at = NOW() WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'sssi', $category, $description, $filename, $id);
            } else {
                // Update only category, description, and updated_at
                $query = "UPDATE news_images SET category = ?, description = ?, uploaded_at = NOW() WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'ssi', $category, $description, $id);
            }

            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Gambar dan deskripsi berhasil diperbarui']);
            } else {
                if ($filename) {
                    unlink($destination);
                }
                echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data']);
            }
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gambar tidak ditemukan']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    }
    exit();
}


echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
?>