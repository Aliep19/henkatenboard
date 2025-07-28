
        let editIndex = -1;

        function saveData() {
            const form = document.getElementById('dataForm');
            const formData = new FormData(form);

            // Validate required fields
            const requiredFields = ['tanggal', 'shift', 'proses', 'mesin', 'kode', 'sebelum', 'saatini'];
            let isValid = true;
            requiredFields.forEach(field => {
                if (!formData.get(field)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning!',
                    text: 'Semua field wajib diisi!',
                    confirmButtonColor: '#DC143C'
                });
                return;
            }

            // Send data to server
            fetch('../proses/machine/create.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: editIndex === -1 ? 'Data berhasil ditambahkan' : 'Data berhasil diupdate',
                        confirmButtonColor: '#DC143C'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Terjadi kesalahan saat menyimpan data',
                        confirmButtonColor: '#DC143C'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan: ' + error.message,
                    confirmButtonColor: '#DC143C'
                });
            });

            resetForm();
            bootstrap.Modal.getInstance(document.getElementById('addModalM')).hide();
        }

        function editData(id) {
            editIndex = id;
            fetch(`../proses/machine/get_data.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('editId').value = data.data.id;
                        document.getElementById('tanggal').value = data.data.tanggal;
                        document.getElementById('shift').value = data.data.shift;
                        document.getElementById('proses').value = data.data.proses;
                        document.getElementById('mesin').value = data.data.mesin;
                        document.getElementById('kode').value = data.data.kode;
                        document.getElementById('alasan').value = data.data.alasan;
                        document.getElementById('sebelum').value = data.data.sebelum;
                        document.getElementById('saatini').value = data.data.saatini;
                        document.getElementById('modalTitle').textContent = 'Edit Data Perubahan';
                        const modal = new bootstrap.Modal(document.getElementById('addModalM'));
                        modal.show();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Gagal mengambil data',
                            confirmButtonColor: '#DC143C'
                        });
                    }
                });
        }

        function deleteData(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC143C',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);

                    fetch(`../proses/machine/delete_data.php`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: 'Data berhasil dihapus',
                                confirmButtonColor: '#DC143C'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: data.message || 'Gagal menghapus data',
                                confirmButtonColor: '#DC143C'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan: ' + error.message,
                            confirmButtonColor: '#DC143C'
                        });
                    });
                }
            });
        }

        function resetForm() {
            document.getElementById('dataForm').reset();
            document.getElementById('editId').value = '';
            document.getElementById('modalTitle').textContent = 'Tambah Data Perubahan';
            editIndex = -1;
        }

        document.getElementById('addModalM').addEventListener('hidden.bs.modal', function () {
            resetForm();
        });
           document.getElementById('gambar').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file && file.size > 2 * 1024 * 1024) { // 2MB
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Ukuran gambar melebihi batas 2MB. Pilih gambar yang lebih kecil.'
            });
            e.target.value = ''; // Kosongkan input file
        }
    });

    function showDetail(sebelum, saatini) {
        document.getElementById('modalSebelum').textContent = sebelum;
        document.getElementById('modalSaatIni').textContent = saatini;
        var myModal = new bootstrap.Modal(document.getElementById('modalPerubahan'));
        myModal.show();
    }
 
    