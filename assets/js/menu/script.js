    // SCRIPT SEBELUM PERUBAHAN DI SEPERTI DI SCRIPT DI BAWAH
        // $(document).ready(function() {
        //     // Initialize bootstrap-select
        //     $('.selectpicker').selectpicker({
        //         width: '100%',
        //         liveSearch: true
        //     });

        //     // Handle absensi form submission
        //     $('#absensiForm').on('submit', function(e) {
        //         e.preventDefault();

        //         const line = $('#lineSelect').val();

        //         if (!line) {
        //             Swal.fire({
        //                 icon: 'warning',
        //                 title: 'Peringatan',
        //                 text: 'Silakan pilih line.',
        //                 confirmButtonText: 'OK'
        //             });
        //             return;
        //         }

        //         // Get current date in YYYY-MM-DD format
        //         const today = new Date().toISOString().split('T')[0];

        //         $.ajax({
        //             url: 'proses/check_hkt.php',
        //             type: 'POST',
        //             data: {
        //                 line: line,
        //                 date: today
        //             },
        //             dataType: 'json',
        //             success: function(response) {
        //                 if (response.success && response.id_hkt) {
        //                     // Redirect to absensi.php with id_hkt
        //                     window.location.href = `absensi.php?id_hkt=${response.id_hkt}`;
        //                 } else {
        //                     Swal.fire({
        //                         icon: 'info',
        //                         title: 'Data Tidak Ditemukan',
        //                         text: 'Maaf, data HKT untuk tanggal ini tidak tersedia.',
        //                         confirmButtonText: 'OK'
        //                     });
        //                 }
        //             },
        //             error: function(xhr, status, error) {
        //                 console.error('Error checking HKT:', error);
        //                 Swal.fire({
        //                     icon: 'error',
        //                     title: 'Kesalahan',
        //                     text: 'Terjadi kesalahan saat memeriksa data HKT.',
        //                     confirmButtonText: 'OK'
        //                 });
        //             }
        //         });
        //     });
        // });

        // Enhanced animation when page loads
        // document.addEventListener('DOMContentLoaded', function() {
        //     const cards = document.querySelectorAll('.menu-card');

        //     // Animate each card with staggered delay
        //     cards.forEach((card, index) => {
        //         card.style.opacity = '0';
        //         card.style.transform = 'translateY(30px) scale(0.95)';
        //         card.style.transition = 'all 0.6s cubic-bezier(0.25, 0.8, 0.25, 1)';

        //         setTimeout(() => {
        //             card.style.opacity = '1';
        //             card.style.transform = 'translateY(0) scale(1)';
        //         }, 150 * index);
        //     });

        //     // Add ripple effect to cards (except Absensi card, WHICH opens modal)
        //     cards.forEach(card => {
        //         if (!card.hasAttribute('data-bs-toggle')) {
        //             card.addEventListener('click', function(e) {
        //                 // Prevent multiple ripples
        //                 if (card.querySelector('.ripple')) return;

        //                 // Create ripple element
        //                 const ripple = document.createElement('span');
        //                 ripple.classList.add('ripple');

        //                 // Position ripple
        //                 const rect = card.getBoundingClientRect();
        //                 const size = Math.max(rect.width, rect.height);
        //                 const x = e.clientX - rect.left - size/2;
        //                 const y = e.clientY - rect.top - size/2;

        //                 // Style ripple
        //                 ripple.style.width = ripple.style.height = `${size}px`;
        //                 ripple.style.left = `${x}px`;
        //                 ripple.style.top = `${y}px`;
        //                 ripple.style.backgroundColor = 'rgba(255, 255, 255, 0.5)';
        //                 ripple.style.position = 'absolute';
        //                 ripple.style.borderRadius = '50%';
        //                 ripple.style.transform = 'scale(0)';
        //                 ripple.style.animation = 'ripple 0.6s linear';
        //                 ripple.style.pointerEvents = 'none';

        //                 // Add ripple to card
        //                 card.appendChild(ripple);

        //                 // Remove ripple after animation
        //                 setTimeout(() => {
        //                     ripple.remove();
        //                     // Navigate after ripple animation completes
        //                     window.location.href = card.getAttribute('onclick').match(/'(.*?)'/)[1];
        //                 }, 600);
        //             });
        //         }
        //     });
        // });
    // AKHIR SCRIPT SEBELUM PERUBAHAN DI SEPERTI DI SCRIPT DI BAWAH
        
    //  <script>    
        $(document).ready(function() {
            // Initialize bootstrap-select
            $('.selectpicker').selectpicker({
                width: '100%',
                liveSearch: true
            });

            // Handle absensi form submission
            $('#absensiForm').on('submit', function(e) {
                e.preventDefault();

                const line = $('#lineSelect').val();

                if (!line) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: 'Silakan pilih line.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // Get current date in YYYY-MM-DD format
                const today = new Date().toISOString().split('T')[0];

                $.ajax({
                    url: 'proses/check_hkt.php',
                    type: 'POST',
                    data: {
                        line: line,
                        date: today
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.id_hkt) {
                            // Redirect to absensi.php with id_hkt
                            window.location.href = `absensi.php?id_hkt=${response.id_hkt}`;
                        } else {
                            Swal.fire({
                                icon: 'info',
                                title: 'Data Tidak Ditemukan',
                                text: 'Maaf, data HKT untuk tanggal ini tidak tersedia.',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error checking HKT:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Kesalahan',
                            text: 'Terjadi kesalahan saat memeriksa data HKT.',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });

        // Enhanced animation when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.menu-card');

            // Animate each card with staggered delay
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px) scale(0.95)';
                card.style.transition = 'all 0.6s cubic-bezier(0.25, 0.8, 0.25, 1)';

                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0) scale(1)';
                }, 150 * index);
            });

            // Add ripple effect to cards (except Absensi card, which opens modal)
            cards.forEach(card => {
                if (!card.hasAttribute('data-bs-toggle')) {
                    card.addEventListener('click', function(e) {
                        // Prevent multiple ripples
                        if (card.querySelector('.ripple')) return;

                        // Create ripple element
                        const ripple = document.createElement('span');
                        ripple.classList.add('ripple');

                        // Position ripple
                        const rect = card.getBoundingClientRect();
                        const size = Math.max(rect.width, rect.height);
                        const x = e.clientX - rect.left - size/2;
                        const y = e.clientY - rect.top - size/2;

                        // Style ripple
                        ripple.style.width = ripple.style.height = `${size}px`;
                        ripple.style.left = `${x}px`;
                        ripple.style.top = `${y}px`;
                        ripple.style.backgroundColor = 'rgba(255, 255, 255, 0.5)';
                        ripple.style.position = 'absolute';
                        ripple.style.borderRadius = '50%';
                        ripple.style.transform = 'scale(0)';
                        ripple.style.animation = 'ripple 0.6s linear';
                        ripple.style.pointerEvents = 'none';

                        // Add ripple to card
                        card.appendChild(ripple);

                        // Remove ripple after animation
                        setTimeout(() => {
                            ripple.remove();
                            // Navigate after ripple animation completes
                            window.location.href = card.getAttribute('onclick').match(/'(.*?)'/)[1];
                        }, 600);
                    });
                }
            });
        });
    //</script>