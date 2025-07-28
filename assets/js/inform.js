
        $(document).ready(function() {


            // Scroll-triggered animation for title and subtitle
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, { threshold: 0.5 });

            document.querySelectorAll('.info-section-title, .info-section-subtitle').forEach(el => {
                observer.observe(el);
            });

            // Animate cards on page load
            const cards = document.querySelectorAll('.info-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(50px) rotate(2deg)';
                card.style.transition = 'all 0.8s cubic-bezier(0.25, 0.8, 0.25, 1)';
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0) rotate(0)';
                }, 200 * index);
            });

            // Parallax tilt effect on card hover
            cards.forEach(card => {
                card.addEventListener('mousemove', (e) => {
                    const rect = card.getBoundingClientRect();
                    const x = e.clientX - rect.left - rect.width / 2;
                    const y = e.clientY - rect.top - rect.height / 2;
                    const tiltX = -(y / rect.height) * 10;
                    const tiltY = (x / rect.width) * 10;
                    card.style.transform = `perspective(1000px) rotateX(${tiltX}deg) rotateY(${tiltY}deg) translateY(-10px)`;
                });

                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(0)';
                });
            });

            // Ripple effect for Update Line Information card only
            const infoCard = document.querySelector('.info-card[data-href]');
            if (infoCard) {
                infoCard.addEventListener('click', function(e) {
                    // Prevent multiple ripples
                    if (this.querySelector('.ripple')) return;

                    // Create ripple element
                    const ripple = document.createElement('span');
                    ripple.classList.add('ripple');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.width = ripple.style.height = `${size}px`;
                    ripple.style.left = `${x}px`;
                    ripple.style.top = `${y}px`;
                    ripple.style.backgroundColor = 'rgba(255, 255, 255, 0.7)';
                    ripple.style.position = 'absolute';
                    ripple.style.borderRadius = '50%';
                    ripple.style.transform = 'scale(0)';
                    ripple.style.animation = 'ripple 0.6s linear';
                    ripple.style.pointerEvents = 'none';
                    
                    this.appendChild(ripple);

                    // SweetAlert2 confirmation
                    Swal.fire({
                        title: 'Navigasi',
                        text: `Apakah Anda ingin menuju ke ${this.querySelector('.card-title').textContent}?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            setTimeout(() => {
                                ripple.remove();
                                window.location.href = this.getAttribute('data-href');
                            }, 600);
                        } else {
                            ripple.remove();
                        }
                    });
                });
            }

            // Handle form submission via AJAX
            $('#uploadForm').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                $.ajax({
                    url: 'proses/upload_img.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Sukses',
                                text: response.message,
                                confirmButtonColor: '#3085d6'
                            }).then(() => {
                                $('#uploadModal').modal('hide');
                                $('#uploadForm')[0].reset();
                                $('.selectpicker').selectpicker('refresh');
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message,
                                confirmButtonColor: '#d33'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan saat mengunggah gambar: ' + error,
                            confirmButtonColor: '#d33'
                        });
                    }
                });
            });
        });
   