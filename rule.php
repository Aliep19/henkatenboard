<?php
session_start();
require_once 'konfigurasi/konfig.php';
include 'proses/rulescontroller.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rules - PT Kayaba Indonesia</title>
    <link rel="shortcut icon" href="assets/img/icon.jpg" type="image/x-icon">
    <link rel="stylesheet" href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/rule.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/fonts.googleapis.css?php echo time(); ?>">
    
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/sweetalert2/dist/sweetalert2.all.min.js"></script>
    
</head>
<body>
    <div class="main-container">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <a class="back-btn" href="home.php">
                    <i class="fas fa-arrow-left"></i>
                    Back to Home
                </a>
                            
                <h1 class="navbar-brand-title mb-0 text-center flex-grow-1">
                    HENKATEN BOARD - PT KAYABA INDONESIA
                </h1>
            
                <!-- Logo Kayaba di kanan -->
                <a href="#" class="logo-container">
                    <img src="assets/img/kyb_logo.png" 
                        alt="Kayaba Logo" 
                        height="40">
                </a>
            </div>
        </nav>

        <div class="content-area">
            <!-- Page Header -->
            <div class="page-header">
                <h2 class="page-title">
                    <i class="fas fa-book"></i>
                    Henkaten Rules
                </h2>
                <p class="text-muted mb-0">Mengelola dan melihat peraturan dan regulasi perusahaan</p>
                <?php if ($is_logged_in): ?>
                    <button class="btn add-btn" data-bs-toggle="modal" data-bs-target="#addRuleModal">
                        <i class="fas fa-plus me-2"></i>Add New Rule
                    </button>
                <?php endif; ?>
            </div>

            <!-- Rules Grid -->
            <div class="row g-4">
                <?php if (empty($rules)): ?>
                    <div class="col-12">
                        <div class="empty-state">
                            <i class="fas fa-file-pdf"></i>
                            <h5>No Rules Available</h5>
                            <p class="mb-0">There are no rules available at the moment. Please check back later or contact your administrator.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($rules as $rule): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="card rule-card">
                                <?php if ($is_logged_in): ?>
                                    <div class="dropdown dropdown-menu-custom">
                                        <button class="btn dropdown-toggle dropdown-toggle-custom" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item edit-btn" href="#"
                                                   data-id="<?php echo $rule['id']; ?>"
                                                   data-title="<?php echo htmlspecialchars($rule['title']); ?>"
                                                   data-bs-toggle="modal" data-bs-target="#editRuleModal">
                                                    <i class="fas fa-edit text-warning"></i>
                                                    Edit Rule
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item delete-btn" href="#"
                                                   data-id="<?php echo $rule['id']; ?>">
                                                    <i class="fas fa-trash text-danger"></i>
                                                    Delete Rule
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="rule-card-body">
                                    <div class="rule-icon">
                                        <i class="fas fa-file-pdf"></i>
                                    </div>
                                    <h5 class="rule-title"><?php echo htmlspecialchars($rule['title']); ?></h5>
                                    <a href="<?php echo htmlspecialchars($rule['pdf_path']); ?>" target="_blank" class="pdf-btn">
                                        <i class="fas fa-eye"></i>
                                        View PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Rule Modal -->
    <div class="modal fade" id="addRuleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>Add New Rule
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-heading me-2"></i>Rule Title
                            </label>
                            <input type="text" name="title" class="form-control" placeholder="Enter descriptive rule title">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-file-pdf me-2"></i>PDF File
                            </label>
                            <input type="file" name="pdf_file" class="form-control" accept=".pdf" required>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>Only PDF files are allowed (max 10MB)
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Rule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Rule Modal -->
    <div class="modal fade" id="editRuleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Edit Rule
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit-id">
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-heading me-2"></i>Rule Title
                            </label>
                            <input type="text" name="title" id="edit-title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-file-pdf me-2"></i>PDF File 
                                <span class="badge bg-secondary ms-2">Optional</span>
                            </label>
                            <input type="file" name="pdf_file" class="form-control" accept=".pdf">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>Leave empty to keep current file
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-warning text-white">
                            <i class="fas fa-save me-2"></i>Update Rule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Handle edit button click
            $('.edit-btn').on('click', function(e) {
                e.preventDefault();
                $('#edit-id').val($(this).data('id'));
                $('#edit-title').val($(this).data('title'));
            });

            // Handle delete button click
            $('.delete-btn').on('click', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: 'Apakah Anda yakin ingin menghapus rule ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Create and submit delete form
                        const form = $('<form>', {
                            'method': 'POST',
                            'action': window.location.href
                        });
                        form.append($('<input>', {
                            'type': 'hidden',
                            'name': 'action',
                            'value': 'delete'
                        }));
                        form.append($('<input>', {
                            'type': 'hidden',
                            'name': 'id',
                            'value': id
                        }));
                        $('body').append(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
</body>
</html>