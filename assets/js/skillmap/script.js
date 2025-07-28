    $(document).ready(function () {
        // Show loading overlay initially
        $('#loadingOverlay').addClass('show');

        // Chart generation function
        function generateChart(chartId, skillValue, maxSkill) {
            const ctx = document.getElementById(chartId).getContext('2d');
            return new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Skill Achieved', 'Remaining'],
                    datasets: [{
                        data: [skillValue, maxSkill - skillValue],
                        backgroundColor: ['#36A2EB', '#E0E0E0'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    }
                }
            });
        }

        // Initialize charts only for visible rows
        function initVisibleCharts() {
            $('canvas.skill-chart:visible').each(function () {
                const canvas = $(this);
                const chartId = canvas.attr('id');
                const skillValue = parseInt(canvas.data('skill')) || 0;
                const maxSkill = 4;

                // Avoid re-initializing the same chart
                if (!canvas.data('initialized')) {
                    generateChart(chartId, skillValue, maxSkill);
                    canvas.data('initialized', true);
                }
            });
        }

        // Pagination and Search variables
        var rowsPerPage = 10;
        var currentPage = 1;
        var $rows = $('#skillTable tbody tr');
        var $pagination = $('#pagination');

        // Calculate average skill
        function calculateAverageSkill() {
            var totalSkill = 0;
            var skillCount = 0;
            
            $rows.each(function() {
                $(this).find('canvas.skill-chart').each(function() {
                    var skillValue = parseInt($(this).data('skill')) || 0;
                    totalSkill += skillValue;
                    skillCount++;
                });
            });
            
            var avgSkill = skillCount > 0 ? (totalSkill / skillCount).toFixed(1) : '0';
            $('#avgSkill').text(avgSkill);
        }

        function updateTable() {
            var searchValue = $('#searchInput').val().toLowerCase();
            var filteredRows = $rows.filter(function () {
                var employeeInfo = $(this).find('.employee-info').text().toLowerCase();
                return employeeInfo.includes(searchValue);
            });

            var totalRows = filteredRows.length;
            var totalPages = Math.ceil(totalRows / rowsPerPage);

            // Update total employees count
            $('#totalEmployees').text(totalRows);

            // Hide all rows
            $rows.hide();

            // Show filtered rows for the current page
            var startIndex = (currentPage - 1) * rowsPerPage;
            var endIndex = startIndex + rowsPerPage;
            var visibleRows = filteredRows.slice(startIndex, endIndex);
            visibleRows.show();

            // Update row numbers
            visibleRows.each(function (index) {
                $(this).find('.row-number').text(startIndex + index + 1);
            });

            // Update pagination controls
            $pagination.empty();
            if (totalRows > 0 && totalPages > 1) {
                // Previous button
                $pagination.append(
                    '<li class="page-item' + (currentPage === 1 ? ' disabled' : '') + '">' +
                    '<a class="page-link" href="#" data-page="' + (currentPage - 1) + '">' +
                    '<i class="fas fa-chevron-left"></i></a></li>'
                );

                // Page numbers
                var startPage = Math.max(1, currentPage - 2);
                var endPage = Math.min(totalPages, currentPage + 2);

                if (startPage > 1) {
                    $pagination.append('<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>');
                    if (startPage > 2) {
                        $pagination.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                    }
                }

                for (var i = startPage; i <= endPage; i++) {
                    $pagination.append(
                        '<li class="page-item' + (i === currentPage ? ' active' : '') + '">' +
                        '<a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>'
                    );
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        $pagination.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                    }
                    $pagination.append('<li class="page-item"><a class="page-link" href="#" data-page="' + totalPages + '">' + totalPages + '</a></li>');
                }

                // Next button
                $pagination.append(
                    '<li class="page-item' + (currentPage === totalPages ? ' disabled' : '') + '">' +
                    '<a class="page-link" href="#" data-page="' + (currentPage + 1) + '">' +
                    '<i class="fas fa-chevron-right"></i></a></li>'
                );
            }

            // Show empty state if no results
            if (totalRows === 0) {
                // Calculate the number of columns dynamically based on the table header
                var colCount = $('#skillTable thead th').length;
                $('#skillTable tbody').html(
                    '<tr><td colspan="' + colCount + '" class="empty-state">' +
                    '<i class="fas fa-search"></i>' +
                    '<div>Tidak ada data yang cocok dengan pencarian Anda.</div>' +
                    '</td></tr>'
                );
            }

            // Initialize visible charts after updating table
            initVisibleCharts();
        }

        // Initialize
        calculateAverageSkill();
        updateTable();

        // Hide loading overlay after initialization
        setTimeout(function() {
            $('#loadingOverlay').removeClass('show');
        }, 500);

        // Search input handler with debounce
        var searchTimeout;
        $('#searchInput').on('keyup', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                currentPage = 1;
                updateTable();
            }, 300);
        });

        // Pagination click handler
        $pagination.on('click', '.page-link', function (e) {
            e.preventDefault();
            var page = $(this).data('page');
            if (page && !$(this).parent().hasClass('disabled')) {
                currentPage = page;
                updateTable();
                
                // Smooth scroll to top of table
                $('html, body').animate({
                    scrollTop: $('.skill-table-container').offset().top - 100
                }, 300);
            }
        });
    });