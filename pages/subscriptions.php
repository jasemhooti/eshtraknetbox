<?php
// subscriptions.php
requireLogin();

$db = getDB();
$stmt = $db->prepare("
    SELECT o.*, p.title as plan_title, p.volume_mb
    FROM orders o
    JOIN plans p ON o.plan_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>
<style>
.order-item {
  background: var(--card-bg);
  border: 1px solid var(--card-border);
  border-radius: 14px;
  padding: 18px;
  margin-bottom: 12px;
  overflow: hidden;
  position: relative;
}
.order-item.approved {
  border-color: rgba(80,220,120,0.35);
  background: rgba(80,220,120,0.04);
}
.order-item.rejected {
  border-color: rgba(255,80,80,0.3);
  background: rgba(255,80,80,0.03);
}
.order-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}
.order-plan { font-weight: 700; font-size: 0.95rem; }
.order-date { font-size: 0.72rem; color: var(--white-60); margin-top: 3px; }
.order-price { font-weight: 800; color: var(--gold); font-size: 0.9rem; }

.sub-credentials {
  background: rgba(212,175,55,0.08);
  border: 1px solid rgba(212,175,55,0.25);
  border-radius: 10px;
  padding: 14px;
  margin-top: 12px;
}
.cred-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px 0;
}
.cred-row + .cred-row { border-top: 1px solid rgba(255,255,255,0.05); }
.cred-label { font-size: 0.78rem; color: var(--white-60); }
.cred-value {
  font-size: 0.88rem;
  font-weight: 700;
  color: var(--gold-light);
  font-family: monospace;
  background: rgba(0,0,0,0.2);
  padding: 2px 10px;
  border-radius: 6px;
  cursor: pointer;
  user-select: all;
}

.pending-notice {
  background: rgba(255,180,0,0.08);
  border: 1px dashed rgba(255,180,0,0.3);
  border-radius: 10px;
  padding: 12px;
  margin-top: 10px;
  font-size: 0.82rem;
  color: #ffd060;
  display: flex;
  gap: 8px;
  align-items: flex-start;
}

.rejected-notice {
  background: rgba(255,80,80,0.08);
  border: 1px dashed rgba(255,80,80,0.3);
  border-radius: 10px;
  padding: 12px;
  margin-top: 10px;
  font-size: 0.82rem;
  color: #ff9090;
}

.empty-state {
  text-align: center;
  padding: 50px 20px;
}
.empty-icon { font-size: 3.5rem; display: block; margin-bottom: 16px; opacity: 0.5; }
</style>

<h1 class="page-title">اشتراک‌های من</h1>
<p class="page-sub">لیست خریدها و وضعیت آن‌ها</p>

<?php if (empty($orders)): ?>
<div class="empty-state">
  <span class="empty-icon">📦</span>
  <p style="color:var(--white-60);margin-bottom:20px">هنوز هیچ خریدی انجام نداده‌اید.</p>
  <a href="index.php?page=plans" class="btn btn-gold" style="display:inline-block;width:auto;padding:12px 28px">خرید اشتراک ←</a>
</div>

<?php else: ?>
<?php foreach ($orders as $order): ?>
<div class="order-item <?= $order['status'] ?> fade-in">
  <div class="order-header">
    <div>
      <div class="order-plan">📡 <?= htmlspecialchars($order['plan_title']) ?></div>
      <div class="order-date"><?= date('Y/m/d H:i', strtotime($order['created_at'])) ?></div>
    </div>
    <div style="text-align:left">
      <div class="order-price"><?= formatPrice($order['amount']) ?></div>
      <div style="margin-top:5px">
        <?php if ($order['status'] === 'pending'): ?>
          <span class="badge badge-pending">⏳ در صف بررسی</span>
        <?php elseif ($order['status'] === 'approved'): ?>
          <span class="badge badge-approved">✅ تایید شده</span>
        <?php else: ?>
          <span class="badge badge-rejected">❌ رد شده</span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if ($order['status'] === 'approved' && $order['subscription_user']): ?>
  <div class="sub-credentials">
    <div style="font-size:0.75rem;color:var(--gold);font-weight:600;margin-bottom:8px">🔑 اطلاعات اشتراک</div>
    <div class="cred-row">
      <span class="cred-label">نام کاربری:</span>
      <span class="cred-value" title="کلیک برای کپی" onclick="copyText(this)"><?= htmlspecialchars($order['subscription_user']) ?></span>
    </div>
    <div class="cred-row">
      <span class="cred-label">رمز عبور:</span>
      <span class="cred-value" title="کلیک برای کپی" onclick="copyText(this)"><?= htmlspecialchars($order['subscription_pass']) ?></span>
    </div>
  </div>

  <?php elseif ($order['status'] === 'pending'): ?>
  <div class="pending-notice">
    <span>🕐</span>
    <span>رسید پرداخت شما دریافت شده و در صف بررسی قرار دارد. پس از تایید توسط ادمین، اشتراک شما فعال خواهد شد.</span>
  </div>

  <?php elseif ($order['status'] === 'rejected'): ?>
  <div class="rejected-notice">
    ❌ این سفارش رد شده است.
    <?php if ($order['admin_note']): ?>
      <br>دلیل: <?= htmlspecialchars($order['admin_note']) ?>
    <?php endif; ?>
  </div>
  <?php endif; ?>

</div>
<?php endforeach; ?>
<?php endif; ?>

<script>
function copyText(el) {
  navigator.clipboard.writeText(el.textContent.trim()).then(() => {
    const orig = el.textContent;
    el.textContent = '✓ کپی شد';
    setTimeout(() => el.textContent = orig, 1500);
  });
}
</script>
