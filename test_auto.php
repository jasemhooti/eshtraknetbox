<?php
require_once 'automation.php';

$result = createNetboxAccount(10, 30);   // تست با ۱۰ گیگ و ۳۰ روز

echo "<pre>";
print_r($result);
echo "</pre>";
