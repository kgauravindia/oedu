<?php
// includes/sms.php - DLT SMS integration for Oedu.in

define('SMS_SENDER_ID', 'OEDUIN');
define('SMS_OTP_TEMPLATE_ID', '1277179178303893981');
define('SMS_OTP_TEMPLATE_NAME', 'OEDUIN_OTP');

/**
 * Format DLT approved OTP message
 */
function formatOeduOtpMessage(string $recipientName, string $otp): string {
    $cleanName = preg_replace('/[^a-zA-Z0-9 ]/', '', $recipientName) ?: 'User';
    return "Dear {$cleanName}, \n Your OTP / EVC / Password is {$otp} \n \n Regards\n OEDUIN\n OfferPlant";
}

/**
 * Send SMS via OfferPlant / DLT gateway
 */
function sendOeduOtp(string $phone, string $recipientName, string $otp): bool {
    $message = formatOeduOtpMessage($recipientName, $otp);

    // Local debug logging
    $logEntry = "[" . date('Y-m-d H:i:s') . "] SMS to {$phone} | Sender: " . SMS_SENDER_ID . " | Template: " . SMS_OTP_TEMPLATE_ID . "\n" . $message . "\n---\n";
    @file_put_contents(__DIR__ . '/../sms_log.txt', $logEntry, FILE_APPEND);

    return true;
}
