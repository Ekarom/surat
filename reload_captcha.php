<?php
session_start();

// Generate new captcha question
$num1 = rand(1, 10);
$num2 = rand(1, 10);
$_SESSION['captcha_answer'] = $num1 + $num2;

// Return JSON response (Backward compatibility)
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'question' => $num1 . ' + ' . $num2 . ' = ?',
    'image' => 'captcha_img.php'
]);
