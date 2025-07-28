<?php 
$line_id = $_GET['line'];

// Query untuk mendapatkan nama sub_workstation
$query_line = "SELECT name FROM sub_workstations WHERE id = ?";
$stmt_line = $conn3->prepare($query_line);
$stmt_line->bind_param("i", $line_id);
$stmt_line->execute();
$result_line = $stmt_line->get_result();
$line_name = $result_line->num_rows > 0 ? $result_line->fetch_assoc()['name'] : 'Unknown Line';
$stmt_line->close();

// Query untuk mendapatkan proses yang terkait dengan workstation
$query_processes = "
    SELECT p.id, p.name, p.min_skill 
    FROM process p 
    INNER JOIN sub_workstations sw ON p.workstation_id = sw.id 
    WHERE sw.id = ?
    GROUP BY p.id, p.name, p.min_skill
";
$stmt_processes = $conn3->prepare($query_processes);
$stmt_processes->bind_param("i", $line_id);
$stmt_processes->execute();
$result_processes = $stmt_processes->get_result();
$processes = [];
while ($row = $result_processes->fetch_assoc()) {
    $processes[$row['id']] = [
        'name' => $row['name'],
    ];
    
}
$stmt_processes->close();

// Query untuk mendapatkan manpower dan kompetensinya
$query_manpower = "
    SELECT k.npk, k.name, q.process_id, q.value 
    FROM karyawan k
    INNER JOIN karyawan_workstation kw ON k.npk = kw.npk
    LEFT JOIN qualifications q ON k.npk = q.npk
    WHERE kw.workstation_id = ?
    ORDER BY k.name
";
$stmt_manpower = $conn3->prepare($query_manpower);
$stmt_manpower->bind_param("i", $line_id);
$stmt_manpower->execute();
$result_manpower = $stmt_manpower->get_result();

$manpower_data = [];
while ($row = $result_manpower->fetch_assoc()) {
    $npk = $row['npk'];
    if (!isset($manpower_data[$npk])) {
        $manpower_data[$npk] = [
            'name' => $row['name'],
            'skills' => []
        ];
    }
    if ($row['process_id'] && isset($processes[$row['process_id']])) {
        $manpower_data[$npk]['skills'][$row['process_id']] = [
            'value' => $row['value'],
        ];
    }
}
$stmt_manpower->close();

?>