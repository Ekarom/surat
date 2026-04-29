<?php
/**
 * Update Server Connection Tester
 * Upload this file to test connection to update server
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// CONFIGURATION - Edit this URL
$updateUrl = "http://data.p171.net/update/api_check_update.php";

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Server Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        .test-section { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        h2 { color: #007bff; margin-top: 0; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; border-left: 4px solid #007bff; }
        code { background: #e9ecef; padding: 2px 6px; border-radius: 3px; }
        .info-box { background: #e7f3ff; border-left: 4px solid #007bff; padding: 15px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table td { padding: 8px; border-bottom: 1px solid #dee2e6; }
        table td:first-child { font-weight: bold; width: 200px; }
    </style>
</head>
<body>
    <h1>🔍 Update Server Connection Test</h1>
    
    <div class="info-box">
        <strong>Testing URL:</strong> <code><?php echo htmlspecialchars($updateUrl); ?></code>
    </div>

    <!-- Test 1: PHP Configuration -->
    <div class="test-section">
        <h2>Test 1: PHP Configuration</h2>
        <table>
            <tr>
                <td>PHP Version</td>
                <td><?php echo phpversion(); ?></td>
            </tr>
            <tr>
                <td>allow_url_fopen</td>
                <td>
                    <?php 
                    $allow_url = ini_get('allow_url_fopen');
                    echo $allow_url ? '<span class="success">✓ Enabled</span>' : '<span class="error">✗ Disabled</span>'; 
                    ?>
                </td>
            </tr>
            <tr>
                <td>cURL Extension</td>
                <td>
                    <?php 
                    $curl_available = function_exists('curl_init');
                    echo $curl_available ? '<span class="success">✓ Available</span>' : '<span class="error">✗ Not Available</span>'; 
                    ?>
                </td>
            </tr>
            <tr>
                <td>JSON Extension</td>
                <td>
                    <?php 
                    $json_available = function_exists('json_decode');
                    echo $json_available ? '<span class="success">✓ Available</span>' : '<span class="error">✗ Not Available</span>'; 
                    ?>
                </td>
            </tr>
        </table>
        
        <?php if (!$allow_url && !$curl_available): ?>
            <div class="error">
                ⚠️ WARNING: Both allow_url_fopen and cURL are disabled! You need at least one enabled to check for updates.
            </div>
        <?php endif; ?>
    </div>

    <!-- Test 2: DNS Resolution -->
    <div class="test-section">
        <h2>Test 2: DNS Resolution</h2>
        <?php
        $parsed = parse_url($updateUrl);
        $host = $parsed['host'] ?? 'unknown';
        $ip = gethostbyname($host);
        ?>
        <table>
            <tr>
                <td>Hostname</td>
                <td><code><?php echo htmlspecialchars($host); ?></code></td>
            </tr>
            <tr>
                <td>Resolved IP</td>
                <td>
                    <code><?php echo htmlspecialchars($ip); ?></code>
                    <?php if ($ip !== $host): ?>
                        <span class="success">✓ DNS OK</span>
                    <?php else: ?>
                        <span class="error">✗ DNS Failed</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <!-- Test 3: cURL Connection -->
    <div class="test-section">
        <h2>Test 3: cURL Connection</h2>
        <?php if (function_exists('curl_init')): ?>
            <?php
            $ch = curl_init($updateUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => 'UpdateChecker/1.0'
            ]);
            
            $start_time = microtime(true);
            $response = curl_exec($ch);
            $elapsed = round((microtime(true) - $start_time) * 1000, 2);
            
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);
            ?>
            
            <table>
                <tr>
                    <td>HTTP Status Code</td>
                    <td>
                        <code><?php echo $httpCode; ?></code>
                        <?php if ($httpCode === 200): ?>
                            <span class="success">✓ OK</span>
                        <?php else: ?>
                            <span class="error">✗ Error</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>Response Time</td>
                    <td><?php echo $elapsed; ?> ms</td>
                </tr>
                <tr>
                    <td>Content Type</td>
                    <td><code><?php echo $info['content_type'] ?? 'unknown'; ?></code></td>
                </tr>
                <tr>
                    <td>Error Message</td>
                    <td><?php echo $error ? '<span class="error">' . htmlspecialchars($error) . '</span>' : '<span class="success">None</span>'; ?></td>
                </tr>
            </table>
            
            <?php if ($response !== false && $httpCode === 200): ?>
                <h3>Response Preview:</h3>
                <pre><?php echo htmlspecialchars(substr($response, 0, 1000)); ?></pre>
                <?php $curl_response = $response; ?>
            <?php else: ?>
                <div class="error">✗ cURL request failed!</div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="warning">⚠️ cURL extension not available</div>
        <?php endif; ?>
    </div>

    <!-- Test 4: file_get_contents Connection -->
    <div class="test-section">
        <h2>Test 4: file_get_contents Connection</h2>
        <?php if (ini_get('allow_url_fopen')): ?>
            <?php
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'ignore_errors' => true,
                    'user_agent' => 'UpdateChecker/1.0'
                ]
            ]);
            
            $start_time = microtime(true);
            $response = @file_get_contents($updateUrl, false, $context);
            $elapsed = round((microtime(true) - $start_time) * 1000, 2);
            ?>
            
            <table>
                <tr>
                    <td>Response Time</td>
                    <td><?php echo $elapsed; ?> ms</td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>
                        <?php if ($response !== false): ?>
                            <span class="success">✓ Success</span>
                        <?php else: ?>
                            <span class="error">✗ Failed</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            
            <?php if ($response !== false): ?>
                <h3>Response Preview:</h3>
                <pre><?php echo htmlspecialchars(substr($response, 0, 1000)); ?></pre>
                <?php $fgc_response = $response; ?>
            <?php else: ?>
                <div class="error">✗ file_get_contents request failed!</div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="warning">⚠️ allow_url_fopen is disabled</div>
        <?php endif; ?>
    </div>

    <!-- Test 5: JSON Parsing -->
    <div class="test-section">
        <h2>Test 5: JSON Parsing</h2>
        <?php
        $test_response = $curl_response ?? $fgc_response ?? null;
        
        if ($test_response):
            $data = json_decode($test_response, true);
            $json_error = json_last_error();
        ?>
            <table>
                <tr>
                    <td>JSON Valid</td>
                    <td>
                        <?php if ($json_error === JSON_ERROR_NONE): ?>
                            <span class="success">✓ Yes</span>
                        <?php else: ?>
                            <span class="error">✗ No</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>JSON Error</td>
                    <td>
                        <?php 
                        if ($json_error === JSON_ERROR_NONE) {
                            echo '<span class="success">None</span>';
                        } else {
                            echo '<span class="error">' . json_last_error_msg() . '</span>';
                        }
                        ?>
                    </td>
                </tr>
            </table>
            
            <?php if ($json_error === JSON_ERROR_NONE && $data): ?>
                <h3>Parsed Data:</h3>
                <pre><?php print_r($data); ?></pre>
                
                <?php if (isset($data['version'])): ?>
                    <div class="success">
                        <h3>✓ Update Server is Working!</h3>
                        <p>Latest Version: <strong><?php echo htmlspecialchars($data['version']); ?></strong></p>
                        <?php if (isset($data['message'])): ?>
                            <p>Message: <?php echo htmlspecialchars($data['message']); ?></p>
                        <?php endif; ?>
                        <?php if (isset($data['download_url'])): ?>
                            <p>Download URL: <code><?php echo htmlspecialchars($data['download_url']); ?></code></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="warning">⚠️ No response received to parse</div>
        <?php endif; ?>
    </div>

    <!-- Summary -->
    <div class="test-section">
        <h2>📊 Summary</h2>
        <?php
        $all_ok = true;
        $issues = [];
        
        if (!$allow_url && !$curl_available) {
            $all_ok = false;
            $issues[] = "Both allow_url_fopen and cURL are disabled";
        }
        
        if ($ip === $host) {
            $all_ok = false;
            $issues[] = "DNS resolution failed";
        }
        
        if (isset($httpCode) && $httpCode !== 200) {
            $all_ok = false;
            $issues[] = "HTTP request returned status $httpCode";
        }
        
        if (!isset($data) || !$data) {
            $all_ok = false;
            $issues[] = "JSON parsing failed or invalid response";
        }
        
        if ($all_ok && isset($data['version'])):
        ?>
            <div class="success">
                <h3>✅ ALL TESTS PASSED!</h3>
                <p>Your application can successfully connect to the update server.</p>
                <p><strong>Latest version available:</strong> <?php echo htmlspecialchars($data['version']); ?></p>
            </div>
        <?php else: ?>
            <div class="error">
                <h3>❌ TESTS FAILED</h3>
                <p>Issues found:</p>
                <ul>
                    <?php foreach ($issues as $issue): ?>
                        <li><?php echo htmlspecialchars($issue); ?></li>
                    <?php endforeach; ?>
                </ul>
                <p><strong>Recommendation:</strong> Check the troubleshooting guide (TROUBLESHOOTING_FAILED_FETCH.md)</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="info-box">
        <strong>Next Steps:</strong>
        <ul>
            <li>If all tests passed, your update checker should work fine</li>
            <li>If tests failed, check the issues listed above</li>
            <li>Refer to <code>TROUBLESHOOTING_FAILED_FETCH.md</code> for detailed solutions</li>
            <li>Make sure the update server URL is correct and accessible</li>
        </ul>
    </div>
</body>
</html>
