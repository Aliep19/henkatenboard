<?php
require_once 'konfigurasi/konfig.php';


class PicProcessController {
    private $conn;
    private $conn3;

    public function __construct($conn, $conn3) {
        $this->conn = $conn;
        $this->conn3 = $conn3;
    }

    public function getPicProcess($id_hkt) {
        $pic_data = [];
        $today = date("Y-m-d");

        if (!isset($id_hkt) || intval($id_hkt) <= 0) {
            return $pic_data;
        }

        // Cek id_hkt
        $query_check_hkt = "SELECT id_hkt FROM hkt_form WHERE id_hkt = ? AND ? BETWEEN date AND to_date";
        $stmt_check_hkt = $this->conn->prepare($query_check_hkt);
        $stmt_check_hkt->bind_param("is", $id_hkt, $today);
        $stmt_check_hkt->execute();
        $result_check_hkt = $stmt_check_hkt->get_result();

        if ($result_check_hkt->num_rows === 0) {
            $stmt_check_hkt->close();
            return $pic_data;
        }
        $stmt_check_hkt->close();

        // Cek inputan terbaru
        $query_check_input = "SELECT COUNT(*) as count FROM perubahan WHERE tanggal = ? AND id_proses IN (SELECT id_proses FROM mp_procees WHERE id_hkt = ?)";
        $stmt_check_input = $this->conn->prepare($query_check_input);
        $stmt_check_input->bind_param("si", $today, $id_hkt);
        $stmt_check_input->execute();
        $result_check_input = $stmt_check_input->get_result();
        $input_count = $result_check_input->fetch_assoc()['count'];
        $stmt_check_input->close();

        if ($input_count === 0) {
            return $pic_data;
        }

        // Ambil data proses
        $query_process = "SELECT mp.id_proses, mp.man_power, mp.mp_pengganti, p.id_perubahan, p.checked 
                         FROM mp_procees mp 
                         LEFT JOIN perubahan p ON mp.id_proses = p.id_proses AND p.tanggal = ?
                         WHERE mp.id_hkt = ? AND mp.mp_pengganti IS NOT NULL";
        $stmt_process = $this->conn->prepare($query_process);
        $stmt_process->bind_param("si", $today, $id_hkt);
        $stmt_process->execute();
        $result_process = $stmt_process->get_result();

        while ($row = $result_process->fetch_assoc()) {
            $id_proses = $row['id_proses'];
            $npk_awal = $row['man_power'];
            $npk_pengganti = $row['mp_pengganti'];
            $id_perubahan = $row['id_perubahan'];
            $checked = $row['checked'];

            $name_awal = $this->getEmployeeName($npk_awal);
            $name_pengganti = !empty($npk_pengganti) ? $this->getEmployeeName($npk_pengganti) : '';

            $process_data = $this->getProcessData($id_proses);
            $process_name = $process_data['name'] ?? 'Unknown Process';
            $row_color = $process_data['status'] == 1 ? 'table-danger' : 'table-light';
            $min_skill = $process_data['min_skill'] ?? 0;

            $npk_to_check = !empty($npk_pengganti) ? $npk_pengganti : $npk_awal;
            $name_to_display = !empty($npk_pengganti) ? $name_pengganti : $name_awal;

            $qualification = $this->getQualification($npk_to_check, $id_proses, $min_skill);

            $pic_data[] = [
                'id_proses' => $id_proses,
                'process_name' => $process_name,
                'npk' => $npk_to_check,
                'name' => $name_to_display,
                'row_color' => $row_color,
                'qualification_status' => $qualification['status'],
                'qualification_color' => $qualification['color'],
                'id_perubahan' => $id_perubahan,
                'checked' => $checked // Can be 1, 0, or NULL
            ];
        }
        $stmt_process->close();

        return $pic_data;
    }

    private function getEmployeeName($npk) {
        $query = "SELECT name FROM karyawan WHERE npk = ?";
        $stmt = $this->conn3->prepare($query);
        $stmt->bind_param("s", $npk);
        $stmt->execute();
        $result = $stmt->get_result();
        $name = $result->num_rows > 0 ? $result->fetch_assoc()['name'] : 'Not Found';
        $stmt->close();
        return $name;
    }

    private function getProcessData($id_proses) {
        $query = "SELECT name, status, min_skill FROM process WHERE id = ?";
        $stmt = $this->conn3->prepare($query);
        $stmt->bind_param("i", $id_proses);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->num_rows > 0 ? $result->fetch_assoc() : [];
        $stmt->close();
        return $data;
    }

    private function getQualification($npk, $process_id, $min_skill) {
        $query = "SELECT value FROM qualifications WHERE npk = ? AND process_id = ?";
        $stmt = $this->conn3->prepare($query);
        $stmt->bind_param("si", $npk, $process_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $value = $result->fetch_assoc()['value'];
            $status = $value >= $min_skill ? 'Qualified' : 'Not Qualified';
            $color = $value >= $min_skill ? 'bg-success' : 'bg-danger';
        } else {
            $status = 'Not Qualified';
            $color = 'bg-danger';
        }
        $stmt->close();
        return ['status' => $status, 'color' => $color];
    }
}

// Inisialisasi kelas dan panggil fungsi
$controller = new PicProcessController($conn, $conn3);
$pic_data = $controller->getPicProcess($output_data['id_hkt']);

function renderBadge($checked) {
    if ($checked === 1) {
        return '<span class="badge rounded-pill px-2 py-1 bg-success text-white">Approved</span>';
    } elseif ($checked === 0) {
        return '<span class="badge rounded-pill px-2 py-1 bg-danger text-white">Not Approved</span>';
    }
    return '<span class="badge rounded-pill px-2 py-1 text-black">-</span>';
}


?>