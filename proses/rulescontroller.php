<?php
// Check if user is logged in
$is_logged_in = isset($_SESSION['log']) && $_SESSION['log'] === 'True';

// Handle form submissions for add/edit/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' && $is_logged_in) {
            // Handle add new rule
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $pdf_file = $_FILES['pdf_file'];

            if ($pdf_file['error'] === UPLOAD_ERR_OK && strtolower(pathinfo($pdf_file['name'], PATHINFO_EXTENSION)) === 'pdf') {
                $upload_dir = 'assets/rules/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                // Gunakan nama unik untuk file
                $new_filename = time() . '_' . basename($pdf_file['name']);
                $pdf_path = $upload_dir . $new_filename;

                move_uploaded_file($pdf_file['tmp_name'], $pdf_path);

                $query = "INSERT INTO rules (title, pdf_path) VALUES (?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ss", $title, $pdf_path);
                $stmt->execute();
                $stmt->close();
            }

        } elseif ($_POST['action'] === 'edit' && $is_logged_in) {
            // Handle edit rule
            $id = intval($_POST['id']);
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $pdf_path = null;

            if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
                if (strtolower(pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION)) === 'pdf') {
                    $upload_dir = 'assets/rules/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    // Ambil file lama dari database
                    $stmt_old = $conn->prepare("SELECT pdf_path FROM rules WHERE id = ?");
                    $stmt_old->bind_param("i", $id);
                    $stmt_old->execute();
                    $result_old = $stmt_old->get_result();
                    if ($row_old = $result_old->fetch_assoc()) {
                        if (file_exists($row_old['pdf_path'])) {
                            unlink($row_old['pdf_path']); // hapus file lama
                        }
                    }
                    $stmt_old->close();

                    // Simpan file baru dengan nama unik
                    $new_filename = time() . '_' . basename($_FILES['pdf_file']['name']);
                    $pdf_path = $upload_dir . $new_filename;
                    move_uploaded_file($_FILES['pdf_file']['tmp_name'], $pdf_path);
                }
            }

            // Update database
            if ($pdf_path) {
                $query = "UPDATE rules SET title = ?, pdf_path = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssi", $title, $pdf_path, $id);
            } else {
                $query = "UPDATE rules SET title = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("si", $title, $id);
            }
            $stmt->execute();
            $stmt->close();

        } elseif ($_POST['action'] === 'delete' && $is_logged_in) {
            // Handle delete rule
            $id = intval($_POST['id']);

            // Ambil file lama sebelum hapus dari DB
            $stmt_get = $conn->prepare("SELECT pdf_path FROM rules WHERE id = ?");
            $stmt_get->bind_param("i", $id);
            $stmt_get->execute();
            $result = $stmt_get->get_result();
            if ($row = $result->fetch_assoc()) {
                if (file_exists($row['pdf_path'])) {
                    unlink($row['pdf_path']);
                }
            }
            $stmt_get->close();

            $query = "DELETE FROM rules WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
    }
    // Redirect to refresh page
    header("Location: rule.php");
    exit;
}

// Fetch all rules from database (assume table 'rules' with columns id, title, pdf_path)
$query_rules = "SELECT id, title, pdf_path FROM rules ORDER BY id DESC";
$result_rules = mysqli_query($conn, $query_rules);
$rules = [];
if ($result_rules) {
    while ($row = mysqli_fetch_assoc($result_rules)) {
        $rules[] = $row;
    }
}
?>
