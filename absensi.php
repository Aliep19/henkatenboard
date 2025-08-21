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
    <title>Henkaten Board - PT Kayaba Indonesia</title>
    <link rel="shortcut icon" href="assets/img/kyb_logo.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/absensi/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
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
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="fas fa-exclamation-circle fa-lg"></i>
            <div class="text-black">
                <strong>Perhatian :</strong>
                Harap melakukan absensi maksimal 4 jam dari awal shift sebelum absensi ditutup.
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
        <button class="btn bg-danger btn-outline-light d-flex align-items-center gap-2 px-3 py-2 shadow-sm mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#legendCollapse" aria-expanded="true" aria-controls="legendCollapse">
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

        <!-- Tabs for Manpower, Support, and Perubahan -->
<ul class="nav nav-tabs mb-4" id="absensiTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <b>
            <button class="nav-link active" id="manpower-tab" data-bs-toggle="tab" data-bs-target="#manpower" type="button" role="tab" aria-controls="manpower" aria-selected="true">
                <i class="fa-solid fa-users me-2"></i> Manpower ( Manual Absensi )
            </button>
        </b>
    </li>
    <li class="nav-item" role="presentation">
        <b>
            <button class="nav-link" id="support-tab" data-bs-toggle="tab" data-bs-target="#support" type="button" role="tab" aria-controls="support" aria-selected="false">
                <i class="fa-solid fa-user-tie me-2"></i> Support ( Foreman & Line Guide )
            </button>
        </b>
    </li>
    <li class="nav-item" role="presentation">
        <b>
            <button class="nav-link" id="perubahan-tab" data-bs-toggle="tab" data-bs-target="#perubahan" type="button" role="tab" aria-controls="perubahan" aria-selected="false">
                <i class="fa-solid fa-id-card me-2"></i> Manpower ( Absensi RFID )
            </button>
        </b>
    </li>
</ul>


        <div class="tab-content" id="absensiTabContent">
            <!-- Manpower Tab -->
            <div class="tab-pane fade show active" id="manpower" role="tabpanel" aria-labelledby="manpower-tab">
                <div class="table-container pb-4">
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
                    <!-- Tombol Aksi Manpower -->
                    <div class="btn-container d-flex justify-content-end gap-2 mt-4 pe-4">
                        <button class="btn btn-success btn-submit-manpower">
                            <i class="fas fa-paper-plane"></i> Submit Manpower
                        </button>
                    </div>
                </div>
            </div>

            <!-- Support Tab -->
            <div class="tab-pane fade" id="support" role="tabpanel" aria-labelledby="support-tab">
                <div class="table-container pb-4">
                    <div class="table-header d-flex align-items-center gap-2">
                        <i class="fas fa-user-shield fa-lg"></i>    
                        <h3>Data Support (Foreman & Line Guide)</h3>
                    </div>
                    <div class="table-responsive">
                        <table id="table_support" class="table table-hover text-center">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Role</th>
                                    <th>NPK Awal</th>
                                    <th>Absensi</th>
                                    <th>NPK Pengganti</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Baris tabel akan diisi secara dinamis dengan JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    <!-- Tombol Aksi Support -->
                    <div class="btn-container d-flex justify-content-end gap-2 mt-4 pe-4">
                        <button class="btn btn-success btn-submit-support">
                            <i class="fas fa-paper-plane"></i> Submit Support
                        </button>
                    </div>
                </div>
            </div>

            <!-- Perubahan Tab -->
            <div class="tab-pane fade" id="perubahan" role="tabpanel" aria-labelledby="perubahan-tab">
                <div class="table-container pb-4">
                    <div class="table-header d-flex align-items-center gap-2">
                        <i class="fas fa-exchange-alt fa-lg"></i>    
                        <h3>Data Perubahan</h3>
                    </div>
                    <div class="table-responsive">
                        <table id="table_perubahan" class="table table-hover text-center">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Process</th>
                                    <th>MP Awal</th>
                                    <th>Reason</th>
                                    <th>MP Pengganti</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Baris tabel akan diisi secara dinamis dengan JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Kembali -->
        <div class="btn-container mt-4">
            <div>
                <a href="menu.php" class="btn-custom btn-secondary-custom">← Kembali</a>
            </div>
        </div>
    </div>
    <br><br>

    <!-- Copyright Footer -->
    <footer class="mt-2 py-2 text-center text-white fixed-bottom" style="background-color: rgb(18, 18, 77); z-index: 1030;">
        <p><b>© 2025 PT Kayaba Indonesia. All Rights Reserved.</b></p>
    </footer>

    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="assets/jquery-ui/jquery-ui.min.js"></script>
    <script src="assets/js/absensi/script.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/absensi/script2.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/absensi/perubahan.js?v=<?php echo time(); ?>"></script>
</body>
</html>