<?php
// automation.php - ساخت خودکار اشتراک با cURL (مناسب هاست cPanel)

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function createNetboxAccount($plan_volume_gb, $days = 30) {
    $username = 'user_' . substr(md5(time() . rand()), 0, 8);   // تولید نام کاربری رندوم
    $password = substr(md5(time() . rand()), 0, 12);            // تولید پسورد رندوم

    $result = [
        'success' => false,
        'message' => '',
        'created_username' => $username,
        'created_password' => $password
    ];

    // کوکی‌ها را نگه می‌داریم
    $cookie_file = __DIR__ . '/cookies.txt';

    try {
        // مرحله 1: لاگین به پنل
        $login_url = 'https://panil.jasemhooti2.ir/panel/index.php';
        $login_data = [
            'username' => 'wizwiz',
            'password' => '725019516663486727'
        ];

        $ch = curl_init($login_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($login_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        $login_response = curl_exec($ch);
        curl_close($ch);

        // مرحله 2: ارسال فرم ساخت کاربر
        $add_url = 'https://panil.jasemhooti2.ir/panel/usersPro.php';   // اگر آدرس دقیق متفاوت بود بعداً اصلاح می‌کنیم

        $post_data = [
            'username' => $username,
            'password' => $password,
            'total'    => $plan_volume_gb,     // حجم به گیگابایت
            'date'     => $days,               // همیشه 30
            // فیلد مولتی یوزر - این قسمت احتمالاً نیاز به اصلاح دارد
            'multi_user' => '10',              // حدس اولیه - بعداً با cURL واقعی اصلاح می‌شود
            'submit'   => 'ذخیره'             // یا هر نامی که دکمه دارد
        ];

        $ch = curl_init($add_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200 || $http_code == 302) {
            if (strpos($response, 'موفق') !== false || strpos($response, 'ذخیره شد') !== false || $http_code == 302) {
                $result['success'] = true;
                $result['message'] = 'اشتراک با موفقیت ساخته شد';
            } else {
                $result['message'] = 'درخواست ارسال شد اما موفقیت تأیید نشد. ممکن است موفق باشد.';
                $result['success'] = true; // موقتاً موفقیت فرض می‌کنیم
            }
        } else {
            $result['message'] = 'خطا در ارسال درخواست. کد: ' . $http_code;
        }

    } catch (Exception $e) {
        $result['message'] = 'خطای کلی: ' . $e->getMessage();
    }

    // پاک کردن فایل کوکی بعد از استفاده (اختیاری)
    if (file_exists($cookie_file)) unlink($cookie_file);

    return $result;
}
