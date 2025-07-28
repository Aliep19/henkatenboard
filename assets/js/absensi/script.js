$(document).ready(function() {
    $('.selectpicker').selectpicker({
        width: '100%',
        liveSearch: true
    });

    $.ajax({
        url: 'proses/get_bagian.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.error) {
                Swal.fire('Error', 'Data tidak ditemukan', 'error');
            } else {
                data.forEach(function(workstation) {
                    $('#workstationDropdown').append(
                        `<option value="${workstation.id}">${workstation.name}</option>`
                    );
                });
                $('#workstationDropdown').selectpicker('refresh');
            }
        },
        error: function(xhr, status, error) {
            Swal.fire('Error', 'Terjadi kesalahan: ' + error, 'error');
        }
    });

    $('#workstationDropdown').on('change', function() {
        var id_bagian = $(this).val();
        $('#lineDropdown').html('<option value="" disabled selected>Pilih Line</option>');

        if (id_bagian) {
            $.ajax({
                url: "proses/get_line.php",
                type: "POST",
                data: { id_bagian: id_bagian },
                dataType: "json",
                success: function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            $('#lineDropdown').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                        $('#lineDropdown').selectpicker('refresh');
                    } else {
                        Swal.fire('Peringatan', 'Tidak ada data line untuk bagian yang dipilih.', 'warning');
                    }
                },
                error: function(xhr, status, error) {
                    console.log(xhr.responseText);
                    Swal.fire('Error', 'Terjadi kesalahan saat mengambil data line.', 'error');
                }
            });
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const id_hkt = document.getElementById('hiddenIdHkt').value;
    const id_bagian = $('#hiddenIdBagian').val();

    console.log('Hidden id_hkt:', id_hkt);
    console.log('Hidden id_bagian:', id_bagian);

    if (id_hkt > 0) {
        getHKTData(id_hkt);
    } else {
        Swal.fire('Error', 'ID HKT tidak valid.', 'error');
    }
});

function togglePengganti(selectElement) {
    const status = selectElement.value;
    const penggantiDropdown = selectElement.closest('td').nextElementSibling.querySelector('.pengganti');

    if (status === '5') { // Status 5 adalah Hadir
        penggantiDropdown.value = '';
        $(penggantiDropdown).selectpicker('val', '');
    }

    $(penggantiDropdown).selectpicker('refresh');
}

