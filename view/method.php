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
    <title>Henkaten Metode</title>
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
                    <h4 class="mb-0">PERUBAHAN METODE</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Legend Card -->
        <div class="card border-kayaba mb-4">
            <div class="card-header bg-kayaba-light">
                <h6 class="mb-0 text-kayaba text-white">
                    <i class="fa fa-info-circle me-2"></i>
                    Keterangan Kode Alasan Perubahan
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex align-items-start mb-3">
                            <span class="badge code-badge me-3 px-2 py-1">A1</span>
                            <small>Perubahan method yang sudah terencana melalui proses PCR / QSD sehingga sudah dilakukan perubahan pada WI (OM/IM/WCB)</small>
                        </div>
                        <div class="d-flex align-items-start mb-3">
                            <span class="badge code-badge me-3 px-2 py-1">A2</span>
                            <small>Perubahan method yang sudah terencana tanpa melalui proses PCR / QSD (Perubahan minor) hanya dilakukan perubahan pada WI (OM/IM/WCB)</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start mb-3">
                            <span class="badge code-badge me-3 px-2 py-1">A3</span>
                            <small>Perubahan method sementara yang disebabkan abnormality dan dilegalkan melalui permission sheet</small>
                        </div>
                        <div class="mt-3">
                            <div class="alert alert-info py-2">
                                <small>
                                    <i class="fa fa-sticky-note me-1"></i>
                                    <strong>Catatan:</strong> A1 & A2 = Terencana | A3 = Tidak Terencana
                                </small>
                            </div>
                        </div>
                    </div>
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
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="fa fa-plus-circle me-1"></i>
                            Tambah Data
                        </button>
                    </div>
                    <div class="col text-end">
                        <h6 class="mb-0">
                            <i class="fa fa-cog me-2"></i>
                            Data Perubahan Methode
                        </h6>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class='text-center'>No</th>
                                <th class='text-center'>Tanggal</th>
                                <th class='text-center'>Shift</th>
                                <th class='text-center'>Proses</th>
                                <th class='text-center'>Kode</th>
                                <th class='text-center'>Keterangan</th>
                                <th class='text-center'>Perubahan</th>
                                <th class='text-center'>Gambar</th>
                                <th class='text-center'>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="dataTable">
                            <?php
                            $sql = "SELECT * FROM method ORDER BY tanggal DESC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                $index = 1;
                                while ($row = $result->fetch_assoc()) {
                                    $statusClass = in_array($row['kode'], ['A1', 'A2']) ? 'success' : 'danger';
                                    $statusText = in_array($row['kode'], ['A1', 'A2']) ? 'Terencana' : 'Tidak Terencana';
                                    echo "<tr>";
                                    echo "<td class='text-center'>$index</td>";
                                    echo "<td class='text-center'>" . date('d/m/Y', strtotime($row['tanggal'])) . "</td>";
                                    echo "<td class='text-center'>Shift " . $row['shift'] . "</td>";
                                    echo "<td class='text-center'>" . htmlspecialchars($row['proses']) . "</td>";
                                    echo "<td class='text-center'><span class='badge code-badge'>" . $row['kode'] . "</span><br><small class='badge text-bg-$statusClass mt-1'>$statusText</small></td>";
                                    echo "<td class='text-start'>" . htmlspecialchars($row['alasan']) . "</td>";
                                    echo "<td class='text-center'>";
                                    echo "<button class='btn btn-info btn-sm' onclick='showDetail(\"" . htmlspecialchars($row['sebelum'], ENT_QUOTES) . "\", \"" . htmlspecialchars($row['saatini'], ENT_QUOTES) . "\")'>Lihat Detail</button>";
                                    echo "</td>";
                                    echo "<td class='text-center'>" . ($row['gambar'] ? "<a href='../Uploads/" . $row['gambar'] . "' target='_blank'><img src='../Uploads/" . $row['gambar'] . "' alt='Gambar' style='max-width:50px; max-height:50px;'></a>" : "-") . "</td>";
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
    <div class="modal fade" id="addModal" tabindex="-1">
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
                                <label class="form-label">Tanggal</label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Shift</label>
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
                                <label class="form-label">Proses</label>
                                <input type="text" class="form-control" id="proses" name="proses" placeholder="Nama Proses" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kode Alasan</label>
                                <select class="form-select" id="kode" name="kode" required>
                                    <option value="">Pilih Kode</option>
                                    <option value="A1">A1 - Terencana via PCR/QSD</option>
                                    <option value="A2">A2 - Terencana Minor</option>
                                    <option value="A3">A3 - Tidak Terencana</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Keterangan <b>(*Optional)</b></label>
                                <textarea class="form-control" id="alasan" name="alasan" rows="3" placeholder="Jelaskan ..."></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kondisi Sebelum</label>
                                <textarea class="form-control" id="sebelum" name="sebelum" rows="3" placeholder="Kondisi sebelum perubahan..." required></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kondisi Saat Ini</label>
                                <textarea class="form-control" id="saatini" name="saatini" rows="3" placeholder="Kondisi saat ini..." required></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gambar</label>
                                <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                                <small class="form-text text-muted">Maks. 2MB, format: JPG, PNG</small>
                            </div>
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
    <script src="../assets/js/method/script.js?v=<?php echo time(); ?>"></script>
</body>
</html>