<?php
// home.php - Landing page
?>
<style>
.hero {
  text-align: center;
  padding: 20px 0 30px;
}
.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(212,175,55,0.1);
  border: 1px solid rgba(212,175,55,0.3);
  border-radius: 50px;
  padding: 6px 16px;
  font-size: 0.78rem;
  color: var(--gold-light);
  margin-bottom: 20px;
}
.hero-title {
  font-size: 2.2rem;
  font-weight: 900;
  line-height: 1.2;
  margin-bottom: 10px;
  background: linear-gradient(135deg, #fff 0%, var(--gold-light) 50%, var(--gold) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.hero-sub {
  color: var(--white-60);
  font-size: 0.9rem;
  line-height: 1.6;
  margin-bottom: 32px;
  max-width: 320px;
  margin-left: auto;
  margin-right: auto;
}
.menu-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}
.menu-card {
  background: var(--card-bg);
  border: 1px solid var(--card-border);
  border-radius: 14px;
  padding: 20px 14px;
  text-decoration: none;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  transition: all 0.25s;
  position: relative;
  overflow: hidden;
}
.menu-card::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(123,47,255,0.05), rgba(212,175,55,0.05));
  opacity: 0;
  transition: opacity 0.25s;
}
.menu-card:hover {
  border-color: var(--gold);
  transform: translateY(-3px);
  box-shadow: 0 8px 30px rgba(212,175,55,0.15);
}
.menu-card:hover::after { opacity: 1; }
.menu-card:active { transform: scale(0.97); }

.menu-icon {
  font-size: 2rem;
  filter: drop-shadow(0 2px 8px rgba(212,175,55,0.3));
}
.menu-label {
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--white);
  text-align: center;
  line-height: 1.3;
}
.menu-card.highlight {
  background: linear-gradient(135deg, rgba(123,47,255,0.2), rgba(212,175,55,0.1));
  border-color: rgba(212,175,55,0.4);
}

.stats-row {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}
.stat-item {
  flex: 1;
  background: var(--card-bg);
  border: 1px solid var(--card-border);
  border-radius: 12px;
  padding: 14px 10px;
  text-align: center;
}
.stat-value {
  font-size: 1.2rem;
  font-weight: 800;
  color: var(--gold);
  display: block;
}
.stat-label {
  font-size: 0.7rem;
  color: var(--white-60);
  margin-top: 2px;
}
</style>

<div class="hero">
  <div class="hero-badge">⚡ سریع · امن · مطمئن</div>
  <h1 class="hero-title">NetBox<br>اینترنت نامحدود</h1>
  <p class="hero-sub">خرید اشتراک اینترنت با کیفیت بالا و سرعت فوق‌العاده. همین حالا شروع کن!</p>
</div>

<?php if (isLoggedIn()): ?>
<div class="stats-row">
  <div class="stat-item">
    <span class="stat-value">👋</span>
    <span class="stat-label">خوش آمدی<br><?= htmlspecialchars($_SESSION['user_name']) ?></span>
  </div>
  <div class="stat-item">
    <span class="stat-value">
    <?php
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status = 'approved'");
    $stmt->execute([$_SESSION['user_id']]);
    echo $stmt->fetchColumn();
    ?>
    </span>
    <span class="stat-label">اشتراک فعال</span>
  </div>
</div>
<?php endif; ?>

<div class="menu-grid">
  <a href="index.php?page=plans" class="menu-card highlight">
    <span class="menu-icon">🛒</span>
    <span class="menu-label">خرید اشتراک</span>
  </a>
  <a href="index.php?page=subscriptions" class="menu-card">
    <span class="menu-icon">📦</span>
    <span class="menu-label">اشتراک‌های خریداری شده</span>
  </a>
  <a href="index.php?page=download" class="menu-card">
    <span class="menu-icon">⬇️</span>
    <span class="menu-label">دانلود اپلیکیشن</span>
  </a>
  <a href="index.php?page=about" class="menu-card">
    <span class="menu-icon">ℹ️</span>
    <span class="menu-label">درباره ما</span>
  </a>
</div>
