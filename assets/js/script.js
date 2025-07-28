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
