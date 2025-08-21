<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Henkaten Board - PT Kayaba Indonesia</title>
    <link rel="stylesheet" href="assets/css/home.css?v=<?php echo time(); ?>">
    <link rel="shortcut icon" href="assets/img/kyb_logo.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link href="assets/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="assets/jquery-ui/jquery-ui.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <script src="assets/js/chart.umd.min.js"></script>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="assets/js/inactivity.js?v=<?php echo time(); ?>"></script>
</head>
<body class="bg-light">
    <?php include 'proses/HomeController.php'; ?>
    
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

    <div class="container-fluid px-0 pb-5">
        <header class="header bg-white shadow-sm">
            <?php require_once 'assets/include/header.php'; ?>
        </header>

        <div class="container-fluid px-3 py-2">
            <br><br>
            <nav class="navbar navbar-expand-lg py-3" style="background: linear-gradient(135deg, #12124d, #1a1a5e); box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <div class="container-fluid">
                    <div class="d-flex align-items-center gap-3">
                        <?php if (isset($_SESSION['log']) && $_SESSION['log'] === 'True'): ?>
                            <a class="fw-semibold d-flex align-items-center gap-2 px-3 py-2 text-white"
                               href="menu.php" style="border-radius: 12px; color: #fff; text-decoration: none;"
                               onmouseover="this.style.transform='translateY(-3px)';this.style.transition='transform 0.2s';"
                               onmouseout="this.style.transform='none';">
                                MENU UTAMA
                            </a>
                        <?php endif; ?>
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
                        <a class="fw-semibold d-flex align-items-center gap-2 px-3 py-2"
                           href="rule.php" style="border-radius: 12px; color: #fff; text-decoration: none;"
                           onmouseover="this.style.transform='translateY(-3px)';this.style.transition='transform 0.2s';"
                           onmouseout="this.style.transform='none';">
                            RULES
                        </a>
                    </div>
                    <div class="ms-auto"></div>
                    <form class="d-flex flex-wrap align-items-center gap-3" method="GET" action="">
                        <div class="form-group">
                            <label for="line-select" class="form-label text-white mb-1"><strong>LINE</strong></label>
                            <select class="form-select" id="line-select" name="line" onchange="this.form.submit()" style="min-width: 150px;">
                                <option value="">-- Pilih LINE --</option>
                                <?php foreach ($lines as $line): ?>
                                    <option value="<?php echo $line['id']; ?>" <?php echo $line['selected']; ?>><?php echo $line['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="shift-select" class="form-label text-white mb-1"><strong>SHIFT</strong></label>
                            <select class="form-select" id="shift-select" name="shift" onchange="this.form.submit()" style="min-width: 120px;">
                                <option value="">-- Pilih SHIFT --</option>
                                <?php foreach ($shifts as $shift): ?>
                                    <option value="<?php echo $shift['id_shift']; ?>" <?php echo $shift['selected']; ?>><?php echo $shift['shift']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="text-white">
                            <label class="form-label mb-1"><strong>JAM KERJA</strong></label>
                            <div class="bg-light text-dark px-3 py-2 rounded">
                                <?php echo isset($dtime_awal) && isset($dtime_akhir) ? date("H:i", strtotime($dtime_awal)) . " - " . date("H:i", strtotime($dtime_akhir)) : "Jam Kerja Tidak Ditemukan"; ?>
                            </div>
                        </div>
                        <div class="text-white">
                            <label class="form-label mb-1"><strong>OUTPUT TARGET</strong></label>
                            <div class="bg-light text-dark px-3 py-2 rounded">
                                <span><?php echo $output_data['output_target']; ?></span>
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
                        <div class="card-header bg-primary-dark text-white d-flex align-items-center justify-content-between" style="background:rgb(18, 18, 77);">
                            <div class="d-flex align-items-center">
                                <i class="fa fa-user-cog me-2"></i>
                                <h5 class="card-title mb-0"><b>PIC OF PROCESS</b></h5>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge" style="background-color: red; width: 20px; height: 20px;">&nbsp;</span>
                                <small class="text-white me-3">S-Process</small>
                                <span class="badge border border-dark" style="background-color: white; width: 20px; height: 20px;">&nbsp;</span>
                                <small class="text-white">Non S-Process</small>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <div class="scroll-container" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>NO</th>
                                                <th>PROCESS</th>
                                                <th>NPK</th>
                                                <th>NAME</th>
                                                <th>MP STATUS</th>
                                                <th>CHECKED</th>
                                            </tr>
                                        </thead>
                                        <tbody id="process-body">
                                            <?php include 'proses/get_pic_process.php'; ?>
                                            <?php if (empty($pic_data)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center fst-italic py-4" style="color: red; font-weight: bold; font-size: 1.2rem;">
                                                        <?php echo isset($output_data['id_hkt']) ? 'Belum dilakukan Update Henkaten' : 'Belum dilakukan planning DHK'; ?>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($pic_data as $index => $item): ?>
                                                    <tr class="process-row <?php echo htmlspecialchars($item['row_color']); ?>">
                                                        <td class="text-center align-middle fw-bold"><?php echo $index + 1; ?></td>
                                                        <td class="align-middle"><?php echo htmlspecialchars($item['process_name']); ?></td>
                                                        <td class="text-center align-middle">
                                                            <span class="badge bg-primary"><?php echo htmlspecialchars($item['npk']); ?></span>
                                                        </td>
                                                        <td class="align-middle">
                                                            <div class="d-flex align-items-center">
                                                                <i class="fa fa-user-tie me-2"></i>
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
                                                        <td class="text-center align-middle">
                                                            <?php if (canForemanEdit($conn3)): ?>
                                                                <div class="approval-container" data-id="<?= htmlspecialchars($item['id_perubahan']); ?>">
                                                                    <?php if ($item['checked'] === null): ?>
                                                                        <button type="button" class="btn btn-sm btn-primary btn-check-trigger">Check</button>
                                                                    <?php else: ?>
                                                                        <?= renderBadge($item['checked']); ?>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php else: ?>
                                                                <?= renderBadge($item['checked']); ?>
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
            </div>

            <div class="row mt-3 g-3">
                <div class="col-md-3">
                    <div class="card table-responsive" style="height: 35vh;">
                        <div class="card-header bg-primary-dark text-white" style="background:rgb(18, 18, 77);">
                            <h5 class="card-title mb-0"><i class="fa fa-circle-user me-2"></i> Foreman & Line Guide</h5>
                        </div>
                        <div class="card-body p-3 scroll-container" style="max-height: 30vh; overflow-y: auto;">
                            <h6 class="section-title text-primary mb-3">
                                <i class="fa fa-user-tie me-2"></i> Foreman
                            </h6>
                            <?php if ($line_id && $shift_id && !empty($leaders['foreman_list'])): ?>
                                <?php foreach ($leaders['foreman_list'] as $npk => $name): ?>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar bg-primary text-white rounded-circle me-3">
                                            <i class="fa fa-user-tie"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo htmlspecialchars($name); ?></div>
                                            <div class="small text-muted">NPK: <?php echo htmlspecialchars($npk); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-muted small">
                                    <?php echo !$line_id ? 'Pilih Line terlebih dahulu untuk melihat Foreman' :
                                              (!$shift_id ? 'Shift tidak ditemukan' : 'Tidak ada data foreman aktif untuk line dan shift ini'); ?>
                                </div>
                            <?php endif; ?>
                            <hr class="my-3">
                            <h6 class="section-title text-success mb-3">
                                <i class="fa fa-user-tie me-2"></i> Line Guide
                            </h6>
                            <?php if ($line_id && $shift_id && !empty($leaders['line_guide_list'])): ?>
                                <?php foreach ($leaders['line_guide_list'] as $npk => $name): ?>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar bg-success text-white rounded-circle me-3">
                                            <i class="fa fa-user-tie"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo htmlspecialchars($name); ?></div>
                                            <div class="small text-muted">NPK: <?php echo htmlspecialchars($npk); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-muted small">
                                    <?php echo !$line_id ? 'Pilih Line terlebih dahulu untuk melihat Line Guide' :
                                              (!$shift_id ? 'Shift tidak ditemukan' : 'Tidak ada data line guide aktif untuk line dan shift ini'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="card h-55 table-responsive">
                        <div class="card-header bg-primary-dark text-white d-flex justify-content-between align-items-center" style="background:rgb(18, 18, 77);">
                            <div class="d-flex align-items-center">
                                <i class="fa fa-calendar me-1"></i>
                                <h5 class="card-title mb-0 ms-1"><b>Historical Man Power</b></h5>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge" style="background-color: red; width: 20px; height: 20px;">&nbsp;</span>
                                    <small class="text-white me-3">S-Process</small>
                                    <span class="badge border border-dark" style="background-color: white; width: 20px; height: 20px;">&nbsp;</span>
                                    <small class="text-white">Non S-Process</small>
                                </div>
                                <input type="date" id="history-date" class="form-control form-control-sm w-auto"
                                       value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="card-body p-0 table-responsive">
                            <div class="table-responsive scroll-container" style="max-height: 30vh; overflow-y: auto;">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>TANGGAL</th>
                                            <th>PROCESS</th>
                                            <th>BEFORE</th>
                                            <th>REASON</th>
                                            <th>AFTER</th>
                                            <th>MP STATUS</th>
                                            <th>SHIFT</th>
                                            <th>STATUS CHECK</th>
                                        </tr>
                                    </thead>
                                    <tbody id="man-power-data">
                                        <tr>
                                            <td colspan="8" class="text-center text-muted fst-italic py-4">
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

<footer class="py-2 text-center text-white bg-primary-dark fixed-bottom ">
    <p class="mb-0"><b>© 2025 PT Kayaba Indonesia. All Rights Reserved.</b></p>
</footer>


        <?php include 'proses/modal/modal_alert.php'; ?>
        <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
        <script src="assets/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
        <script src="assets/jquery-ui/jquery-ui.min.js"></script>
        <script src="assets/js/script.js?v=<?php echo time(); ?>"></script>
    </div>
</body>
</html>
<?php $conn->close(); ?>