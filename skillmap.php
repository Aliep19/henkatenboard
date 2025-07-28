<?php
session_start();
require_once 'konfigurasi/konfig.php';

// Pastikan line dipilih
if (!isset($_GET['line']) || empty($_GET['line'])) {
    header('Location: home.php');
    exit;
}
require_once 'assets/include/skillmap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skill Map - PT Kayaba Indonesia</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="shortcut icon" href="assets/img/icon.jpg" type="image/x-icon">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">   
    <link rel="stylesheet" href="assets/css/skillmap/style.css?v=<?php echo time(); ?>">
   
</head>
<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <div class="container-fluid px-4">
        <!-- Header -->

        <!-- Main Content -->
        <div class="main-card">
            <h1 class="page-title">Skill Map Dashboard</h1>
            <div class="line-badge">
                <i class="fas fa-industry me-2"></i>
                Line: <?php echo htmlspecialchars($line_name); ?>
            </div>

            <!-- Statistics Bar -->
            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-number" id="totalEmployees"><?php echo count($manpower_data); ?></div>
                    <div class="stat-label">Total Karyawan</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo count($processes); ?></div>
                    <div class="stat-label">Total Proses</div>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="action-bar">
                <button class="btn btn-back" onclick="window.location.href='home.php'">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </button>
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="searchInput" class="search-input" placeholder="Cari NPK atau Nama Karyawan...">
                </div>
            </div>

            <!-- Skill Table -->
            <div class="skill-table-container">
                <div class="table-responsive">
                    <table class="skill-table table" id="skillTable">
                        <thead>
                            <tr>
                                <th style="width: 60px;">No</th>
                                <th style="width: 200px;">Karyawan</th>
                                <?php foreach ($processes as $process_id => $process): ?>
                                    <th style="width: 120px;"><?php echo htmlspecialchars($process['name']); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($manpower_data)): ?>
                                <tr>
                                    <td colspan="<?php echo 2 + count($processes); ?>" class="empty-state">
                                        <i class="fas fa-users"></i>
                                        <div>Tidak ada data manpower untuk line ini.</div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $index = 0; foreach ($manpower_data as $npk => $data): ?>
                                    <tr>
                                        <td class="row-number"><?php echo ++$index; ?></td>
                                        <td class="employee-info">
                                            <div class="employee-npk"><?php echo htmlspecialchars($npk); ?></div>
                                            <div class="employee-name"><?php echo htmlspecialchars($data['name']); ?></div>
                                        </td>
                                        <?php foreach ($processes as $process_id => $process): ?>
                                            <td class="skill-cell">
                                                <?php
                                                $skill_value = isset($data['skills'][$process_id]['value']) ? $data['skills'][$process_id]['value'] : 0;
                                                $max_skill = 4;
                                                $chart_id = "chart_{$npk}_{$process_id}";
                                                ?>
                                                <canvas id="<?php echo $chart_id; ?>" class="skill-chart" 
                                                        data-skill="<?php echo $skill_value; ?>"></canvas>
                                                <div class="skill-value"><?php echo $skill_value . '/' . $max_skill; ?></div>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination Controls -->
            <div class="pagination-wrapper">
                <nav aria-label="Table pagination">
                    <ul class="pagination justify-content-center mb-0" id="pagination"></ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-5 py-3 text-center text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
        <p class="mb-0"><b>© 2025 PT Kayaba Indonesia. All Rights Reserved.</b></p>
    </footer>

    <script src="assets/js/chart.umd.min.js"></script>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/skillmap/script.js?v=<?php echo time(); ?>"></script>
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