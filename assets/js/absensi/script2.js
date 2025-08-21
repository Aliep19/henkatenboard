$(document).ready(function() {
    const id_hkt = document.getElementById('hiddenIdHkt').value;

    if (id_hkt > 0) {
        getSupportData(id_hkt);
    } else {
        Swal.fire('Error', 'ID HKT tidak valid.', 'error');
    }

    // Initialize selectpicker
    $('.selectpicker').selectpicker({
        width: '100%',
        liveSearch: true
    });

    // Submit Support Form
    $('.btn-submit-support').click(function() {
        const now = new Date();
        const currentHour = now.getHours();
        const currentMinute = now.getMinutes();
        const currentTime = `${String(currentHour).padStart(2, '0')}:${String(currentMinute).padStart(2, '0')}:00`;

        // Fungsi bantu untuk membuat Date dari waktu shift
        function getShiftStartDateTime(shiftTimeStr) {
            const [h, m, s] = shiftTimeStr.split(':');
            const date = new Date(now);
            date.setHours(+h, +m, +s, 0);
            return date;
        }

        // Tentukan shift dan waktu mulai
        let shiftStart;
        if (currentTime >= '22:30:00' || currentTime <= '05:59:59') {
            shiftStart = getShiftStartDateTime('22:30:00');
            if (currentTime <= '05:59:59') {
                shiftStart.setDate(shiftStart.getDate() - 1);
            }
        } else if (currentTime >= '06:00:00' && currentTime <= '14:29:59') {
            shiftStart = getShiftStartDateTime('06:00:00');
        } else {
            shiftStart = getShiftStartDateTime('14:30:00');
        }

        const diffMinutes = (now - shiftStart) / 60000;

        if (diffMinutes > 240) {
            Swal.fire({
                icon: 'warning',
                iconColor: 'red',
                title: 'Absensi Ditutup!',
                text: 'Absensi maksimal dilakukan dalam 4 jam setelah shift dimulai.',
            });
            return;
        }

        Swal.fire({
            title: 'Apakah anda sudah benar mengisi data?',
            text: "Pastikan semua data support yang dimasukkan sudah benar.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Kirimkan!',
            cancelButtonText: 'Tidak',
        }).then((result) => {
            if (result.isConfirmed) {
                updateSupportData(id_hkt);
            }
        });
    });
});

function togglePenggantiSupport(selectElement) {
    const status = selectElement.value;
    const penggantiDropdown = selectElement.closest('td').nextElementSibling.querySelector('.pengganti');

    if (status === '5') {
        penggantiDropdown.value = '';
        $(penggantiDropdown).selectpicker('val', '');
    }

    $(penggantiDropdown).selectpicker('refresh');
}

