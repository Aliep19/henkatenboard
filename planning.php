<?php
session_start();
// Pemeriksaan sesi: pastikan pengguna sudah login
if (!isset($_SESSION['log']) || $_SESSION['log'] !== 'True') {
    header("Location: login.php");
    exit();
}
// Database connections and queries remain the same
require_once 'konfigurasi/konfig.php';
include 'src/planning.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Henkaten Board - PT Kayaba Indonesia</title>
    <link rel="shortcut icon" href="assets/img/icon.jpg" type="image/x-icon">
    <link rel="stylesheet" href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="assets/jquery-ui/jquery-ui.min.css">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/planning/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/form.css?v=<?php echo time(); ?>">
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="assets/js/inactivity.js?v=<?php echo time(); ?>"></script>
    <!-- Include SheetJS library for Excel parsing -->
    <script src="assets/js/xlsx.full.min.js"></script>
</head>
<body>
    <div class="container-custom py-4">
        <header class="mb-4">
            <?php require_once 'assets/include/header.php'; ?>
        </header>

        <!-- Modal for History DHK -->
        <div class="modal fade" id="historyDhkModal" tabindex="-1" aria-labelledby="historyDhkModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="historyDhkModalLabel">History DHK</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="historyDhkContent">
                            <!-- History data will be loaded here -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for Detail DHK -->
        <div class="modal fade" id="detailDhkModal" tabindex="-1" aria-labelledby="detailDhkModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="detailDhkModalLabel">Detail Proses dan Man Power</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="detailDhkContent">
                            <!-- Detail data will be loaded here -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="card-custom">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <!-- Action Buttons with Toggle Switch -->
                <div class="d-flex gap-2 align-items-center">
                    <button onclick="window.location.href='menu.php'" class="btn btn-custom btn-back text-white" style="background-color: #dc3545;">
                        Kembali
                    </button>
                
                    <div class="dropdown">
                        <button class="btn btn-custom btn-history text-white d-flex align-items-center gap-2 dropdown-toggle shadow-sm" type="button" id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="min-width: 120px;">
                            <i class="fa fa-fill-drip"></i> <span>Auto fill Data</span>
                        </button>
                        
                        <ul class="dropdown-menu dropdown-menu-end shadow rounded-3 p-2" aria-labelledby="actionDropdown" style="min-width: 210px;">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2 rounded" href="#" id="historyDhkButton" data-bs-toggle="modal" data-bs-target="#historyDhkModal">
                                    <i class="fa fa-history text-primary"></i>
                                    <span>History DHK</span>
                                </a>
                            </li>
                            <li>
                                <label for="excelFileInput" class="dropdown-item d-flex align-items-center gap-2 py-2 rounded mb-0" style="cursor:pointer;">
                                    <i class="fa fa-file-excel text-success"></i>
                                    <span>Auto Fill</span>
                                    <input type="file" id="excelFileInput" accept=".xlsx, .xls" style="display: none;">
                                </label>
                            </li>
                        </ul>
                    </div>
                    <style>
                        .dropdown-menu .dropdown-item:hover, 
                        .dropdown-menu .dropdown-item:focus {
                            background-color: #f0f4fa;
                            color: #0c3b69;
                        }
                        .dropdown-menu .dropdown-item i {
                            font-size: 1.2rem;
                        }
                    </style>
                    <!-- Toggle Switch for Auto Fill -->
                    <div class="d-flex align-items-center">
                        <label class="toggle-switch">
                            <input type="checkbox" id="autoFillToggle" checked>
                            <span class="slider"></span>
                        </label>
                        <span class="toggle-label">Auto Fill</span>
                    </div>
                </div>
                <h2 class="page-title mb-0">Tambah Data Produksi</h2>
            </div>
            
            <div class="card-body-custom">
                <form id="hktForm">
                    <!-- Date and Production Info Section -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="header-section">
                                <label class="form-label fw-bold">TANGGAL</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label for="datepicker-mulai" class="form-label">Mulai</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-primary text-white">
                                                <i class="fa fa-calendar-times"></i>
                                            </span>
                                            <input type="text" class="form-control" id="datepicker-mulai" name="tanggal_mulai" placeholder="Tanggal mulai">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label for="datepicker-akhir" class="form-label">Sampai</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-primary text-white">
                                                <i class="fa fa-calendar-times"></i>
                                            </span>
                                            <input type="text" class="form-control" id="datepicker-akhir" name="tanggal_akhir" placeholder="Tanggal akhir">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="header-section">
                                        <label class="form-label">WORKSTATION</label>
                                        <select class="form-select" id="bagianSelect" name="id_bagian">
                                            <option selected disabled>Pilih Workstation</option>
                                            <?php while ($row = mysqli_fetch_assoc($result_workstations)) {
                                                echo "<option value='{$row['id']}'>{$row['name']}</option>";
                                            } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="header-section">
                                        <label class="form-label">LINE</label>
                                        <select class="settings form-select" id="lineSelect" name="id_line">
                                            <option selected disabled>Pilih Line</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="header-section">
                                        <label class="form-label">SHIFT</label>
                                        <select class="form-select" id="shiftSelect" name="id_shift">
                                            <option selected disabled>Pilih Shift</option>
                                            <?php 
                                            while ($row = mysqli_fetch_assoc($result_shift)) {
                                                echo "<option value='{$row['id_shift']}' data-jam-kerja='{$row['jam_kerja']}'>{$row['shift']}</option>";
                                            } 
                                            mysqli_data_seek($result_shift, 0);
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="header-section">
                                        <label class="form-label">WORK HOURS</label>
                                        <input type="text" class="form-control" id="workHours" name="work_hours" readonly>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="header-section">
                                        <label class="form-label">OUTPUT TARGET</label>
                                        <input type="number" class="form-control" id="outputTarget" name="output_target" placeholder="Target">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Foreman and Line Guide Section -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="header-section">
                                <label class="form-label">FOREMAN 1</label>
                                <select class="form-select" id="foreman1Select" name="foreman1">
                                    <option selected disabled>Pilih Foreman 1</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="header-section">
                                <label class="form-label">FOREMAN 2</label>
                                <select class="form-select" id="foreman2Select" name="foreman2">
                                    <option selected disabled>Pilih Foreman 2</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="header-section">
                                <label class="form-label">LINE GUIDE 1</label>
                                <select class="form-select" id="lineGuide1Select" name="line_guide1" onchange="disableOptions()">
                                    <option selected disabled>Pilih Line Guide 1</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="header-section">
                                <label class="form-label">LINE GUIDE 2</label>
                                <select class="form-select" id="lineGuide2Select" name="line_guide2" onchange="disableOptions()">
                                    <option selected disabled>Pilih Line Guide 2</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    
                <!-- Color Legend -->
                        <div class="bg-dark text-white p-3 rounded mb-3 ">
                            <div class="mb-2 fw-bold">Keterangan Warna Manpower :</div>
                            <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                                <div class="d-flex align-items-center me-3">
                                    <div class="me-2 rounded" style="width: 20px; height: 20px; background-color: #dc3545;"></div>
                                    <small>Not Qualified</small>
                                </div>
                                <div class="d-flex align-items-center me-3">
                                    <div class="me-2 border rounded" style="width: 20px; height: 20px; background-color: #ffffff;"></div>
                                    <small>Qualified</small>
                                </div>
                                <div class="d-flex align-items-center me-3">
                                    <div class="me-2 rounded" style="width: 20px; height: 20px; background-color: #ffc107;"></div>
                                    <small>Qualified S-Proses Tetapi Belum Punya Sertifikat</small>
                                </div>
                            </div>

                            <div class="mb-2 fw-bold">Keterangan Warna Proses :</div>
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <div class="d-flex align-items-center me-3">
                                    <div class="me-2 rounded" style="width: 20px; height: 20px; background-color: #dc3545;"></div>
                                    <small>S-Process</small>
                                </div>
                                <div class="d-flex align-items-center me-3">
                                    <div class="me-2 border rounded" style="width: 20px; height: 20px; background-color: #ffffff;"></div>
                                    <small>Non S-Process</small>
                                </div>
                            </div>
                        </div>
                    

                    <!-- Table Section with nice styling -->
                    <div class="table-responsive mb-4">
                        <table class="table table-striped table-hover table-custom align-middle text-center">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">NO</th>
                                    <th style="width: 40%;">PROCESS</th>
                                    <th style="width: 55%;">MAN POWER</th>
                                </tr>
                            </thead>
                            <tbody id="prosesTableBody">
                                <!-- Data proses dimuat melalui AJAX -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Hidden input -->
                    <input type="hidden" id="id_hkt" name="id_hkt" value="">

                    <!-- Form Action Buttons -->
                    <div class="btn-action-bar d-flex justify-content-end">
                        <button type="reset" class="btn btn-danger btn-reset">
                            <i class="fa fa-undo"></i> RESET
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-upload"></i> SUBMIT
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- Copyright Footer -->
        <footer class="text-center">
            <marquee behavior="" direction="Right">
            <p class="mb-0"><b>© 2025 PT Kayaba Indonesia. All Rights Reserved.</b></p>
            </marquee>
        </footer>

        <?php //include 'proses/modal/modal_profile.php'; ?>

        <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
        <script src="assets/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
        <script src="assets/jquery-ui/jquery-ui.min.js"></script>
        
        <!-- SCRIPT KESELURUHAN FUNGSI DI PLANNING.PHP -->
        <script src="assets/js/planning/script.js?v=<?php echo time(); ?>"></script>

    </body>
</html>