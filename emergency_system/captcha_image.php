<?php
session_start();

$code = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6);
$_SESSION['login_captcha'] = $code;

$width = 140;
$height = 45;
$image = imagecreatetruecolor($width, $height);

// Colors for the "zigzag" box
$bg = imagecolorallocate($image, 15, 23, 42); // Dark slate background matching theme
$textColor = imagecolorallocate($image, 255, 255, 255);
$lineColor = imagecolorallocate($image, 100, 116, 139);
$dotColor = imagecolorallocate($image, 148, 163, 184);

imagefill($image, 0, 0, $bg);

// Add zigzag lines
for($i = 0; $i < 8; $i++) {
    imageline($image, 0, rand() % $height, $width, rand() % $height, $lineColor);
}

// Add noise dots
for($i = 0; $i < 100; $i++) {
    imagesetpixel($image, rand() % $width, rand() % $height, $dotColor);
}

// Draw characters in a zigzag pattern
$x = 15;
for ($i = 0; $i < strlen($code); $i++) {
    $y = rand(8, 22);
    // 5 is the largest built-in GD font
    imagestring($image, 5, $x, $y, $code[$i], $textColor);
    $x += 16 + rand(-2, 4);
}

// Add an overlay line to make it harder for bots
imageline($image, 10, rand(10, 35), $width - 10, rand(10, 35), $textColor);

header('Content-Type: image/png');
// Clear any potential output buffers before sending image
if (ob_get_length()) ob_clean();
imagepng($image);
imagedestroy($image);
