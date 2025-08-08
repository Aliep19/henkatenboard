<?php
session_start();
ob_clean();
ini_set('display_errors', 1);
error_reporting(E_ALL);

function generateCaptchaCode($length = 6) {
    $characters = "abcdeghjkmnpqrstuvwxyz2345689";
    $captchaCode = '';
    for ($i = 0; $i < $length; $i++) {
        $captchaCode .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $captchaCode;
}

$captchaCode = generateCaptchaCode();
$_SESSION['captcha_code'] = $captchaCode;

header('Content-Type: image/png');
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");

$imageWidth = 130;
$imageHeight = 60;
$image = imagecreatetruecolor($imageWidth, $imageHeight);

$backgroundColor = imagecolorallocate($image, 240, 240, 240);
imagefill($image, 0, 0, $backgroundColor);

// noise
for ($i = 0; $i < 40; $i++) {
    $lineColor = imagecolorallocate($image, rand(100, 200), rand(100, 200), rand(100, 200));
    imageline($image, rand(0, $imageWidth), rand(0, $imageHeight), rand(0, $imageWidth), rand(0, $imageHeight), $lineColor);
}

// font
$fontFile = __DIR__ . '/1.ttf';
if (!file_exists($fontFile)) {
    die("Font tidak ditemukan: $fontFile");
}

$fontSize = 22;
$fontColor = imagecolorallocate($image, 0, 0, 0);

$textBox = imagettfbbox($fontSize, 0, $fontFile, $captchaCode);
$textWidth = $textBox[2] - $textBox[0];
$textHeight = $textBox[1] - $textBox[7];
$textX = (int)(($imageWidth - $textWidth) / 2);
$textY = (int)(($imageHeight + $fontSize) / 2);

$drawn = imagettftext($image, $fontSize, 0, $textX, $textY, $fontColor, $fontFile, $captchaCode);
if (!$drawn) {
    die("Gagal menggambar CAPTCHA");
}

imagepng($image);
imagedestroy($image);