function getHKTData(id_hkt) {
    console.log('Fetching data for id_hkt:', id_hkt);

    $.ajax({
        url: 'proses/get_hkt.php',
        type: 'POST',
        data: { id_hkt: id_hkt },
        dataType: 'json',
        success: function(response) {
            console.log('HKT Data:', response);

            if (response.error) {
                console.error('Error from get_hkt:', response.error);
                Swal.fire('Error', response.error, 'error');
                return;
            }

            // Ambil data MP Repair untuk tanggal HKT
            getMPRepairData(id_hkt, function(mpRepairData) {
                const tbody = $('#table_mp tbody');
                tbody.empty();

                response.forEach((item, index) => {
                    const qualificationValue = parseInt(item.qualification_value) || 0; // Nilai kualifikasi MP awal
                    const processMinSkill = parseInt(item.min_skill) || 0; // Minimum skill proses

                    const row = $(`
                        <tr>
                            <td style="text-align: left; text-justify: inter-word;">${index + 1}</td>
                            <td class="proses-name" 
                                data-id="${item.id}" 
                                data-status="${item.status}" 
                                data-min_skill="${item.min_skill}"
                                style="text-align: left; text-justify: inter-word;">
                                ${item.proses_name}
                            </td>
                            <td class="man-power" 
                                data-npk="${item.npk}" 
                                style="background-color: ${qualificationValue < processMinSkill ? 'red' : 'white'}; text-align: left; text-justify: inter-word;">
                                ${item.man_power}
                            </td>
                            <td style="text-align: left; text-justify: inter-word;">
                                <select class="form-select status" onchange="togglePengganti(this)">
                                    <option value="5" ${item.status === 'Hadir' ? 'selected' : ''}>Hadir</option>
                                    <option value="4" ${item.status === 'Sakit' ? 'selected' : ''}>Sakit</option>
                                    <option value="3" ${item.status === 'Train' ? 'selected' : ''}>Train</option>
                                    <option value="2" ${item.status === 'Cuti' ? 'selected' : ''}>Cuti</option>
                                    <option value="1" ${item.status === 'Izin' ? 'selected' : ''}>Izin</option>
                                    <option value="0" ${item.status === 'Tanpa Keterangan' ? 'selected' : ''}>Tanpa Keterangan</option>
                                </select>
                            </td>
                            <td style="text-align: left; text-justify: inter-word;">
                                <input type="hidden" class="mp_pengganti" value="">
                                <select class="selectpicker pengganti" 
                                        data-live-search="true" 
                                        data-style="btn-light">
                                    <option value="" selected disabled>Pilih MP Pengganti</option>
                                </select>
                            </td>
                        </tr>
                    `);
                    

                    const penggantiSelect = row.find('.pengganti');
                    const processId = item.id; // ID proses dari tabel

                    // Isi dropdown hanya dengan data MP Repair
                    if (mpRepairData && mpRepairData.length > 0) {
                        mpRepairData.forEach(emp => {
                            // Cari kualifikasi MP untuk proses spesifik ini
                            const qualification = emp.qualifications ? emp.qualifications.find(q => q.process_id === processId) : null;
                            const qualificationValue = qualification ? parseInt(qualification.qualification_value) || 0 : 0;

                            const displayText = `${emp.npk} - ${emp.name}`;
                            const option = new Option(displayText, emp.npk);
                            $(option).attr('data-mp-npk', emp.npk);
                            $(option).attr('data-process-id', processId);
                            $(option).attr('data-qualification-value', qualificationValue);
                            $(option).attr('data-process-min-skill', processMinSkill);

                            // Pewarnaan untuk MP pengganti
                            if (!qualification || qualificationValue < processMinSkill) {
                                $(option).css('background-color', 'red');
                            } else {
                                $(option).css('background-color', 'white');
                            }

                            penggantiSelect.append(option);
                        });
                    } else {
                        penggantiSelect.append(new Option('Tidak ada MP Repair tersedia', ''));
                    }

                    penggantiSelect.on('change', function() {
                        const statusSelect = row.find('.status');
                        const statusValue = statusSelect.val();
                        const selectedOption = $(this).find('option:selected');
                        const qualificationValue = parseInt(selectedOption.attr('data-qualification-value')) || 0;
                        const processMinSkill = parseInt(selectedOption.attr('data-process-min-skill')) || 0;

                        if (statusValue === '5') {
                            Swal.fire({
                                title: 'Peringatan!',
                                text: 'Absensi Man Power adalah Hadir. Tidak dapat memilih MP Pengganti.',
                                icon: 'warning',
                                confirmButtonText: 'OK'
                            });
                            $(this).val('').selectpicker('refresh');
                            row.find('.mp_pengganti').val('');
                            return;
                        }

                        row.find('.mp_pengganti').val(selectedOption.val());

                        // Cek apakah MP tidak qualified
                        if (!selectedOption.attr('data-process-id') || qualificationValue < processMinSkill) {
                            Swal.fire({
                                title: 'Peringatan!',
                                text: `Manpower ini tidak memiliki skill untuk proses ini atau skill (${qualificationValue}) kurang dari minimum (${processMinSkill}). Pilih alasan:`,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Pilih',
                                cancelButtonText: 'Batalkan',
                                input: 'select',
                                inputOptions: {
                                    'not_qualified': 'Dipilih karena tidak ada MP qualified',
                                    'ojt': 'Sedang Training (OJT)'
                                },
                                inputPlaceholder: 'Pilih alasan',
                                inputValidator: (value) => {
                                    return !value && 'Anda harus memilih alasan!';
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    const reason = result.value; // Simpan alasan yang dipilih
                                    row.find('.mp_pengganti').attr('data-reason', reason); // Simpan alasan di elemen
                                    Swal.fire('Dipilih!', `Manpower telah dipilih dengan alasan: ${reason === 'not_qualified' ? 'Tidak ada MP qualified' : 'Sedang Training (OJT)'}.`, 'success');
                                } else {
                                    $(this).val('').selectpicker('refresh');
                                    row.find('.mp_pengganti').val('');
                                    row.find('.mp_pengganti').removeAttr('data-reason');
                                }
                            });
                        } else {
                            row.find('.mp_pengganti').removeAttr('data-reason'); // Hapus alasan jika qualified
                        }
                    });

                    penggantiSelect.selectpicker('refresh');

                    // Pewarnaan untuk proses dengan process_status = 1 (sesuai kode lama)
                    if (item.process_status === 1) {
                        row.find('.proses-name').css('background-color', 'red');
                    }

                    tbody.append(row);
                });
                $('.pengganti').selectpicker({
                    dropupAuto: false,
                    container: 'body'
                });
            });
        },
        error: function(xhr, status, error) {
            console.error('Error fetching HKT data:', error);
            Swal.fire('Error', 'Gagal mengambil data HKT.', 'error');
        }
    });
}

