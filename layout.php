<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>NetBox - سرویس اینترنت</title>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root {
  --purple-deep: #0d0020;
  --purple-dark: #1a0040;
  --purple-mid: #3d0080;
  --purple-bright: #7b2fff;
  --purple-light: #a855f7;
  --gold: #d4af37;
  --gold-light: #f0d060;
  --gold-pale: #fef3c7;
  --white: #ffffff;
  --white-60: rgba(255,255,255,0.6);
  --white-30: rgba(255,255,255,0.3);
  --white-10: rgba(255,255,255,0.1);
  --white-5: rgba(255,255,255,0.05);
  --card-bg: rgba(255,255,255,0.06);
  --card-border: rgba(212,175,55,0.25);
  --radius: 16px;
  --shadow: 0 8px 32px rgba(0,0,0,0.4);
}

* { margin:0; padding:0; box-sizing:border-box; }

body {
  font-family: 'Vazirmatn', sans-serif;
  background: var(--purple-deep);
  color: var(--white);
  min-height: 100vh;
  overflow-x: hidden;
}

/* Animated background */
body::before {
  content: '';
  position: fixed;
  inset: 0;
  background:
    radial-gradient(ellipse 80% 60% at 20% 20%, rgba(123,47,255,0.25) 0%, transparent 60%),
    radial-gradient(ellipse 60% 80% at 80% 80%, rgba(212,175,55,0.12) 0%, transparent 60%),
    radial-gradient(ellipse 100% 100% at 50% 50%, rgba(61,0,128,0.4) 0%, transparent 70%);
  pointer-events: none;
  z-index: 0;
}

