<?php
// automation.php - نسخه بهبود یافته برای پنل wizwiz

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function createNetboxAccount($plan_volume_gb, $days = 30) {
    $username = 'user_' . substr(md5(microtime(true) . rand()), 0, 10);
    $password = substr(str_shuffle('abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 12);

    $result = [
        'success' => false,
        'message' => '',
        'created_username' => $username,
        'created_password' => $password,
        'volume' => $plan_volume_gb,
        'days' => $days
    ];

    $cookie_file = __DIR__ . '/temp_cookies_' . rand(1000,9999) . '.txt';

    try {
        // 1. لاگین
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
        curl_exec($ch);
        curl_close($ch);

        // 2. ارسال فرم ساخت کاربر - چندین روش تست
        $add_url = 'https://panil.jasemhooti2.ir/panel/add_userPro.php';

        // نسخه 1: POST به add_userPro.php
        $post_data = [
            'username'   => $username,
            'password'   => $password,
            'total'      => $plan_volume_gb,   // گیگابایت
            'date'       => $days,             // 30 روز
            'multi'      => '10',              // نام‌های رایج مولتی یوزر
            'op_multi'   => '10',
            'limitip'    => '10',
            'add'        => '1',
            'submit'     => 'ذخیره'
        ];

        $ch = curl_init($add_url);
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
        $success_keywords = ['موفق', 'ذخیره شد', 'success', 'created', 'اضافه شد', 'ثبت شد'];
        $is_success = false;
        foreach ($success_keywords as $word) {
            if (stripos($response, $word) !== false) {
                $is_success = true;
                break;
            }
        }

        if ($http_code == 200 || $http_code == 302 || $is_success) {
            $result['success'] = true;
            $result['message'] = 'اشتراک با موفقیت ساخته شد ✅ (درخواست ارسال شد)';
        } else {
            $result['message'] = 'درخواست ارسال شد اما کاربر ساخته نشد. کد HTTP: ' . $http_code;
            // لاگ پاسخ برای دیباگ
            file_put_contents(__DIR__ . '/last_response.log', $response);
        }

    } catch (Exception $e) {
        $result['message'] = 'خطا: ' . $e->getMessage();
    }

    // پاک کردن کوکی
    if (file_exists($cookie_file)) @unlink($cookie_file);

    return $result;
}
