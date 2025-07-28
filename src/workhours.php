<?php
// Mengatur zona waktu ke Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

// Inisialisasi variabel tanggal dan waktu otomatis
$date = date('Y-m-d H:i:s');
$time = date('H:i:s');
$jumatCheck = date('N'); // Mengecek apakah hari ini Jumat (5)
$lossMng = 0;

// Penentuan shift berdasarkan waktu
if ($time >= '22:30:00' || $time <= '05:59:59') {
    $shift = 1; // Shift malam
    $arr_start_shift1 = array('22:30:00', '23:00:00', '00:00:00', '01:00:00', '02:00:00', '03:00:00', '04:00:00', '05:00:00');
    $arr_finish_shift1 = array('22:59:59', '23:59:59', '00:59:59', '01:59:59', '02:59:59', '02:59:59', '04:59:59', '05:59:59');
    for ($i = 0; $i < count($arr_start_shift1); $i++) {
        $timeShift[$i]['start_time'] = $arr_start_shift1[$i];
        $timeShift[$i]['finish_time'] = $arr_finish_shift1[$i];
    }
} elseif ($time >= '06:00:00' && $time <= '14:29:59') {
    $shift = 2; // Shift pagi
    $arr_start_shift2 = array('06:00:00', '07:00:00', '08:00:00', '09:00:00', '10:00:00', '11:00:00', '12:00:00', '13:00:00', '14:00:00');
    $arr_finish_shift2 = array('06:59:59', '07:59:59', '08:59:59', '09:59:59', '10:59:59', '11:59:59', '12:59:59', '13:59:59', '14:29:59');
    for ($i = 0; $i < count($arr_start_shift2); $i++) {
        $timeShift[$i]['start_time'] = $arr_start_shift2[$i];
        $timeShift[$i]['finish_time'] = $arr_finish_shift2[$i];
    }
} elseif ($time >= '14:30:00' && $time <= '22:29:59') {
    $shift = 3; // Shift sore
    $arr_start_shift3 = array('14:30:00', '15:00:00', '16:00:00', '17:00:00', '18:00:00', '19:00:00', '20:00:00', '21:00:00', '22:00:00');
    $arr_finish_shift3 = array('14:59:59', '15:59:59', '16:59:59', '17:59:59', '18:59:59', '19:59:59', '20:59:59', '21:59:59', '22:29:59');
    for ($i = 0; $i < count($arr_start_shift3); $i++) {
        $timeShift[$i]['start_time'] = $arr_start_shift3[$i];
        $timeShift[$i]['finish_time'] = $arr_finish_shift3[$i];
    }
}

// Penentuan waktu awal dan akhir shift
if ($shift == 1) {
    if ($time >= '22:30:00') {
        $dtime_awal = date('Y-m-d') . ' 22:30:00';
        $dtime_akhir = date('Y-m-d', strtotime('+ 1 day')) . ' 06:00:00';
    } elseif ($time >= '00:00:00') {
        $dtime_awal = date('Y-m-d', strtotime('-1 day')) . ' 22:30:00';
        $dtime_akhir = date('Y-m-d') . ' 05:59:59';
    }
} else if ($shift == 2) {
    $dtime_awal = date('Y-m-d') . ' 06:00:00';
    $dtime_akhir = date('Y-m-d') . ' 14:30:00';
} else if ($shift == 3) {
    $dtime_awal = date('Y-m-d') . ' 14:30:00';
    $dtime_akhir = date('Y-m-d') . ' 22:30:00';
}
?>