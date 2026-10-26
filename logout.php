<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// تدمير جلسة PHP بالكامل
$_SESSION = array();
session_destroy();

// التوجيه لصفحة تسجيل الدخول
header("Location: login.html");
exit();
?>