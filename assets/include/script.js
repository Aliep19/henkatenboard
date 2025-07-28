
        // START TIME
        function startTime() {
            const today = new Date();
            let h = today.getHours();
            let m = today.getMinutes();
            let s = today.getSeconds();
            m = checkTime(m);
            s = checkTime(s);
            document.getElementById('txt').innerHTML = 
                h + ":" + m + ':<span style="font-size:0.7em;">' + s + '</span>';

            // Format tanggal
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('date').innerHTML = today.toLocaleDateString('id-ID', options);

            setTimeout(startTime, 1000);
        }

        function checkTime(i) {
            if (i < 10) {
                i = "0" + i;
            }
            return i;
        }

        // Alert jika belum login menggunakan SweetAlert2
        function alertLogin() {
            Swal.fire({
                icon: 'info',
                title: 'Silahkan Login',
                text: 'Silahkan login terlebih dahulu untuk membuka profil.',
                confirmButtonText: 'OK',
                iconColor: '#dc3545'
            }).then(() => {
                window.location.href = "login.php";
            });
        }

        $(document).ready(function() {
            // Konfirmasi logout dengan SweetAlert2
            $('#logoutBtn').on('click', function() {
                Swal.fire({
                    title: 'Yakin ingin logout?',
                    text: 'Anda akan keluar dari sesi saat ini.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Logout',
                    cancelButtonText: 'Tidak'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'proses/logout.php';
                    } else {
                        $('#userModal').modal('hide');
                    }
                });
            });
        });
   