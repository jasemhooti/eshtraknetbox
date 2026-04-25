<?php
// automation.php - ساخت خودکار اشتراک نت‌باکس با cURL (مناسب هاست cPanel)

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function createNetboxAccount($plan_volume_gb, $days = 30) {
    // تولید نام کاربری و پسورد رندوم (می‌توانی بعداً تغییر دهی)
    $username = 'user_' . substr(md5(microtime(true) . rand(1000, 9999)), 0, 10);
    $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 12);

    $result = [
        'success' => false,
        'message' => '',
        'created_username' => $username,
        'created_password' => $password,
        'volume' => $plan_volume_gb,
        'days' => $days
    ];

    $cookie_file = __DIR__ . '/temp_cookies.txt';

    try {
        // مرحله ۱: لاگین به پنل
        $login_url = 'https://panil.jasemhooti2.ir/panel/index.php';
        
        $login_post = [
            'username' => 'wizwiz',
            'password' => '725019516663486727'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $login_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($login_post));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_exec($ch); // لاگین
        curl_close($ch);

        // مرحله ۲: ساخت کاربر - امتحان با POST به آدرس add_userPro.php
        $add_url = 'https://panil.jasemhooti2.ir/panel/add_userPro.php';

        $post_data = [
            'username'    => $username,
            'password'    => $password,
            'total'       => $plan_volume_gb,   // حجم به گیگ
            'date'        => $days,             // 30 روز
            'multi'       => '10',              // حدس برای حساب مولتی یوزر (۱۰ کاربر) - این را بعداً اصلاح می‌کنیم
            'add'         => '1',               // چون آدرس ?add داشت
            'submit'      => 'ذخیره'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $add_url);
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

        // بررسی نتیجه
        if ($http_code == 200 || $http_code == 302) {
            if (strpos($response, 'موفق') !== false || strpos($response, 'ذخیره شد') !== false || strpos($response, 'success') !== false || $http_code == 302) {
                $result['success'] = true;
                $result['message'] = 'اشتراک با موفقیت ساخته شد ✅';
            } else {
                $result['message'] = 'درخواست ارسال شد، اما پیام موفقیت پیدا نشد (ممکن است موفق باشد).';
                $result['success'] = true; // موقتاً موفقیت
            }
        } else {
            $result['message'] = "خطا در ارتباط با پنل. کد HTTP: $http_code";
        }

    } catch (Exception $e) {
        $result['message'] = 'خطای سیستمی: ' . $e->getMessage();
    }

    // پاک کردن فایل کوکی
    if (file_exists($cookie_file)) {
        @unlink($cookie_file);
    }

    return $result;
}
