<?php
// Panggil fungsi stempel dan simpan outputnya ke file
stempelreject("Danish Luthfi (MIS)", "22-12-2021", "../assets/img/luths.png");
echo "<img src='../assets/img/luths.png' alt='Stempel'>";
stempelcheck("Danish Luthfi (MIS)", "22-12-2021", "../assets/img/luth.png");
echo "<img src='../assets/img/luth.png' alt='Stempel'>";

function stempelcheck($nama, $date, $output_file) {
    // Membuat gambar stempel dengan ukuran 300x160 piksel
    $image = imagecreatetruecolor(300, 160);

    // Set latar belakang menjadi transparan
    imagesavealpha($image, true);
    $background_color = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $background_color);

    // Warna teks putih
    $text_color = imagecolorallocate($image, 0, 0, 0);

    // Menambahkan gambar ikon decline ke gambar stempel dengan mengubah ukurannya
    $icon = imagecreatefrompng("../assets/img/yes.png");
    $icon_width = 80; // Ubah sesuai dengan ukuran yang diinginkan
    $icon_height = 80; // Ubah sesuai dengan ukuran yang diinginkan
    $icon_x = (imagesx($image) - $icon_width) / 2; // Hitung posisi x untuk menempatkan ikon di tengah
    $icon_y = (imagesy($image) - $icon_height - 20) / 2; // Hitung posisi y untuk menempatkan ikon di tengah
    imagecopyresampled($image, $icon, $icon_x, $icon_y, 0, 0, $icon_width, $icon_height, imagesx($icon), imagesy($icon));
    imagedestroy($icon);

    // Set path ke file font TrueType (TTF)
    $font_file = 'Candu.ttf'; // Sesuaikan dengan lokasi font Anda

    // Ukuran teks
    $text_size = 12;

    // Hitung lebar teks "Rejected by [nama]" dan tanggal
    $text_approved_width = imagettfbbox($text_size, 0, $font_file, "Approved by $nama");
    $text_date_width = imagettfbbox($text_size, 0, $font_file, "$date");

    // Hitung posisi x untuk menempatkan teks "Rejected by [nama]" di tengah
    $text_approved_x = (imagesx($image) - ($text_approved_width[2] - $text_approved_width[0])) / 2;

    // Hitung posisi x untuk menempatkan tanggal di tengah
    $text_date_x = (imagesx($image) - ($text_date_width[2] - $text_date_width[0])) / 2;

    // Koordinat untuk menempatkan teks "Rejected by [nama]" pada gambar stempel
    $text_y = 130;

    // Menambahkan teks "Rejected by [nama]" pada gambar stempel dengan font TTF
    imagettftext($image, $text_size, 0, $text_approved_x, $text_y, $text_color, $font_file, "Approved by $nama");

    // Menambahkan tanggal di bawah teks "Rejected by"
    $date_y = $text_y + 20; // Menempatkan tanggal 20 piksel di bawah teks "Rejected by"
    imagettftext($image, $text_size, 0, $text_date_x, $date_y, $text_color, $font_file, "$date");

    // Simpan gambar stempel ke file
    imagepng($image, $output_file);

    // Menghapus gambar dari memori
    imagedestroy($image);
}



function stempelreject($nama, $date, $output_file) {
    // Membuat gambar stempel dengan ukuran 300x160 piksel
    $image = imagecreatetruecolor(300, 160);

    // Set latar belakang menjadi transparan
    imagesavealpha($image, true);
    $background_color = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $background_color);

    // Warna teks putih
    $text_color = imagecolorallocate($image, 0, 0, 0);

    // Menambahkan gambar ikon decline ke gambar stempel dengan mengubah ukurannya
    $icon = imagecreatefrompng("../assets/img/decline.png");
    $icon_width = 110; // Ubah sesuai dengan ukuran yang diinginkan
    $icon_height = 110; // Ubah sesuai dengan ukuran yang diinginkan
    $icon_x = (imagesx($image) - $icon_width) / 2; // Hitung posisi x untuk menempatkan ikon di tengah
    $icon_y = (imagesy($image) - $icon_height - 20) / 2; // Hitung posisi y untuk menempatkan ikon di tengah
    imagecopyresampled($image, $icon, $icon_x, $icon_y, 0, 0, $icon_width, $icon_height, imagesx($icon), imagesy($icon));
    imagedestroy($icon);

    // Set path ke file font TrueType (TTF)
    $font_file = 'Candu.ttf'; // Sesuaikan dengan lokasi font Anda

    // Ukuran teks
    $text_size = 12;

    // Hitung lebar teks "Rejected by [nama]" dan tanggal
    $text_approved_width = imagettfbbox($text_size, 0, $font_file, "Rejected by $nama");
    $text_date_width = imagettfbbox($text_size, 0, $font_file, "$date");

    // Hitung posisi x untuk menempatkan teks "Rejected by [nama]" di tengah
    $text_approved_x = (imagesx($image) - ($text_approved_width[2] - $text_approved_width[0])) / 2;

    // Hitung posisi x untuk menempatkan tanggal di tengah
    $text_date_x = (imagesx($image) - ($text_date_width[2] - $text_date_width[0])) / 2;

    // Koordinat untuk menempatkan teks "Rejected by [nama]" pada gambar stempel
    $text_y = 130;

    // Menambahkan teks "Rejected by [nama]" pada gambar stempel dengan font TTF
    imagettftext($image, $text_size, 0, $text_approved_x, $text_y, $text_color, $font_file, "Rejected by $nama");

    // Menambahkan tanggal di bawah teks "Rejected by"
    $date_y = $text_y + 20; // Menempatkan tanggal 20 piksel di bawah teks "Rejected by"
    imagettftext($image, $text_size, 0, $text_date_x, $date_y, $text_color, $font_file, "$date");

    // Simpan gambar stempel ke file
    imagepng($image, $output_file);

    // Menghapus gambar dari memori
    imagedestroy($image);
}


?>
