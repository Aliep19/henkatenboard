<?php
// Query untuk mengambil data dari tabel `line`
$query_line = "SELECT id, name, workstation_id FROM sub_workstations";
$result_line = mysqli_query($conn3, $query_line);

$query_s_proses = "SELECT id_s_process, workstation_id FROM s_process_workstation";
$result_s_proses = mysqli_query($conn3, $query_s_proses);

// Query untuk mengambil data dari tabel `shift`
$query_shift = "SELECT id_shift, shift, jam_kerja FROM shift";
$result_shift = mysqli_query($conn, $query_shift);

// Query untuk mengambil data dari tabel `proses`
$query_proses = "SELECT id, workstation_id, name, min_skill FROM process";
$result_proses = mysqli_query($conn3, $query_proses);

// Query untuk mengambil data foreman (golongan 3, acting 2)
$query_foreman = "SELECT npk, full_name, golongan, acting FROM ct_users WHERE golongan = 3 AND acting = 2";
$result_foreman = mysqli_query($conn2, $query_foreman);

// Query untuk mengambil data line guide (golongan 1, acting 2)
$query_line_guide = "SELECT npk, full_name, golongan, acting FROM ct_users WHERE golongan = 1 AND acting = 2";
$result_line_guide = mysqli_query($conn2, $query_line_guide);

// Query untuk mengambil data `workstations` dari `skillmap_db`
$query_workstations = "SELECT id, name FROM workstations";
$result_workstations = mysqli_query($conn3, $query_workstations);
?>