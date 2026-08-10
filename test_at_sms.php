<?php
require_once __DIR__ . '/config/sms.php';

putenv('HTTP_PROXY=');
putenv('HTTPS_PROXY=');
putenv('http_proxy=');
putenv('https_proxy=');

function at_config_value(string $key, string $default = ''): string
{
    if (defined($key)) {
        return trim((string)constant($key));
    }

    $value = getenv($key);

    if ($value === false || trim((string)$value) === '') {
        return $default;
    }

    return trim((string)$value);
}

function at_sms_endpoint(): string
{
    $environment = strtolower(at_config_value('AT_ENV', 'sandbox'));

    if ($environment === 'live' || $environment === 'production') {
        return 'https://api.africastalking.com/version1/messaging';
    }

    return 'https://api.sandbox.africastalking.com/version1/messaging';
}

function send_test_sms(string $phone, string $message): array
{
    $username = at_config_value('AT_USERNAME', 'sandbox');
    $apiKey = at_config_value('AT_API_KEY');
    $senderId = at_config_value('AT_SENDER_ID');

    $caFile = 'C:/wamp64/ssl/cacert.pem';
    $endpoint = at_sms_endpoint();

    if ($username === '' || $apiKey === '' || $apiKey === 'PASTE_YOUR_SANDBOX_API_KEY_HERE') {
        return [
            'ok' => false,
            'error' => 'Africa’s Talking API key is missing or still using placeholder value.',
            'username' => $username,
            'api_key_length' => strlen($apiKey),
            'endpoint' => $endpoint,
        ];
    }

    if (!function_exists('curl_init')) {
        return [
            'ok' => false,
            'error' => 'cURL is not enabled.',
        ];
    }

    if (!is_file($caFile)) {
        return [
            'ok' => false,
            'error' => 'CA certificate file was not found.',
            'expected_ca_file' => $caFile,
        ];
    }

    $payload = [
        'username' => $username,
        'to' => $phone,
        'message' => $message,
    ];

    /*
    |--------------------------------------------------------------------------
    | IMPORTANT FOR SANDBOX
    |--------------------------------------------------------------------------
    | Leave senderId empty in sandbox unless Africa's Talking gave you one.
    */
    if ($senderId !== '') {
        $payload['from'] = $senderId;
    }

    $debugFile = __DIR__ . '/curl_debug_sms_official.log';
    $verbose = fopen($debugFile, 'w');

    $ch = curl_init($endpoint);

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_HTTPHEADER => [
            'apiKey: ' . $apiKey,
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'Expect:',
        ],

        CURLOPT_POSTFIELDS => http_build_query($payload),

        CURLOPT_TIMEOUT => 90,
        CURLOPT_CONNECTTIMEOUT => 20,

        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_CAINFO => $caFile,

        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,

        CURLOPT_USERAGENT => 'MzumbeVotingSystem/1.0 PHP-cURL',

        CURLOPT_PROXY => '',
        CURLOPT_NOPROXY => '*',

        CURLOPT_VERBOSE => true,
        CURLOPT_STDERR => $verbose,
    ]);

    $responseBody = curl_exec($ch);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $primaryIp = curl_getinfo($ch, CURLINFO_PRIMARY_IP);
    $connectTime = curl_getinfo($ch, CURLINFO_CONNECT_TIME);
    $sslTime = curl_getinfo($ch, CURLINFO_APPCONNECT_TIME);
    $startTransferTime = curl_getinfo($ch, CURLINFO_STARTTRANSFER_TIME);
    $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);

    curl_close($ch);

    if (is_resource($verbose)) {
        fclose($verbose);
    }

    return [
        'ok' => $responseBody !== false && $statusCode >= 200 && $statusCode < 300,
        'http_status' => $statusCode,
        'curl_errno' => $curlErrno,
        'curl_error' => $curlError,
        'endpoint' => $endpoint,
        'primary_ip' => $primaryIp,
        'username' => $username,
        'api_key_length' => strlen($apiKey),
        'to' => $phone,
        'ca_file_exists' => is_file($caFile),
        'connect_time' => $connectTime,
        'ssl_handshake_time' => $sslTime,
        'start_transfer_time' => $startTransferTime,
        'total_time' => $totalTime,
        'debug_log' => $debugFile,
        'response_raw' => $responseBody,
        'response_json' => json_decode((string)$responseBody, true),
    ];
}

/*
|--------------------------------------------------------------------------
| IMPORTANT:
| Use the exact phone number opened/registered in Africa's Talking Simulator.
|--------------------------------------------------------------------------
*/
$result = send_test_sms(
    '+255712345678',
    'Test SMS from Mzumbe Electoral Portal'
);

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);