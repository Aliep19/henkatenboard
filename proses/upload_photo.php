<?php
// session_start();

// // Pemeriksaan sesi
// if (!isset($_SESSION['log']) || $_SESSION['log'] !== 'True') {
//     header("Location: ../login.php");
//     exit();
// }

// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo'])) {
//     $npk = $_SESSION['npk'];
//     $upload_dir = "../assets/img/profiles/";
//     $upload_file = $upload_dir . $npk . ".jpg"; // Simpan dengan nama berdasarkan NPK

//     // Validasi tipe file
//     $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
//     $file_type = $_FILES['profile_photo']['type'];
//     if (!in_array($file_type, $allowed_types)) {
//         echo "<script>alert('Tipe file harus JPG, JPEG, atau PNG!'); window.location='../home.php';</script>";
//         exit();
//     }

//     // Validasi ukuran file (300KB)
//     if ($_FILES['profile_photo']['size'] > 300 * 1024) {
//         echo "<script>alert('Ukuran file maksimal 300KB!'); window.location='../home.php';</script>";
//         exit();
//     }

//     // Validasi dimensi gambar
//     list($width, $height) = getimagesize($_FILES['profile_photo']['tmp_name']);
//     if ($width < 100 || $height < 100) {
//         echo "<script>alert('Dimensi gambar minimal 100px x 100px!'); window.location='../home.php';</script>";
//         exit();
//     }

//     // Hapus foto lama jika ada
//     if (file_exists($upload_file)) {
//         unlink($upload_file);
//     }

//     // Simpan foto baru
//     if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_file)) {
//         echo "<script>alert('Foto profil berhasil diperbarui!'); window.location='../home.php';</script>";
//     } else {
//         echo "<script>alert('Gagal mengunggah foto!'); window.location='../home.php';</script>";
//     }
// } else {
//     header("Location: ../home.php");
//     exit();
// }
?>