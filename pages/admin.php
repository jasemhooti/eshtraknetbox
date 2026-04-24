<?php
// admin.php
requireAdmin();

$db = getDB();
$error = '';
$success = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action'] ?? '';
    $order_id = (int)($_POST['order_id'] ?? 0);

    if ($action === 'approve' && $order_id) {
        $sub_user = trim($_POST['sub_user'] ?? '');
        $sub_pass = trim($_POST['sub_pass'] ?? '');

        if (empty($sub_user) || empty($sub_pass)) {
            $error = 'برای تایید باید نام کاربری و رمز اشتراک را وارد کنید.';
        } else {
            $stmt = $db->prepare("UPDATE orders SET status='approved', subscription_user=?, subscription_pass=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$sub_user, $sub_pass, $order_id]);
            $success = "سفارش #$order_id تایید و اشتراک ارسال شد.";
        }
    } elseif ($action === 'reject' && $order_id) {
        $note = trim($_POST['admin_note'] ?? '');
        $stmt = $db->prepare("UPDATE orders SET status='rejected', admin_note=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$note, $order_id]);
        $success = "سفارش #$order_id رد شد.";
    }
}

// Filter
$filter = $_GET['filter'] ?? 'pending';
$where = $filter === 'all' ? '' : "WHERE o.status = '$filter'";

$orders = $db->query("
    SELECT o.*, u.name as user_name, u.phone as user_phone, p.title as plan_title, p.volume_mb
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN plans p ON o.plan_id = p.id
    $where
    ORDER BY o.created_at DESC
")->fetchAll();

$counts = $db->query("
    SELECT status, COUNT(*) as cnt FROM orders GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<style>
.admin-tabs {
  display:flex; gap:8px; margin-bottom:20px; overflow-x:auto; padding-bottom:4px;
}
.tab-btn {
  padding: 8px 16px;
  border-radius: 50px;
  font-size: 0.8rem;
  font-weight: 600;
  text-decoration: none;
  white-space: nowrap;
  border: 1px solid var(--card-border);
  color: var(--white-60);
  background: var(--card-bg);
  transition: all 0.2s;
}
.tab-btn.active {
  background: linear-gradient(135deg, var(--purple-mid), var(--purple-bright));
  color: #fff;
  border-color: transparent;
}

.order-admin-card {
  background: var(--card-bg);
  border: 1px solid var(--card-border);
  border-radius: 14px;
  padding: 16px;
  margin-bottom: 12px;
}
.order-admin-card.pending { border-right: 3px solid #ffd060; }
.order-admin-card.approved { border-right: 3px solid #80ff90; }
.order-admin-card.rejected { border-right: 3px solid #ff9090; }

.order-meta { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px; }
.user-info { }
.user-name { font-weight:700; font-size:0.95rem; }
.user-phone { font-size:0.75rem; color:var(--white-60); margin-top:2px; direction:ltr; display:inline-block; }
.order-meta-right { text-align:left; }
.order-amount { color:var(--gold); font-weight:800; font-size:0.9rem; }
.order-date { font-size:0.7rem; color:var(--white-60); margin-top:3px; }

.receipt-thumb {
  width: 100%; max-height: 160px; object-fit: cover;
  border-radius: 8px; margin: 10px 0;
  cursor: pointer; border: 1px solid var(--card-border);
}

.action-form { margin-top: 12px; }
.action-form input {
  width:100%; padding:10px 14px;
  background: rgba(255,255,255,0.07);
  border: 1px solid rgba(212,175,55,0.25);
  border-radius: 8px; color:#fff;
  font-family:'Vazirmatn',sans-serif; font-size:0.85rem;
  margin-bottom: 8px; outline:none;
}
.action-form input:focus { border-color: var(--gold); }
.action-btns { display:flex; gap:8px; }
.action-btns button {
  flex:1; padding:10px; border-radius:8px;
  font-family:'Vazirmatn',sans-serif; font-size:0.82rem;
  font-weight:600; cursor:pointer; border:none; transition:all 0.2s;
}
.btn-approve { background: rgba(80,220,120,0.15); color:#80ff90; border:1px solid rgba(80,220,120,0.35); }
.btn-approve:hover { background: rgba(80,220,120,0.25); }
.btn-reject { background: rgba(255,80,80,0.12); color:#ff9090; border:1px solid rgba(255,80,80,0.3); }
.btn-reject:hover { background: rgba(255,80,80,0.22); }

.stats-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:20px; }
.stat-box { background:var(--card-bg); border:1px solid var(--card-border); border-radius:12px; padding:12px; text-align:center; }
.stat-n { font-size:1.4rem; font-weight:900; }
.stat-l { font-size:0.68rem; color:var(--white-60); margin-top:2px; }

/* Modal */
.modal-overlay {
  display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85);
  z-index:1000; align-items:center; justify-content:center;
}
.modal-overlay.open { display:flex; }
.modal-img { max-width:95vw; max-height:90vh; border-radius:12px; }
.modal-close {
  position:fixed; top:16px; right:16px;
  background:rgba(0,0,0,0.6); color:#fff;
  border:none; border-radius:50%; width:40px; height:40px;
  font-size:1.2rem; cursor:pointer;
}
</style>

<h1 class="page-title">پنل مدیریت</h1>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-box">
    <div class="stat-n" style="color:#ffd060"><?= $counts['pending'] ?? 0 ?></div>
    <div class="stat-l">در انتظار</div>
  </div>
  <div class="stat-box">
    <div class="stat-n" style="color:#80ff90"><?= $counts['approved'] ?? 0 ?></div>
    <div class="stat-l">تایید شده</div>
  </div>
  <div class="stat-box">
    <div class="stat-n" style="color:#ff9090"><?= $counts['rejected'] ?? 0 ?></div>
    <div class="stat-l">رد شده</div>
  </div>
</div>

<?php if ($error): ?><div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>

<!-- Tabs -->
<div class="admin-tabs">
  <a href="?page=admin&filter=pending" class="tab-btn <?= $filter==='pending'?'active':'' ?>">⏳ در انتظار (<?= $counts['pending']??0 ?>)</a>
  <a href="?page=admin&filter=approved" class="tab-btn <?= $filter==='approved'?'active':'' ?>">✅ تایید شده</a>
  <a href="?page=admin&filter=rejected" class="tab-btn <?= $filter==='rejected'?'active':'' ?>">❌ رد شده</a>
  <a href="?page=admin&filter=all" class="tab-btn <?= $filter==='all'?'active':'' ?>">📋 همه</a>
</div>

<!-- Orders -->
<?php if (empty($orders)): ?>
<div style="text-align:center;padding:40px;color:var(--white-60)">
  <span style="font-size:2.5rem;display:block;margin-bottom:12px">📭</span>
  سفارشی در این دسته وجود ندارد.
</div>
<?php else: ?>
<?php foreach ($orders as $o): ?>
<div class="order-admin-card <?= $o['status'] ?> fade-in">
  <div class="order-meta">
    <div class="user-info">
      <div class="user-name">👤 <?= htmlspecialchars($o['user_name']) ?></div>
      <div class="user-phone"><?= htmlspecialchars($o['user_phone']) ?></div>
      <div style="margin-top:4px"><span class="badge <?= 'badge-'.$o['status'] ?>">
        <?= $o['status']==='pending'?'⏳ در انتظار':($o['status']==='approved'?'✅ تایید':'❌ رد') ?>
      </span></div>
    </div>
    <div class="order-meta-right">
      <div class="order-amount"><?= formatPrice($o['amount']) ?></div>
      <div style="font-size:0.75rem;color:var(--white-60);margin-top:3px"><?= htmlspecialchars($o['plan_title']) ?></div>
      <div class="order-date">#<?= $o['id'] ?> · <?= date('Y/m/d H:i', strtotime($o['created_at'])) ?></div>
    </div>
  </div>

  <?php if ($o['receipt_image']): ?>
  <img
    src="<?= UPLOAD_URL . htmlspecialchars($o['receipt_image']) ?>"
    class="receipt-thumb"
    alt="رسید"
    onclick="openModal(this.src)"
  >
  <?php endif; ?>

  <?php if ($o['status'] === 'pending'): ?>
  <div class="action-form">
    <form method="POST">
      <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
      <input type="hidden" name="action" value="approve">
      <input type="text" name="sub_user" placeholder="نام کاربری اشتراک" required>
      <input type="text" name="sub_pass" placeholder="رمز عبور اشتراک" required>
      <div class="action-btns">
        <button type="submit" class="btn-approve">✅ تایید و ارسال اشتراک</button>
      </div>
    </form>
    <form method="POST" style="margin-top:8px">
      <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
      <input type="hidden" name="action" value="reject">
      <input type="text" name="admin_note" placeholder="دلیل رد (اختیاری)">
      <div class="action-btns">
        <button type="submit" class="btn-reject" onclick="return confirm('آیا این سفارش رد شود؟')">❌ رد کردن</button>
      </div>
    </form>
  </div>

  <?php elseif ($o['status'] === 'approved'): ?>
  <div style="background:rgba(80,220,120,0.08);border-radius:8px;padding:10px;margin-top:8px;font-size:0.82rem">
    👤 نام کاربری: <strong><?= htmlspecialchars($o['subscription_user']) ?></strong><br>
    🔑 رمز: <strong><?= htmlspecialchars($o['subscription_pass']) ?></strong>
  </div>

  <?php elseif ($o['status'] === 'rejected' && $o['admin_note']): ?>
  <div style="font-size:0.8rem;color:#ff9090;margin-top:8px">دلیل رد: <?= htmlspecialchars($o['admin_note']) ?></div>
  <?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>

<!-- Image modal -->
<div class="modal-overlay" id="modal" onclick="closeModal()">
  <button class="modal-close" onclick="closeModal()">✕</button>
  <img class="modal-img" id="modal-img" src="" alt="رسید بزرگ">
</div>

<script>
function openModal(src) {
  document.getElementById('modal-img').src = src;
  document.getElementById('modal').classList.add('open');
}
function closeModal() {
  document.getElementById('modal').classList.remove('open');
}
</script>
