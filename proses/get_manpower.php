<?php
require_once '../konfigurasi/konfig.php';

if (isset($_POST['id_bagian']) && is_numeric($_POST['id_bagian']) && 
    isset($_POST['id_line']) && is_numeric($_POST['id_line'])) {
    
    $id_bagian = intval($_POST['id_bagian']);
    $id_line = intval($_POST['id_line']);

    $data = [];

    $query = "
    SELECT 
        k.npk AS employee_npk,
        k.name AS employee_name,
        k.role AS employee_role,
        MAX(q.value) AS qualification_value,
        p.id AS process_id,
        p.min_skill AS process_min_skill,
        p.s_process_id AS s_process_id,
        CASE 
            WHEN p.s_process_id IS NOT NULL AND p.s_process_id != 0 THEN 
                CASE 
                    WHEN spc.npk IS NOT NULL THEN 1
                    ELSE 0
                END
            ELSE NULL
        END AS has_certification
    FROM 
        karyawan k
    INNER JOIN 
        karyawan_workstation kw ON kw.npk = k.npk
    INNER JOIN 
        qualifications q ON q.npk = k.npk
    INNER JOIN 
        process p ON p.id = q.process_id
    INNER JOIN 
        sub_workstations sw ON sw.id = p.workstation_id
    LEFT JOIN 
        s_process_certification spc ON spc.npk = k.npk AND spc.s_process_id = p.s_process_id
    WHERE 
        sw.id = ? 
        AND q.value IS NOT NULL
    GROUP BY 
        k.npk, k.name, k.role, p.id, p.min_skill, p.s_process_id, spc.npk
";

    $stmt = $conn3->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $id_line);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $stmt->close();
    } else {
        echo json_encode(["error" => "Query preparation failed: " . $conn3->error]);
        exit;
    }

    echo json_encode($data);
} else {
    echo json_encode(["error" => "Invalid or missing parameters"]);
}

$conn3->close();
?>