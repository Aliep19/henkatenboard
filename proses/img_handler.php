<?php
// Default image atau pesan jika belum ada line yang dipilih
$image_path = "";
$display_message = "<p class='text-muted mt-2'>Pilih Line untuk melihat atau mengunggah layout</p>";
$line_name = "";
$upload_error = "";

// Jika line dipilih, tentukan path gambar berdasarkan id line
if (isset($_GET['line']) && !empty($_GET['line'])) {
    $line_id = $_GET['line'];
    // Query untuk mengambil nama line
    $query_line_name = "SELECT name FROM sub_workstations WHERE id = ?";
    $stmt_line_name = mysqli_prepare($conn3, $query_line_name);
    mysqli_stmt_bind_param($stmt_line_name, "i", $line_id);
    mysqli_stmt_execute($stmt_line_name);
    $result_line_name = mysqli_stmt_get_result($stmt_line_name);

    if ($row_line_name = mysqli_fetch_assoc($result_line_name)) {
        $line_name = strtolower(str_replace(' ', '_', $row_line_name['name']));
        $image_path = "assets/img/line_layouts/line_$line_name.png";
        $display_message = "<p class='text-muted mt-2'>Gambar layout untuk " . htmlspecialchars($row_line_name['name']) . " belum tersedia</p>";

        // Cek apakah file gambar ada
        if (file_exists($image_path)) {
            $display_message = "";
        }
    }
    mysqli_stmt_close($stmt_line_name);
}

// Proses upload gambar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['layout_image']) && !empty($_GET['line'])) {
    $target_dir = "assets/img/line_layouts/";
    // Pastikan direktori ada, jika tidak buat
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $target_file = $target_dir . "line_$line_name.png";
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($_FILES["layout_image"]["name"], PATHINFO_EXTENSION));

    // Cek apakah file adalah gambar
    $check = getimagesize($_FILES["layout_image"]["tmp_name"]);
    if ($check !== false) {
        $width = $check[0];  // Lebar gambar
        $height = $check[1]; // Tinggi gambar
        $uploadOk = 1;

        // Validasi ukuran dimensi gambar
        if ($width < 600 || $width > 1500 || $height < 300 || $height > 800) {
            $upload_error = "Ukuran gambar tidak sesuai. Lebar harus antara 600-1500px dan tinggi antara 300-800px. Ukuran saat ini: {$width}x{$height}px.";
            $uploadOk = 0;
        }
    } else {
        $upload_error = "File bukan gambar.";
        $uploadOk = 0;
    }

    // Batasi jenis file
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        $upload_error .= " Hanya file JPG, JPEG, dan PNG yang diperbolehkan.";
        $uploadOk = 0;
    }

    // Batasi ukuran file (contoh: 5MB)
    if ($_FILES["layout_image"]["size"] > 5000000) {
        $upload_error .= " Ukuran file terlalu besar. Maksimum 5MB.";
        $uploadOk = 0;
    }

    // Jika semua validasi lolos, upload file
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["layout_image"]["tmp_name"], $target_file)) {
            $upload_error = "Gambar berhasil diunggah untuk line $line_name.";
            $image_path = $target_file;
            $display_message = "";
        } else {
            $upload_error = "Terjadi kesalahan saat mengunggah file.";
        }
    }
}
?>

<!-- Tampilan Gambar -->
<div class="image-container text-center">
    <!-- Tampilkan gambar jika ada -->
<?php if (!empty($image_path) && file_exists($image_path)): ?>
    <img src="<?php echo $image_path . '?v=' . filemtime($image_path); ?>" 
         alt="Layout <?php echo htmlspecialchars($line_name); ?>" 
         class="img-fluid rounded shadow zoomable" 
         style="width: 100vh; height: auto; object-fit: contain; border: 2px solid #ddd;">
<?php endif; ?>


    <!-- Tampilkan pesan jika tidak ada gambar atau line belum dipilih -->
    <?php if (!empty($display_message)): ?>
        <div class="alert alert-info" role="alert" style="margin-top: 10px; padding: 10px;">
            <?php echo $display_message; ?>
        </div>
    <?php endif; ?>

    <!-- Tampilkan pesan error atau sukses upload -->
    <?php if (!empty($upload_error)): ?>
        <div class="alert <?php echo strpos($upload_error, 'berhasil') !== false ? 'alert-success' : 'alert-danger'; ?>" role="alert" style="margin-top: 10px; padding: 10px;">
            <?php echo $upload_error; ?>
        </div>
        <script>
            // Alert untuk pesan error atau sukses
            <?php if (strpos($upload_error, 'berhasil') !== false): ?>
                Swal.fire('Sukses', 'Gambar berhasil diunggah!', 'success');
            <?php else: ?>
                Swal.fire('Error', 'Gagal mengunggah gambar. <?php echo strip_tags($upload_error); ?>', 'error');
            <?php endif; ?>
        </script>
    <?php endif; ?>
</div>
