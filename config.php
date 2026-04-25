<?php
// config.php

define('SITE_URL', 'hast.jasemhooti2.ir'); // آدرس سایت خودت
define('ADMIN_CARD', '6219861934798843');

define('PANEL_URL', 'https://panil.jasemhooti2.ir/panel');
define('PANEL_USERNAME', 'wizwiz');
define('PANEL_PASSWORD', '725019516663486727');

// تنظیمات دیتابیس (تغییر بده)
$db_host = 'localhost';
$db_name = 'instane4_jasemdb';
$db_user = 'instane4_jasemuser';
$db_pass = 'Mm09370126906';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("خطای اتصال به دیتابیس: " . $e->getMessage());
}
