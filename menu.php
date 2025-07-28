<?php
session_start();
// Pemeriksaan sesi: pastikan pengguna sudah login
if (!isset($_SESSION['log']) || $_SESSION['log'] !== 'True') {
    header("Location: login.php");
    exit();
}
require_once 'konfigurasi/konfig.php';
include 'src/workhours.php';

// Access control logic
$isProduction = (isset($_SESSION['dept']) && stripos($_SESSION['dept'], 'Production') === 0);
$isQA = (isset($_SESSION['dept']) && strcasecmp($_SESSION['dept'], 'QA') === 0);
$isGolongan34 = (isset($_SESSION['golongan']) && in_array($_SESSION['golongan'], [3, 4]));

// Determine access permissions
$canAccessPlanning = $isProduction && $isGolongan34;
$canAccessAbsensi = $isProduction && $isGolongan34;
$canAccessInformasi = ($isProduction && $isGolongan34) || $isQA;

// Query to fetch sub_workstations based on user's dept
$sub_workstations = [];
if ($canAccessAbsensi) {
    $dept = mysqli_real_escape_string($conn3, $_SESSION['dept']);
    $query_sub_workstations = "
        SELECT sw.id, sw.name 
        FROM sub_workstations sw
        JOIN workstations w ON sw.workstation_id = w.id
        JOIN department d ON w.dept_id = d.id
        WHERE d.dept_name = '$dept'";
    $result_sub_workstations = mysqli_query($conn3, $query_sub_workstations);
    while ($row = mysqli_fetch_assoc($result_sub_workstations)) {
        $sub_workstations[] = $row;
    }
    mysqli_free_result($result_sub_workstations);
}
mysqli_close($conn3);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Henkaten Board - PT Kayaba Indonesia</title>
    <link rel="stylesheet" href="assets/css/menu.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/home.css?v=<?php echo time(); ?>">
    <link rel="shortcut icon" href="assets/img/icon.jpg" type="image/x-icon">
    <link rel="stylesheet" href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link href="assets/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="assets/jquery-ui/jquery-ui.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="assets/js/inactivity.js?v=<?php echo time(); ?>"></script>
</head>
<body>
    <!-- Header -->
    <header class="header mb-4" style="background-color: red;">
        <?php require_once 'assets/include/header.php'; ?>
    </header>

    <!-- Main Content -->
    <main class="menu-container" style="height: 90vh;">
        <h1 class="menu-section-title">Main Menu</h1>
        <p class="menu-section-subtitle">Pilih menu untuk mengakses berbagai fitur</p>

        <div class="container" style="height: 50vh;">
            <div class="row justify-content-center">
                <!-- Dashboard Henkaten Card -->
                <div class="col-md-6 col-lg-3 d-flex justify-content-center">
                    <div class="menu-card" onclick="windowJob.location.href='home.php'">
                        <div class="card-hover-effect"></div>
                        <div class="d-flex justify-content-center">
                            <img src="assets/img/dashboard.png" class="card-img-top" style="height: 20vh; width: 20vh" alt="Dashboard Henkaten">
                        </div>
                        <div class="card-body">
                            <i class="fa fa-house-user menu-icon"></i>
                            <h5 class="card-title">Dashboard Henkaten</h5>
                            <p class="card-text">Akses dashboard utama untuk memantau terkait data Henkaten secara efisien</p>
                        </div>
                    </div>
                </div>

                <!-- Planning DHK Card -->
                <?php if ($canAccessPlanning): ?>
                <div class="col-md-6 col-lg-3 d-flex justify-content-center">
                    <div class="menu-card" onclick="window.location.href='planning.php'">
                        <div class="card-hover-effect"></div>
                        <img src="assets/img/planning.png" class="card-img-top" alt="Planning DHK">
                        <div class="card-body">
                            <i class="fa fa-calendar-check menu-icon"></i>
                            <h5 class="card-title">Planning DHK</h5>
                            <p class="card-text">Kelola dan lihat perencanaan kerja harian serta jadwal untuk produktivitas optimal</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Absensi Card -->
                <?php if ($canAccessAbsensi): ?>
                <div class="col-md-6 col-lg-3 d-flex justify-content-center">
                    <div class="menu-card" data-bs-toggle="modal" data-bs-target="#absensiModal">
                        <div class="card-hover-effect"></div>
                        <div class="d-flex justify-content-center">
                            <img src="assets/img/calendar.png" class="card-img-top" style="height: 20vh; width: 20vh" alt="Absensi">
                        </div>
                        <div class="card-body">
                            <i class="fa fa-clipboard-list menu-icon"></i>
                            <h5 class="card-title">Absensi</h5>
                            <p class="card-text">Pengelolaan absensi karyawan dan perubahan yang komprehensif</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Informasi Card -->
                <?php if ($canAccessInformasi): ?>
                <div class="col-md-6 col-lg-3 d-flex justify-content-center">
                    <div class="menu-card" onclick="window.location.href='datainformasi.php'">
                        <div class="card-hover-effect"></div>
                        <div class="d-flex justify-content-center">
                            <img src="assets/img/inform.png" class="card-img-top" style="height: 20vh; width: 20vh" alt="Informasi">
                        </div>
                        <div class="card-body">
                            <i class="fa fa-circle-info menu-icon"></i>
                            <h5 class="card-title">Update Informasi</h5>
                            <p class="card-text">Update informasi terkait line maupun seputar berita di Produksi dan Quality Assurance</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Absensi Selection Modal -->
    <?php if ($canAccessAbsensi): ?>
    <div class="modal fade" id="absensiModal" tabindex="-1" aria-labelledby="absensiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="absensiModalLabel">Pilih Line</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="absensiForm">
                        <div class="mb-3">
                            <label for="lineSelect" class="form-label">Line : </label>
                            <select class="selectpicker" id="lineSelect" name="line" data-live-search="true" required>
                                <option value="" disabled selected>Pilih Line</option>
                                <?php foreach ($sub_workstations as $sub_workstation): ?>
                                    <option value="<?php echo $sub_workstation['id']; ?>"><?php echo htmlspecialchars($sub_workstation['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Lanjutkan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Copyright Footer -->
    <footer class="mt-2 py-2 text-center text-white" style="background-color: rgb(18, 18, 77);">
        <p><b>© 2025 PT Kayaba Indonesia. All Rights Reserved.</p></b>
    </footer>

    <?php include 'proses/modal/modal_alert.php'; ?>

    <?php
    // Tampilkan notifikasi welcome jika pengguna baru saja login
    if (isset($_SESSION['just_logged_in']) && $_SESSION['just_logged_in'] === true) {
        $nama = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'User';
    ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Login Successful!',
                text: 'Welcome, <?php echo htmlspecialchars($nama); ?>!',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6'
            });
        </script>
    <?php
        // Hapus flag agar notifikasi tidak muncul lagi saat refresh
        unset($_SESSION['just_logged_in']);
    }
    ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="assets/js/menu/script.js?v=<?php echo time(); ?>"></script>

    <style>
        @keyframes ripple {
            to {
                transform: scale(3);
                opacity: 0;
            }
        }
    </style>
</body>
</html>