function getSupportData(id_hkt) {
    $.ajax({
        url: 'proses/get_foreman_lineguide.php',
        type: 'POST',
        data: { id_hkt: id_hkt },
        dataType: 'json',
        success: function(response) {
            if (response.error) {
                Swal.fire('Error', response.error, 'error');
                return;
            }

            getMPRepairData(id_hkt, function(mpRepairData) {
                const tbody = $('#table_support tbody');
                tbody.empty();

                response.support.forEach((item, index) => {
                    const row = $(`
                        <tr>
                            <td>${index + 1}</td>
                            <td class="role-name">${item.role}</td>
                            <td class="support-npk" data-npk="${item.npk}">${item.name} (${item.npk})</td>
                            <td>
                                <select class="form-select status" onchange="togglePenggantiSupport(this)">
                                    <option value="5">Hadir</option>
                                    <option value="4">Sakit</option>
                                    <option value="3">Train</option>
                                    <option value="2">Cuti</option>
                                    <option value="1">Izin</option>
                                    <option value="0">Tanpa Keterangan</option>
                                </select>
                            </td>
                            <td>
                                <input type="hidden" class="npk_pengganti" value="">
                                <select class="selectpicker pengganti" data-live-search="true" data-style="btn-light">
                                    <option value="" selected disabled>Pilih NPK Pengganti</option>
                                </select>
                            </td>
                        </tr>
                    `);

                    const penggantiSelect = row.find('.pengganti');

                    if (mpRepairData && mpRepairData.length > 0) {
                        mpRepairData.forEach(emp => {
                            const displayText = `${emp.npk} - ${emp.name}`;
                            const option = new Option(displayText, emp.npk);
                            $(option).attr('data-mp-npk', emp.npk);
                            penggantiSelect.append(option);
                        });
                    } else {
                        penggantiSelect.append(new Option('Tidak ada MP Repair tersedia', ''));
                    }

                    penggantiSelect.on('change', function() {
                        const statusSelect = row.find('.status');
                        const statusValue = statusSelect.val();
                        const selectedOption = $(this).find('option:selected');

                        if (statusValue === '5') {
                            Swal.fire({
                                title: 'Peringatan!',
                                text: 'Absensi Support adalah Hadir. Tidak dapat memilih Pengganti.',
                                icon: 'warning',
                                confirmButtonText: 'OK'
                            });
                            $(this).val('').selectpicker('refresh');
                            row.find('.npk_pengganti').val('');
                            return;
                        }

                        row.find('.npk_pengganti').val(selectedOption.val());
                    });

                    penggantiSelect.selectpicker('refresh');
                    tbody.append(row);
                });
                $('.pengganti').selectpicker({
                    dropupAuto: false,
                    container: 'body'
                });
            });
        },
        error: function(xhr, status, error) {
            Swal.fire('Error', 'Gagal mengambil data Support.', 'error');
        }
    });
}

function getMPRepairData(id_hkt, callback) {
    const id_bagian = $('#hiddenIdBagian').val();
    const id_shift = $('#shift').text().trim();
    const tanggal = new Date().toISOString().split('T')[0];

    $.ajax({
        url: 'proses/get_mprepair.php',
        type: 'POST',
        data: { 
            id_hkt: id_hkt,
            id_bagian: id_bagian,
            id_shift: id_shift,
            tanggal: tanggal
        },
        dataType: 'json',
        success: function(response) {
            if (response.error) {
                callback([]);
            } else {
                callback(response);
            }
        },
        error: function() {
            callback([]);
        }
    });
}

function updateSupportData(id_hkt) {
    const rows = $('#table_support tbody tr');
    const dataToSend = [];

    const tanggal = new Date().toISOString().split('T')[0];

    rows.each(function() {
        const row = $(this);
        const npkAwal = row.find('.support-npk').data('npk');
        const statusValue = row.find('.status').val();
        const npkPengganti = row.find('.npk_pengganti').val();

        if (!npkAwal || !statusValue) {
            Swal.fire('Peringatan!', 'Data tidak lengkap pada beberapa baris tabel support.', 'warning');
            return false;
        }

        dataToSend.push({
            id_hkt: id_hkt,
            npk_awal: npkAwal,
            absen: statusValue,
            npk_pengganti: npkPengganti,
            tanggal: tanggal
        });
    });

    if (dataToSend.length === 0) {
        Swal.fire('Gagal!', 'Tidak ada data valid untuk dikirim.', 'error');
        return;
    }

    $.ajax({
        url: 'proses/submit_absen.php',
        type: 'POST',
        data: JSON.stringify(dataToSend),
        contentType: 'application/json',
        success: function(response) {
            if (Array.isArray(response) && response.some(item => item.success)) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses!',
                    text: 'Absensi support berhasil dilakukan dan data telah masuk ke database.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    const redirectUrl = "home.php?status=success&id_hkt=" + encodeURIComponent(id_hkt);
                    window.location.href = redirectUrl;
                });
            } else {
                let errorMessage = 'Beberapa data gagal diperbarui.';
                response.forEach(item => {
                    if (item.error) {
                        errorMessage = item.error;
                    }
                });
                Swal.fire('Gagal!', errorMessage, 'error');
            }
        },
        error: function() {
            Swal.fire('Gagal!', 'Terjadi kesalahan saat mengirim data support.', 'error');
        }
    });
}