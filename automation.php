<?php
// automation.php - نسخه نهایی با مدیریت بهتر session

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function createNetboxAccount($plan_volume_gb, $days = 30) {
    $username = 'user_' . substr(md5(microtime(true) . rand()), 0, 10);
    $password = substr(str_shuffle('abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 14);

    $result = [
        'success' => false,
        'message' => '',
        'created_username' => $username,
        'created_password' => $password,
        'volume' => $plan_volume_gb,
        'days' => $days
    ];

    $cookie_file = __DIR__ . '/cookies.txt';   // فایل ثابت برای همه درخواست‌ها

    try {
        // ====================== مرحله ۱: لاگین ======================
        $login_url = 'https://panil.jasemhooti2.ir/panel/index.php';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $login_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'username' => 'wizwiz',
            'password' => '725019516663486727'
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);   // مهم: فالو ریدایرکت
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_REFERER, $login_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: fa-IR,fa;q=0.9,en;q=0.8'
        ]);

        $login_response = curl_exec($ch);
        $login_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($login_http !== 200 && $login_http !== 302) {
            $result['message'] = "خطا در لاگین - کد: $login_http";
            return $result;
        }

        // ====================== مرحله ۲: ساخت کاربر ======================
        $add_url = 'https://panil.jasemhooti2.ir/panel/add_userPro.php?add';

        $post_data = [
            'username'  => $username,
            'password'  => $password,
            'total'     => (string)$plan_volume_gb,
            'date'      => (string)$days,
            'limitip'   => '10',        // مهم‌ترین فیلد برای مولتی یوزر (۱۰ نفره)
            'add'       => '1',
            'submit'    => 'ذخیره'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $add_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_REFERER, 'https://panil.jasemhooti2.ir/panel/usersPro.php');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: fa-IR,fa;q=0.9'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // ذخیره پاسخ برای دیباگ
        file_put_contents(__DIR__ . '/last_response.log', "HTTP Code: $http_code\nTime: " . date('Y-m-d H:i:s') . "\n\n" . $response);

        // تشخیص موفقیت
        $success_keywords = ['موفق', 'ذخیره شد', 'success', 'اضافه شد', 'ثبت شد', 'created', 'کاربر جدید'];
        $is_success = false;
        foreach ($success_keywords as $word) {
            if (stripos($response, $word) !== false) {
                $is_success = true;
                break;
            }
        }

        if ($is_success || $http_code == 302) {
            $result['success'] = true;
            $result['message'] = 'اشتراک با موفقیت ساخته شد ✅';
        } else {
            $result['message'] = "درخواست ارسال شد اما کاربر ساخته نشد (HTTP: $http_code). صفحه لاگین برگشته یا خطا وجود دارد.";
        }

    } catch (Exception $e) {
        $result['message'] = 'خطای سیستمی: ' . $e->getMessage();
    }

    return $result;
}        curl_close($ch);

        // ارسال به آدرس درست
        $add_url = 'https://panil.jasemhooti2.ir/panel/add_userPro.php?add';

        $post_data = [
            'username'   => $username,
            'password'   => $password,
            'total'      => $plan_volume_gb,
            'date'       => $days,
            'limitip'    => '10',           // حساب مولتی یوزر - ۱۰ نفره
            'multiuser'  => '10',
            'op_multi'   => '10',
            'add'        => '1',
            'submit'     => 'ذخیره'
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

        file_put_contents(__DIR__ . '/last_response.log', "HTTP Code: $http_code\n\n" . $response);

        $success_keywords = ['موفق', 'ذخیره شد', 'success', 'اضافه شد', 'ثبت شد', 'created'];
        $is_success = false;
        foreach ($success_keywords as $word) {
            if (stripos($response, $word) !== false) $is_success = true;
        }

        if ($is_success || $http_code == 302 || $http_code == 200) {
            $result['success'] = true;
            $result['message'] = 'درخواست ارسال شد ✅ (ممکن است موفق باشد - در پنل چک کن)';
        } else {
            $result['message'] = "درخواست ارسال شد اما موفقیت تأیید نشد. HTTP: $http_code";
        }

    } catch (Exception $e) {
        $result['message'] = 'خطا: ' . $e->getMessage();
    }

    if (file_exists($cookie_file)) @unlink($cookie_file);

    return $result;
}
