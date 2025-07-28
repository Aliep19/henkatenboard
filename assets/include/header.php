<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Henkaten Board - PT Kayaba Indonesia</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="shortcut icon" href="assets/img/icon.jpg" type="image/x-icon">

    <!-- Bootstrap 5 CSS -->
    <link href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="assets/include/style.css" rel="stylesheet">      
    <!-- jQuery & Datepicker CSS -->
    <link href="assets/jquery-ui/jquery-ui.min.css" rel="stylesheet">
</head>

<body onload="startTime()">
    <div class="container-fluid">
        <header class="header">
            <div class="header-left">
                <a href="home.php">
                    <img src="assets/img/kyb.png" alt="KYB Logo" class="logo" style="background-color: white; padding: 5px; border-radius: 10px;">
                </a>
                <div class="title">
                    <h1>HENKATEN BOARD - PT KAYABA INDONESIA</h1>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3 header-right">
                
                <h2 id="date" style="color:rgb(0, 0, 0); margin: 0; font-size: 1rem;font-weight: bolder; font-family: 'Arial', sans-serif; background-color:rgb(0, 217, 255); padding: 5px 10px; border-radius: 5px;"></h2>
                <h2 id="txt" style="color: rgb(255, 215, 0); margin: 0; font-size: 2rem; font-family: sans-serif;"></h2>
                <?php
                // Cek apakah pengguna sudah login
                if (isset($_SESSION['log']) && $_SESSION['log'] === 'True') {
                    $full_name = isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'User';
                    // Ambil nama depan saja
                    $first_name = explode(' ', $full_name)[0];
                ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-light btn-profile dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Profil Pengguna">
                            <span class="welcome-text">Selamat Datang, <?php echo $first_name; ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown" style="background-color: #343a40; border: none;">
                            <li>
                                <button type="button" class="dropdown-item text-white btn btn-logout" id="logoutBtn">Logout</button>
                            </li>
                        </ul>
                    </div>
                <?php
                } else {
                ?>
                    <button class="btn btn-outline-light btn-profile" onclick="alertLogin()" title="Profil Pengguna">
                        <span class="welcome-text">Selamat Datang, Guest</span>
                    </button>
                <?php
                }
                ?>
            </div>
        </header>
    </div>
    <script src="assets/include/script.js"></script>
</body>
</html>