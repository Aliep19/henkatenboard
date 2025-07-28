<?php
require_once '../konfigurasi/konfig.php';

header('Content-Type: application/json');

if (!isset($_POST['id_hkt']) || empty($_POST['id_hkt'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID HKT tidak valid']);
    exit;
}

$id_hkt = mysqli_real_escape_string($conn, $_POST['id_hkt']);

$query = "
    SELECT 
        h.id_hkt,
        DATE_FORMAT(h.date, '%d-%m-%Y') AS date,
        DATE_FORMAT(h.to_date, '%d-%m-%Y') AS to_date,
        h.id_shifft AS id_shift,
        h.output_target,
        h.foreman1,
        h.foreman2,
        h.line_guide1,
        h.line_guide2,
        h.id_bagian,
        h.id_line,
        GROUP_CONCAT(
            CONCAT(mp.id_proses, ':', mp.man_power)
            SEPARATOR ', '
        ) AS manpower
    FROM henkaten.hkt_form h
    LEFT JOIN henkaten.mp_procees mp ON h.id_hkt = mp.id_hkt
    WHERE h.id_hkt = '$id_hkt'
    GROUP BY h.id_hkt
";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(['status' => 'error', 'message' => 'Query error: ' . mysqli_error($conn)]);
    exit;
}

$row = mysqli_fetch_assoc($result);

if (!$row) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
    exit;
}

$manpower = [];
if ($row['manpower']) {
    $manpower_pairs = explode(', ', $row['manpower']);
    foreach ($manpower_pairs as $pair) {
        list($process_id, $manpower_npk) = explode(':', $pair);
        $manpower[] = [
            'process_id' => $process_id,
            'manpower_npk' => $manpower_npk
        ];
    }
}

$response = [
    'status' => 'success',
    'data' => [
        'id_hkt' => $row['id_hkt'],
        'date' => $row['date'],
        'to_date' => $row['to_date'],
        'id_shift' => $row['id_shift'],
        'output_target' => $row['output_target'],
        'foreman1' => $row['foreman1'],
        'foreman2' => $row['foreman2'],
        'line_guide1' => $row['line_guide1'],
        'line_guide2' => $row['line_guide2'],
        'id_bagian' => $row['id_bagian'],
        'id_line' => $row['id_line'],
        'manpower' => $manpower
    ]
];

echo json_encode($response);

mysqli_close($conn);
?>