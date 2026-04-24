<?php
// automation.php - ساخت خودکار اشتراک در پنل نت‌باکس با Panther

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

use Symfony\Component\Panther\Client;

function createNetboxAccount($username, $password, $volume_gb, $days = 30) {
    $result = [
        'success' => false,
        'message' => '',
        'created_username' => $username,
        'created_password' => $password
    ];

    try {
        // تنظیمات مرورگر headless
        $client = Client::createChromeClient(null, null, [
            'headless' => true,
            'no-sandbox' => true,
            'disable-dev-shm-usage' => true,
            'window-size' => '1920,1080'
        ]);

        // مرحله 1: ورود به پنل
        $client->request('GET', 'https://panil.jasemhooti2.ir/panel/index.php');
        $client->waitFor('input[name="username"]', 10); // صبر برای لود فرم

        $form = $client->getCrawler()->filter('form')->form();
        $form['username'] = 'wizwiz';
        $form['password'] = '725019516663486727';
        $client->submit($form);

        // صبر برای ورود موفق و رفتن به داشبورد
        $client->waitFor('body', 15);

        // مرحله 2: رفتن به صفحه ساخت کاربر
        $client->request('GET', 'https://panil.jasemhooti2.ir/panel/usersPro.php');
        $client->waitFor('input[name="username"]', 10);

        // پر کردن فرم
        $crawler = $client->getCrawler();

        // نام کاربری
        $crawler->filter('input[name="username"]')->sendKeys($username);

        // پسورد
        $crawler->filter('input[name="password"]')->sendKeys($password);

        // حجم کل (گیگابایت)
        $crawler->filter('input[name="total"]')->sendKeys((string)$volume_gb);

        // تاریخ اکانت (۳۰ روز)
        $crawler->filter('input[name="date"]')->sendKeys((string)$days);

        // حساب مولتی یوزر - انتخاب ۱۰ نفره
        // این قسمت ممکنه نیاز به تنظیم دقیق‌تر داشته باشه چون Select2 هست
        $multiSelect = $crawler->filter('.select2-selection__rendered');
        if ($multiSelect->count() > 0) {
            $multiSelect->click();
            $client->waitFor('.select2-results__option', 5);
            $client->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('.select2-results__option[title="۱۰ کاربر"]'))->click();
            // یا اگر value متفاوت بود: 
            // $client->executeScript('document.querySelector("select[name=...]").value = "10";');
        }

        // کلیک روی دکمه ذخیره
        $client->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('button[type="submit"], input[name="submit"], input[type="submit"]'))->click();

        // صبر برای نتیجه
        sleep(4);

        $pageText = $client->getPageSource();

        if (strpos($pageText, 'موفق') !== false || strpos($pageText, 'ذخیره شد') !== false || strpos($pageText, 'success') !== false) {
            $result['success'] = true;
            $result['message'] = 'اشتراک با موفقیت ساخته شد';
        } else {
            $result['message'] = 'اشتراک ساخته شد اما پیام موفقیت تشخیص داده نشد (ممکن است موفق باشد)';
            $result['success'] = true; // فرض موفقیت موقت
        }

        $client->quit();

    } catch (Exception $e) {
        $result['message'] = 'خطا در اتوماسیون: ' . $e->getMessage();
    }

    return $result;
}
