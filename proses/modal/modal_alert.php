 
   <!-- Timeout Modal -->
   <div class="modal fade" id="timeoutModal" tabindex="-1" aria-labelledby="timeoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title d-flex align-items-center" id="timeoutModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Peringatan! Sesi Akan Habis
                </h5>
            </div>
            <div class="modal-body text-center">
                <p class="fw-bold text-danger fs-5">⚠ Tidak ada aktivitas terdeteksi!</p>
                <p>Sesi Anda akan otomatis berakhir demi keamanan sistem.</p>
                <p>Silakan klik tombol <strong>"Tetap Login"</strong> di bawah ini untuk melanjutkan sesi Anda.</p>
                <div id="countdown" class="text-danger fw-bold fs-4 mt-3">30 detik tersisa</div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success btn-lg px-4" id="stayLoggedIn">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Tetap Login
                </button>
            </div>
        </div>
    </div>
</div>