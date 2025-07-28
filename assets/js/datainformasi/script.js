
        
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
   

        $(document).ready(function() {
            // Fetch news images on modal show
            $('#newsModal').on('show.bs.modal', function() {
                fetchNews();
                $('#newsForm')[0].reset();
                $('#news_id').val('');
                $('#news_image').prop('required', true);
            });

            // Handle form submission for Insert/Update
            $('#newsForm').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                var newsId = $('#news_id').val();
                formData.append('action', newsId ? 'update' : 'insert');

                $.ajax({
                    url: 'informasi/proses_news.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(response) {
                        Swal.fire({
                            icon: response.success ? 'success' : 'error',
                            title: response.success ? 'Berhasil!' : 'Gagal!',
                            text: response.message,
                            showConfirmButton: true
                        }).then(() => {
                            if (response.success) {
                                $('#newsForm')[0].reset();
                                $('#news_id').val('');
                                $('#news_image').prop('required', true);
                                fetchNews();
                            }
                        });
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Terjadi kesalahan pada server',
                            showConfirmButton: true
                        });
                    }
                });
            });

            // Fetch news images
function fetchNews() {
    $.ajax({
        url: 'informasi/proses_news.php',
        type: 'GET',
        data: { action: 'fetch' },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data.length > 0) {
                var html = '';
                response.data.forEach(function(item) {
                    html += `
                        <tr>
                            <td>${item.category === 'production' ? 'Produksi' : 'Quality Assurance'}</td>
                            <td>${item.description}</td>
                            <td><img src="assets/img/uploads/news/${item.filename}" alt="News Image"></td>
                            <td>${new Date(item.uploaded_at).toLocaleString()}</td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-btn" data-id="${item.id}" data-category="${item.category}" data-description="${item.description}" data-filename="${item.filename}">Edit</button>
                                <button class="btn btn-sm btn-danger delete-btn" data-id="${item.id}">Hapus</button>
                            </td>
                        </tr>
                    `;
                });
                $('#newsTableBody').html(html);
            } else {
                $('#newsTableBody').html('<tr><td colspan="5">Tidak ada data</td></tr>');
            }
        },
        error: function() {
            $('#newsTableBody').html('<tr><td colspan="5">Terjadi kesalahan saat mengambil data</td></tr>');
        }
    });
}

// Handle Edit button
$(document).on('click', '.edit-btn', function() {
    var id = $(this).data('id');
    var category = $(this).data('category');
    var description = $(this).data('description');
    var filename = $(this).data('filename');
    $('#news_id').val(id);
    $('#news_category').val(category);
    $('#news_description').val(description);
    $('#news_image').prop('required', false); // Image is optional for update
    Swal.fire({
        icon: 'info',
        title: 'Mengedit Gambar',
        text: `Gambar saat ini: ${filename}`,
        showConfirmButton: true
    });
});

            // Handle Edit button
            $(document).on('click', '.edit-btn', function() {
                var id = $(this).data('id');
                var category = $(this).data('category');
                var filename = $(this).data('filename');
                $('#news_id').val(id);
                $('#news_category').val(category);
                $('#news_image').prop('required', false); // Image is optional for update
                // Optionally display current image name or preview
                Swal.fire({
                    icon: 'info',
                    title: 'Mengedit Gambar',
                    text: `Gambar saat ini: ${filename}`,
                    showConfirmButton: true
                });
            });

            // Handle Delete button
            $(document).on('click', '.delete-btn', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Apakah Anda yakin ingin menghapus gambar ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'informasi/proses_news.php',
                            type: 'POST',
                            data: { action: 'delete', id: id },
                            dataType: 'json',
                            success: function(response) {
                                Swal.fire({
                                    icon: response.success ? 'success' : 'error',
                                    title: response.success ? 'Berhasil!' : 'Gagal!',
                                    text: response.message,
                                    showConfirmButton: true
                                }).then(() => {
                                    if (response.success) {
                                        fetchNews();
                                    }
                                });
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: 'Terjadi kesalahan pada server',
                                    showConfirmButton: true
                                });
                            }
                        });
                    }
                });
            });
        });
  