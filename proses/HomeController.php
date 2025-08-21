<?php
session_start();
require_once 'konfigurasi/konfig.php';
require_once 'src/workhours.php';
require_once 'src/get_home.php';

// Store selected line and shift in session
if (isset($_GET['line'])) {
    $_SESSION['selected_line'] = $_GET['line'];
}
if (isset($_GET['shift'])) {
    $_SESSION['selected_shift'] = $_GET['shift'];
}

// Redirect to include line and shift in URL if they are in session but not in GET
if (!isset($_GET['line']) && isset($_SESSION['selected_line']) || !isset($_GET['shift']) && isset($_SESSION['selected_shift'])) {
    $line = urlencode($_SESSION['selected_line'] ?? '');
    $shift = urlencode($_SESSION['selected_shift'] ?? '');
    header("Location: home.php?line=$line&shift=$shift");
    exit;
}

// Fetch line options sesuai dept user
function getLines($conn3) {
    $lines = [];

    // Default query ambil semua line
    $query = "SELECT id, name FROM sub_workstations ORDER BY name ASC";

    if (isset($_SESSION['dept']) && !empty($_SESSION['dept'])) {
        $dept = mysqli_real_escape_string($conn3, $_SESSION['dept']);

        // Cek apakah dept ada di tabel department
        $checkDept = mysqli_query($conn3, "SELECT 1 FROM department WHERE dept_name = '$dept' LIMIT 1");

        if ($checkDept && mysqli_num_rows($checkDept) > 0) {
            // Kalau dept ada → filter line berdasarkan dept
            $query = "
                SELECT sw.id, sw.name 
                FROM sub_workstations sw
                JOIN workstations w ON sw.workstation_id = w.id
                JOIN department d ON w.dept_id = d.id
                WHERE d.dept_name = '$dept'
                ORDER BY sw.name ASC
            ";
        }
        mysqli_free_result($checkDept);
    }

    $result = mysqli_query($conn3, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $lines[] = [
                'id' => $row['id'],
                'name' => htmlspecialchars($row['name']),
                'selected' => (isset($_GET['line']) && $_GET['line'] == $row['id']) || 
                             (!isset($_GET['line']) && isset($_SESSION['selected_line']) && $_SESSION['selected_line'] == $row['id'])
                             ? 'selected' : ''
            ];
        }
        mysqli_free_result($result);
    }

    return $lines;
}


// Fetch shift options
function getShifts($conn, $shift) {
    $query = "SELECT id_shift, shift FROM shift";
    $result = mysqli_query($conn, $query);
    $shifts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $shifts[] = [
            'id_shift' => $row['id_shift'],
            'shift' => htmlspecialchars($row['shift']),
            'selected' => (isset($_GET['shift']) && $_GET['shift'] == $row['id_shift']) || 
                         (!isset($_GET['shift']) && isset($_SESSION['selected_shift']) && $_SESSION['selected_shift'] == $row['id_shift']) ||
                         (!isset($_GET['shift']) && $row['id_shift'] == $shift)
                         ? 'selected' : ''
        ];
    }
    return $shifts;
}

// Fetch output target
function getOutputTarget($conn, $line_id, $shift_id) {
    $output_target = "Tidak Ada Target";
    $id_hkt = null;
    if ($line_id && $shift_id) {
        $today = date("Y-m-d");
        $query = "SELECT id_hkt, output_target FROM hkt_form WHERE id_line = ? AND id_shifft = ? AND ? BETWEEN date AND to_date LIMIT 1";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("iis", $line_id, $shift_id, $today);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $output_target = $row['output_target'] . " Units";
                $id_hkt = $row['id_hkt'];
            }
            $stmt->close();
        }
    }
    return ['output_target' => $output_target, 'id_hkt' => $id_hkt];
}

// Fetch foreman and line guide from absen_support table
function getForemanAndLineGuide($conn, $conn3, $line_id, $shift_id) {
    $foreman_list = [];
    $line_guide_list = [];

    if ($line_id && $shift_id) {
        // Query absen_support table for the current date
        $query = "SELECT npk, absen, pengganti FROM absen_support 
                  WHERE tanggal = CURDATE() AND id_hkt IN (
                      SELECT id_hkt FROM hkt_form WHERE id_line = ? AND id_shifft = ?
                  )";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("ii", $line_id, $shift_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $npk = str_pad($row['npk'], 5, "0", STR_PAD_LEFT);
                $absen = (int)$row['absen'];
                $pengganti = !empty($row['pengganti']) ? str_pad($row['pengganti'], 5, "0", STR_PAD_LEFT) : null;

                // Determine which NPK to use based on absen status
                $effective_npk = ($absen !== 5 && $pengganti) ? $pengganti : $npk;
                $employee_name = getEmployeeName($conn3, $effective_npk) ?: $effective_npk;

                // Fetch role from hkt_form to determine if NPK is foreman or line guide
                $role_query = "SELECT foreman, foreman_2, line_guide, line_guide2 
                               FROM hkt_form 
                               WHERE id_hkt = (SELECT id_hkt FROM absen_support WHERE npk = ? AND tanggal = CURDATE())";
                $role_stmt = $conn->prepare($role_query);
                $role_stmt->bind_param("s", $npk);
                $role_stmt->execute();
                $role_result = $role_stmt->get_result();
                $role_data = $role_result->fetch_assoc();

                if ($role_data) {
                    if ($npk == $role_data['foreman'] || $npk == $role_data['foreman_2'] || 
                        ($pengganti && ($pengganti == $role_data['foreman'] || $pengganti == $role_data['foreman_2']))) {
                        $foreman_list[$effective_npk] = $employee_name;
                    } elseif ($npk == $role_data['line_guide'] || $npk == $role_data['line_guide2'] || 
                             ($pengganti && ($pengganti == $role_data['line_guide'] || $pengganti == $role_data['line_guide2']))) {
                        $line_guide_list[$effective_npk] = $employee_name;
                    }
                }

                $role_stmt->close();
            }
            $stmt->close();
        }
    }

    return ['foreman_list' => $foreman_list, 'line_guide_list' => $line_guide_list];
}

// Helper to get employee name
function getEmployeeName($conn, $npk) {
    $query = "SELECT name FROM karyawan WHERE npk = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $npk);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['name'];
        }
        $stmt->close();
    }
    return null;
}

function canForemanEdit($conn3) {
    if (!isset($_SESSION['log'], $_SESSION['golongan']) || $_SESSION['log'] !== 'True' || $_SESSION['golongan'] != 4) {
        return false; // bukan foreman
    }

    if (empty($_SESSION['dept'])) {
        return false; // tidak ada dept
    }

    $dept = mysqli_real_escape_string($conn3, $_SESSION['dept']);
    $checkDept = mysqli_query($conn3, "SELECT 1 FROM department WHERE dept_name = '$dept' LIMIT 1");
    $isValid = $checkDept && mysqli_num_rows($checkDept) > 0;
    mysqli_free_result($checkDept);

    return $isValid;
}

// Data preparation
$line_id = isset($_GET['line']) ? $_GET['line'] : null;
$shift_id = isset($_GET['shift']) ? $_GET['shift'] : null;
$lines = getLines($conn3);
$shifts = getShifts($conn, $shift);
$output_data = getOutputTarget($conn, $line_id, $shift_id);
$leaders = getForemanAndLineGuide($conn, $conn3, $line_id, $shift_id);
?>