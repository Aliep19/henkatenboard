<?php
    // Query untuk mengambil data `workstations` dari `skillmap_db`
    $query_workstations = "SELECT id, name FROM workstations";
    $result_workstations = mysqli_query($conn3, $query_workstations);

    // Query untuk mengambil data dari tabel `line`
    $query_line = "SELECT id, name, workstation_id FROM sub_workstations";
    $result_line = mysqli_query($conn3, $query_line);

    $id_hkt = isset($_GET['id_hkt']) ? intval($_GET['id_hkt']) : 0;

    // Jika id_hkt tidak valid
    if ($id_hkt <= 0) {
        echo "ID HKT tidak valid.";
        exit;
    }

    // Query untuk mengambil data berdasarkan id_hkt
    $sql = "SELECT * FROM hkt_form WHERE id_hkt = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_hkt);
    $stmt->execute();
    $result = $stmt->get_result();

    // Periksa apakah data ditemukan
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc(); // Ambil data sebagai array asosiatif

        // Inisialisasi variabel dari data hasil query
        $id_bagian = $data['id_bagian'];
        $line = $data['id_line'];
        $shift = $data['id_shifft'];
        $output_target = $data['output_target'];

        // Query untuk mendapatkan name berdasarkan id_bagian di tabel workstations
        $sql2 = "SELECT name FROM workstations WHERE id = ?";
        $stmt2 = $conn3->prepare($sql2);
        $stmt2->bind_param("i", $id_bagian);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        if ($result2->num_rows > 0) {
            $workstation_data = $result2->fetch_assoc();
            $bagian_name = $workstation_data['name'];
        } else {
            echo "Bagian tidak ditemukan di tabel workstations.";
            exit;
        }

        // Query untuk mendapatkan name berdasarkan id_line di tabel sub_workstations
        $sql3 = "SELECT name FROM sub_workstations WHERE id = ?";
        $stmt3 = $conn3->prepare($sql3);
        $stmt3->bind_param("i", $line);
        $stmt3->execute();
        $result3 = $stmt3->get_result();

        if ($result3->num_rows > 0) {
            $sub_workstation_data = $result3->fetch_assoc();
            $line_name = $sub_workstation_data['name'];
        } else {
            echo "Line tidak ditemukan di tabel sub_workstations.";
            exit;
        }

        $stmt2->close();
        $stmt3->close();
    } else {
        echo "Data tidak ditemukan.";
        exit;
    }

    // Tutup koneksi
    $stmt->close();
    $conn->close();
    $conn3->close();
?>