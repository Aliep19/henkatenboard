function handleSkillMap() {
    var lineId = $('#line-select').val();
    if (!lineId) {
        Swal.fire('Peringatan', 'Pilih Line terlebih dahulu.', 'warning');
        return;
    }
    // Arahkan ke skill_map.php dengan parameter line
    window
    .location.href = 'skillmap.php?line=' + lineId;
}

        $(document).ready(function() {
            var inactivityTimeout;
            var inactivityTime = 600000; // 10 seconds in milliseconds

            // Ambil nilai PHP ke dalam variabel JS
            var phpSelectedLine = typeof selectedLine !== 'undefined' ? selectedLine : '';
            var phpSelectedShift = typeof selectedShift !== 'undefined' ? selectedShift : '';

            // Create overlay for smooth transition
            if ($('#transition-overlay').length === 0) {
            $('body').append('<div id="transition-overlay" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:#fff;opacity:0;z-index:9999;pointer-events:none;transition:opacity 0.5s;"></div>');
            }

            function showTransitionOverlay(callback) {
            var $overlay = $('#transition-overlay');
            $overlay.css({opacity: 0, display: 'block', pointerEvents: 'auto'});
            $overlay.show().css('opacity', 1);
            setTimeout(function() {
                if (typeof callback === 'function') callback();
            }, 500); // match transition duration
            }

            function resetInactivityTimer() {
            clearTimeout(inactivityTimeout);
            inactivityTimeout = setTimeout(function() {
                var line = $('#line-select').val() || phpSelectedLine;
                var shift = $('#shift-select').val() || phpSelectedShift;
                var url = 'information.php';
                if (line || shift) {
                url += '?';
                if (line) url += 'line=' + encodeURIComponent(line);
                if (line && shift) url += '&';
                if (shift) url += 'shift=' + encodeURIComponent(shift);
                }
                showTransitionOverlay(function() {
                window.location.href = url;
                });
            }, inactivityTime);
            }

            // Reset timer on user activity
            $(document).on('mousemove keydown click change', function() {
            resetInactivityTimer();
            });

            // Start the timer initially
            resetInactivityTimer();
        });


            $(document).ready(function () {
                $('.clickable-badge').on('click', function () {
                    var npk = $(this).data('npk');
                    var name = $(this).data('name');
                    $('#modalManpowerName').text(name);
                    $('#modalManpowerNPK').text(npk);
                    $('#skillsTableBody').empty();
                    $('#noSkillsMessage').hide();
                    $.ajax({
                        url: 'proses/get_skills.php',
                        type: 'POST',
                        data: { npk: npk },
                        dataType: 'json',
                        success: function (response) {
                            if (response.status === 'success' && response.skills.length > 0) {
                                $.each(response.skills, function (index, skill) {
                                    var canvasId = 'pieChart_' + skill.process_id + '_' + index;
                                    var row = `
                                        <tr>
                                            <td>${index + 1}</td>
                                            <td>${skill.process_name}</td>
                                            <td>
                                                <canvas id="${canvasId}" style="max-width: 50px; max-height: 50px;"></canvas>
                                                <div class="text-center mt-2">${skill.skill_value} / 4</div>
                                            </td>
                                        </tr>
                                    `;
                                    $('#skillsTableBody').append(row);
                                    var ctx = document.getElementById(canvasId).getContext('2d');
                                    new Chart(ctx, {
                                        type: 'pie',
                                        data: {
                                            labels: ['Skill Achieved', 'Remaining'],
                                            datasets: [{
                                                data: [skill.skill_value, 4 - skill.skill_value],
                                                backgroundColor: ['#36A2EB', '#E0E0E0'],
                                                borderWidth: 1
                                            }]
                                        },
                                        options: {
                                            responsive: true,
                                            maintainAspectRatio: true,
                                            plugins: {
                                                legend: { display: false },
                                                tooltip: {
                                                    enabled: true,
                                                    callbacks: {
                                                        label: function (context) {
                                                            var label = context.label || '';
                                                            var value = context.raw || 0;
                                                            return `${label}: ${value}`;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    });
                                });
                            } else {
                                $('#noSkillsMessage').show();
                            }
                            $('#skillsModal').modal('show');
                        },
                        error: function (xhr, status, error) {
                            Swal.fire('Error', 'Gagal mengambil data skill: ' + error, 'error');
                            $('#noSkillsMessage').show();
                            $('#skillsModal').modal('show');
                        }
                    });
                });

                // Ambil nilai PHP ke dalam variabel JS secara aman
                var phpSelectedLine = window.selectedLine !== undefined ? window.selectedLine : '';
                var phpSelectedShift = window.selectedShift !== undefined ? window.selectedShift : '';

                function fetchHistoricalManPower(lineId, selectedDate) {
                    $.ajax({
                        url: 'proses/get_historical_mp.php',
                        type: 'POST',
                        data: { line_id: lineId, selected_date: selectedDate },
                        success: function(response) {
                            $('#man-power-data').html(response);
                        },
                        error: function(xhr, status, error) {
                            Swal.fire('Error', 'Gagal mengambil data Historical Man Power: ' + error, 'error');
                            $('#man-power-data').html(
                                "<tr><td colspan='6' class='text-center text-muted fst-italic py-4'>Gagal memuat data</td></tr>"
                            );
                        }
                    });
                }

                // Pastikan variabel PHP sudah di-echo ke JS sebelum file ini
                var lineId = typeof window.selectedLine !== 'undefined' && window.selectedLine !== '' 
                    ? window.selectedLine 
                    : ($('#line-select').val() || '');
                var selectedDate = $('#history-date').val();

                if (lineId) {
                    fetchHistoricalManPower(lineId, selectedDate);
                }

                $('#line-select').on('change', function() {
                    lineId = $(this).val();
                    selectedDate = $('#history-date').val();
                    if (lineId) {
                        fetchHistoricalManPower(lineId, selectedDate);
                    } else {
                        $('#man-power-data').html(
                            "<tr><td colspan='6' class='text-center text-muted fst-italic py-4'>Pilih Line terlebih dahulu</td></tr>"
                        );
                    }
                });

                $('#history-date').on('change', function() {
                    selectedDate = $(this).val();
                    lineId = $('#line-select').val();
                    if (lineId) {
                        fetchHistoricalManPower(lineId, selectedDate);
                    } else {
                        Swal.fire('Peringatan', 'Pilih Line terlebih dahulu.', 'warning');
                    }
                });
                // Auto refresh page setiap 10 menit
                setInterval(function () {
                    location.reload();
                }, 600000); // 600000ms = 10 menit

            });

        // Function to handle manual book opening
        function openManualBook(type) {
            if (type === 'henkaten') {
                window.open('assets/documents/user_manual_henkaten.pdf', '_blank');
            } 

        }

            function logCekAbsensi() {
                fetch('cek_absensi.php')
                    .then(res => res.text())
                    .then(text => {
                        console.log('--- Log Cek Absensi ---\n' + text);
                    })
                    .catch(err => {
                        console.error('[❌] Gagal fetch cek_absensi.php:', err);
                    });
            }

            logCekAbsensi(); // saat load pertama
            setInterval(logCekAbsensi, 300000); // ulang tiap 5 menit

// Fungsi auto-scroll untuk semua container dengan kelas .scroll-container
const scrollContainers = document.querySelectorAll('.scroll-container');
const scrollDirections = new Map(); // Menyimpan arah scroll untuk setiap container

function autoScroll() {
    scrollContainers.forEach(container => {
        if (!container) return;

        // Inisialisasi arah scroll jika belum ada
        if (!scrollDirections.has(container)) {
            scrollDirections.set(container, 1); // 1 = ke bawah, -1 = ke atas
        }

        // Ambil arah scroll
        let direction = scrollDirections.get(container);
        container.scrollTop += direction;

        // Tolerance biar gak nyangkut
        const tolerance = 2;

        // Jika sampai bawah (scrollTop + clientHeight >= scrollHeight)
        if (container.scrollTop + container.clientHeight >= container.scrollHeight - tolerance) {
            scrollDirections.set(container, -1);
        }
        // Jika sampai atas
        else if (container.scrollTop <= tolerance) {
            scrollDirections.set(container, 1);
        }
    });
}

// Jalankan auto-scroll tiap 20ms (lebih smooth)
setInterval(autoScroll, 50);

                $(document).ready(function() {
    // Handle form submission for checked status
    $('.check-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var id_perubahan = form.data('perubahan-id');
        var checked = form.find('input[name="checked_' + id_perubahan + '"]:checked').val();

        if (checked === undefined) {
            Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: 'Please select Approved or Not Approved'
            });
            return;
        }

        $.ajax({
            url: 'proses/submit_check.php',
            type: 'POST',
            data: {
                id_perubahan: id_perubahan,
                checked: checked
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function() {
                        location.reload(); // Reload to update the table
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while submitting'
                });
            }
        });
    });
});
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".btn-check-trigger").forEach(btn => {
        btn.addEventListener("click", function() {
            let container = btn.closest(".approval-container");
            let id = container.getAttribute("data-id");

            Swal.fire({
                title: 'Pilih Status',
                input: 'radio',
                inputOptions: {
                    1: 'Approved',
                    0: 'Not Approved'
                },
                inputValidator: (value) => {
                    if (!value) {
                        return 'Silakan pilih salah satu opsi!';
                    }
                },
                confirmButtonText: 'Submit',
                showCancelButton: true,
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    let checked = result.value;

                    fetch("proses/submit_Check.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: "id_perubahan=" + encodeURIComponent(id) + "&checked=" + encodeURIComponent(checked)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === "success") {
                            Swal.fire("Berhasil!", data.message, "success").then(() => {
                                // ubah tampilan jadi hasil + warna
                                if (checked === "1") {
                                    container.innerHTML = `<span class="text-success fw-bold">Approved</span>`;
                                } else {
                                    container.innerHTML = `<span class="text-danger fw-bold">Not Approved</span>`;
                                }
                            });
                        } else {
                            Swal.fire("Gagal!", data.message, "error");
                        }
                    })
                    .catch(err => {
                        Swal.fire("Error!", err, "error");
                    });
                }
            });
        });
    });
});
           
