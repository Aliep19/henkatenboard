<?php
    session_start(); // Mulai session sebelum mengakses $_SESSION

    // Check if user is already logged in
    if (isset($_SESSION['log']) && $_SESSION['log'] === 'True') {
        header("Location: home.php");
        exit();
    }

    require_once __DIR__ . './assets/phpPasswordHashingLib/passwordLib.php';
    require 'konfigurasi/konfig.php';

    // Function to mask the phone number and show only the last 4 digits
    function maskPhoneNumber($phone)
    {
        if (strlen($phone) <= 4) {
            return $phone;
        }
        $lastPart = substr($phone, -4);
        $maskedPart = str_repeat('*', strlen($phone) - 4);
        return $maskedPart . $lastPart;
    }

    // Check if user is in OTP verification phase
    $show_otp_modal = false;
    $no_hp = '';
    $remaining_time = 300; // Default timer duration in seconds
    if (isset($_SESSION['otp_code']) && isset($_SESSION['npk']) && isset($_SESSION['otp_sent_time'])) {
        // Calculate remaining time
        $elapsed_time = time() - $_SESSION['otp_sent_time'];
        $remaining_time = max(0, 300 - $elapsed_time); // Ensure non-negative

        if ($remaining_time > 0) {
            // Fetch phone number for OTP modal
            $npk = mysqli_real_escape_string($conn4, $_SESSION['npk']);
            $sql_no_hp = "SELECT no_hp FROM hp WHERE npk = '$npk'";
            $result_no_hp = mysqli_query($conn4, $sql_no_hp);
            if ($no_hp_row = mysqli_fetch_assoc($result_no_hp)) {
                $no_hp = $no_hp_row['no_hp'];
            }
            $show_otp_modal = true;
        } else {
            // OTP expired, clear session and show error
            unset($_SESSION['otp_code']);
            unset($_SESSION['otp_sent_time']);
            unset($_SESSION['npk']);
            unset($_SESSION['golongan']);
            unset($_SESSION['acting']);
            unset($_SESSION['redirect_url']);
            echo '<script>Swal.fire("Error", "OTP telah kedaluwarsa. Silakan login kembali.", "error").then(() => { window.location.href = "login.php"; });</script>';
            exit();
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login HenkatenBoard</title>
    <link rel="stylesheet" href="assets/css/login.css?v=<?php echo time(); ?>">
    <link rel="shortcut icon" href="assets/img/icon.jpg" type="image/x-icon">
    <link rel="stylesheet" href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/bootstrap-icons/bootstrap-icons/bootstrap-icons.min.css">
</head>

<body>
    <div class="container">
        <div class="login-box" style="box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.5);">
            <a href="home.php">
                <img src="assets/img/kyb.png" alt="KYB Logo" style="width: 200px; margin-bottom: 20px;">
            </a>
            <form id="loginForm" method="post">
                <div class="textbox mb-3">
                    <label for="npk" class="form-label" style="font-weight: bold;">NPK </label>
                    <input type="text" id="npk" name="npk" class="form-control" placeholder="Type your NPK" required>
                </div>
                <div class="textbox mb-3">
                    <label for="pwd" class="form-label">PASSWORD </label>
                    <div class="input-group">
                        <input type="password" id="pwd" name="pwd" class="form-control" placeholder="Type your Password" required>
                        <span class="input-group-text" style="background: transparent; border-left: none;">
                            <i class="bi bi-eye-slash" id="togglePassword" style="cursor: pointer;"></i>
                        </span>
                    </div>
                </div>
                <div class="textbox mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="captcha-image">
                            <img src="assets/Captcha/Captcha.php" alt="Captcha Code" id="captchaImage" class="img-fluid">
                        </div>
                        <div class="captcha-input flex-grow-1">
                            <input type="text" class="form-control" id="user_input" name="captcha" 
                                   placeholder="Enter captcha code" maxlength="8" required>
                            <small class="text-muted mt-1 d-block">
                                Captcha not read? Refresh <a href="javascript:void(0);" 
                                   onclick="refreshCaptcha()" class="text-decoration-none">here</a>
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" id="login" name="login" value="login" 
                            class="btn btn-danger w-100">Login</button>
                </div>
            </form>
            <div id="notification" class="notification"></div>
        </div>
    </div>

    <script src="assets/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            var passwordField = document.getElementById('pwd');
            var type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });

        function refreshCaptcha() {
            const captchaImage = document.getElementById('captchaImage');
            const timestamp = new Date().getTime();
            captchaImage.src = 'assets/Captcha/Captcha.php?ts=' + timestamp;
        }
    </script>

    <?php
    if (isset($_POST['login'])) {
        $npk = trim($_POST['npk']);
        $pwd = $_POST['pwd'];
        $captcha_input = $_POST['captcha'];

        $_SESSION['captcha_code'] = isset($_SESSION['captcha_code']) ? $_SESSION['captcha_code'] : '';
        $captcha = $_SESSION['captcha_code'];

        if ($captcha_input == $captcha) {
            $npk = mysqli_real_escape_string($conn2, $npk);
            $pwd = mysqli_real_escape_string($conn2, $pwd);

            $query = "SELECT * FROM ct_users WHERE npk = '$npk'";
            $result = mysqli_query($conn2, $query);
            $hitung = mysqli_num_rows($result);

            if ($hitung > 0) {
                $row = mysqli_fetch_assoc($result);
                if (password_verify($pwd, $row['pwd'])) {
                    // Store temporary session data for OTP verification
                    $_SESSION['npk'] = $npk;
                    $_SESSION['golongan'] = $row['golongan'];
                    $_SESSION['dept'] = $row['dept'];
                    $_SESSION['sect'] = $row['sect'];
                    $_SESSION['acting'] = $row['acting'];

                    $golongan = $_SESSION['golongan'];

                    // Set redirect URL based on golongan
                    if ($golongan == 3) {
                        $_SESSION['redirect_url'] = 'menu.php';
                    } elseif ($golongan == 4) {
                        $_SESSION['redirect_url'] = 'menu.php';
                    }
                    else{
                        $_SESSION['redirect_url'] = 'menu.php';
                    }

                    // Generate OTP code
                    $otp_code = sprintf('%06d', mt_rand(0, 999999));
                    $_SESSION['otp_code'] = $otp_code;

                    // Get phone number from 'hp' table
                    $sql_no_hp = "SELECT no_hp FROM hp WHERE npk = '$npk'";
                    $result_no_hp = mysqli_query($conn4, $sql_no_hp);

                    if ($no_hp_row = mysqli_fetch_assoc($result_no_hp)) {
                        $no_hp = $no_hp_row['no_hp'];
                    } else {
                        $no_hp = '';
                    }

                    // Insert/update OTP in the database
                    $sql_check = "SELECT COUNT(*) as count FROM otp WHERE npk = '$npk'";
                    $result_check = mysqli_query($conn, $sql_check);
                    $check_row = mysqli_fetch_assoc($result_check);

                    if ($check_row['count'] > 0) {
                        $sql_update = "UPDATE otp SET otp = '$otp_code', no_hp = '$no_hp', send = '2', `use` = '2' WHERE npk = '$npk'";
                        mysqli_query($conn, $sql_update);
                    } else {
                        $sql_insert = "INSERT INTO otp (npk, otp, no_hp, send, `use`) VALUES ('$npk', '$otp_code', '$no_hp', '2', '2')";
                        mysqli_query($conn, $sql_insert);
                    }

                    $_SESSION['otp_sent_time'] = time();
                    $show_otp_modal = true;
                    $remaining_time = 300; // Reset timer for new OTP
                } else {
                    echo '<script>Swal.fire("Error", "Password salah", "error").then(() => { window.location.href = "login.php"; });</script>';
                    exit();
                }
            } else {
                echo '<script>Swal.fire("Error", "NPK tidak ditemukan", "error").then(() => { window.location.href = "login.php"; });</script>';
                exit();
            }
        } else {
            echo '<script>Swal.fire("Error", "Captcha salah", "error").then(() => { window.location.href = "login.php"; });</script>';
            exit();
        }
    }
    ?>

    <?php if ($show_otp_modal): ?>
        <!-- OTP Modal -->
        <div class="modal fade" id="otpModal" tabindex="-1" role="dialog" aria-labelledby="otpModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="otpModalLabel">Masukan OTP</h5>
                    </div>
                    <div class="modal-body">
                        <p>Silakan masukkan kode OTP</p>
                        <div class="alert alert-info">OTP telah dikirim ke:
                            <strong><?php echo htmlspecialchars(maskPhoneNumber($no_hp)); ?></strong>
                        </div>

                        <!-- Form OTP -->
                        <form id="otpForm" method="POST">
                            <div class="otp-container">
                                <input type="text" name="otp1" id="otp1" maxlength="1" class="otp-field" required>
                                <input type="text" name="otp2" id="otp2" maxlength="1" class="otp-field" required>
                                <input type="text" name="otp3" id="otp3" maxlength="1" class="otp-field" required>
                                <input type="text" name="otp4" id="otp4" maxlength="1" class="otp-field" required>
                                <input type="text" name="otp5" id="otp5" maxlength="1" class="otp-field" required>
                                <input type="text" name="otp6" id="otp6" maxlength="1" class="otp-field" required>
                            </div>
                            <div id="countdown" class="text-primary"></div>
                            <div id="resendOtp" class="text-danger d-none" style="cursor: pointer;">Kirim Ulang OTP</div>
    </div>

                    <div class="modal-footer">
                        <button type="submit" id="verifyBtn" class="btn btn-primary btn-disabled" disabled>Verifikasi OTP</button>
                    </div>
                    </form>

                    <!-- Loader -->
                    <div id="loader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); z-index: 9999; text-align: center;">
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                            <img src="assets/img/loading.gif" alt="Loading..." style="width: 150px; height: 150px;">
                            <p>Loading, please wait...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.min.js"></script>
    
    <!-- Proses Login -->
    <script>
        $(document).ready(function() {
            <?php if ($show_otp_modal): ?>
                $('#otpModal').modal('show');
            <?php endif; ?>

            $('#otpModal').on('shown.bs.modal', function() {
                startTimer(<?php echo $remaining_time; ?>);
            });

            $("#otpForm").on('submit', function(event) {
                event.preventDefault();

                // Collect OTP input values
                let otp1 = $('#otp1').val();
                let otp2 = $('#otp2').val();
                let otp3 = $('#otp3').val();
                let otp4 = $('#otp4').val();
                let otp5 = $('#otp5').val();
                let otp6 = $('#otp6').val();

                // Show loader and disable form inputs
                $('#loader').show();
                $('#otpForm :input').prop('disabled', true);

                // Loader delay 0.5 seconds before AJAX
                setTimeout(function() {
                    // Send OTP to server via AJAX
                    $.ajax({
                        url: 'proses/verify_otp.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            otp1: otp1,
                            otp2: otp2,
                            otp3: otp3,
                            otp4: otp4,
                            otp5: otp5,
                            otp6: otp6
                        },
                        success: function(response) {
                            $('#loader').hide();
                            $('#otpForm :input').prop('disabled', false);

                            if (response.status === 'success') {
                                window.location.href = response.redirect_url;
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message,
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            $('#loader').hide();
                            $('#otpForm :input').prop('disabled', false);

                            Swal.fire({
                                icon: 'error',
                                title: 'Server Error',
                                text: 'There was a problem connecting to the server. Please try again.',
                            });

                            console.log(xhr);
                        }
                    });
                }, 500);
            });
        });

        function showAlert(message, type, redirectURL = null) {
            Swal.fire({
                icon: type,
                title: message,
                showConfirmButton: false,
                timer: 1500
            }).then(function() {
                if (redirectURL) {
                    window.location.href = redirectURL;
                } else {
                    $('#otpModal').modal('show');
                }
            });
        }

        let otpInputFields = document.querySelectorAll('.otp-field');
        let verifyBtn = document.getElementById('verifyBtn');

        otpInputFields.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
                if (e.target.value && index < otpInputFields.length - 1) {
                    otpInputFields[index + 1].focus();
                }
                enableVerifyBtn();
            });
            input.addEventListener('keyup', (e) => {
                if (e.key === 'Backspace' && index > 0 && !e.target.value) {
                    otpInputFields[index - 1].focus();
                }
            });
        });

        function startTimer(remainingTime) {
            let timer = remainingTime;
            let countdownInterval = setInterval(function() {
                let minutes = Math.floor(timer / 60);
                let seconds = timer % 60;

                seconds = seconds < 10 ? '0' + seconds : seconds;
                document.getElementById('countdown').textContent = `${minutes}:${seconds} detik tersisa`;

                // Stop the timer at 0
                if (timer <= 0) {
                    clearInterval(countdownInterval);
                    document.getElementById('countdown').textContent = "Waktu habis!";
                    document.getElementById('resendOtp').classList.remove("d-none");
                }

                timer--;
            }, 1000);
        }

        function enableVerifyBtn() {
            let allFilled = true;
            otpInputFields.forEach(input => {
                if (input.value === '') {
                    allFilled = false;
                }
            });
            verifyBtn.disabled = !allFilled;
        }
    </script>

    <!-- RESEND OTP -->
