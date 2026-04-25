<?php
require_once 'automation.php';

$result = createNetboxAccount(5, 30);   // تست با ۵ گیگ

echo "<pre dir='rtl'>";
print_r($result);
echo "</pre>";

if (file_exists('last_response.log')) {
    echo "<h3>محتوای آخرین پاسخ پنل (برای دیباگ):</h3>";
    echo "<pre>" . htmlspecialchars(file_get_contents('last_response.log')) . "</pre>";
}
