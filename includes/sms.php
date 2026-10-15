<?php
// includes/sms.php - DLT SMS & OTP Integration for Online Education (Oedu.in)
// Using OfferPlant / morg.in SMS Gateway as in advocateindex

if (!defined('SMS_AUTH_KEY')) {
    define('SMS_AUTH_KEY', 'b0e99bea1fa7d15e27e1c5fd8e3c868');
}
if (!defined('SMS_SENDER_ID')) {
    define('SMS_SENDER_ID', 'OEDUIN');
}
if (!defined('SMS_OTP_TEMPLATE_ID')) {
    define('SMS_OTP_TEMPLATE_ID', '1277179178303893981');
}

/**
 * Format DLT approved OTP message for OEDUIN
 */
function formatOeduOtpMessage(string $recipientName, string $otp): string {
    $cleanName = preg_replace('/[^a-zA-Z0-9 ]/', '', $recipientName) ?: 'Student';
    return "Dear {$cleanName}, \n Your OTP / EVC / Password is {$otp} \n \n Regards\n OEDUIN\n OfferPlant";
}

/**
 * Dispatch SMS via morg.in / OfferPlant Gateway
 */
function sendOeduOtp(string $phone, string $recipientName, string $otp): array {
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($cleanPhone) === 12 && substr($cleanPhone, 0, 2) === '91') {
        $cleanPhone = substr($cleanPhone, 2);
    }

    $message = formatOeduOtpMessage($recipientName, $otp);

    $payload = [
        'smsContent'     => $message,
        'groupId'        => '',
        'routeId'        => 1,
        'mobileNumbers'  => $cleanPhone,
        'senderId'       => SMS_SENDER_ID,
        'signature'      => '',
        'smsContentType' => 'english'
    ];

    $logEntry = "[" . date('Y-m-d H:i:s') . "] Attempt to {$cleanPhone} (OTP: {$otp}):\n" . $message . "\n";

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => "http://msg.morg.in/rest/services/sendSMS/sendGroupSms?AUTH_KEY=" . SMS_AUTH_KEY,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => "",
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_CUSTOMREQUEST  => "POST",
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            "Cache-Control: no-cache",
            "Content-Type: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    $result = [];
    if ($err) {
        $result = [
            'status' => 'error',
            'msg'    => $err,
            'otp'    => $otp
        ];
        $logEntry .= "Gateway Error: {$err}\n---\n";
    } else {
        $result = [
            'status'   => 'success',
            'response' => $response,
            'otp'      => $otp
        ];
        $logEntry .= "Gateway Response: {$response}\n---\n";
    }

    @file_put_contents(__DIR__ . '/../sms_log.txt', $logEntry, FILE_APPEND);

    return $result;
}