function getMPRepairData(id_hkt, callback) {
    const id_bagian = $('#hiddenIdBagian').val();
    const id_shift = $('#shift').text().trim(); // Ubah dari .val() ke .text()
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
            console.log('MP Repair Data:', response);
            if (response.error) {
                console.error('Error from get_mp_repair:', response.error);
                $('#mpRepairQualifiedList').html('<p>Tidak ada MP Repair tersedia</p>');
                $('#mpRepairRegularList').html('<p>Tidak ada MP Repair Regular</p>');
                callback([]);
            } else {
                let qualifiedList = '';
                let regularList = '';
                response.forEach(emp => {
                    const displayText = `${emp.name} - ${emp.npk}`;
                    let isQualified = false;
                    // Cek apakah ada kualifikasi yang memenuhi salah satu proses
                    if (emp.qualifications && emp.qualifications.some(q => parseInt(q.qualification_value) >= parseInt(q.process_min_skill))) {
                        qualifiedList += `<button class="btn btn-custom" data-npk="${emp.npk}">${displayText}</button>`;
                        isQualified = true;
                    } 
                    if (!isQualified) {
                        regularList += `<button class="btn btn-custom" data-npk="${emp.npk}" style="background-color: red;">${displayText}</button>`;
                    }
                });
                $('#mpRepairQualifiedList').html(qualifiedList || '<p>Tidak ada MP Repair Qualified</p>');
                $('#mpRepairRegularList').html(regularList || '<p>Tidak ada MP Repair Regular</p>');
                callback(response);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching MP Repair data:', error);
            $('#mpRepairQualifiedList').html('<p>Tidak ada MP Repair tersedia</p>');
            $('#mpRepairRegularList').html('<p>Tidak ada MP Repair tersedia</p>');
            callback([]);
        }
    });
}

$('.btn.btn-success').click(function () {
    const id_hkt = document.getElementById("hiddenIdHkt").value;

    if (!id_hkt) {
        Swal.fire('Gagal!', 'ID HKT tidak ditemukan.', 'error');
        return;
    }

    // Ambil waktu sekarang
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
        // Shift 1
        shiftStart = getShiftStartDateTime('22:30:00');
        if (currentTime <= '05:59:59') {
            shiftStart.setDate(shiftStart.getDate() - 1); // Jika lewat tengah malam
        }
    } else if (currentTime >= '06:00:00' && currentTime <= '14:29:59') {
        // Shift 2
        shiftStart = getShiftStartDateTime('06:00:00');
    } else {
        // Shift 3
        shiftStart = getShiftStartDateTime('14:30:00');
    }

    const diffMinutes = (now - shiftStart) / 60000;

    if (diffMinutes > 90) {
        Swal.fire({
            icon: 'warning',
            iconColor: 'red',
            title: 'Absensi Ditutup!',
            text: 'Absensi maksimal dilakukan dalam 1 1/2 jam setelah shift dimulai.',
        });
        return;
    }

    // Jika belum lewat 1 jam, lanjutkan konfirmasi absensi
    Swal.fire({
        title: 'Apakah anda sudah benar mengisi data?',
        text: "Pastikan semua data yang dimasukkan sudah benar.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Kirimkan!',
        cancelButtonText: 'Tidak',
    }).then((result) => {
        if (result.isConfirmed) {
            updateMPData(id_hkt);
        }
    });
});


