<?php

session_start();

$str = 'ABClmn5678stuvwxopqrLMNOPQRSbcdefghijkTUVWXYZa234yz01DEFGHIJK9';
$code = substr(str_shuffle($str), 10, 6);
$_SESSION['captcha'] = $code;

$img = imagecreatetruecolor(120, 40);
$bg = imagecolorallocate($img, 255, 255, 255);
$noise = imagecolorallocate($img, 150, 150, 150);

imagefill($img, 0, 0, $bg);

for ($i = 0; $i < rand(2, 8); $i++) {
    imageline(
        $img,
        rand(0, 180), rand(0, 60),
        rand(0, 180), rand(0, 60),
        $noise
    );
}

for ($i = 0; $i < 80; $i++) {
    imagesetpixel($img, rand(0, 180), rand(0, 60), $noise);
}

$x = 8;

for ($i = 0; $i < strlen($code); $i++) {

    $char = $code[$i];

    $shade = rand(0, 120);

    $color = imagecolorallocate($img, $shade, $shade, $shade);

    $size = rand(4, 5);

    $y = rand(12, 25);

    imagestring($img, $size, $x, $y, $char, $color);

    $x += 20;
}

header('Content-Type: image/png');
imagepng($img);
