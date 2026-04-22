<?php
// plans.php
requireLogin();

$db = getDB();
$plans = $db->query("SELECT * FROM plans WHERE is_active=1 ORDER BY sort_order")->fetchAll();
?>
<style>
.plan-card {
  background: var(--card-bg);
  border: 1px solid var(--card-border);
  border-radius: 14px;
  padding: 16px 18px;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  text-decoration: none;
  transition: all 0.25s;
  cursor: pointer;
  position: relative;
  overflow: hidden;
}
.plan-card:hover, .plan-card.selected {
  border-color: var(--gold);
  background: rgba(212,175,55,0.08);
  box-shadow: 0 4px 20px rgba(212,175,55,0.15);
}
.plan-card.popular {
  border-color: rgba(123,47,255,0.5);
}
.popular-tag {
  position: absolute;
  top: 0;
  left: 0;
  background: linear-gradient(135deg, var(--purple-bright), var(--purple-light));
  font-size: 0.65rem;
  padding: 2px 10px;
  border-radius: 0 0 8px 0;
  color: #fff;
  font-weight: 600;
}
.plan-info { display: flex; align-items: center; gap: 12px; }
.plan-icon {
  width: 42px; height: 42px;
  background: linear-gradient(135deg, rgba(123,47,255,0.2), rgba(212,175,55,0.1));
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.3rem;
}
.plan-name { font-size: 1rem; font-weight: 700; color: var(--white); }
.plan-size { font-size: 0.75rem; color: var(--white-60); margin-top: 2px; }
.plan-price {
  font-size: 1rem;
  font-weight: 800;
  color: var(--gold);
  text-align: left;
}
.plan-price-sub { font-size: 0.7rem; color: var(--white-30); text-align: left; margin-top: 2px; }
.plan-radio {
  width: 18px; height: 18px;
  border-radius: 50%;
  border: 2px solid var(--white-30);
  margin-right: 4px;
  flex-shrink: 0;
  transition: all 0.2s;
  display: flex; align-items: center; justify-content: center;
}
.plan-card.selected .plan-radio {
  border-color: var(--gold);
  background: var(--gold);
}
.plan-card.selected .plan-radio::after {
  content: '✓';
  font-size: 0.65rem;
  color: var(--purple-deep);
  font-weight: 900;
}
</style>

<h1 class="page-title">خرید اشتراک</h1>
<p class="page-sub">پلن مناسب خود را انتخاب کنید</p>

<form action="index.php?page=order" method="GET">
  <input type="hidden" name="page" value="order">
  <?php foreach ($plans as $i => $plan): ?>
  <label class="plan-card <?= $i === 4 ? 'popular' : '' ?>" onclick="selectPlan(this, <?= $plan['id'] ?>)">
    <?php if ($i === 4): ?><span class="popular-tag">⭐ پرطرفدار</span><?php endif; ?>
    <div class="plan-info">
      <div class="plan-radio" id="radio-<?= $plan['id'] ?>"></div>
      <div class="plan-icon">
        <?= $plan['volume_mb'] >= 1024 ? '🔥' : '📡' ?>
      </div>
      <div>
        <div class="plan-name"><?= htmlspecialchars($plan['title']) ?></div>
        <div class="plan-size">حجم: <?= formatVolume($plan['volume_mb']) ?></div>
      </div>
    </div>
    <div>
      <div class="plan-price"><?= formatPrice($plan['price']) ?></div>
      <div class="plan-price-sub">یک‌بار</div>
    </div>
  </label>
  <?php endforeach; ?>

  <div style="margin-top: 20px;">
    <input type="hidden" name="plan_id" id="selected_plan_id" value="">
    <button type="submit" class="btn btn-gold" onclick="return checkSelect()">
      ادامه و پرداخت ←
    </button>
  </div>
</form>

<script>
function selectPlan(el, id) {
  document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById('selected_plan_id').value = id;
}
function checkSelect() {
  if (!document.getElementById('selected_plan_id').value) {
    alert('لطفاً یک پلن انتخاب کنید');
    return false;
  }
  return true;
}
</script>
