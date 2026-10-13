<?php
// enquiry-submit.php — Form submission & DLT OTP trigger (.php only)
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/sms.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$senderName = trim($_POST['sender_name'] ?? '');
$senderPhone = trim($_POST['sender_phone'] ?? '');
$senderEmail = trim($_POST['sender_email'] ?? '');
$senderType = in_array($_POST['sender_type'] ?? '', ['parent', 'student']) ? $_POST['sender_type'] : 'parent';
$targetType = in_array($_POST['target_type'] ?? '', ['educator', 'institution']) ? $_POST['target_type'] : 'educator';
$targetId = (int) ($_POST['target_id'] ?? 1);
$message = trim($_POST['message'] ?? '');
$cityOrPincode = trim($_POST['city_or_pincode'] ?? '');
$redirectUrl = trim($_POST['redirect_url'] ?? 'index.php');

if (!$senderName || !$senderPhone || !$message) {
    die("Error: Please provide your name, phone number, and query details. <a href='javascript:history.back()'>Go back</a>");
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO enquiries (sender_name, sender_phone, sender_email, sender_type, target_type, target_id, message, city_or_pincode, status, created_at, updated_at)
        VALUES (:name, :phone, :email, :stype, :ttype, :tid, :msg, :city, 'new', NOW(), NOW())
    ");
    $stmt->execute([
        ':name' => $senderName,
        ':phone' => $senderPhone,
        ':email' => $senderEmail,
        ':stype' => $senderType,
        ':ttype' => $targetType,
        ':tid' => $targetId,
        ':msg' => $message,
        ':city' => $cityOrPincode,
    ]);

    // Generate and send DLT OTP via OEDUIN Template 1277179178303893981
    $generatedOtp = (string) rand(100000, 999999);
    sendOeduOtp($senderPhone, $senderName, $generatedOtp);

    $successMsg = urlencode("Your enquiry has been successfully submitted! A verification code has been dispatched via OEDUIN.");
    $separator = (strpos($redirectUrl, '?') !== false) ? '&' : '?';
    header("Location: {$redirectUrl}{$separator}success={$successMsg}");
    exit;
} catch (PDOException $e) {
    die("Database Error saving enquiry: " . htmlspecialchars($e->getMessage()));
}
