<?php
    session_start();
    // Pemeriksaan sesi: pastikan pengguna sudah login
    if (!isset($_SESSION['log']) || $_SESSION['log'] !== 'True') {
        header("Location: login.php");
        exit();
    }
    require_once 'konfigurasi/konfig.php';
    include 'src/workhours.php';

    // Query untuk mengambil data sub_workstations
    $query_sub_workstations = "SELECT id, name FROM sub_workstations";
    $result_sub_workstations = mysqli_query($conn3, $query_sub_workstations);
    $sub_workstations = [];
    while ($row = mysqli_fetch_assoc($result_sub_workstations)) {
        $sub_workstations[] = $row;
    }
    mysqli_free_result($result_sub_workstations);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Informasi - PT Kayaba Indonesia</title>
    <link rel="shortcut icon" href="assets/img/icon.jpg" type="image/x-icon">
    <link rel="stylesheet" href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="assets/jquery-ui/jquery-ui.min.css">
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="assets/css/inform.css?v=<?php echo time(); ?>">
    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="assets/js/inactivity.js"></script>
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header>
        <?php require_once 'assets/include/header.php'; ?>
    </header>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Back Button -->
        <a href="menu.php" class="back-button">
            Kembali ke Menu
        </a>

        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Update Informasi</h1>
            <p class="page-subtitle">
                Kelola informasi sistem dengan mudah. Pilih kategori di bawah untuk memperbarui data line, berita, atau perubahan henkaten.
            </p>
        </div>

        <!-- Cards Grid -->
        <div class="cards-grid">
            <!-- Update Line Images Card -->
            <div class="info-card" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <div class="card-overlay"></div>
                <img src="assets/img/update_images.png" class="card-image" alt="Update Line Images">
                <div class="card-body">
                    <div class="card-icon"><i class="fa-solid fa-image"></i></div>
                    <h5 class="card-title">Update Line Images</h5>
                    <p class="card-description">Kelola dan perbarui gambar layout untuk setiap line produksi dengan mudah dan cepat.</p>
                </div>
            </div>

            <!-- Update News Images Card -->
            <div class="info-card" data-bs-toggle="modal" data-bs-target="#newsModal">
                <div class="card-overlay"></div>
                <img src="assets/img/update_info.png" class="card-image" alt="Update News Images">
                <div class="card-body">
                    <div class="card-icon"><i class="fa-solid fa-newspaper"></i></div>
                    <h5 class="card-title">Update News Images</h5>
                    <p class="card-description">Kelola gambar berita untuk departemen Produksi dan Quality Assurance.</p>
                </div>
            </div>

            <!-- Update Henkaten Card -->
            <div class="info-card" data-bs-toggle="modal" data-bs-target="#henkatenModal">
                <div class="card-overlay"></div>
                <img src="assets/img/change-management.png" class="card-image" alt="Update Henkaten">
                <div class="card-body">
                    <div class="card-icon"><i class="fa-solid fa-gears"></i></div>
                    <h5 class="card-title">Update Henkaten</h5>
                    <p class="card-description">Kelola perubahan pada Metode, Mesin, dan Material dalam sistem produksi.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk Update Henkaten -->
    <div class="modal fade" id="henkatenModal" tabindex="-1" aria-labelledby="henkatenModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="henkatenModalLabel">Pilih Jenis Perubahan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-4">Silakan pilih kategori Henkaten yang ingin Anda perbarui:</p>
                    <div class="henkaten-options">
                        <a href="view/method.php" class="henkaten-btn">
                            <div class="henkaten-icon">🔧</div>
                            <div>
                                <strong>Method</strong>
                                <div class="text-muted small">Kelola perubahan metode kerja</div>
                            </div>
                        </a>
                        <a href="view/material.php" class="henkaten-btn">
                            <div class="henkaten-icon">🧱</div>
                            <div>
                                <strong>Material</strong>
                                <div class="text-muted small">Kelola perubahan material</div>
                            </div>
                        </a>
                        <a href="view/machine.php" class="henkaten-btn">
                            <div class="henkaten-icon">🛠️</div>
                            <div>
                                <strong>Machine</strong>
                                <div class="text-muted small">Kelola perubahan mesin</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk Upload Gambar -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">Unggah Layout Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="lineSelect" class="form-label">Pilih Line</label>
                            <select class="form-select" id="lineSelect" name="line_id" required>
                                <option value="" disabled selected>-- Pilih Line --</option>
                                <?php foreach ($sub_workstations as $sub_workstation): ?>
                                    <option value="<?php echo $sub_workstation['id']; ?>"><?php echo htmlspecialchars($sub_workstation['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="layout_image" class="form-label">Pilih Gambar Layout</label>
                            <input type="file" class="form-control" id="layout_image" name="layout_image" accept="image/*" required>
                            <div class="form-text text-muted mt-2">
                                💡 Rekomendasi: Lebar 800-1200px, Tinggi 400-600px
                            </div>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Unggah Gambar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk Kelola Berita -->
    <div class="modal fade" id="newsModal" tabindex="-1" aria-labelledby="newsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newsModalLabel">Kelola Gambar Berita</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form untuk Insert/Update -->
                    <form id="newsForm" enctype="multipart/form-data">
                        <input type="hidden" id="news_id" name="id">
                        <div class="mb-3">
                            <label for="news_category" class="form-label">Kategori</label>
                            <select class="form-select" id="news_category" name="category" required>
                                <option value="" disabled selected>-- Pilih Kategori --</option>
                                <option value="production">Produksi</option>
                                <option value="qa">Quality Assurance</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="news_description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="news_description" name="description" rows="4" placeholder="Masukkan deskripsi berita" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="news_image" class="form-label">Pilih Gambar Berita</label>
                            <input type="file" class="form-control" id="news_image" name="news_image" accept="image/*">
                            <div class="form-text text-muted mt-2">
                                📁 Maksimal 5MB, format: JPG, PNG, GIF
                            </div>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Simpan</button>
                    </form>
                    
                    <hr class="my-4">
                    
                    <!-- Tabel untuk Menampilkan Data -->
                    <h6 class="mb-3">Daftar Gambar Berita</h6>
                    <div class="news-table">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Kategori</th>
                                    <th>Deskripsi</th>
                                    <th>Gambar</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="newsTableBody">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p><b>© 2025 PT Kayaba Indonesia. All Rights Reserved.</b></p>
    </footer>
datainformasi
    <script src="assets/js/datainformasi/script.js"></script>
</body>
</html>
<?php mysqli_close($conn3); ?><?php mysqli_close($conn); ?>