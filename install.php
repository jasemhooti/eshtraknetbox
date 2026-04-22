<?php
session_start();

$step = isset($_POST['step']) ? (int)$_POST['step'] : (isset($_GET['step']) ? (int)$_GET['step'] : 1);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 2) {
    $db_host = trim($_POST['db_host'] ?? 'localhost');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = $_POST['db_pass'] ?? '';
    $site_url = rtrim(trim($_POST['site_url'] ?? ''), '/');
    $admin_phone = trim($_POST['admin_phone'] ?? '');

    if (!$db_name || !$db_user || !$site_url || !$admin_phone) {
        $error = 'لطفاً همه فیلدهای ضروری را پر کنید.';
        $step = 1;
    } else {
        try {
            $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$db_name`");

            // ۱. ایجاد جدول کاربران (اصلاح شده)
            $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
                `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `phone` VARCHAR(20) NOT NULL UNIQUE,
                `name` VARCHAR(100) NOT NULL,
                `is_admin` TINYINT(1) DEFAULT 0,
                `admin_password` VARCHAR(255) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // ۲. ایجاد جدول پلن‌ها (اصلاح شده)
            $pdo->exec("CREATE TABLE IF NOT EXISTS `plans` (
                `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `title` VARCHAR(100) NOT NULL,
                `volume_mb` INT(11) NOT NULL,
                `price` INT(11) NOT NULL,
                `is_active` TINYINT(1) DEFAULT 1,
                `sort_order` INT(11) DEFAULT 0
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // ۳. ایجاد جدول سفارشات (اصلاح شده با Foreign Key استاندارد)
            $pdo->exec("CREATE TABLE IF NOT EXISTS `orders` (
                `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT(11) UNSIGNED NOT NULL,
                `plan_id` INT(11) UNSIGNED NOT NULL,
                `amount` INT(11) NOT NULL,
                `payment_method` VARCHAR(50) DEFAULT 'card',
                `receipt_image` VARCHAR(255) DEFAULT NULL,
                `status` ENUM('pending','approved','rejected') DEFAULT 'pending',
                `subscription_user` VARCHAR(255) DEFAULT NULL,
                `subscription_pass` VARCHAR(255) DEFAULT NULL,
                `admin_note` TEXT DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT `fk_user_order` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk_plan_order` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // درج پلن‌های پیش‌فرض
            $pdo->exec("INSERT INTO `plans` (`title`, `volume_mb`, `price`, `sort_order`) VALUES
                ('200 مگابایت', 200, 80000, 1),
                ('300 مگابایت', 300, 120000, 2),
                ('400 مگابایت', 400, 160000, 3),
                ('500 مگابایت', 500, 200000, 4),
                ('1 گیگابایت', 1024, 400000, 5),
                ('2 گیگابایت', 2048, 800000, 6),
                ('3 گیگابایت', 3072, 1200000, 7),
                ('4 گیگابایت', 4096, 1600000, 8),
                ('5 گیگابایت', 5120, 2000000, 9)
            ");

            // درج کاربر ادمین
            $admin_pass_hash = password_hash('jasemmmm', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO `users` (`phone`, `name`, `is_admin`, `admin_password`) VALUES (?, ?, ?, ?)");
            $stmt->execute([$admin_phone, 'مدیر سیستم', 1, $admin_pass_hash]);

            // ذخیره فایل کانفیگ
            $config_content = "<?php
define('DB_HOST', '$db_host');
define('DB_NAME', '$db_name');
define('DB_USER', '$db_user');
define('DB_PASS', '$db_pass');
define('SITE_URL', '$site_url');
define('ADMIN_CARD', '6219861934798843');
define('ADMIN_CARD_NAME', 'حوتی');
define('UPLOAD_DIR', __DIR__ . '/uploads/receipts/');
define('UPLOAD_URL', SITE_URL . '/uploads/receipts/');
";
            file_put_contents(__DIR__ . '/config.php', $config_content);

            // ایجاد پوشه آپلود
            if (!is_dir(__DIR__ . '/uploads/receipts')) {
                mkdir(__DIR__ . '/uploads/receipts', 0755, true);
            }
            file_put_contents(__DIR__ . '/uploads/receipts/.htaccess', "Options -Indexes\nAddType text/plain .php .php3 .php4 .php5 .phtml");

            $step = 3;
            $success = 'نصب با موفقیت انجام شد!';

        } catch (PDOException $e) {
            $error = 'خطا در اتصال یا اجرای کوئری: ' . $e->getMessage();
            $step = 1;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>نصب NetBox</title>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Vazirmatn',sans-serif;background:linear-gradient(135deg,#1a0533 0%,#2d1060 50%,#1a0533 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.box{background:rgba(255,255,255,0.05);backdrop-filter:blur(20px);border:1px solid rgba(212,175,55,0.3);border-radius:20px;padding:40px;max-width:500px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,0.5)}
h1{color:#d4af37;font-size:1.8rem;text-align:center;margin-bottom:8px}
.sub{color:rgba(255,255,255,0.6);text-align:center;margin-bottom:30px;font-size:0.9rem}
.form-group{margin-bottom:18px}
label{display:block;color:#d4af37;margin-bottom:6px;font-size:0.9rem;font-weight:500}
input{width:100%;padding:12px 16px;background:rgba(255,255,255,0.08);border:1px solid rgba(212,175,55,0.3);border-radius:10px;color:#fff;font-family:'Vazirmatn',sans-serif;font-size:0.95rem;outline:none;transition:border-color 0.3s}
input:focus{border-color:#d4af37}
input::placeholder{color:rgba(255,255,255,0.3)}
button{width:100%;padding:14px;background:linear-gradient(135deg,#d4af37,#f0d060);border:none;border-radius:10px;color:#1a0533;font-family:'Vazirmatn',sans-serif;font-size:1rem;font-weight:700;cursor:pointer;margin-top:10px;transition:transform 0.2s,opacity 0.2s}
button:hover{transform:translateY(-2px);opacity:0.9}
.error{background:rgba(255,80,80,0.15);border:1px solid rgba(255,80,80,0.4);color:#ff8080;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:0.9rem}
.success{background:rgba(80,255,120,0.15);border:1px solid rgba(80,255,120,0.4);color:#80ff90;padding:16px;border-radius:10px;text-align:center;margin-bottom:20px}
.step-badge{background:linear-gradient(135deg,#7b2fff,#5500cc);color:#fff;border-radius:50px;padding:4px 14px;font-size:0.8rem;display:inline-block;margin-bottom:16px}
.hint{color:rgba(255,255,255,0.4);font-size:0.8rem;margin-top:4px}
.go-btn{display:inline-block;margin-top:16px;padding:12px 28px;background:linear-gradient(135deg,#d4af37,#f0d060);color:#1a0533;border-radius:10px;font-weight:700;text-decoration:none;font-family:'Vazirmatn',sans-serif}
</style>
</head>
<body>
<div class="box">
  <h1>⚡ NetBox</h1>
  <p class="sub">نصب و راه‌اندازی سیستم</p>

  <?php if ($step === 1 || ($step === 2 && $error)): ?>
  <span class="step-badge">مرحله ۱ - اطلاعات پایه</span>
  <?php if ($error): ?><div class="error">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="POST">
    <input type="hidden" name="step" value="2">
    <div class="form-group">
      <label>هاست دیتابیس</label>
      <input type="text" name="db_host" value="localhost" required>
      <div class="hint">معمولاً localhost است</div>
    </div>
    <div class="form-group">
      <label>نام دیتابیس</label>
      <input type="text" name="db_name" placeholder="مثال: netbox_db" required>
    </div>
    <div class="form-group">
      <label>نام کاربری دیتابیس</label>
      <input type="text" name="db_user" placeholder="نام کاربری MySQL" required>
    </div>
    <div class="form-group">
      <label>رمز دیتابیس</label>
      <input type="password" name="db_pass" placeholder="رمز MySQL (خالی اگر ندارد)">
    </div>
    <div class="form-group">
      <label>آدرس سایت</label>
      <input type="text" name="site_url" value="<?= 'http://' . $_SERVER['HTTP_HOST'] . str_replace('/install.php', '', $_SERVER['REQUEST_URI']) ?>" required>
      <div class="hint">بدون / در انتها</div>
    </div>
    <div class="form-group">
      <label>شماره تلفن ادمین</label>
      <input type="text" name="admin_phone" placeholder="09xxxxxxxxx" required>
      <div class="hint">با این شماره وارد پنل ادمین می‌شوید (رمز: jasemmmm)</div>
    </div>
    <button type="submit">🚀 شروع نصب</button>
  </form>

  <?php elseif ($step === 3): ?>
  <div class="success">
    ✅ <strong>نصب با موفقیت انجام شد!</strong><br><br>
    سیستم آماده استفاده است.<br>
    <strong style="color:#d4af37">مهم: فایل install.php را حذف کنید!</strong>
  </div>
  <div style="text-align:center">
    <a href="index.php" class="go-btn">ورود به سایت →</a>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
