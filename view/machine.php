<?php
    session_start();
    // Pemeriksaan sesi: pastikan pengguna sudah login
    if (!isset($_SESSION['log']) || $_SESSION['log'] !== 'True') {
        header("Location: login.php");
        exit();
    }
    require_once '../konfigurasi/konfig.php';
    include '../src/workhours.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Henkaten Mesin</title>
    <link rel="shortcut icon" href="../assets/img/icon.jpg" type="image/x-icon">
    <link rel="stylesheet" href="../assets/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link href="../assets/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="../assets/jquery-ui/jquery-ui.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <script src="../assets/js/chart.umd.min.js"></script>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="../assets/css/method/style.css?v=<?php echo time(); ?>">
</head>
<body class="bg-light">
    
    <!-- Header -->
    <div class="header-kayaba p-3 mb-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <img src="../assets/img/kyb_logo.png" alt="KYB Logo" style="height:45px; width:auto; margin-right:16px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5));">
                        <div>
                            <h5 class="mb-0">PT. KAYABA INDONESIA</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <h4 class="mb-0">PERUBAHAN MESIN</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Kode Perubahan Accordion -->
        <div class="card border-kayaba mb-4">
            <div class="card-header bg-kayaba-light">
                <h6 class="mb-0 text-kayaba text-white">
                    <i class="fa fa-info-circle me-2"></i>Keterangan Kode Alasan Perubahan
                </h6>
            </div>
            <div class="card-body">
                <div class="accordion" id="accordionKodePerubahan">

                    <!-- TERENCANA -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTerencana">
                            <button class="accordion-button text-primary fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTerencana" aria-expanded="true" aria-controls="collapseTerencana">
                                Terencana (A1 & A2)
                            </button>
                        </h2>
                        <div id="collapseTerencana" class="accordion-collapse collapse show" aria-labelledby="headingTerencana">
                            <div class="accordion-body">
                                <div class="mb-3">
                                    <span class="badge bg-primary me-2">A1</span>
                                    Perubahan mesin atau line secara terencana (sudah melalui proses PCR/QSD) yang merupakan bagian dari improvement.
                                </div>
                                <div>
                                    <span class="badge bg-primary me-2">A2</span>
                                    Pergantian Jig/Tools & Alat Ukur secara reguler.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TIDAK TERENCANA -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTidakTerencana">
                            <button class="accordion-button collapsed text-danger fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTidakTerencana" aria-expanded="false" aria-controls="collapseTidakTerencana">
                                Tidak Terencana (A3, A4, A5)
                            </button>
                        </h2>
                        <div id="collapseTidakTerencana" class="accordion-collapse collapse" aria-labelledby="headingTidakTerencana">
                            <div class="accordion-body">
                                <div class="mb-3">
                                    <span class="badge bg-danger me-2">A3</span>
                                    Perpindahan mesin atau line secara tidak terencana ke mesin atau line lain yang secara reguler tidak diperuntukkan untuk melakukan proses pada part tersebut.
                                    <br><strong>Wajib Permission Sheet</strong><br>
                                    <em>Contoh: Mesin cleaning model A digunakan untuk model B karena mesin model B rusak.</em>
                                </div>
                                <div class="mb-3">
                                    <span class="badge bg-danger me-2">A4</span>
                                    Pergantian Jig/Tools & Alat Ukur dikarenakan terjadi abnormality.
                                    <br><em>Contoh: Jig patah, alat ukur sudah tidak sesuai.</em>
                                </div>
                                <div>
                                    <span class="badge bg-danger me-2">A5</span>
                                    Segala bentuk abnormality pada mesin/Jig/Tools & Alat Ukur tanpa memerlukan pergantian.
                                    <br><em>Contoh: Mesin/alat diperbaiki atau disetting ulang, tidak perlu henkaten.</em>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Catatan -->
                <div class="alert alert-info mt-3 py-2">
                    <small>
                        <i class="fa fa-sticky-note me-1"></i>
                        <strong>Catatan:</strong> A1 & A2 = Terencana | A3, A4, A5 = Tidak Terencana
                    </small>
                </div>
            </div>
        </div>


        <!-- Main Card -->
        <div class="card border-0 shadow">
            <div class="card-header header-kayaba">
                <div class="row align-items-center">
                    <div class="col-auto d-flex gap-2">
                        <a href="../datainformasi.php" class="btn btn-danger btn-sm">
                            <i class="fa fa-arrow-left me-1"></i>
                            Kembali
                        </a>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModalM">
                            <i class="fa fa-plus-circle me-1"></i>
                            Tambah Data
                        </button>
                    </div>
                    <div class="col text-end">
                        <h6 class="mb-0">
                            <i class="fa fa-cog me-2"></i>
                            Data Perubahan Machine
                        </h6>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class='text-center'>No</th>
                                <th class='text-center'>Tanggal</th>
                                <th class='text-center'>Shift</th>
                                <th class='text-center'>Proses</th>
                                <th class='text-center'>Mesin</th>
                                <th class='text-center'>Kode</th>
                                <th class='text-center'>Keterangan</th>
                                <th class='text-center'>Perubahan</th> <!-- Ubah ini -->
                                <th class='text-center'>Gambar</th>
                                <th class='text-center'>Aksi</th>
                            </tr>
                            </thead>

                        <tbody id="dataTable">
                            <?php
                            $sql = "SELECT * FROM machine ORDER BY tanggal DESC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                $index = 1;
                                while ($row = $result->fetch_assoc()) {
                                    switch ($row['kode']) {
                                        case 'A1':
                                            $statusClass = 'success';
                                            $statusText = 'Terencana';
                                            break;
                                        case 'A2':
                                            $statusClass = 'success';
                                            $statusText = 'Terencana';
                                            break;
                                        case 'A3':
                                            $statusClass = 'danger';
                                            $statusText = 'Tidak Terencana';
                                            break;
                                        case 'A4':
                                            $statusClass = 'danger';
                                            $statusText = 'Tidak Terencana';
                                            break;
                                        case 'A5':
                                            $statusClass = 'danger';
                                            $statusText = 'Tidak Terencana';
                                            break;
                                        default:
                                            $statusClass = 'secondary';
                                            $statusText = 'Tidak Diketahui';
                                            break;
                                    }
                                    echo "<tr>";
                                    echo "<td class='text-center'>$index</td>";
                                    echo "<td class='text-center'>" . date('d/m/Y', strtotime($row['tanggal'])) . "</td>";
                                    echo "<td class='text-center'>Shift " . $row['shift'] . "</td>";
                                    echo "<td class='text-center'>" . htmlspecialchars($row['proses']) . "</td>";
                                    echo "<td class='text-center'>" . htmlspecialchars($row['mesin']) . "</td>";
                                    echo "<td class='text-center'><span class='badge code-badge'>" . $row['kode'] . "</span><br><small class='badge text-bg-$statusClass mt-1'>$statusText</small></td>";
                                    echo "<td class='text-center'>" . htmlspecialchars($row['alasan']) . "</td>";
                                    echo "<td class='text-center'>";
                                    echo "<button class='btn btn-info btn-sm' onclick='showDetail(\"" . htmlspecialchars($row['sebelum'], ENT_QUOTES) . "\", \"" . htmlspecialchars($row['saatini'], ENT_QUOTES) . "\")'>Lihat Detail</button>";
                                    echo "</td>";
                                    echo "<td class='text-center'>" . ($row['gambar'] ? "<a href='../uploads/" . $row['gambar'] . "' target='_blank'><img src='../uploads/" . $row['gambar'] . "' alt='Gambar' style='max-width:50px; max-height:50px;'></a>" : "-") . "</td>";
                                    echo "<td class='text-center'>";
                                    echo "<div class='btn-group' role='group'>";
                                    echo "<button class='btn btn-warning btn-sm' onclick='editData(" . $row['id'] . ")' title='Edit'><i class='fa fa-pencil'></i></button>";
                                    echo "<button class='btn btn-danger btn-sm' onclick='deleteData(" . $row['id'] . ")' title='Delete'><i class='fa fa-trash'></i></button>";
                                    echo "</div>";
                                    echo "</td>";
                                    echo "</tr>";
                                    $index++;
                                }
                            }
                            $conn->close();
                            ?>
                        </tbody>
                        <!-- Modal Perubahan -->
                            <div class="modal fade" id="modalPerubahan" tabindex="-1" aria-labelledby="modalPerubahanLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="modalPerubahanLabel">Detail Perubahan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Sebelum:</strong> <span id="modalSebelum"></span></p>
                                    <p><strong>Saat Ini:</strong> <span id="modalSaatIni"></span></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                </div>
                                </div>
                            </div>
                            </div>

                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="addModalM" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header header-kayaba">
                    <h6 class="modal-title">
                        <i class="fa fa-plus-circle me-2"></i>
                        <span id="modalTitle">Tambah Data Perubahan</span>
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
    <form id="dataForm" enctype="multipart/form-data">
        <input type="hidden" id="editId" name="editId">
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="tanggal" class="form-label">Tanggal</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="shift" class="form-label">Shift</label>
                <select class="form-select" id="shift" name="shift" required>
                    <option value="">Pilih Shift</option>
                    <option value="1">Shift 1</option>
                    <option value="2">Shift 2</option>
                    <option value="3">Shift 3</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="proses" class="form-label">Proses</label>
                <input type="text" class="form-control" id="proses" name="proses" placeholder="Nama Proses" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="mesin" class="form-label">Mesin</label>
                <input type="text" class="form-control" id="mesin" name="mesin" placeholder="Nama Mesin" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="kode" class="form-label">Kode Alasan</label>
                <select class="form-select" id="kode" name="kode" required>
                    <option value="">Pilih Kode</option>
                    <option value="A1">A1 - Perubahan mesin/line terencana (PCR/QSD), bagian dari improvement.</option>
                    <option value="A2">A2 - Pergantian Jig/Tools & Alat Ukur secara reguler.</option>
                    <option value="A3">A3 - Perpindahan mesin/line tidak terencana ke mesin/line lain (Wajib Permission Sheet).</option>
                    <option value="A4">A4 - Pergantian Jig/Tools & Alat Ukur karena abnormality.</option>
                    <option value="A5">A5 - Abnormality pada mesin/Jig/Tools & Alat Ukur tanpa pergantian.</option>
                </select>
            </div>

        <div class="col-md-6 mb-3">
            <label for="alasan" class="form-label">Keterangan <b>(*Optional)</b></label>
            <textarea class="form-control" id="alasan" name="alasan" rows="3" placeholder="Jelaskan..." required></textarea>
        </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="sebelum" class="form-label">Kondisi Sebelum</label>
                <textarea class="form-control" id="sebelum" name="sebelum" rows="3" placeholder="Kondisi sebelum perubahan..." required></textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label for="saatini" class="form-label">Kondisi Saat Ini</label>
                <textarea class="form-control" id="saatini" name="saatini" rows="3" placeholder="Kondisi saat ini..." required></textarea>
            </div>
        </div>

        <div class="mb-3">
            <label for="gambar" class="form-label">Gambar</label>
            <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
            <div class="form-text">Maks. 2MB, format: JPG, PNG</div>
        </div>
    </form>
</div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-kayaba" onclick="saveData()">
                        <i class="fa fa-save me-1"></i>
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/machine/script.js?v=<?php echo time(); ?>"></script>
</body>
</html>