$(document).ready(function () {
    var idHkt = $('#hiddenIdHkt').val();
    var pendingUpdates = {}; // Menyimpan perubahan yang belum disubmit

    $('.selectpicker').selectpicker({
        width: '100%',
        liveSearch: false
    });

    // Tambahkan tombol update di bawah tabel
    $('#table_perubahan').after(`
        <div class="btn-container d-flex justify-content-end gap-2 mt-4 pe-4">
            <button id="btn-update-perubahan" class="btn btn-success">
                <i class="fas fa-save me-2"></i> Update Absensi
            </button>
        </div>
    `);

    function loadPerubahanData() {
        $.ajax({
            url: 'proses/fetch_perubahan.php',
            type: 'POST',
            data: { id_hkt: idHkt },
            dataType: 'json',
            success: function (response) {
                var tbody = $('#table_perubahan tbody');
                tbody.empty();

                console.log('Response:', response);

                if (response.error || response.length === 0) {
                    tbody.append(`
                        <tr>
                            <td colspan="8" class="text-center">${response.error || 'Belum ada absensi di hari ini'}</td>
                        </tr>
                    `);
                    console.warn('No data or error:', response.error || 'Kosong');
                    return;
                }

                var reasonToStatus = {
                    '5': 'Hadir',
                    '4': 'Sakit',
                    '3': 'Train',
                    '2': 'Cuti',
                    '1': 'Izin',
                    '0': 'Tanpa Keterangan'
                };

                $.each(response, function (index, item) {
                    var status = reasonToStatus[item.reason] || 'Tanpa Keterangan';
                    var qualificationValue = parseInt(item.qualification_value) || 0;
                    var processMinSkill = parseInt(item.min_skill) || 0;
                    var mpPenggantiDisplay = item.mp_pengganti || '-';

                    var row = `
                        <tr data-id-perubahan="${item.id_perubahan}" data-reason-awal="${item.reason}">
                            <td style="text-align: left; text-justify: inter-word;">${index + 1}</td>
                            <td class="proses-name" 
                                data-id="${item.id_proses}" 
                                data-status="${item.process_status}" 
                                style="text-align: left; text-justify: inter-word; background-color: ${item.process_status === 1 ? 'red' : 'white'};">
                                ${item.proses_name}
                            </td>
                            <td class="man-power" 
                                data-npk="${item.npk_awal}" 
                                style="text-align: left; text-justify: inter-word; background-color: ${qualificationValue < processMinSkill ? 'red' : 'white'};">
                                ${item.mp_awal} (${item.npk_awal})
                            </td>
                            <td style="text-align: left; text-justify: inter-word;">
                                <select class="selectpicker status" data-style="btn-light" data-initial-value="${item.reason}">
                                    <option value="5" ${status === 'Hadir' ? 'selected' : ''}>Hadir</option>
                                    <option value="4" ${status === 'Sakit' ? 'selected' : ''}>Sakit</option>
                                    <option value="3" ${status === 'Train' ? 'selected' : ''}>Train</option>
                                    <option value="2" ${status === 'Cuti' ? 'selected' : ''}>Cuti</option>
                                    <option value="1" ${status === 'Izin' ? 'selected' : ''}>Izin</option>
                                    <option value="0" ${status === 'Tanpa Keterangan' ? 'selected' : ''}>Tanpa Keterangan</option>
                                </select>
                            </td>
                            <td style="text-align: left; text-justify: inter-word;">${mpPenggantiDisplay}</td>
                            <td>${item.tanggal}</td>
                        </tr>`;
                    tbody.append(row);
                });

                $('.selectpicker').selectpicker({
                    dropupAuto: false,
                    container: 'body'
                }).on('changed.bs.select', function () {
                    var $select = $(this);
                    var $row = $select.closest('tr');
                    var idPerubahan = $row.data('id-perubahan');
                    var newReason = $select.val();
                    var initialReason = $select.data('initial-value');

                    // Tandai sebagai perubahan yang perlu diupdate
                    if (newReason !== initialReason) {
                        pendingUpdates[idPerubahan] = {
                            id_perubahan: idPerubahan,
                            reason: newReason
                        };
                        $row.find('.update-status').html('<span class="badge badge-warning">Menunggu update</span>');
                    } else {
                        delete pendingUpdates[idPerubahan];
                        $row.find('.update-status').html('<span class="badge badge-secondary">Belum diupdate</span>');
                    }
                
                });
            },
            error: function (xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Gagal memuat data perubahan: ' + error + '. Status: ' + status
                });
                console.error('Error memuat data perubahan:', status, error, xhr.responseText);
            }
        });
    }

    // Handle klik tombol update
    $('#btn-update-perubahan').on('click', function() {
        if (Object.keys(pendingUpdates).length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Tidak ada perubahan',
                text: 'Tidak ada perubahan yang perlu diupdate.'
            });
            return;
        }
        
        Swal.fire({
            title: 'Update Perubahan?',
            text: `Anda akan mengupdate ${Object.keys(pendingUpdates).length} data perubahan.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Update!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                var updates = Object.values(pendingUpdates);
                
                $.ajax({
                    url: 'proses/fetch_perubahan.php',
                    type: 'POST',
                    data: {
                        action: 'update_multiple',
                        updates: updates
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.success,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            
                            
                            // Reload data untuk mendapatkan status terbaru
                            setTimeout(loadPerubahanData, 1000);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: response.error || 'Gagal memperbarui data.'
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Gagal memperbarui data: ' + error + '. Status: ' + status
                        });
                        console.error('Error updating data:', status, error, xhr.responseText);
                    }
                });
            }
        });
    });

    $('#perubahan-tab').on('shown.bs.tab', function () {
        loadPerubahanData();
    });

    if ($('#perubahan-tab').hasClass('active')) {
        loadPerubahanData();
    }
});