.app-wrapper {
  position: relative;
  z-index: 1;
  max-width: 480px;
  margin: 0 auto;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* Header */
.app-header {
  padding: 20px 20px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid rgba(212,175,55,0.15);
  background: rgba(13,0,32,0.6);
  backdrop-filter: blur(20px);
  position: sticky;
  top: 0;
  z-index: 100;
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
}

.logo-icon {
  width: 38px;
  height: 38px;
  background: linear-gradient(135deg, var(--purple-bright), var(--gold));
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  box-shadow: 0 4px 15px rgba(123,47,255,0.4);
}

.logo-text {
  font-size: 1.4rem;
  font-weight: 900;
  background: linear-gradient(135deg, var(--gold), var(--gold-light));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: 1px;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.user-pill {
  background: var(--white-5);
  border: 1px solid var(--card-border);
  border-radius: 50px;
  padding: 6px 14px;
  font-size: 0.78rem;
  color: var(--white-60);
  display: flex;
  align-items: center;
  gap: 6px;
}

.logout-btn {
  background: rgba(255,80,80,0.1);
  border: 1px solid rgba(255,80,80,0.3);
  color: #ff8080;
  border-radius: 8px;
  padding: 6px 12px;
  font-size: 0.78rem;
  text-decoration: none;
  font-family: 'Vazirmatn', sans-serif;
  cursor: pointer;
  transition: all 0.2s;
}
.logout-btn:hover { background: rgba(255,80,80,0.2); }

.admin-btn {
  background: linear-gradient(135deg, var(--gold), var(--gold-light));
  color: var(--purple-deep);
  border-radius: 8px;
  padding: 6px 12px;
  font-size: 0.78rem;
  text-decoration: none;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

/* Main content */
.app-content {
  flex: 1;
  padding: 24px 20px;
}

/* Cards */
.card {
  background: var(--card-bg);
  border: 1px solid var(--card-border);
  border-radius: var(--radius);
  padding: 20px;
  margin-bottom: 16px;
  backdrop-filter: blur(10px);
  box-shadow: var(--shadow);
}

.card-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--gold);
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Buttons */
.btn {
  display: block;
  width: 100%;
  padding: 14px 20px;
  border-radius: 12px;
  font-family: 'Vazirmatn', sans-serif;
  font-size: 0.95rem;
  font-weight: 600;
  text-align: center;
  text-decoration: none;
  cursor: pointer;
  border: none;
  transition: all 0.25s;
  position: relative;
  overflow: hidden;
}

.btn::before {
  content: '';
  position: absolute;
  inset: 0;
  background: rgba(255,255,255,0);
  transition: background 0.2s;
}
.btn:hover::before { background: rgba(255,255,255,0.08); }
.btn:active { transform: scale(0.98); }

.btn-gold {
  background: linear-gradient(135deg, #b8860b, var(--gold), var(--gold-light), var(--gold));
  color: var(--purple-deep);
  box-shadow: 0 4px 20px rgba(212,175,55,0.35);
}

.btn-purple {
  background: linear-gradient(135deg, var(--purple-mid), var(--purple-bright));
  color: white;
  box-shadow: 0 4px 20px rgba(123,47,255,0.35);
}

.btn-outline {
  background: transparent;
  border: 1px solid var(--card-border);
  color: var(--gold);
}

.btn-danger {
  background: rgba(255,80,80,0.15);
  border: 1px solid rgba(255,80,80,0.4);
  color: #ff8080;
}

.btn + .btn { margin-top: 10px; }

/* Form elements */
.form-group { margin-bottom: 16px; }
.form-label {
  display: block;
  color: var(--gold-light);
  font-size: 0.85rem;
  font-weight: 500;
  margin-bottom: 7px;
}
.form-input {
  width: 100%;
  padding: 12px 16px;
  background: rgba(255,255,255,0.07);
  border: 1px solid rgba(212,175,55,0.25);
  border-radius: 10px;
  color: var(--white);
  font-family: 'Vazirmatn', sans-serif;
  font-size: 0.95rem;
  outline: none;
  transition: border-color 0.3s, box-shadow 0.3s;
}
.form-input:focus {
  border-color: var(--gold);
  box-shadow: 0 0 0 3px rgba(212,175,55,0.1);
}
.form-input::placeholder { color: var(--white-30); }

/* Alerts */
.alert {
  padding: 12px 16px;
  border-radius: 10px;
  font-size: 0.88rem;
  margin-bottom: 16px;
  display: flex;
  align-items: flex-start;
  gap: 8px;
}
.alert-error { background: rgba(255,80,80,0.12); border: 1px solid rgba(255,80,80,0.3); color: #ff9090; }
.alert-success { background: rgba(80,220,120,0.12); border: 1px solid rgba(80,220,120,0.3); color: #80ff90; }
.alert-info { background: rgba(123,47,255,0.12); border: 1px solid rgba(123,47,255,0.3); color: #c090ff; }
.alert-warning { background: rgba(255,180,0,0.12); border: 1px solid rgba(255,180,0,0.3); color: #ffd060; }

/* Badge */
.badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 10px;
  border-radius: 50px;
  font-size: 0.75rem;
  font-weight: 600;
}
.badge-pending { background: rgba(255,180,0,0.15); color: #ffd060; border: 1px solid rgba(255,180,0,0.3); }
.badge-approved { background: rgba(80,220,120,0.15); color: #80ff90; border: 1px solid rgba(80,220,120,0.3); }
.badge-rejected { background: rgba(255,80,80,0.15); color: #ff9090; border: 1px solid rgba(255,80,80,0.3); }

/* Page title */
.page-title {
  font-size: 1.5rem;
  font-weight: 800;
  margin-bottom: 6px;
  background: linear-gradient(135deg, var(--white), var(--gold-light));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.page-sub {
  color: var(--white-60);
  font-size: 0.85rem;
  margin-bottom: 24px;
}

/* Bottom nav */
.bottom-nav {
  position: sticky;
  bottom: 0;
  background: rgba(13,0,32,0.9);
  backdrop-filter: blur(20px);
  border-top: 1px solid rgba(212,175,55,0.15);
  padding: 10px 0 max(10px, env(safe-area-inset-bottom));
  display: flex;
  justify-content: space-around;
  z-index: 100;
}

.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  padding: 6px 10px;
  text-decoration: none;
  border-radius: 10px;
  transition: all 0.2s;
  min-width: 56px;
}
.nav-item .nav-icon { font-size: 1.3rem; transition: transform 0.2s; }
.nav-item .nav-label { font-size: 0.65rem; color: var(--white-30); font-weight: 500; white-space: nowrap; }
.nav-item.active .nav-icon { transform: scale(1.1); }
.nav-item.active .nav-label { color: var(--gold); }
.nav-item:hover .nav-icon { transform: scale(1.1); }

/* Divider */
.divider {
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(212,175,55,0.2), transparent);
  margin: 16px 0;
}

/* Loading */
.spinner {
  display: inline-block;
  width: 20px; height: 20px;
  border: 2px solid rgba(212,175,55,0.3);
  border-top-color: var(--gold);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Fade in animation */
.fade-in {
  animation: fadeIn 0.3s ease forwards;
}
@keyframes fadeIn {
  from { opacity:0; transform: translateY(10px); }
  to { opacity:1; transform: translateY(0); }
}

/* Scrollbar */
::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(212,175,55,0.3); border-radius: 4px; }
</style>
</head>
<body>
<div class="app-wrapper">

  <header class="app-header">
    <a href="index.php" class="logo">
      <div class="logo-icon">⚡</div>
      <span class="logo-text">NetBox</span>
    </a>
    <div class="header-actions">
      <?php if (isLoggedIn()): ?>
        <?php if (isAdmin()): ?>
          <a href="index.php?page=admin" class="admin-btn">🛡 پنل ادمین</a>
        <?php else: ?>
          <span class="user-pill">👤 <?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></span>
        <?php endif; ?>
        <a href="index.php?page=logout" class="logout-btn">خروج</a>
      <?php endif; ?>
    </div>
  </header>

  <main class="app-content fade-in">
    <?= $content ?>
  </main>

  <nav class="bottom-nav">
    <a href="index.php?page=home" class="nav-item <?= $page==='home'?'active':'' ?>">
      <span class="nav-icon">🏠</span>
      <span class="nav-label">خانه</span>
    </a>
    <a href="index.php?page=plans" class="nav-item <?= $page==='plans'?'active':'' ?>">
      <span class="nav-icon">🛒</span>
      <span class="nav-label">خرید</span>
    </a>
    <a href="index.php?page=subscriptions" class="nav-item <?= $page==='subscriptions'?'active':'' ?>">
      <span class="nav-icon">📦</span>
      <span class="nav-label">اشتراک‌ها</span>
    </a>
    <a href="index.php?page=download" class="nav-item <?= $page==='download'?'active':'' ?>">
      <span class="nav-icon">⬇️</span>
      <span class="nav-label">دانلود</span>
    </a>
    <a href="index.php?page=about" class="nav-item <?= $page==='about'?'active':'' ?>">
      <span class="nav-icon">ℹ️</span>
      <span class="nav-label">درباره ما</span>
    </a>
  </nav>

</div>
</body>
</html>
