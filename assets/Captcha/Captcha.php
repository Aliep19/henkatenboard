<?php
// Mulai session untuk menyimpan kode captcha
session_start();

// Fungsi untuk membuat kode captcha acak
function generateCaptchaCode($length = 6)
{
    $characters = "abcdeghjkmnpqrstuvwxyz2345689";
    $captchaCode = '';
    for ($i = 0; $i < $length; $i++) {
        $captchaCode .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $captchaCode;
}

// Membuat kode captcha dan menyimpannya di session
$captchaCode = generateCaptchaCode();
$_SESSION['captcha_code'] = $captchaCode;

// Membuat gambar captcha
header('Content-Type: image/png');

$imageWidth = 130; // Change the width to your desired size (e.g., 130)
$imageHeight = 60; // Change the height to your desired size (e.g., 60)
$image = imagecreatetruecolor($imageWidth, $imageHeight);

// Warna background
$backgroundColor = imagecolorallocate($image, 240, 240, 240);
imagefill($image, 0, 0, $backgroundColor);

// Tambahkan efek garis pada gambar captcha
for ($i = 0; $i < 40; $i++) {
    $captchaLineColor = imagecolorallocate($image, rand(100, 200), rand(100, 200), rand(100, 200));
    $lineX1 = rand(0, $imageWidth);
    $lineY1 = rand(0, $imageHeight);
    $lineX2 = $lineX1 + rand(-10, 10);
    $lineY2 = $lineY1 + rand(-10, 10);
    imageline($image, $lineX1, $lineY1, $lineX2, $lineY2, $captchaLineColor);
}


// Efek melayang-melayang pada tulisan captcha
$fontFile = '1.ttf';
$fontSize = 22;
$fontColor = imagecolorallocate($image, 0, 0, 0);

$textBox = imagettfbbox($fontSize, 0, $fontFile, $captchaCode);
$textWidth = $textBox[2] - $textBox[0];
$textHeight = $textBox[1] - $textBox[7];
$textX = ($imageWidth - $textWidth) / 2;
$baseY = ($imageHeight - $textHeight) / 2 + $fontSize;

// Add wavy effect to the Y-coordinate of the text
$textY = $baseY + sin($textX / 10) * 10;

// Membuat tulisan captcha dengan efek gelombang
imagettftext($image, $fontSize, 0, $textX, $textY, $fontColor, $fontFile, $captchaCode);

// Tampilkan gambar captcha
imagepng($image);

// Hapus gambar dari memori
imagedestroy($image);