function updateMPData(id_hkt) {
    const rows = $('#table_mp tbody tr');
    const dataToSend = [];

    // Ambil idShift dari elemen <div> dengan id="shift"
    const idShift = $('#shift').text().trim(); // Ubah dari .val() ke .text()

    if (!idShift) {
        Swal.fire('Gagal!', 'Data shift tidak ditemukan.', 'error');
        return;
    }

    rows.each(function() {
        const row = $(this);
        const npkAwal = row.find('.man-power').data('npk');
        const statusValue = row.find('.status').val();
        const mpPengganti = row.find('.mp_pengganti').val();
        const reason = row.find('.mp_pengganti').attr('data-reason') || '';
        const idProses = row.find('.proses-name').data('id');
        const today = new Date();
        const tanggalDatabase = today.toISOString().split('T')[0];


        if (!npkAwal || !statusValue || !idProses || !idShift) {
            console.warn('Data incomplete for row:', row);
            Swal.fire('Peringatan!', 'Data tidak lengkap pada beberapa baris tabel. Periksa kembali isian.', 'warning');
            return false; // Hentikan iterasi jika data tidak lengkap
        }

        dataToSend.push({
            id_hkt: id_hkt,
            id_proses: idProses,
            npk_awal: npkAwal,
            absen: statusValue,
            mp_pengganti: mpPengganti,
            reason: reason,
            id_shift: idShift,
            tanggal: tanggalDatabase
        });
    });

    // Jika tidak ada data untuk dikirim, tampilkan peringatan
    if (dataToSend.length === 0) {
        Swal.fire('Gagal!', 'Tidak ada data valid untuk dikirim.', 'error');
        return;
    }

    console.log('Data to send:', dataToSend);

    $.ajax({
        url: 'proses/update_mp.php',
        type: 'POST',
        data: JSON.stringify(dataToSend),
        contentType: 'application/json',
        success: function(response) {
            console.log('Response from server:', response);
            if (Array.isArray(response) && response.some(item => item.success)) {
                const redirectUrl = "home.php?status=success&id_hkt=" + encodeURIComponent(id_hkt);
                window.location.href = redirectUrl;
            } else {
                Swal.fire('Gagal!', 'Beberapa data gagal diperbarui.', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error updating data:', error);
            Swal.fire('Gagal!', 'Terjadi kesalahan saat mengirim data.', 'error');
        }
    });
}

/* SCRIPT UNTUK MODE DEVELOPMENT */
// Show Dev Submit button only in development environment
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            $('.btn-dev-submit').show();
        }

        // Handle Dev Submit button click
        $('.btn-dev-submit').click(function () {
            const id_hkt = document.getElementById("hiddenIdHkt").value;

            if (!id_hkt) {
                Swal.fire('Gagal!', 'ID HKT tidak ditemukan.', 'error');
                return;
            }

            Swal.fire({
                title: 'Apakah anda sudah benar mengisi data?',
                text: "Pastikan semua data yang dimasukkan sudah benar (Mode Development).",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Kirimkan!',
                cancelButtonText: 'Tidak',
            }).then((result) => {
                if (result.isConfirmed) {
                    updateMPData(id_hkt);
                }
            });
        });