<?php
// login.php - سیستم ثبت‌نام و ورود
$error = '';
$success = '';
$mode = $_GET['mode'] ?? 'login'; // login | register

$db = getDB();

// --- ثبت‌نام ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'register') {
    $phone = preg_replace('/\D/', '', trim($_POST['phone'] ?? ''));
    $name  = trim($_POST['name'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if (strlen($phone) < 10) {
        $error = 'شماره تلفن معتبر وارد کنید (مثال: 09123456789)';
        $mode = 'register';
    } elseif (empty($name)) {
        $error = 'نام خود را وارد کنید.';
        $mode = 'register';
    } elseif (strlen($pass) < 6) {
        $error = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
        $mode = 'register';
    } elseif ($pass !== $pass2) {
        $error = 'رمز عبور و تکرار آن یکسان نیستند.';
        $mode = 'register';
    } else {
        $stmt = $db->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            $error = 'این شماره تلفن قبلاً ثبت‌نام کرده است. لطفاً وارد شوید.';
            $mode = 'register';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (phone, name, password) VALUES (?, ?, ?)");
            $stmt->execute([$phone, $name, $hash]);
            $success = 'ثبت‌نام با موفقیت انجام شد! اکنون وارد شوید.';
            $mode = 'login';
        }
    }
}

// --- ورود ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'login') {
    $phone = preg_replace('/\D/', '', trim($_POST['phone'] ?? ''));
    $pass  = $_POST['password'] ?? '';

    if (!$phone || !$pass) {
        $error = 'شماره تلفن و رمز عبور را وارد کنید.';
    } else {
        $stmt = $db->prepare("SELECT * FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'این شماره تلفن ثبت‌نام نکرده است. ابتدا ثبت‌نام کنید.';
        } elseif ($user['is_admin']) {
            $error = 'برای ورود به پنل ادمین از صفحه مخصوص استفاده کنید.';
        } elseif (empty($user['password']) || !password_verify($pass, $user['password'])) {
            $error = 'رمز عبور اشتباه است.';
        } else {
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_phone'] = $user['phone'];
            $_SESSION['is_admin']   = false;
            $redirect = $_GET['redirect'] ?? 'home';
            header('Location: index.php?page=' . urlencode($redirect));
            exit;
        }
    }
}
?>
<style>
.auth-wrap {
  display: flex;
  flex-direction: column;
  justify-content: center;
  min-height: calc(100vh - 200px);
}
.auth-header {
  text-align: center;
  margin-bottom: 28px;
}
.auth-icon {
  font-size: 3rem;
  display: block;
  margin-bottom: 12px;
  filter: drop-shadow(0 4px 15px rgba(212,175,55,0.4));
}
.toggle-tabs {
  display: flex;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(212,175,55,0.2);
  border-radius: 12px;
  padding: 4px;
  margin-bottom: 22px;
}
.toggle-tab {
  flex: 1;
  padding: 10px;
  text-align: center;
  border-radius: 9px;
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  color: var(--white-60);
  transition: all 0.25s;
}
.toggle-tab.active {
  background: linear-gradient(135deg, var(--purple-mid), var(--purple-bright));
  color: #fff;
  box-shadow: 0 3px 12px rgba(123,47,255,0.35);
}
.pass-wrap { position: relative; }
.pass-wrap .form-input { padding-left: 44px; }
.eye-btn {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: var(--white-60);
  cursor: pointer;
  font-size: 1.1rem;
  padding: 0;
  line-height: 1;
}
.hint-text {
  font-size: 0.75rem;
  color: var(--white-30);
  margin-top: 5px;
}
</style>

