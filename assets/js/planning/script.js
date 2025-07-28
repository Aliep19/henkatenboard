$(document).ready(function() {
    // SECTION: Variabel Global
    // Variabel untuk menyimpan status aplikasi
    
    let selectedManpower = [];    // Menyimpan tenaga kerja yang dipilih dari dropdown
    let isAutoFillEnabled = true;  // Status toggle auto-fill (default: aktif)
    let excelData = null;         // Menyimpan data Excel yang diunggah

    // SECTION: Fungsi Inisialisasi
    // Inisialisasi datepicker jQuery UI untuk tanggal mulai dan akhir
        function initializeDatepickers() {
        let today = new Date();
        let tomorrow = new Date(today);
        tomorrow.setDate(today.getDate() + 1); // Tanggal besok

        $("#datepicker-mulai").datepicker({
            dateFormat: "dd-mm-yy",
            minDate: tomorrow, // Hanya boleh pilih tanggal besok atau setelahnya
            onSelect: function(selectedDate) {
                let minDate = $(this).datepicker("getDate");
                $("#datepicker-akhir").datepicker("option", "minDate", minDate);
            }
        });

        $("#datepicker-akhir").datepicker({
            dateFormat: "dd-mm-yy",
            minDate: tomorrow, // Hanya boleh pilih tanggal besok atau setelahnya
            onSelect: function(selectedDate) {
                let maxDate = $(this).datepicker("getDate");
                $("#datepicker-mulai").datepicker("option", "maxDate", maxDate);
            }
        });
    }

    // Inisialisasi Bootstrap Select untuk semua elemen selectpicker
    function initializeSelectpicker() {
        $('.selectpicker').selectpicker();
    }

    // SECTION: Fungsi Utilitas
    // Memperbarui daftar tenaga kerja yang dipilih dari dropdown
    function updateSelectedManpower() {
        selectedManpower = [];
        $('.manpower-select').each(function() {
            let selectedValue = $(this).val();
            if (selectedValue) {
                selectedManpower.push(selectedValue);
            }
        });
    }

    // Mengaktifkan atau menonaktifkan dropdown tenaga kerja berdasarkan status auto-fill
    function toggleManpowerDropdowns(disable) {
        $('.manpower-select').each(function() {
            $(this).prop('disabled', disable).selectpicker('refresh');
        });
    }


    // Menonaktifkan opsi duplikat di dropdown line guide
    function disableOptions() {
        let lineGuide1 = $('#lineGuide1Select').val();
        let lineGuide2 = $('#lineGuide2Select').val();

        $('#lineGuide1Select option').each(function() {
            let optionValue = $(this).val();
            $(this).prop('disabled', optionValue && optionValue === lineGuide2);
        });

        $('#lineGuide2Select option').each(function() {
            let optionValue = $(this).val();
            $(this).prop('disabled', optionValue && optionValue === lineGuide1);
        });

        $('#lineGuide1Select, #lineGuide2Select').selectpicker('refresh');
    }


    // SECTION: Fungsi Manpower
    function getManpower(item_id, min_skill) {
        let id_bagian = $("#bagianSelect").val();
        let id_line = $("#lineSelect").val();

        if (!id_bagian || !id_line) {
            console.error("ID Bagian atau ID Line tidak valid.");
            return;
        }

        $.ajax({
            url: 'proses/get_manpower.php',
            type: 'POST',
            data: { id_bagian, id_line },
            dataType: 'json',
            success: function(response) {
                let dropdown = $(`.manpower-select[data-item-id="${item_id}"]`);
                if (!dropdown.length) {
                    console.error(`Dropdown with item_id=${item_id} not found.`);
                    return;
                }

                dropdown.empty().append('<option selected disabled>Pilih Man Power</option>');

                let filteredManpower = response.filter(manpower => manpower.process_id === item_id);
                if (filteredManpower.length > 0) {
                    $.each(filteredManpower, function(index, item) {
                        let isUnderqualified = item.qualification_value < min_skill;
                        let isUncertified = item.s_process_id && item.s_process_id != 0 && item.has_certification == 0;
                        let optionClass = isUnderqualified ? 'text-white bg-danger' : (isUncertified ? 'text-white bg-warning' : '');
                        let option = `
                            <option class="npk-karyawan ${optionClass}" 
                                    value="${item.employee_npk}" 
                                    data-qualification="${item.qualification_value}"
                                    data-has-certification="${item.has_certification}"
                                    data-s-process-id="${item.s_process_id}">
                                ${item.employee_npk} - ${item.employee_name}
                            </option>`;
                        dropdown.append(option);
                    });

                    dropdown.selectpicker('refresh');
                    if (isAutoFillEnabled) {
                        dropdown.prop('disabled', true).selectpicker('refresh');
                    }

                    dropdown.on('change', function() {
                        if (isAutoFillEnabled) {
                            Swal.fire({
                                title: 'Peringatan!',
                                text: 'Auto Fill Excel sedang aktif. Nonaktifkan terlebih dahulu untuk mengisi manual.',
                                icon: 'warning',
                                confirmButtonText: 'OK'
                            });
                            $(this).val('').selectpicker('refresh');
                            return;
                        }

                        let selectedOption = $(this).find(':selected');
                        let qualification = selectedOption.data('qualification');
                        let hasCertification = selectedOption.data('has-certification');
                        let sProcessId = selectedOption.data('s-process-id');
                        let selectedValue = $(this).val();

                        if (qualification < min_skill) {
                            Swal.fire({
                                title: 'Manpower tidak memenuhi!',
                                text: `Skill yang dibutuhkan: ${min_skill}. Manpower memiliki skill: ${qualification}.`,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Iya, Pilih',
                                cancelButtonText: 'Tidak, Pilih Ulang'
                            }).then(result => {
                                if (result.isConfirmed) {
                                    $(this).val(selectedValue).selectpicker('refresh');
                                    Swal.fire('Dipilih!', 'Manpower telah dipilih.', 'success');
                                    updateSelectedManpower();
                                } else {
                                    $(this).val('').selectpicker('refresh');
                                    updateSelectedManpower();
                                }
                            });
                        } else if (sProcessId && sProcessId != 0 && hasCertification == 0) {
                            Swal.fire({
                                title: 'Manpower belum tersertifikasi!',
                                text: `Manpower ini tidak memiliki sertifikasi untuk s-process terkait. Pilih alasan:`,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Pilih',
                                cancelButtonText: 'Batalkan',
                                input: 'select',
                                inputOptions: {
                                    'not_certified': 'Belum mempunyai sertifikasi',
                                    'ojt': 'Sedang masa OJT'
                                },
                                inputPlaceholder: 'Pilih alasan',
                                inputValidator: (value) => {
                                    return !value && 'Anda harus memilih alasan!';
                                }
                            }).then(result => {
                                if (result.isConfirmed) {
                                    $(this).val(selectedValue).selectpicker('refresh');
                                    $(this).attr('data-reason', result.value); // Store the reason
                                    Swal.fire('Dipilih!', `Manpower telah dipilih dengan alasan: ${result.value === 'not_certified' ? 'Belum mempunyai sertifikasi' : 'Sedang masa OJT'}.`, 'success');
                                    updateSelectedManpower();
                                } else {
                                    $(this).val('').selectpicker('refresh');
                                    $(this).removeAttr('data-reason'); // Remove reason if cancelled
                                    updateSelectedManpower();
                                }
                            });
                        } else {
                            $(this).removeAttr('data-reason'); // Remove reason if qualified
                            updateSelectedManpower();
                        }
                    });
                } else {
                    console.warn(`No manpower found for process_id=${item_id}`);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    }

    // SECTION: Fungsi Auto-Fill Excel
    // Menangani unggahan file Excel dan pengisian otomatis
    $('#excelFileInput').on('change', function(e) {
        let file = e.target.files[0];
        if (!file) return;

        if (!isAutoFillEnabled) {
            Swal.fire({
                title: 'Peringatan!',
                text: 'Auto Fill Excel dinonaktifkan. Aktifkan toggle Auto Fill terlebih dahulu.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            $(this).val('');
            return;
        }

        let id_line = $('#lineSelect').val();
        let id_shift = $('#shiftSelect').val();

        if (!id_line || !id_shift) {
            Swal.fire({
                title: 'Peringatan!',
                text: 'Silahkan pilih Line dan Shift terlebih dahulu.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            $(this).val('');
            return;
        }

        let reader = new FileReader();
        reader.onload = function(e) {
            let data = new Uint8Array(e.target.result);
            let workbook = XLSX.read(data, { type: 'array' });
            let sheetName = workbook.SheetNames[0];
            let sheet = workbook.Sheets[sheetName];
            excelData = XLSX.utils.sheet_to_json(sheet, { header: 1, blankrows: false });

            let lineName = $('#lineSelect option:selected').text().trim();
            let shiftName = $('#shiftSelect option:selected').text().trim();

            let shiftColumnMap = {
                'Shift 1': { name: 1, npk: 2 },
                'Shift 2': { name: 3, npk: 4 },
                'Shift 3': { name: 5, npk: 6 }
            };
            let shiftColumns = shiftColumnMap[shiftName];

            if (!shiftColumns) {
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Shift tidak dikenali. Pilih Shift 1, Shift 2, atau Shift 3.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                $(this).val('');
                return;
            }

            let lineFound = false;
            let processStartRow = null;
            for (let i = 0; i < excelData.length; i++) {
                let row = excelData[i];
                if (row[shiftColumns.name] && row[shiftColumns.name].toString().trim() === lineName) {
                    lineFound = true;
                    processStartRow = i + 1;
                    break;
                }
            }

            if (!lineFound) {
                Swal.fire({
                    title: 'Peringatan!',
                    text: `Line ${lineName} tidak ditemukan di file Excel untuk Shift ${shiftName}.`,
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                $(this).val('');
                return;
            }

            let processManpower = [];
            for (let i = processStartRow; i < excelData.length; i++) {
                let row = excelData[i];
                let processName = row[7] ? row[7].toString().trim() : '';
                let manpower = row[shiftColumns.name] ? row[shiftColumns.name].toString().trim() : '';
                let npk = row[shiftColumns.npk] ? row[shiftColumns.npk].toString().trim() : '';

                if (processName && manpower && npk) {
                    processManpower.push({ process_name: processName, npk, manpower });
                } else {
                    break;
                }
            }

            if (processManpower.length === 0) {
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Tidak ada data proses ditemukan di file Excel.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                $(this).val('');
                return;
            }

            $('.manpower-select').each(function() {
                let $dropdown = $(this);
                let processName = $dropdown.closest('tr').find('td:eq(1)').text().trim();
                let matchedProcess = processManpower.find(item => item.process_name.toLowerCase() === processName.toLowerCase());

                if (matchedProcess) {
                    let $option = $dropdown.find(`option[value="${matchedProcess.npk}"]`);
                    $dropdown.val($option.length > 0 ? matchedProcess.npk : '').selectpicker('refresh');
                } else {
                    $dropdown.val('').selectpicker('refresh');
                }
            });

            updateSelectedManpower();

            Swal.fire({
                title: 'Berhasil!',
                text: 'Manpower telah diisi otomatis dari file Excel.',
                icon: 'success',
                confirmButtonText: 'OK'
            });
            $(this).val('');
        };

        reader.onerror = function() {
            Swal.fire({
                title: 'Gagal!',
                text: 'Terjadi kesalahan saat membaca file Excel.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            $(this).val('');
        };

        reader.readAsArrayBuffer(file);
    });

    // SECTION: Fungsi Riwayat DHK
    // Menampilkan riwayat DHK berdasarkan ID line
    function displayHistoryDhk() {
        let id_line = $('#lineSelect').val();
        if (!id_line) {
            Swal.fire({
                title: 'Peringatan!',
                text: 'Silahkan pilih Line terlebih dahulu.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }

        Swal.fire({
            title: 'Memuat...',
            text: 'Sedang mengambil data history DHK',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: 'proses/get_history.php',
            type: 'POST',
            data: { id_line },
            dataType: 'json',
            success: function(response) {
                Swal.close();
                if (response.status === 'success' && response.data.length > 0) {
                    let groupedData = {};
                    response.data.forEach(item => {
                        let key = `${item.date}_${item.to_date}_${item.shift}`;
                        if (!groupedData[key]) {
                            groupedData[key] = { date: item.date, to_date: item.to_date, shift: item.shift, process_manpower: [] };
                        }
                        groupedData[key].process_manpower.push({
                            process_name: item.process_name,
                            man_power: item.man_power,
                            employee_name: item.employee_name
                        });
                    });

                    let html = `
                        <table class="table table-striped table-hover align-middle text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 10%;">No</th>
                                    <th style="width: 20%;">Tanggal Mulai</th>
                                    <th style="width: 20%;">Tanggal Berakhir</th>
                                    <th style="width: 15%;">Shift</th>
                                    <th style="width: 35%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>`;

                    let index = 1;
                    Object.keys(groupedData).forEach(key => {
                        let group = groupedData[key];
                        html += `
                            <tr>
                                <td>${index++}</td>
                                <td>${group.date}</td>
                                <td>${group.to_date}</td>
                                <td>${group.shift}</td>
                                <td>
                                    <button class="btn btn-detail btn-primary" data-key="${key}">View Detail</button>
                                    <button class="btn btn-auto-fill btn-success" data-key="${key}">Auto Fill</button>
                                </td>
                            </tr>`;
                    });

                    html += `</tbody></table>`;
                    $('#historyDhkContent').html(html);
                    window.groupedData = groupedData;

                    $('.btn-detail').on('click', function() {
                        let key = $(this).data('key');
                        let group = groupedData[key];
                        let detailHtml = `<ul class="process-manpower-list">`;
                        group.process_manpower.forEach(item => {
                            detailHtml += `
                                <li>
                                    <span class="process">${item.process_name}</span>: 
                                    <span class="manpower">${item.man_power ? item.man_power + ' - ' + item.employee_name : '-'}</span>
                                </li>`;
                        });
                        detailHtml += `</ul>`;
                        $('#detailDhkContent').html(detailHtml);
                        $('#detailDhkModal').modal('show');
                    });

                    $('.btn-auto-fill').on('click', function() {
                        let key = $(this).data('key');
                        let group = groupedData[key];
                        let id_line = $('#lineSelect').val();

                        if (!id_line) {
                            Swal.fire({
                                title: 'Peringatan!',
                                text: 'Silahkan pilih Line terlebih dahulu di form utama.',
                                icon: 'warning',
                                confirmButtonText: 'OK'
                            });
                            return;
                        }

                        if ($('#prosesTableBody').children().length === 0) {
                            Swal.fire({
                                title: 'Peringatan!',
                                text: 'Tabel proses belum dimuat. Silahkan pilih Line untuk memuat proses.',
                                icon: 'warning',
                                confirmButtonText: 'OK'
                            });
                            return;
                        }

                        $('.manpower-select').each(function() {
                            let $dropdown = $(this);
                            let processName = $dropdown.closest('tr').find('td:eq(1)').text().trim();
                            let historyItem = group.process_manpower.find(item => item.process_name === processName);

                            if (historyItem && historyItem.man_power) {
                                let $option = $dropdown.find(`option[value="${historyItem.man_power}"]`);
                                $dropdown.val($option.length > 0 ? historyItem.man_power : '').selectpicker('refresh');
                            } else {
                                $dropdown.val('').selectpicker('refresh');
                            }
                        });

                        updateSelectedManpower();
                        
                        $('#historyDhkModal').modal('hide');

                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Manpower telah diisi otomatis berdasarkan data history.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                    });
                } else {
                    $('#historyDhkContent').html('<p class="text-center">Tidak ada data history untuk line ini.</p>');
                }
            },
            error: function(xhr, status, error) {
                Swal.close();
                console.error('Error:', error);
                Swal.fire({
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan saat mengambil data history DHK.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                $('#historyDhkContent').html('<p class="text-center">Terjadi kesalahan saat memuat data.</p>');
            }
        });
    }

// SECTION: Pengiriman Formulir
// Menangani pengiriman formulir HKT
function submitHktForm(e) {
    e.preventDefault();
    
    // Validate required fields
    const tanggal = $("#datepicker-mulai").val();
    const tanggal_akhir = $("#datepicker-akhir").val();
    const id_shift = $("#shiftSelect").val();

    if (!tanggal || !tanggal_akhir || !id_shift) {
        let missingFields = [];
        if (!tanggal) missingFields.push("Tanggal Mulai");
        if (!tanggal_akhir) missingFields.push("Tanggal Berakhir");
        if (!id_shift) missingFields.push("Shift");

        Swal.fire({
            title: "Peringatan!",
            text: `Silakan isi ${missingFields.join(" dan ")} terlebih dahulu.`,
            icon: "warning",
            confirmButtonText: "OK"
        });
        return;
    }

    const formData = {
        tanggal: tanggal,
        tanggal_akhir: tanggal_akhir,
        id_bagian: $("#bagianSelect").val(),
        id_line: $("#lineSelect").val(),
        id_shift: id_shift,
        output_target: $("#outputTarget").val(),
        foreman1: $("#foreman1Select").val(),
        foreman2: $("#foreman2Select").val(),
        line_guide1: $("#lineGuide1Select").val(),
        line_guide2: $("#lineGuide2Select").val(),
        man_power: []
    };

    $(".manpower-select").each(function() {
        let processId = $(this).data("item-id");
        let manpowerValue = $(this).val();
        let reason = $(this).attr('data-reason') || ''; // Include reason if exists
        if (processId && manpowerValue) {
            formData.man_power.push({ process_id: processId, manpower: manpowerValue, reason: reason });
        }
    });

    Swal.fire({
        title: "Apakah Anda yakin?",
        text: "Apakah Anda yakin sudah mengisi form dengan benar?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Ya, Kirim!",
        cancelButtonText: "Tidak"
    }).then(result => {
        if (result.isConfirmed) {
            $.ajax({
                url: "proses/add_form.php",
                type: "POST",
                data: { formData: JSON.stringify(formData) },
                success: function(response) {
                    const res = JSON.parse(response);
                    if (res.status === "success") {
                        Swal.fire({
                            title: "Berhasil!",
                            text: "Data berhasil dikirim.",
                            icon: "success",
                            confirmButtonText: "OK"
                        }).then(() => {
                            window.location.href = `menu.php?status=success&id_hkt=${encodeURIComponent(res.id_hkt)}`;
                        });
                    } else {
                        Swal.fire({
                            title: "Gagal!",
                            text: res.message,
                            icon: "error",
                            confirmButtonText: "OK"
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error: " + xhr.responseText);
                    Swal.fire({
                        title: "Gagal!",
                        text: "Terjadi kesalahan saat mengirim data: " + xhr.responseText,
                        icon: "error",
                        confirmButtonText: "OK"
                    });
                }
            });
        }
    });
}

    // SECTION: Event Handlers
    // Menangani perubahan toggle auto-fill
    $('#autoFillToggle').on('change', function() {
        isAutoFillEnabled = $(this).is(':checked');
        toggleManpowerDropdowns(isAutoFillEnabled);
        Swal.fire({
            title: isAutoFillEnabled ? 'Auto Fill Diaktifkan' : 'Auto Fill Dinonaktifkan',
            text: isAutoFillEnabled ? 'Manpower hanya dapat diisi Secara Otomatis' : 'Anda sekarang dapat mengisi manpower secara manual.',
            icon: 'info',
            confirmButtonText: 'OK'
        });
    });

    // Menangani klik tombol riwayat DHK
    $('#historyDhkButton').on('click', displayHistoryDhk);

    // Menangani pengiriman formulir
    $("#hktForm").submit(submitHktForm);

    // Menangani perubahan dropdown bagian
    $('#bagianSelect').on('change', function() {
        let id_bagian = $(this).val();
        $('#lineSelect').html('<option selected>Pilih Line</option>');
        selectedManpower = [];
       

        if (id_bagian) {
            $.ajax({
                url: "proses/get_line.php",
                type: "POST",
                data: { id_bagian },
                dataType: "json",
                success: function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            $('#lineSelect').append(`<option value="${value.id}">${value.name}</option>`);
                        });
                    } else {
                        alert('Tidak ada data line untuk bagian yang dipilih.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    alert('Terjadi kesalahan saat mengambil data line.');
                }
            });
        }
    });

    // Menangani perubahan dropdown line
    $('#lineSelect').change(function() {
        let id_line = $(this).val();
        selectedManpower = [];

        $.ajax({
            url: 'proses/get_guideline.php',
            type: 'POST',
            data: { id_line },
            dataType: 'json',
            success: function(response) {
                $('#lineGuide1Select, #lineGuide2Select').empty().append('<option selected value="">Pilih Line Guide</option>');
                response.forEach(guide => {
                    let option = $('<option></option>').val(guide.npk).text(guide.name);
                    $('#lineGuide1Select').append(option.clone());
                    $('#lineGuide2Select').append(option.clone());
                });
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                alert('Terjadi kesalahan saat mengambil data line guide.');
            }
        });

        $.ajax({
            url: 'proses/get_foreman.php',
            type: 'POST',
            data: { id_line },
            dataType: 'json',
            success: function(response) {
                $('#foreman1Select, #foreman2Select').empty().append('<option selected value="">Pilih Foreman</option>');
                response.forEach(foreman => {
                    let option = $('<option></option>').val(foreman.npk).text(foreman.name);
                    $('#foreman1Select').append(option.clone());
                    $('#foreman2Select').append(option.clone());
                });
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                alert('Terjadi kesalahan saat mengambil data foreman.');
            }
        });

        $.ajax({
    url: 'proses/get_proses.php',
    type: 'POST',
    data: { id_line },
    dataType: 'json',
    success: function(response) {
        $('#prosesTableBody').empty();
        if (response.length > 0) {
            $.each(response, function(index, item) {
                let row = `
                    <tr>
                        <td class="p-2">${index + 1}</td>
                        <td class="p-2 ${item.status == 1 ? 'bg-danger text-white' : 'bg-light'}">${item.name}</td>
                        <td class="p-2">
                            <select class="bg-white form-select-sm manpower-select selectpicker" 
                                    data-item-id="${item.id}" 
                                    data-min-skill="${item.min_skill}"
                                    data-live-search="true">
                                <option selected disabled>Pilih Man Power</option>
                            </select>
                        </td>
                    </tr>`;
                $('#prosesTableBody').append(row);
                getManpower(item.id, item.min_skill);
            });
            
            // Inisialisasi selectpicker dengan opsi untuk fix posisi dropdown
            $('.selectpicker').selectpicker({
                dropupAuto: false,        // Mencegah dropdown berubah posisi otomatis
                container: 'body'         // Optional: untuk memastikan dropdown tidak terpotong
            });
            
            toggleManpowerDropdowns(isAutoFillEnabled);
        } else {
            $('#prosesTableBody').append('<tr><td colspan="3" class="p-2">Tidak ada proses untuk line ini.</td></tr>');
        }
    },
    error: function(xhr, status, error) {
        console.error('Error:', error);
    }
});
    });

    // Menangani perubahan dropdown shift
    $('#shiftSelect').on('change', function() {
        let jamKerja = $(this).find('option:selected').data('jam-kerja');
        $('#workHours').val(jamKerja || '');
    });

    // Menangani perubahan dropdown workstation
    $('#workstationSelect').change(function() {
        let workstationId = $(this).val();
        $('#subWorkstationSelect').html('<option selected disabled>Pilih Sub Workstation</option>');

        if (workstationId) {
            $.ajax({
                url: "proses/get_sub_workstations.php",
                type: "POST",
                data: { workstation_id: workstationId },
                dataType: "json",
                success: function(data) {
                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            $('#subWorkstationSelect').append(`<option value="${value.id}">${value.name}</option>`);
                        });
                    } else {
                        alert('Tidak ada sub workstation untuk workstation yang dipilih.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }
    });

    // Menangani perubahan dropdown tenaga kerja
    $(document).on('change', '.manpower-select', function() {
        updateSelectedManpower();
        
    });


    // SECTION: Inisialisasi
    // Inisialisasi aplikasi saat halaman dimuat
    initializeDatepickers();
    initializeSelectpicker();
    toggleManpowerDropdowns(isAutoFillEnabled);
    Swal.fire({
    title: 'Informasi',
    text: 'Mohon planning maksimal H-1 dari tanggal awal yang di planning',
    icon: 'info',
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 5000,
    timerProgressBar: true
});


});