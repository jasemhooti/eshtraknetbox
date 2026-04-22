<?php
// admin_login.php
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = preg_replace('/\D/', '', trim($_POST['phone'] ?? ''));
    $pass  = $_POST['password'] ?? '';

    if ($phone && $pass) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE phone = ? AND is_admin = 1");
        $stmt->execute([$phone]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($pass, $admin['admin_password'])) {
            $_SESSION['user_id']   = $admin['id'];
            $_SESSION['user_name'] = $admin['name'];
            $_SESSION['user_phone']= $admin['phone'];
            $_SESSION['is_admin']  = true;
            header('Location: index.php?page=admin');
            exit;
        } else {
            $error = 'شماره یا رمز عبور اشتباه است.';
        }
    } else {
        $error = 'همه فیلدها را پر کنید.';
    }
}
?>
<style>
.admin-login-wrap {
  display:flex; flex-direction:column; justify-content:center;
  min-height: calc(100vh - 200px);
}
</style>

<div class="admin-login-wrap">
  <div style="text-align:center;margin-bottom:28px">
    <span style="font-size:3rem;display:block;margin-bottom:12px">🛡</span>
    <h1 class="page-title">پنل مدیریت</h1>
    <p class="page-sub">ورود اختصاصی ادمین</p>
  </div>

  <?php if ($error): ?>
  <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="card">
    <form method="POST">
      <div class="form-group">
        <label class="form-label">شماره تلفن ادمین</label>
        <input type="tel" name="phone" class="form-input" placeholder="09xxxxxxxxx" required autofocus>
      </div>
      <div class="form-group">
        <label class="form-label">رمز عبور</label>
        <input type="password" name="password" class="form-input" placeholder="رمز عبور" required>
      </div>
      <button type="submit" class="btn btn-purple" style="margin-top:6px">🛡 ورود به پنل ادمین</button>
    </form>
  </div>

  <a href="index.php" style="text-align:center;display:block;color:var(--white-30);font-size:0.8rem;text-decoration:none;margin-top:16px">
    ← بازگشت به سایت
  </a>
</div>
