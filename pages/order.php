<?php
// order.php
requireLogin();

$db = getDB();
$error = '';
$success = '';
$order_done = false;

// Get plan
$plan_id = (int)($_GET['plan_id'] ?? $_POST['plan_id'] ?? 0);
if (!$plan_id) { header('Location: index.php?page=plans'); exit; }

$stmt = $db->prepare("SELECT * FROM plans WHERE id = ? AND is_active = 1");
$stmt->execute([$plan_id]);
$plan = $stmt->fetch();
if (!$plan) { header('Location: index.php?page=plans'); exit; }

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    if (empty($_FILES['receipt']['name'])) {
        $error = 'لطفاً تصویر رسید را آپلود کنید.';
    } else {
        $file = $_FILES['receipt'];
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        if (!in_array($file['type'], $allowed)) {
            $error = 'فقط فایل تصویری (JPG, PNG, GIF, WEBP) قبول است.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $error = 'حجم فایل نباید بیشتر از ۵ مگابایت باشد.';
        } else {
            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0755, true);
            }
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'receipt_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
            $dest = UPLOAD_DIR . $filename;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $stmt = $db->prepare("INSERT INTO orders (user_id, plan_id, amount, payment_method, receipt_image) VALUES (?,?,?,?,?)");
                $stmt->execute([$_SESSION['user_id'], $plan_id, $plan['price'], 'card', $filename]);
                $order_done = true;
            } else {
                $error = 'خطا در آپلود فایل. لطفاً مجدد امتحان کنید.';
            }
        }
    }
}