<div class="auth-wrap">
  <div class="auth-header">
    <span class="auth-icon"><?= $mode === 'register' ? '📝' : '🔐' ?></span>
    <h1 class="page-title"><?= $mode === 'register' ? 'ثبت‌نام' : 'ورود به حساب' ?></h1>
    <p class="page-sub"><?= $mode === 'register' ? 'یک حساب کاربری بسازید' : 'با شماره و رمز وارد شوید' ?></p>
  </div>

  <div class="toggle-tabs">
    <a href="?page=login&mode=login" class="toggle-tab <?= $mode === 'login' ? 'active' : '' ?>">🔑 ورود</a>
    <a href="?page=login&mode=register" class="toggle-tab <?= $mode === 'register' ? 'active' : '' ?>">📝 ثبت‌نام</a>
  </div>

  <?php if ($error): ?>
  <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($success): ?>
  <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <?php if ($mode === 'register'): ?>
  <div class="card">
    <form method="POST" action="?page=login&mode=register">
      <input type="hidden" name="form_type" value="register">
      <div class="form-group">
        <label class="form-label">شماره تلفن</label>
        <input type="tel" name="phone" class="form-input" placeholder="09xxxxxxxxx"
               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required autofocus>
        <div class="hint-text">برای ورودهای بعدی از این شماره استفاده می‌شود</div>
      </div>
      <div class="form-group">
        <label class="form-label">نام و نام خانوادگی</label>
        <input type="text" name="name" class="form-input" placeholder="نام کامل شما"
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">رمز عبور</label>
        <div class="pass-wrap">
          <input type="password" name="password" id="reg-pass" class="form-input" placeholder="حداقل ۶ کاراکتر" required>
          <button type="button" class="eye-btn" onclick="togglePass('reg-pass',this)">👁</button>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">تکرار رمز عبور</label>
        <div class="pass-wrap">
          <input type="password" name="password2" id="reg-pass2" class="form-input" placeholder="رمز عبور را مجدد وارد کنید" required>
          <button type="button" class="eye-btn" onclick="togglePass('reg-pass2',this)">👁</button>
        </div>
      </div>
      <button type="submit" class="btn btn-gold" style="margin-top:6px">📝 ثبت‌نام</button>
    </form>
  </div>
  <p style="text-align:center;color:var(--white-30);font-size:0.8rem;margin-top:14px">
    قبلاً ثبت‌نام کرده‌اید؟
    <a href="?page=login&mode=login" style="color:var(--gold-light);text-decoration:none">وارد شوید ←</a>
  </p>

  <?php else: ?>
  <div class="card">
    <form method="POST" action="?page=login&mode=login<?= isset($_GET['redirect']) ? '&redirect='.urlencode($_GET['redirect']) : '' ?>">
      <input type="hidden" name="form_type" value="login">
      <div class="form-group">
        <label class="form-label">شماره تلفن</label>
        <input type="tel" name="phone" class="form-input" placeholder="09xxxxxxxxx"
               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required autofocus>
      </div>
      <div class="form-group">
        <label class="form-label">رمز عبور</label>
        <div class="pass-wrap">
          <input type="password" name="password" id="login-pass" class="form-input" placeholder="رمز عبور خود را وارد کنید" required>
          <button type="button" class="eye-btn" onclick="togglePass('login-pass',this)">👁</button>
        </div>
      </div>
      <button type="submit" class="btn btn-gold" style="margin-top:6px">🔑 ورود به حساب</button>
    </form>
  </div>
  <p style="text-align:center;color:var(--white-30);font-size:0.8rem;margin-top:14px">
    حساب ندارید؟
    <a href="?page=login&mode=register" style="color:var(--gold-light);text-decoration:none">ثبت‌نام کنید ←</a>
  </p>
  <?php endif; ?>

  <div class="divider" style="margin:20px 0;"></div>
  <a href="?page=admin_login" style="text-align:center;display:block;color:var(--white-30);font-size:0.8rem;text-decoration:none">
    🛡 ورود به پنل مدیریت
  </a>
</div>

<script>
function togglePass(id, btn) {
  const input = document.getElementById(id);
  if (input.type === 'password') { input.type = 'text'; btn.textContent = '🙈'; }
  else { input.type = 'password'; btn.textContent = '👁'; }
}
</script>
