<?php
/**
 * Dynamic Math Captcha Image Generator
 */
// Prevent any errors from being displayed and breaking the image stream
error_reporting(0);
ini_set('display_errors', 0);

// Clear any previous output buffer
if (ob_get_level()) ob_end_clean();
ob_start();

// Include dbconn for session consistency
require_once 'dbconn.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate numbers (1-10)
$num1 = rand(1, 10);
$num2 = rand(1, 10);
$_SESSION['captcha_answer'] = $num1 + $num2;

// Image dimensions
$width = 130;
$height = 45;

// Create image
$image = imagecreatetruecolor($width, $height);

// Enable alpha blending
imagealphablending($image, true);
imagesavealpha($image, true);

// Colors
$bgColor = imagecolorallocate($image, 28, 51, 68); // Dark blueish background to match screenshot
$white = imagecolorallocate($image, 255, 255, 255);
$noiseColor = imagecolorallocatealpha($image, 255, 255, 255, 50);

// Fill with background color
imagefill($image, 0, 0, $bgColor);

// Add noise lines (thicker diagonal lines like in screenshot)
imagesetthickness($image, 2);
imageline($image, 0, 5, $width, 40, $noiseColor);
imageline($image, 0, 40, $width, 5, $noiseColor);
imagesetthickness($image, 1);
imageline($image, 20, 0, 110, 45, $noiseColor);

// Add noise dots
for ($i = 0; $i < 60; $i++) {
    imagesetpixel($image, rand(0, $width), rand(0, $height), $noiseColor);
}

// Text content
$text = "$num1 + $num2 = ?";

// Try to use Poppins font
$fontPath = __DIR__ . '/plugins/fonts/poppins/Poppins-Medium.ttf';

if (file_exists($fontPath)) {
    $fontSize = 18;
    // Get text box size
    $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
    $text_width = $bbox[2] - $bbox[0];
    $text_height = $bbox[1] - $bbox[7];
    
    // Center text
    $x = ($width - $text_width) / 2;
    $y = ($height + $text_height) / 2 - 2;
    
    imagettftext($image, $fontSize, 0, $x, $y, $white, $fontPath, $text);
} else {
    imagestring($image, 5, 20, 15, $text, $white);
}

// Clean buffer and send image
ob_end_clean();
header('Content-Type: image/png');
header('Cache-Control: no-cache, must-revalidate');
imagepng($image);
imagedestroy($image);
exit();
