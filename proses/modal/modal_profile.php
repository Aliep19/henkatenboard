 <!-- User Profile Modal -->
 <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-black" id="userModalLabel">Profil Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                    // Ambil data pengguna yang login
                    $npk = $_SESSION['npk'];
                    $query_user = "SELECT npk, full_name, dept, email, no_telp FROM ct_users WHERE npk = '$npk'";
                    $result_user = mysqli_query($conn2, $query_user);
                    $user = mysqli_fetch_assoc($result_user);

                    // Tentukan path foto profil berdasarkan NPK
                    $profile_photo = "assets/img/profiles/{$user['npk']}.jpg";
                    if (!file_exists($profile_photo)) {
                        $profile_photo = "assets/img/default-avatar.png";
                    }
                    ?>
                    <form id="profileForm" action="proses/upload_photo.php" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <!-- Bagian Foto -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label text-black">Foto Profile</label>
                                    <img src="<?php echo $profile_photo . '?v=' . time(); ?>" alt="Foto Profil" class="profile-photo mt-2">
                                    <input type="file" class="form-control" name="profile_photo" id="profilePhoto" accept="image/jpeg,image/jpg,image/png">
                                </div>
                            </div>
                            <!-- Bagian Informasi -->
                            <div class="col-md-8">
                                <label class="form-label text-black">Data User</label>
                                <div class="mb-3">
                                    <label class="form-label text-black">Nama</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-black">NPK</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['npk']); ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-black">Departemen</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['dept']); ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-black">No Telpon</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['no_telp']); ?>" readonly>
                                </div>
                                
                            </div>
                        </div>
                        <div class="modal-footer">
                            
                            <button type="submit" class="btn btn-submit">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
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

    // Validasi file upload di sisi klien
    $('#profilePhoto').on('change', function() {
        const file = this.files[0];
        if (file) {
            // Validasi tipe file
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan',
                    text: 'Tipe file harus JPG, JPEG, atau PNG!'
                });
                this.value = '';
                return;
            }

            // Validasi ukuran file (300KB = 300 * 1024 bytes)
            if (file.size > 300 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan',
                    text: 'Ukuran file maksimal 300KB!'
                });
                this.value = '';
                return;
            }

            // Validasi dimensi gambar
            const img = new Image();
            img.src = URL.createObjectURL(file);
            img.onload = function() {
                if (this.width < 100 || this.height < 100) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Kesalahan',
                        text: 'Dimensi gambar minimal 100px x 100px!'
                    });
                    $('#profilePhoto').val('');
                }
            };
        }
    });
});
</script>