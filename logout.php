<?php
// logout.php — Student Sign Out
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

unset($_SESSION['student_user_id']);
unset($_SESSION['student_name']);
unset($_SESSION['student_email']);
unset($_SESSION['student_phone']);
unset($_SESSION['student_role']);

header("Location: login.php?msg=logged_out");
exit;
