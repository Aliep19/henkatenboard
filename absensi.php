<?php
        session_start();

        // Pemeriksaan sesi: pastikan pengguna sudah login
        if (!isset($_SESSION['log']) || $_SESSION['log'] !== 'True') {
            header("Location: login.php");
            exit();
        }

        require_once 'konfigurasi/konfig.php';
            include 'src/workhours.php';
                include 'src/absensi.php';  
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>henkaten Board - PT Kayaba Indonesia</title>
    <link rel="shortcut icon" href="assets/img/icon.jpg" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/absensi/style.css?v=<?php echo time(); ?>"><!-- STYLING FORM ABSENSI -->
    <link rel="stylesheet" href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <!-- <link rel="stylesheet" href="assets/bootstrap-icons/bootstrap-icons.min.css"> -->
    <link href="assets/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="assets/jquery-ui/jquery-ui.min.css" rel="stylesheet">
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/sweetalert2/dist/sweetalert2.all.min.js"></script>
</head>

<body>
    <div class="main-container pb-5">
        <!-- Header -->
        <header class="header mb-4">
            <?php require_once 'assets/include/header.php'; ?>
        </header>

        <!-- Page Header -->
        <br><br>
        <!-- Informational Alert -->
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="fas fa-exclamation-circle fa-lg"></i>
            <div class="text-black">
                <strong>Perhatian :</strong>
            Harap melakukan absensi maksimal 1 1/2 jam dari awal shift sebelum absensi ditutup.
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div class="page-header">
            <h1 class="page-title">Form Absensi Henkaten</h1>
            <p class="page-subtitle">PT Kayaba Indonesia - Manajemen Kehadiran Karyawan</p>
        </div>

        <!-- Informasi HKT Form -->
        <div class="info-card">
            <div class="card-header-custom">
                <h2 class="card-header-title">Informasi Henkaten</h2>
            </div>
            <div class="card-body-custom">
                <div class="form-row">
                    <label class="form-label-custom">Bagian</label>
                    <div class="form-value" id="bagian"><?php echo htmlspecialchars($bagian_name); ?></div>
                </div>
                <div class="form-row">
                    <label class="form-label-custom">Line</label>
                    <div class="form-value" id="line"><?php echo htmlspecialchars($line_name); ?></div>
                </div>
                <div class="form-row">
                    <label class="form-label-custom">Shift</label>
                    <div class="form-value" id="shift"><?php echo htmlspecialchars($shift); ?></div>
                </div>
                <div class="form-row">
                    <label class="form-label-custom">Output Target</label>
                    <div class="form-value" id="output_target"><?php echo htmlspecialchars($output_target); ?></div>
                </div>
            </div>
        </div>

        <!-- Toggle Button -->
                    <button class="btn bg-danger btn-outline-light d-flex align-items-center gap-2 px-3 py-2  shadow-sm mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#legendCollapse" aria-expanded="true" aria-controls="legendCollapse">
                        <i class="fas fa-info-circle"></i> 
                        <span>Keterangan Warna</span>
                    </button>

                    <!-- Konten Legend -->
                    <div class="collapse show" id="legendCollapse">
                        <div class="bg-white text-black p-3 rounded mb-3">
                            <div class="mb-2 fw-bold">Keterangan Warna Manpower:</div>
                            <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                                <div class="d-flex align-items-center me-3">
                                    <div class="me-2 rounded" style="width: 20px; height: 20px; background-color: #dc3545;"></div>
                                    <small>Manpower Not Qualified</small>
                                </div>
                                <div class="d-flex align-items-center me-3">
                                    <div class="me-2 border rounded shadow-sm" style="width: 20px; height: 20px; background-color: #ffffff; border-color:rgb(0, 0, 0) !important; box-shadow: 0 1px 4px rgba(0,0,0,0.10);"></div>
                                    <small>Manpower Qualified</small>
                                </div>
                            </div>

                            <div class="mb-2 fw-bold">Keterangan Warna Proses:</div>
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <div class="d-flex align-items-center me-3">
                                    <div class="me-2 rounded" style="width: 20px; height: 20px; background-color: #dc3545;"></div>
                                    <small>S - Process</small>
                                </div>
                                <div class="d-flex align-items-center me-3">
                                    <div class="me-2 border rounded shadow-sm" style="width: 20px; height: 20px; background-color: #ffffff; border-color:rgb(0, 0, 0) !important; box-shadow: 0 1px 4px rgba(0,0,0,0.10);"></div>
                                    <small>Non S - Process</small>
                                </div>
                            </div>
                        </div>
                    </div>

        <!-- Section Tabel MP -->
        <div class="table-container">
            <!-- Hidden Inputs -->
            <input type="hidden" id="hiddenIdHkt" value="<?php echo isset($_GET['id_hkt']) ? intval($_GET['id_hkt']) : 0; ?>">
            <input type="hidden" id="hiddenIdBagian" value="<?php echo isset($id_bagian) ? intval($id_bagian) : 0; ?>">

            <div class="table-header d-flex align-items-center gap-2">
                <i class="fas fa-users fa-lg"></i>    
                <h3>Data Manpower</h3>
            </div>
            
            <div class="table-responsive">
                <table id="table_mp" class="table table-hover text-center">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Process</th>
                            <th>MP Awal</th>
                            <th>Absensi</th>
                            <th>MP Pengganti</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Baris tabel akan diisi secara dinamis dengan JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="btn-container">
            <div>
                <a href="menu.php" class="btn-custom btn-secondary-custom">← Kembali</a>
            </div>
            <div>
                <button class="btn btn-danger">
                    <i class="fas fa-undo"></i> Reset
                </button>
                <button class="btn btn-success">
                    <i class="fas fa-paper-plane"></i> Submit
                </button>
                <!-- <button class="btn btn-warning btn-dev-submit" style="display: none;">Dev Submit</button> -->
            </div>
        </div>
    </div>
    <br>
    <br>

    <!-- Copyright Footer -->
    <footer class="mt-2 py-2 text-center text-white fixed-bottom" style="background-color: rgb(18, 18, 77); z-index: 1030;">
        <p><b>© 2025 PT Kayaba Indonesia. All Rights Reserved.</b></p>
    </footer>

    <?php //include 'proses/modal/modal_profile.php'; ?>
    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="assets/jquery-ui/jquery-ui.min.js"></script>
    <script src="assets/js/absensi/script.js?v=<?php echo time(); ?>"></script>
</body>
</html>