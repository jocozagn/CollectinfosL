<?php

$source = __DIR__.'/../public/images/collectinfo-logo.jpg';
$dir = __DIR__.'/../public';

[$width, $height] = getimagesize($source);
$src = imagecreatefromjpeg($source);

// Monogramme « Ci » : partie supérieure (~58 % de la hauteur)
$cropHeight = (int) round($height * 0.58);
$cropSize = min($width, $cropHeight);
$offsetX = (int) round(($width - $cropSize) / 2);
$icon = imagecreatetruecolor($cropSize, $cropSize);
imagealphablending($icon, false);
imagesavealpha($icon, true);
$transparent = imagecolorallocatealpha($icon, 0, 0, 0, 127);
imagefill($icon, 0, 0, $transparent);
imagecopy($icon, $src, 0, 0, $offsetX, 0, $cropSize, $cropSize);

foreach ([16, 32, 180] as $size) {
    $resized = imagecreatetruecolor($size, $size);
    imagealphablending($resized, false);
    imagesavealpha($resized, true);
    imagefill($resized, 0, 0, $transparent);
    imagecopyresampled($resized, $icon, 0, 0, 0, 0, $size, $size, $cropSize, $cropSize);
    $path = $dir."/favicon-{$size}.png";
    imagepng($resized, $path);
    imagedestroy($resized);
    echo "Created {$path}\n";
}

// favicon.ico (32x32 PNG wrapped — browsers accept PNG at /favicon.ico too; also copy as ico via PNG)
copy($dir.'/favicon-32.png', $dir.'/favicon.png');
copy($dir.'/favicon-32.png', $dir.'/favicon.ico');

imagedestroy($icon);
imagedestroy($src);

echo "Done.\n";
