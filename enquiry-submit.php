<?php
// enquiry-submit.php — Form submission & DLT OTP trigger (.php only)
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/sms.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// 1. Determine safe redirect URL
$redirectUrl = trim($_POST['redirect_url'] ?? '');
if (empty($redirectUrl) && !empty($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    $parsed = parse_url($referer);
    $currentHost = $_SERVER['HTTP_HOST'] ?? '';
    if (empty($parsed['host']) || $parsed['host'] === $currentHost) {
        $redirectUrl = $referer;
    }
}
if (empty($redirectUrl)) {
    $redirectUrl = 'index.php';
}

/**
 * Helper to redirect back with a query parameter (success or error)
 */
function redirectWithParam(string $url, string $paramName, string $message): void {
    // Strip existing success/error params to prevent stacking
    $parts = explode('?', $url, 2);
    $base = $parts[0];
    $queryParams = [];
    if (isset($parts[1])) {
        parse_str($parts[1], $queryParams);
        unset($queryParams['success'], $queryParams['error']);
    }
    $queryParams[$paramName] = $message;
    $target = $base . '?' . http_build_query($queryParams);

    if (!headers_sent()) {
        header("Location: {$target}");
        exit;
    }

    // Fallback UI if headers already sent
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $paramName === 'error' ? 'Notice' : 'Success' ?> - Online Education</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body style="background:#F8FAFC; display:flex; align-items:center; justify-content:center; min-height:100vh; font-family:sans-serif; margin:0; padding:20px;">
        <div style="background:#FFF; max-width:480px; width:100%; padding:32px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.08); text-align:center;">
            <div style="font-size:2.5rem; margin-bottom:12px;"><?= $paramName === 'error' ? '⚠️' : '✅' ?></div>
            <h2 style="color:#0F172A; font-size:1.3rem; margin-bottom:10px; font-weight:800;"><?= $paramName === 'error' ? 'Form Notice' : 'Submission Successful' ?></h2>
            <p style="color:#475569; font-size:0.95rem; line-height:1.5; margin-bottom:24px;"><?= htmlspecialchars($message) ?></p>
            <a href="<?= htmlspecialchars($target) ?>" style="display:inline-block; background:#FF5722; color:#FFF; padding:10px 24px; border-radius:8px; text-decoration:none; font-weight:700;">Continue →</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// 2. Extract and normalize submitted fields
$senderName = trim($_POST['sender_name'] ?? $_POST['student_name'] ?? $_POST['name'] ?? '');
$senderPhone = trim($_POST['sender_phone'] ?? $_POST['phone'] ?? $_POST['mobile'] ?? '');
$senderEmail = trim($_POST['sender_email'] ?? $_POST['email'] ?? '');
$senderType = in_array($_POST['sender_type'] ?? '', ['parent', 'student']) ? $_POST['sender_type'] : 'student';

// Target Type
$rawTargetType = strtolower(trim($_POST['target_type'] ?? ''));
if (in_array($rawTargetType, ['educator', 'institution', 'university', 'college', 'institute'])) {
    $targetType = $rawTargetType;
} elseif (!empty($_POST['institution_name']) || (!empty($_POST['source']) && str_contains($_POST['source'], 'university'))) {
    $targetType = 'university';
} else {
    $targetType = 'institution';
}

$targetId = (int)($_POST['target_id'] ?? $_POST['institution_id'] ?? 1);
if ($targetId <= 0) {
    $targetId = 1;
}

// Location / State / Pincode
$cityOrPincode = trim($_POST['city_or_pincode'] ?? $_POST['state'] ?? $_POST['city'] ?? '');

// Message / Query text: accept message, or build from interested_stream / institution_name / state
$rawMessage = trim($_POST['message'] ?? $_POST['query'] ?? $_POST['details'] ?? '');
if (!empty($rawMessage)) {
    $message = $rawMessage;
} else {
    $details = [];
    if (!empty($_POST['interested_stream'])) {
        $details[] = "Course: " . trim($_POST['interested_stream']);
    }
    if (!empty($_POST['institution_name'])) {
        $details[] = "Institution: " . trim($_POST['institution_name']);
    }
    if (!empty($_POST['state'])) {
        $details[] = "State: " . trim($_POST['state']);
    }

    if (!empty($details)) {
        $message = "Inquiry: " . implode(" | ", $details);
    } else {
        $message = "General admission and fee counseling requested.";
    }
}

// 3. Validation: Name and Phone are required
if ($senderName === '') {
    redirectWithParam($redirectUrl, 'error', 'Please provide your full name to submit an enquiry.');
}

$cleanDigits = preg_replace('/[^0-9]/', '', $senderPhone);
if (strlen($cleanDigits) < 10) {
    redirectWithParam($redirectUrl, 'error', 'Please provide a valid 10-digit mobile number for verification.');
}

// 4. Save to Database
try {
    $stmt = $pdo->prepare("
        INSERT INTO enquiry (sender_name, sender_phone, sender_email, sender_type, target_type, target_id, message, city_or_pincode, status, created_at, updated_at)
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

    // 5. Generate and send DLT OTP via OEDUIN Template 1277179178303893981
    $generatedOtp = (string) rand(100000, 999999);
    sendOeduOtp($senderPhone, $senderName, $generatedOtp);

    redirectWithParam($redirectUrl, 'success', 'Your admission enquiry has been successfully submitted! A verification code has been dispatched via OEDUIN SMS.');
} catch (PDOException $e) {
    redirectWithParam($redirectUrl, 'error', 'Database Error saving enquiry: ' . $e->getMessage());
}
