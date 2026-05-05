<?php
// Mocking the backend environment for logic testing

function clean_numeric($val) {
    if (empty($val)) return '';
    $val = trim($val);
    if (strpos(strtoupper($val), 'E+') !== false) {
        $val = sprintf("%.0f", (float)$val);
    }
    return preg_replace('/[^0-9]/', '', $val);
}

function format_date_to_db($dateStr)
{
    if (empty($dateStr) || $dateStr == '00-00-0000' || $dateStr == '-' || $dateStr == '0000-00-00')
        return NULL;

    $dateStr = trim($dateStr);

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr))
        return $dateStr;

    if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $dateStr, $matches)) {
        return $matches[3] . '-' . sprintf('%02d', $matches[2]) . '-' . sprintf('%02d', $matches[1]);
    }

    if (is_numeric($dateStr) && $dateStr > 10000 && $dateStr < 100000) {
        $unix_date = ($dateStr - 25569) * 86400;
        return date("Y-m-d", $unix_date);
    }

    $time = strtotime($dateStr);
    if ($time)
        return date('Y-m-d', $time);

    return NULL;
}

// TEST CASES
echo "--- Testing clean_numeric ---\n";
$tests_nip = [
    '123456' => '123456',
    '1.23E+5' => '123000',
    '1.23456789012346E+17' => '123456789012346000',
    'NIP: 123-456' => '123456'
];
foreach($tests_nip as $input => $expected) {
    $result = clean_numeric($input);
    echo "Input: $input | Expected: $expected | Result: $result | " . ($result === $expected ? "OK" : "FAIL") . "\n";
}

echo "\n--- Testing format_date_to_db ---\n";
$tests_date = [
    '2024-05-12' => '2024-05-12',
    '12-05-2024' => '2024-05-12',
    '12/05/2024' => '2024-05-12',
    '45424' => '2024-05-12', // 2024-05-12 in Excel
    'May 12, 2024' => '2024-05-12',
    'invalid' => '' // NULL
];
foreach($tests_date as $input => $expected) {
    $result = format_date_to_db($input) ?? '';
    echo "Input: $input | Expected: $expected | Result: $result | " . ($result === $expected ? "OK" : "FAIL") . "\n";
}
?>
