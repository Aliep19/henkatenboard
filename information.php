<?php
session_start();
include 'src/workhours.php';
require_once 'konfigurasi/konfig.php';
include 'src/inform.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Henkaten Board Portal - PT Kayaba Indonesia</title>
    <link rel="shortcut icon" href="assets/img/icon.jpg" type="image/x-icon">
    <link rel="stylesheet" href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/informasi/style.css?v=<?php echo time(); ?>">
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <?php if ($hasNews): ?>
        <div id="newsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="8000">
            <div class="carousel-inner">
                <?php foreach ($news_images as $index => $image): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <div class="fullscreen-container">
                            <img src="<?php echo htmlspecialchars($image['filename']); ?>"
                                 alt="<?php 
                                    switch ($image['category']) {
                                        case 'production': echo 'Informasi Produksi'; break;
                                        case 'qa': echo 'Informasi Quality'; break;
                                        case 'method': echo 'Henkaten Method'; break;
                                        case 'material': echo 'Henkaten Material'; break;
                                        case 'machine': echo 'Henkaten Machine'; break;
                                    }
                                 ?> <?php echo $index + 1; ?>"
                                 class="fullscreen-image clickable-image"
                                 data-caption="<?php 
                                    switch ($image['category']) {
                                        case 'production': echo 'Informasi Produksi'; break;
                                        case 'qa': echo 'Informasi Quality'; break;
                                        case 'method': echo 'Henkaten Method'; break;
                                        case 'material': echo 'Henkaten Material'; break;
                                        case 'machine': echo 'Henkaten Machine'; break;
                                    }
                                 ?> <?php echo $index + 1; ?>">
                            <div class="image-overlay"></div>
                            <div class="content-overlay">
                                <div class="top-section"></div>
                                <div class="bottom-section">
                                    <div class="info-card">
                                        <h3 class="info-title">
                                            #<?php echo $index + 1; ?><br>
                                            <?php 
                                            switch ($image['category']) {
                                                case 'production': echo 'Informasi Produksi'; break;
                                                case 'qa': echo 'Informasi Quality'; break;
                                                case 'method': echo 'Henkaten Method'; break;
                                                case 'material': echo 'Henkaten Material'; break;
                                                case 'machine': echo 'Henkaten Machine'; break;
                                            }
                                            ?> 
                                        </h3>
                                        <p class="info-description">
                                            <?php echo !empty($image['description']) ? htmlspecialchars($image['description']) : 'No description available for this item.'; ?>
                                        </p>
                                        <div class="info-meta">
                                            <div class="upload-date">
                                                <i class="fa fa-calendar-check"></i>
                                                <?php echo date("d M Y", strtotime($image['uploaded_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($news_images) > 1): ?>
                <div class="nav-controls left">
                    <button class="nav-btn" type="button" data-bs-target="#newsCarousel" data-bs-slide="prev">
                        <img src="assets/img/back.png" alt="Previous">
                    </button>
                </div>
                <div class="nav-controls right">
                    <button class="nav-btn" type="button" data-bs-target="#newsCarousel" data-bs-slide="next">
                        <img src="assets/img/next.png" alt="Next">
                    </button>
                </div>
                <div class="progress-indicators">
                    <?php for ($i = 0; $i < count($news_images); $i++): ?>
                        <div class="progress-dot bg-dark <?php echo $i === 0 ? 'active' : ''; ?>" 
                             data-bs-target="#newsCarousel" 
                             data-bs-slide-to="<?php echo $i; ?>"></div>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
            <div class="bottom-nav">
                <a href="home.php<?php 
                    $params = [];
                    if (isset($_GET['line'])) $params[] = 'line=' . urlencode($_GET['line']);
                    if (isset($_GET['shift'])) $params[] = 'shift=' . urlencode($_GET['shift']);
                    echo !empty($params) ? '?' . implode('&', $params) : '';
                ?>" class="home-btn">
                    <i class="fa fa-house"></i>
                    <span>Kembali ke Home</span>
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-info-circle"></i>
            <h2>Tidak ada berita terbaru</h2>
            <p>Belum ada informasi baru dari QA, Production, Method, Material, atau Machine dalam sebulan terakhir.</p>
            <div class="mt-4">
                <a href="home.php<?php 
                    $params = [];
                    if (isset($_GET['line'])) $params[] = 'line=' . urlencode($_GET['line']);
                    if (isset($_GET['shift'])) $params[] = 'shift=' . urlencode($_GET['shift']);
                    echo !empty($params) ? '?' . implode('&', $params) : '';
                ?>" class="home-btn">
                    <i class="bi bi-house-door"></i>
                    <span>Kembali ke Home</span>
                </a>
            </div>
        </div>
    <?php endif; ?>
    <script src="assets/js/info.js?v=<?php echo time(); ?>"></script>
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
<?php mysqli_close($conn); ?>