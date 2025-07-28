<?php
// Ambil data untuk PIC of Process
$pic_data = [];
$today = date("Y-m-d"); // Tanggal hari ini

if (isset($id_hkt) && intval($id_hkt) > 0) {
    // Query untuk memastikan id_hkt sesuai dengan tanggal hari ini
    $query_check_hkt = "SELECT id_hkt FROM hkt_form WHERE id_hkt = ? AND ? BETWEEN date AND to_date";
    $stmt_check_hkt = $conn->prepare($query_check_hkt);
    $stmt_check_hkt->bind_param("is", $id_hkt, $today);
    $stmt_check_hkt->execute();
    $result_check_hkt = $stmt_check_hkt->get_result();

    if ($result_check_hkt->num_rows > 0) {
        // Query untuk memeriksa apakah ada inputan terbaru di tabel perubahan untuk tanggal hari ini
        $query_check_input = "SELECT COUNT(*) as count FROM perubahan WHERE tanggal = ? AND id_proses IN (SELECT id_proses FROM mp_procees WHERE id_hkt = ?)";
        $stmt_check_input = $conn->prepare($query_check_input);
        $stmt_check_input->bind_param("si", $today, $id_hkt);
        $stmt_check_input->execute();
        $result_check_input = $stmt_check_input->get_result();
        $input_count = $result_check_input->fetch_assoc()['count'];
        $stmt_check_input->close();

        // Hanya ambil data jika ada inputan untuk tanggal hari ini
        if ($input_count > 0) {
            // Query untuk mendapatkan data dari tabel mp_procees berdasarkan id_hkt
            $query_process = "SELECT id_proses, man_power, mp_pengganti FROM mp_procees WHERE id_hkt = ? AND mp_pengganti IS NOT NULL";
            $stmt_process = $conn->prepare($query_process);
            $stmt_process->bind_param("i", $id_hkt);
            $stmt_process->execute();
            $result_process = $stmt_process->get_result();

            if ($result_process->num_rows > 0) {
                while ($row = $result_process->fetch_assoc()) {
                    $id_proses = $row['id_proses'];
                    $npk_awal = $row['man_power'];
                    $npk_pengganti = $row['mp_pengganti'];

                    // Query untuk mendapatkan nama dari tabel karyawan berdasarkan npk awal
                    $query_karyawan_awal = "SELECT name FROM karyawan WHERE npk = ?";
                    $stmt_karyawan_awal = $conn3->prepare($query_karyawan_awal);
                    $stmt_karyawan_awal->bind_param("s", $npk_awal);
                    $stmt_karyawan_awal->execute();
                    $result_karyawan_awal = $stmt_karyawan_awal->get_result();
                    $name_awal = $result_karyawan_awal->num_rows > 0 ? $result_karyawan_awal->fetch_assoc()['name'] : 'Not Found';

                    // Query untuk mendapatkan nama dari tabel karyawan berdasarkan npk pengganti jika ada
                    $name_pengganti = '';
                    if (!empty($npk_pengganti)) {
                        $query_karyawan_pengganti = "SELECT name FROM karyawan WHERE npk = ?";
                        $stmt_karyawan_pengganti = $conn3->prepare($query_karyawan_pengganti);
                        $stmt_karyawan_pengganti->bind_param("s", $npk_pengganti);
                        $stmt_karyawan_pengganti->execute();
                        $result_karyawan_pengganti = $stmt_karyawan_pengganti->get_result();
                        $name_pengganti = $result_karyawan_pengganti->num_rows > 0 ? $result_karyawan_pengganti->fetch_assoc()['name'] : 'Not Found';
                    }

                    // Query untuk mendapatkan nama proses, status, dan min_skill dari tabel process
                    $query_proses_name = "SELECT name, status, min_skill FROM process WHERE id = ?";
                    $stmt_proses_name = $conn3->prepare($query_proses_name);
                    $stmt_proses_name->bind_param("i", $id_proses);
                    $stmt_proses_name->execute();
                    $result_proses_name = $stmt_proses_name->get_result();

                    // Inisialisasi variabel kualifikasi
                    $qualification_status = '';
                    $qualification_color = '';
                    $npk_to_check = !empty($npk_pengganti) ? $npk_pengganti : $npk_awal; // Gunakan npk_pengganti jika ada, jika tidak gunakan npk_awal
                    $name_to_display = !empty($npk_pengganti) ? $name_pengganti : $name_awal; // Tampilkan nama pengganti jika ada, jika tidak nama awal

                    if ($result_proses_name->num_rows > 0) {
                        $row_proses = $result_proses_name->fetch_assoc();
                        $process_name = $row_proses['name'];
                        $status = $row_proses['status']; // 1 for S Process, 0 for regular process
                        $min_skill = $row_proses['min_skill'];

                        // Tentukan warna baris berdasarkan status dan keberadaan pengganti
                        $row_color = !empty($npk_pengganti) ? ($status == 1 ? 'table-danger' : 'table-warning') : ''; // Merah untuk S Process, Kuning untuk proses biasa jika ada pengganti

                        // Cek kualifikasi man power (baik pengganti maupun awal) di tabel qualifications
                        $query_qualification = "SELECT value FROM qualifications WHERE npk = ? AND process_id = ?";
                        $stmt_qualification = $conn3->prepare($query_qualification);
                        $stmt_qualification->bind_param("si", $npk_to_check, $id_proses);
                        $stmt_qualification->execute();
                        $result_qualification = $stmt_qualification->get_result();

                        if ($result_qualification->num_rows > 0) {
                            $qualification_value = $result_qualification->fetch_assoc()['value'];
                            // Anggap qualified jika value >= min_skill
                            if ($qualification_value >= $min_skill) {
                                $qualification_status = 'Qualified';
                                $qualification_color = 'bg-success';
                            } else {
                                $qualification_status = 'Not Qualified';
                                $qualification_color = 'bg-danger';
                            }
                        } else {
                            // Jika tidak ada data kualifikasi, anggap tidak qualified
                            $qualification_status = 'Not Qualified';
                            $qualification_color = 'bg-danger';
                        }
                        $stmt_qualification->close();
                    } else {
                        $process_name = 'Unknown Process';
                        $row_color = ''; // Default tidak ada warna
                    }

                    // Tambahkan data ke array
                    $pic_data[] = [
                        'id_proses' => $id_proses,
                        'process_name' => $process_name,
                        'npk' => $npk_to_check, // Gunakan npk yang sesuai
                        'name' => $name_to_display, // Tampilkan nama yang sesuai
                        'row_color' => $row_color,
                        'qualification_status' => $qualification_status, // Status kualifikasi
                        'qualification_color' => $qualification_color // Warna badge kualifikasi
                    ];

                    $stmt_karyawan_awal->close();
                    if (!empty($npk_pengganti)) {
                        $stmt_karyawan_pengganti->close();
                    }
                    $stmt_proses_name->close();
                }
            }
            $stmt_process->close();
        } else {
            // Jika tidak ada inputan untuk tanggal hari ini, kosongkan data
            $pic_data = [];
        }
    } else {
        // Jika id_hkt tidak sesuai dengan tanggal hari ini, kosongkan data
        $pic_data = [];
    }
    $stmt_check_hkt->close();
}
?>