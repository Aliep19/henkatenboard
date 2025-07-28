$(document).ready(function() {
    // Konfigurasi
    const SESSION_TIMEOUT = 3600; // Sesuaikan dengan SESSION_TIMEOUT di konfig.php (dalam detik)
    const WARNING_TIME = 30; // Waktu sebelum timeout untuk menampilkan modal (dalam detik)
    let lastActivity = Date.now();
    let timeoutWarningShown = false;

    // Fungsi untuk memperbarui waktu aktivitas di server
    function updateActivity() {
        $.ajax({
            url: 'proses/session/update_activity.php',
            type: 'POST',
            cache: false,
            success: function(response) {
                console.log('Aktivitas diperbarui:', response);
            },
            error: function(xhr, status, error) {
                console.error('Gagal memperbarui aktivitas:', error);
            }
        });
        lastActivity = Date.now();
        timeoutWarningShown = false;
        $('#timeoutModal').modal('hide'); // Sembunyikan modal jika aktif
    }

    // Fungsi untuk memeriksa sesi dan menampilkan modal
    function checkSession() {
        const currentTime = Date.now();
        const timeSinceLastActivity = (currentTime - lastActivity) / 1000; // Konversi ke detik

        if (timeSinceLastActivity >= SESSION_TIMEOUT) {
            // Sesi kedaluwarsa, logout langsung
            Swal.fire({
                icon: 'warning',
                title: 'Sesi Kedaluwarsa',
                text: 'Sesi Anda telah berakhir. Anda akan diarahkan ke halaman login.',
                showConfirmButton: false,
                timer: 2000
            }).then(() => {
                window.location.href = 'login.php';
            });
            return;
        }

        if (timeSinceLastActivity >= (SESSION_TIMEOUT - WARNING_TIME) && !timeoutWarningShown) {
            // Tampilkan modal peringatan
            timeoutWarningShown = true;
            $('#timeoutModal').modal({
                backdrop: 'static',
                keyboard: false
            }).modal('show');

            // Mulai hitung mundur
            let timeLeft = WARNING_TIME;
            const countdownElement = $('#countdown');
            countdownElement.text(`${timeLeft} detik tersisa`);

            const countdownInterval = setInterval(() => {
                timeLeft--;
                countdownElement.text(`${timeLeft} detik tersisa`);

                if (timeLeft <= 0) {
                    clearInterval(countdownInterval);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sesi Kedaluwarsa',
                        text: 'Sesi Anda telah berakhir. Anda akan diarahkan ke halaman login.',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        window.location.href = 'login.php';
                    });
                }
            }, 1000);
        }
    }

    // Dengarkan aktivitas pengguna
    $(document).on('click mousemove keypress', function() {
        updateActivity();
    });

    // Tombol "Tetap Login" di modal
    $('#stayLoggedIn').on('click', function() {
        updateActivity();
    });

    // Periksa sesi setiap detik
    setInterval(checkSession, 1000);

    // Panggil updateActivity saat halaman dimuat
    updateActivity();
});