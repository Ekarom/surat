<?php
/**
 * Dynamic Math Captcha Image Generator - Refined to match screenshot
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
$bgColor = imagecolorallocate($image, 28, 51, 68); // Dark blueish background
$white = imagecolorallocate($image, 255, 255, 255);
$accentColor = imagecolorallocate($image, 52, 152, 219); // Light blue accent for lines/border
$noiseColorAlpha = imagecolorallocatealpha($image, 52, 152, 219, 60); // Transparent light blue

// Fill with background color
imagefill($image, 0, 0, $bgColor);

// Draw a subtle border inside the image (to match the screenshot's outline)
imagerectangle($image, 0, 0, $width - 1, $height - 1, $accentColor);

// Add noise lines (Diagonal lines in light blue)
imagesetthickness($image, 1);
for ($i = 0; $i < 4; $i++) {
    imageline($image, rand(0, $width/2), rand(0, $height), rand($width/2, $width), rand(0, $height), $noiseColorAlpha);
}

// Add noise dots (Light blue dots)
for ($i = 0; $i < 80; $i++) {
    imagesetpixel($image, rand(1, $width-2), rand(1, $height-2), $accentColor);
}

// Text content
$text = "$num1 + $num2 = ?";

// Try to use Poppins font
$fontPath = __DIR__ . '/plugins/fonts/poppins/Poppins-Medium.ttf';

if (file_exists($fontPath)) {
    $fontSize = 17;
    // Get text box size
    $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
    $text_width = $bbox[2] - $bbox[0];
    $text_height = $bbox[1] - $bbox[7];
    
    // Center text
    $x = ($width - $text_width) / 2;
    $y = ($height + $text_height) / 2 + 5; // Adjusted offset for vertical centering
    
    // Draw text
    imagettftext($image, $fontSize, 0, $x, $y, $white, $fontPath, $text);
} else {
    // Fallback if font doesn't exist
    imagestring($image, 5, 25, 15, $text, $white);
}

// Clean buffer and send image
ob_end_clean();
header('Content-Type: image/png');
header('Cache-Control: no-cache, must-revalidate');
imagepng($image);
imagedestroy($image);
exit();
