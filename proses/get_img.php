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

// Tambahkan timestamp untuk mencegah caching
$timestamp = time();
?>

<!-- Tampilan Gambar -->
<div class="image-container text-center" style="border: 5px solid #ccc; padding: 30px; min-height: 300px; background-color: #f9f9f9; border-radius: 10px;">
    <!-- Tampilkan gambar jika ada -->
    <?php if (!empty($image_path) && file_exists($image_path)): ?>
        <img src="<?php echo $image_path . '?t=' . $timestamp; ?>" alt="Layout <?php echo htmlspecialchars($line_name); ?>" class="img-fluid rounded shadow zoomable" style="max-height: 250px; object-fit: contain; border: 2px solid #ddd;">
        <p class="text-muted small mt-1">Layout untuk Line: <?php echo htmlspecialchars($line_name); ?></p>
    <?php endif; ?>

    <!-- Tampilkan pesan jika tidak ada gambar atau line belum dipilih -->
    <?php if (!empty($display_message)): ?>
        <div class="alert alert-info" role="alert" style="margin-top: 10px; padding: 10px;">
            <?php echo $display_message; ?>
        </div>
    <?php endif; ?>
</div>
<?php mysqli_close($conn3); ?>