<script>
    const resendOtpElement = document.getElementById('resendOtp');
    if (resendOtpElement) {
        resendOtpElement.addEventListener('click', function() {
            resendOtp();
        });
    }

    function resendOtp() {
        // Tampilkan loader
        $('#loader').show();
        $('#otpForm :input').prop('disabled', true);

        // Kirim permintaan ke server untuk mengirim ulang OTP
        $.ajax({
            url: 'proses/resend_otp.php',
            type: 'POST',
            dataType: 'json',
            data: {
                npk: '<?php echo isset($_SESSION['npk']) ? $_SESSION['npk'] : ''; ?>'
            },
            success: function(response) {
                $('#loader').hide();
                $('#otpForm :input').prop('disabled', false);

                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'OTP Dikirim Ulang!',
                        text: 'OTP baru telah dikirim ke nomor Anda.',
                        showConfirmButton: false,
                        timer: 2000
                    });

                    // Reset timer
                    document.getElementById('countdown').textContent = '5:00 detik tersisa';
                    document.getElementById('resendOtp').classList.add('d-none');

                    // Mulai ulang timer with full duration
                    startTimer(300);

                    // Kosongkan input OTP
                    otpInputFields.forEach(input => input.value = '');
                    verifyBtn.disabled = true;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Mengirim OTP',
                        text: response.message,
                    });
                }
            },
            error: function(xhr, status, error) {
                $('#loader').hide();
                $('#otpForm :input').prop('disabled', false);

                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Server',
                    text: 'Terjadi masalah saat menghubungi server. Silakan coba lagi.',
                });

                console.log(xhr);
            }
        });
    }
</script>
    
</body>
</html>