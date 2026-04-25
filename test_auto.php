<?php
require_once 'automation.php';

echo "<h2>تست ساخت کاربر - نسخه جدید</h2>";

$result = createNetboxAccount(10, 30);   // ۱۰ گیگ - ۳۰ روز

echo "<pre dir='rtl' style='background:#f4f4f4;padding:15px'>";
print_r($result);
echo "</pre>";

if (file_exists('last_response.log')) {
    echo "<h3>محتوای آخرین پاسخ پنل (برای پیدا کردن مشکل):</h3>";
    echo "<pre dir='rtl' style='background:#000;color:#0f0;padding:15px;font-size:13px'>";
    echo htmlspecialchars(file_get_contents('last_response.log'));
    echo "</pre>";
} else {
    echo "<p>فایل last_response.log ساخته نشد.</p>";
}