$payment_method = $_POST['payment_method'] ?? 'card';
?>
<style>
.order-summary {
  background: linear-gradient(135deg, rgba(123,47,255,0.15), rgba(212,175,55,0.08));
  border: 1px solid rgba(212,175,55,0.3);
  border-radius: 14px;
  padding: 18px;
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.method-option {
  background: var(--card-bg);
  border: 1px solid var(--card-border);
  border-radius: 12px;
  padding: 14px 16px;
  margin-bottom: 10px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 12px;
  transition: all 0.2s;
  position: relative;
}
.method-option.active {
  border-color: var(--gold);
  background: rgba(212,175,55,0.08);
}
.method-option.disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.method-radio {
  width: 20px; height: 20px;
  border-radius: 50%;
  border: 2px solid var(--white-30);
  flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s;
}
.method-option.active .method-radio {
  border-color: var(--gold);
  background: var(--gold);
}
.method-option.active .method-radio::after {
  content: '';
  width: 6px; height: 6px;
  background: var(--purple-deep);
  border-radius: 50%;
}
.disabled-tag {
  position: absolute;
  left: 12px;
  background: rgba(255,80,80,0.2);
  color: #ff9090;
  font-size: 0.65rem;
  padding: 2px 8px;
  border-radius: 20px;
  border: 1px solid rgba(255,80,80,0.3);
}
.card-box {
  background: linear-gradient(135deg, rgba(212,175,55,0.15), rgba(123,47,255,0.1));
  border: 1px solid rgba(212,175,55,0.4);
  border-radius: 14px;
  padding: 20px;
  margin-bottom: 16px;
}
.card-number {
  font-size: 1.4rem;
  font-weight: 800;
  color: var(--gold-light);
  letter-spacing: 3px;
  text-align: center;
  display: block;
  margin-bottom: 6px;
}
.card-name {
  text-align: center;
  color: var(--white-60);
  font-size: 0.85rem;
}
.amount-due {
  text-align: center;
  margin: 16px 0;
  padding: 14px;
  background: rgba(212,175,55,0.08);
  border-radius: 10px;
  border: 1px solid rgba(212,175,55,0.2);
}
.amount-label { color: var(--white-60); font-size: 0.8rem; }
.amount-value { font-size: 1.6rem; font-weight: 900; color: var(--gold); display: block; margin-top: 4px; }

.upload-area {
  border: 2px dashed rgba(212,175,55,0.3);
  border-radius: 12px;
  padding: 24px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
}
.upload-area:hover { border-color: var(--gold); background: rgba(212,175,55,0.05); }
.upload-area input[type=file] {
  position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
}
.upload-icon { font-size: 2rem; display: block; margin-bottom: 8px; }
.upload-text { color: var(--white-60); font-size: 0.85rem; }
#preview-img { max-width: 100%; border-radius: 10px; margin-top: 10px; display: none; }

.success-state { text-align: center; padding: 20px 0; }
.success-icon { font-size: 4rem; display: block; margin-bottom: 16px; }
</style>

<?php if ($order_done): ?>
<div class="success-state fade-in">
  <span class="success-icon">✅</span>
  <h2 class="page-title">رسید ارسال شد!</h2>
  <p style="color:var(--white-60);font-size:0.9rem;margin:12px 0 24px;line-height:1.7">
    رسید پرداخت شما دریافت شد و در صف بررسی قرار گرفت.<br>
    پس از تایید ادمین، اشتراک شما فعال می‌شود.
  </p>
  <a href="index.php?page=subscriptions" class="btn btn-gold">مشاهده وضعیت سفارش ←</a>
  <a href="index.php?page=home" class="btn btn-outline" style="margin-top:10px">بازگشت به خانه</a>
</div>

<?php else: ?>
<h1 class="page-title">تکمیل خرید</h1>
<p class="page-sub">پلن انتخابی و روش پرداخت</p>

<!-- Order summary -->
<div class="order-summary">
  <div>
    <div style="font-size:0.8rem;color:var(--white-60)">پلن انتخابی</div>
    <div style="font-weight:700;font-size:1rem;margin-top:3px"><?= htmlspecialchars($plan['title']) ?></div>
  </div>
  <div style="text-align:left">
    <div style="font-size:0.8rem;color:var(--white-60)">مبلغ قابل پرداخت</div>
    <div style="font-weight:900;font-size:1.1rem;color:var(--gold);margin-top:3px"><?= formatPrice($plan['price']) ?></div>
  </div>
</div>

<?php if ($error): ?>
<div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Payment methods -->
<div class="card-title" style="color:var(--gold);margin-bottom:12px;font-weight:700">💳 روش پرداخت</div>

<div class="method-option disabled" onclick="return false;">
  <div class="method-radio"></div>
  <div>
    <div style="font-weight:600">درگاه پرداخت اینترنتی</div>
    <div style="font-size:0.75rem;color:var(--white-60);margin-top:2px">پرداخت آنلاین</div>
  </div>
  <span class="disabled-tag">غیرفعال</span>
</div>

<div class="method-option active" id="card-method" onclick="selectMethod('card')">
  <div class="method-radio"></div>
  <div>
    <div style="font-weight:600">کارت به کارت</div>
    <div style="font-size:0.75rem;color:var(--white-60);margin-top:2px">واریز مستقیم</div>
  </div>
</div>

<!-- Card info -->
<div id="card-info-box">
  <div class="card-box">
    <span class="card-number">6219-8619-3479-8843</span>
    <div class="card-name">👤 به نام: حوتی</div>
  </div>
  <div class="amount-due">
    <span class="amount-label">مبلغ واریزی:</span>
    <span class="amount-value"><?= formatPrice($plan['price']) ?></span>
  </div>
</div>

<!-- Upload receipt -->
<form method="POST" enctype="multipart/form-data">
  <input type="hidden" name="plan_id" value="<?= $plan_id ?>">
  <input type="hidden" name="payment_method" value="card">

  <div class="card-title" style="color:var(--gold);margin:16px 0 10px;font-weight:700">📎 ارسال رسید پرداخت</div>
  <div class="upload-area" onclick="document.getElementById('receipt-file').click()">
    <input type="file" name="receipt" id="receipt-file" accept="image/*" onchange="previewReceipt(this)">
    <span class="upload-icon">📷</span>
    <div class="upload-text">تصویر رسید را انتخاب کنید<br><small>(JPG, PNG - حداکثر ۵ مگابایت)</small></div>
    <img id="preview-img" src="" alt="پیش‌نمایش">
  </div>

  <button type="submit" name="confirm_payment" value="1" class="btn btn-gold" style="margin-top:16px">
    ✅ پرداخت کردم - ارسال رسید
  </button>
  <a href="index.php?page=plans" class="btn btn-outline">← بازگشت</a>
</form>
<?php endif; ?>

<script>
function selectMethod(m) {
  // Only card is active
}
function previewReceipt(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      const img = document.getElementById('preview-img');
      img.src = e.target.result;
      img.style.display = 'block';
      document.querySelector('.upload-icon').style.display = 'none';
      document.querySelector('.upload-text').style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>
