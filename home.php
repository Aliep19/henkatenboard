<?php
session_start();
require_once 'konfigurasi/konfig.php';
include 'src/workhours.php';
include 'src/get_home.php';

// Store selected line and shift in session
if (isset($_GET['line'])) {
    $_SESSION['selected_line'] = $_GET['line'];
}
if (isset($_GET['shift'])) {
    $_SESSION['selected_shift'] = $_GET['shift'];
}

    // Redirect to include line and shift in URL if they are in session but not in GET
    if (!isset($_GET['line']) && isset($_SESSION['selected_line']) || !isset($_GET['shift']) && isset($_SESSION['selected_shift'])) {
        $line = urlencode($_SESSION['selected_line'] ?? '');
        header("Location: home.php?line=$line&shift=$shift");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Henkaten Board - PT Kayaba Indonesia</title>
   
    <link rel="stylesheet" href="assets/css/home.css?v=<?php echo time(); ?>">
    <link rel="shortcut icon" href="assets/img/icon.jpg" type="image/x-icon">
    <link rel="stylesheet" href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link href="assets/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="assets/jquery-ui/jquery-ui.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <script src="assets/js/chart.umd.min.js"></script>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="assets/js/inactivity.js?v=<?php echo time(); ?>"></script>
    
</head>
<body class="bg-light">
    <div class="modal fade" id="skillsModal" tabindex="-1" aria-labelledby="skillsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary-dark text-white">
                    <h5 class="modal-title" id="skillsModalLabel">
                        Skills for <span id="modalManpowerName"></span> ( NPK : <span id="modalManpowerNPK"></span> ) 
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Process Name</th>
                                    <th>Skill Value</th>
                                </tr>
                            </thead>
                            <tbody id="skillsTableBody"></tbody>
                        </table>
                    </div>
                    <div id="noSkillsMessage" class="text-center text-muted fst-italic py-4" style="display: none;">
                        Tidak ada data skill untuk karyawan ini.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-0">
        <header class="header bg-white shadow-sm">
            <?php require_once 'assets/include/header.php'; ?>
        </header>

        <div class="container-fluid px-3 py-2">
            <br><br>
            <nav class="navbar navbar-expand-lg py-3" style="background: linear-gradient(135deg, #12124d, #1a1a5e); box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <div class="container-fluid">
                    <div class="d-flex align-items-center gap-3">
                        <a class="fw-semibold d-flex align-items-center gap-2 px-3 py-2 <?php echo !isset($_SESSION['log']) || $_SESSION['log'] !== 'True' ? 'disabled text-white' : 'text-white'; ?>" 
                           href="menu.php" style="border-radius: 12px; color: #fff; text-decoration: none;<?php echo (!isset($_SESSION['log']) || $_SESSION['log'] !== 'True') ? ' pointer-events: none; opacity: 0.7;' : ''; ?>"
                           onmouseover="this.style.transform='translateY(-3px)';this.style.transition='transform 0.2s';"
                           onmouseout="this.style.transform='none';">
                            MENU UTAMA
                        </a>
                        <a class="fw-semibold d-flex align-items-center gap-2 px-3 py-2" 
                           href="#" onclick="handleSkillMap()" style="border-radius: 12px; color: #fff; text-decoration: none;"
                           onmouseover="this.style.transform='translateY(-3px)';this.style.transition='transform 0.2s';"
                           onmouseout="this.style.transform='none';">
                            SKILL MAP
                        </a>
                        <a class="fw-semibold d-flex align-items-center gap-2 px-3 py-2" 
                           href="information.php" style="border-radius: 12px; color: #fff; text-decoration: none;"
                           onmouseover="this.style.transform='translateY(-3px)';this.style.transition='transform 0.2s';"
                           onmouseout="this.style.transform='none';">
                            INFORMASI
                        </a>
                        <!-- Updated Panduan Button with Dropdown -->
                        <div class="dropdown">
                            <a class="fw-semibold d-flex align-items-center gap-2 px-3 py-2 dropdown-toggle" 
                               href="#" id="panduanDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                               style="border-radius: 12px; color: #fff; text-decoration: none;"
                               onmouseover="this.style.transform='translateY(-3px)';this.style.transition='transform 0.2s';"
                               onmouseout="this.style.transform='none';">
                            PANDUAN
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="panduanDropdown">
                                <li>
                                    <a class="dropdown-item" href="#" onclick="openManualBook('henkaten')">
                                        <i class="fa fa-book-open-reader me-2"></i>User Manual Book Henkaten
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="ms-auto"></div>
                    <form class="d-flex flex-wrap align-items-center gap-3" method="GET" action="">
                        <div class="form-group">
                            <label for="line-select" class="form-label text-white mb-1"><strong>LINE</strong></label>
                            <select class="form-select" id="line-select" name="line" onchange="this.form.submit()" style="min-width: 150px;">
                                <option value="">-- Pilih LINE --</option>
                                <?php
                                $query = "SELECT sub_workstations.id, sub_workstations.name FROM sub_workstations";
                                $result = mysqli_query($conn3, $query);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $selected = (isset($_GET['line']) && $_GET['line'] == $row['id']) || 
                                                (!isset($_GET['line']) && isset($_SESSION['selected_line']) && $_SESSION['selected_line'] == $row['id']) 
                                                ? 'selected' : '';
                                    echo '<option value="' . $row['id'] . '" ' . $selected . '>' . htmlspecialchars($row['name']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="shift-select" class="form-label text-white mb-1"><strong>SHIFT</strong></label>
                            <select class="form-select" id="shift-select" name="shift" onchange="this.form.submit()" style="min-width: 120px;">
                                <option value="">-- Pilih SHIFT --</option>
                                <?php
                                $query_shift = "SELECT id_shift, shift FROM shift";
                                $result_shift = mysqli_query($conn, $query_shift);
                                while ($row = mysqli_fetch_assoc($result_shift)) {
                                    $selected = (isset($_GET['shift']) && $_GET['shift'] == $row['id_shift']) || 
                                                (!isset($_GET['shift']) && isset($_SESSION['selected_shift']) && $_SESSION['selected_shift'] == $row['id_shift']) 
                                                ? 'selected' : 
                                                (!isset($_GET['shift']) && $row['id_shift'] == $shift ? 'selected' : '');
                                    echo '<option value="' . $row['id_shift'] . '" ' . $selected . '>' . htmlspecialchars($row['shift']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="text-white">
                            <label class="form-label mb-1"><strong>JAM KERJA</strong></label>
                            <div class="bg-light text-dark px-3 py-2 rounded">
                                <?= isset($dtime_awal) && isset($dtime_akhir) ? date("H:i", strtotime($dtime_awal)) . " - " . date("H:i", strtotime($dtime_akhir)) : "Jam Kerja Tidak Ditemukan"; ?>
                            </div>
                        </div>
                        <div class="text-white">
                            <label class="form-label mb-1"><strong>OUTPUT TARGET</strong></label>
                            <div class="bg-light text-dark px-3 py-2 rounded">
                                <?php
                                $output_target = "Tidak Ada Target";
                                $id_hkt = null;
                                if (isset($_GET['line']) && isset($_GET['shift'])) {
                                    $id_line = $_GET['line'];
                                    $shift_id = $_GET['shift'];
                                    $today = date("Y-m-d");
                                    $query_target = "
                                        SELECT id_hkt, output_target 
                                        FROM hkt_form 
                                        WHERE id_line = ? 
                                        AND id_shifft = ?
                                        AND ? BETWEEN date AND to_date
                                        LIMIT 1
                                    ";
                                    $stmt_target = $conn->prepare($query_target);
                                    if ($stmt_target === false) {
                                        die('MySQL prepare error (output_target): ' . $conn->error);
                                    }
                                    $stmt_target->bind_param("iis", $id_line, $shift_id, $today);
                                    $stmt_target->execute();
                                    $result_target = $stmt_target->get_result();
                                    if ($result_target && $result_target->num_rows > 0) {
                                        $row_target = $result_target->fetch_assoc();
                                        $output_target = $row_target['output_target'] . " Units";
                                        $id_hkt = $row_target['id_hkt'];
                                    }
                                    $stmt_target->close();
                                }
                                ?>
                                <span><?= $output_target ?></span>
                            </div>
                        </div>
                    </form>
                </div>
            </nav>

            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body p-3">
                            <?php include 'proses/img_handler.php'; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-55">
                        <div class="card-header bg-primary-dark text-white d-flex align-items-center" style="background:rgb(18, 18, 77);">
                            <i class="fa fa-user-cog me-2"></i>
                            <h5 class="card-title mb-0"><b>PIC OF PROCESS</b></h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>NO</th>
                                            <th>PROCESS</th>
                                            <th>NPK</th>
                                            <th>NAME</th>
                                            <th>MP STATUS</th>
                                        </tr>
                                    </thead>
                                    <tbody id="process-body">
                                        <?php include 'proses/get_pic_process.php'; ?>
                                        <?php if (empty($pic_data)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center fst-italic py-4" style="color: red; font-weight: bold; font-size: 1.2rem;">
                                                    <?php echo isset($id_hkt) ? 'Belum dilakukan Update Henkaten' : 'Belum dilakukan planning DHK'; ?>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($pic_data as $index => $item): ?>
                                                <tr class="process-row <?= htmlspecialchars($item['row_color']); ?>">
                                                    <td class="text-center align-middle fw-bold"><?php echo $index + 1; ?></td>
                                                    <td class="align-middle"><?php echo htmlspecialchars($item['process_name']); ?></td>
                                                    <td class="text-center align-middle">
                                                        <span class="badge bg-primary"><?php echo htmlspecialchars($item['npk']); ?></span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fa fa fa-user-tie me-2"></i>
                                                            <span><?php echo htmlspecialchars($item['name']); ?></span>
                                                        </div>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <?php if (!empty($item['qualification_status'])): ?>
                                                            <span class="badge <?php echo htmlspecialchars($item['qualification_color']); ?> text-white clickable-badge" 
                                                                data-npk="<?php echo htmlspecialchars($item['npk']); ?>" 
                                                                data-name="<?php echo htmlspecialchars($item['name']); ?>" 
                                                                style="cursor: pointer;">
                                                                <?php echo htmlspecialchars($item['qualification_status']); ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span>-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3 g-3">
                <div class="col-md-4">
                    <div class="card table-responsive" style="height: 35vh;">
                        <div class="card-header bg-primary-dark text-white" style="background:rgb(18, 18, 77);">
                            <h5 class="card-title mb-0"><i class="fa fa-circle-user me-2"></i> Foreman & Line Guide</h5>
                        </div>
                        <div class="card-body p-3">
                            <h6 class="section-title text-primary mb-3">
                                <i class="fa fa-user-tie me-2"></i> Foreman
                            </h6>
                            <?php
                            $line_id = isset($_GET['line']) ? $_GET['line'] : '';
                            $shift_id = isset($_GET['shift']) ? $_GET['shift'] : '';
                            if ($line_id && $shift_id) {
                                $query_foreman = "
                                    SELECT foreman, foreman_2 
                                    FROM hkt_form 
                                    WHERE date <= CURDATE() AND to_date >= CURDATE() AND id_line = ? AND id_shifft = ?";
                                $stmt_foreman = $conn->prepare($query_foreman);
                                if ($stmt_foreman === false) {
                                    die('MySQL prepare error (foreman): ' . $conn->error);
                                }
                                $stmt_foreman->bind_param("ii", $line_id, $shift_id);
                                $stmt_foreman->execute();
                                $result_foreman = $stmt_foreman->get_result();
                                $foreman_list = [];
                                if ($result_foreman && $result_foreman->num_rows > 0) {
                                    while ($row = $result_foreman->fetch_assoc()) {
                                        if (!empty($row['foreman'])) {
                                            $npk = str_pad($row['foreman'], 5, "0", STR_PAD_LEFT);
                                            $foreman_list[$npk] = $row['foreman'];
                                        }
                                        if (!empty($row['foreman_2'])) {
                                            $npk = str_pad($row['foreman_2'], 5, "0", STR_PAD_LEFT);
                                            $foreman_list[$npk] = $row['foreman_2'];
                                        }
                                    }
                                }
                                if (!empty($foreman_list)) {
                                    foreach ($foreman_list as $npk_padded => $npk_raw) {
                                        $query_nama = "SELECT name FROM karyawan WHERE npk = ?";
                                        $stmt_nama = $conn3->prepare($query_nama);
                                        if ($stmt_nama === false) {
                                            die('MySQL prepare error (nama): ' . $conn3->error);
                                        }
                                        $stmt_nama->bind_param("s", $npk_padded);
                                        $stmt_nama->execute();
                                        $result_nama = $stmt_nama->get_result();
                                        $nama = $npk_raw;
                                        if ($result_nama && $result_nama->num_rows > 0) {
                                            $row_nama = $result_nama->fetch_assoc();
                                            $nama = $row_nama['name'];
                                        }
                                        echo '
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="avatar bg-primary text-white rounded-circle me-3">
                                                <i class="fa fa-user-tie"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">' . htmlspecialchars($nama) . '</div>
                                                <div class="small text-muted">NPK: ' . htmlspecialchars($npk_padded) . '</div>
                                            </div>
                                        </div>';
                                    }
                                } else {
                                    echo '<div class="text-muted small">Tidak ada data foreman aktif untuk line dan shift ini</div>';
                                }
                                $stmt_foreman->close();
                            } else {
                                if (!$line_id) {
                                    echo '<div class="text-muted small">Pilih Line terlebih dahulu untuk melihat Foreman</div>';
                                } elseif (!$shift_id) {
                                    echo '<div class="text-muted small">Shift tidak ditemukan</div>';
                                }
                            }
                            ?>
                            <hr class="my-3">
                            <h6 class="section-title text-success mb-3">
                                <i class="fa fa-user-tie me-2"></i> Line Guide
                            </h6>
                            <?php
                            if ($line_id && $shift_id) {
                                $query_lg = "
                                    SELECT line_guide, line_guide2 
                                    FROM hkt_form 
                                    WHERE date <= CURDATE() AND to_date >= CURDATE() AND id_line = ? AND id_shifft = ?";
                                $stmt_lg = $conn->prepare($query_lg);
                                if ($stmt_lg === false) {
                                    die('MySQL prepare error (line_guide): ' . $conn->error);
                                }
                                $stmt_lg->bind_param("ii", $line_id, $shift_id);
                                $stmt_lg->execute();
                                $result_lg = $stmt_lg->get_result();
                                $line_guide_list = [];
                                if ($result_lg && $result_lg->num_rows > 0) {
                                    while ($row = $result_lg->fetch_assoc()) {
                                        if (!empty($row['line_guide'])) {
                                            $npk = str_pad($row['line_guide'], 5, "0", STR_PAD_LEFT);
                                            $line_guide_list[$npk] = $row['line_guide'];
                                        }
                                        if (!empty($row['line_guide2'])) {
                                            $npk = str_pad($row['line_guide2'], 5, "0", STR_PAD_LEFT);
                                            $line_guide_list[$npk] = $row['line_guide2'];
                                        }
                                    }
                                }
                                if (!empty($line_guide_list)) {
                                    foreach ($line_guide_list as $npk_padded => $npk_raw) {
                                        $query_nama = "SELECT name FROM karyawan WHERE npk = ?";
                                        $stmt_nama = $conn3->prepare($query_nama);
                                        if ($stmt_nama === false) {
                                            die('MySQL prepare error (nama): ' . $conn3->error);
                                        }
                                        $stmt_nama->bind_param("s", $npk_padded);
                                        $stmt_nama->execute();
                                        $result_nama = $stmt_nama->get_result();
                                        $nama = $npk_raw;
                                        if ($result_nama && $result_nama->num_rows > 0) {
                                            $row_nama = $result_nama->fetch_assoc();
                                            $nama = $row_nama['name'];
                                        }
                                        echo '
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="avatar bg-success text-white rounded-circle me-3">
                                                <i class="fa fa fa-user-tie"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">' . htmlspecialchars($nama) . '</div>
                                                <div class="small text-muted">NPK: ' . htmlspecialchars($npk_padded) . '</div>
                                            </div>
                                        </div>';
                                    }
                                } else {
                                    echo '<div class="text-muted small">Tidak ada data line guide aktif untuk line dan shift ini</div>';
                                }
                                $stmt_lg->close();
                            } else {
                                if (!$line_id) {
                                    echo '<div class="text-muted small">Pilih Line terlebih dahulu untuk melihat Line Guide</div>';
                                } elseif (!$shift_id) {
                                    echo '<div class="text-muted small">Shift tidak ditemukan</div>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card h-55 table-responsive">
                        <div class="card-header bg-primary-dark text-white d-flex justify-content-between align-items-center" style="background:rgb(18, 18, 77);">
                            <div class="d-flex align-items-center">
                                <i class="fa fa-calendar me-1"></i>
                                <h5 class="card-title mb-0 ms-1"><b>Historical Man Power</b></h5>
                            </div>
                            <input type="date" id="history-date" class="form-control form-control-sm w-auto" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="card-body p-0 table-responsive">
                            <div class="table-responsive history-table">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>TANGGAL</th>
                                            <th>PROCESS</th>
                                            <th>BEFORE</th>
                                            <th>REASON</th>
                                            <th>AFTER</th>
                                            <th>SHIFT</th>
                                        </tr>
                                    </thead>
                                    <tbody id="man-power-data">
                                        <tr>
                                            <td colspan="6" class="text-center text-muted fst-italic py-4">
                                                Pilih Line terlebih dahulu
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <footer class="mt-3 py-2 text-center text-white bg-primary-dark">
            <p class="mb-0"><b>© 2025 PT Kayaba Indonesia. All Rights Reserved.</b></p>
        </footer>

        <?php include 'proses/modal/modal_alert.php'; ?>
        <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
        <script src="assets/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
        <script src="assets/jquery-ui/jquery-ui.min.js"></script>
        <script src="assets/js/script.js?v=<?php echo time(); ?>"></script>
        
        <script>
        // Function to handle manual book opening
        function openManualBook(type) {
            if (type === 'henkaten') {
                window.open('assets/documents/user_manual_henkaten.pdf', '_blank');
            } 

        }
        </script>
        <script>
function logCekAbsensi() {
    fetch('cek_absensi.php')
        .then(res => res.text())
        .then(text => {
            console.log('--- Log Cek Absensi ---\n' + text);
        })
        .catch(err => {
            console.error('[❌] Gagal fetch cek_absensi.php:', err);
        });
}

logCekAbsensi(); // saat load pertama
setInterval(logCekAbsensi, 300000); // ulang tiap 5 menit
</script>
      
    </body>
</html>
<?php
$conn->close();
?>