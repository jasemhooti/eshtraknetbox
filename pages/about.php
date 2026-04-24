<?php // about.php ?>
<style>
.about-hero { text-align:center; padding: 10px 0 24px; }
.about-logo { font-size: 3.5rem; display: block; margin-bottom: 12px; }
.contact-card {
  background: var(--card-bg);
  border: 1px solid var(--card-border);
  border-radius: 14px;
  padding: 16px 18px;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 14px;
  text-decoration: none;
  transition: all 0.2s;
}
.contact-card:hover {
  border-color: var(--gold);
  background: rgba(212,175,55,0.06);
  transform: translateY(-2px);
}
.contact-icon { font-size: 1.8rem; flex-shrink: 0; }
.contact-label { font-size: 0.75rem; color: var(--white-60); }
.contact-value { font-size: 0.95rem; font-weight: 600; color: var(--white); margin-top: 2px; }
.contact-arrow { margin-right: auto; color: var(--gold-light); }
</style>

<div class="about-hero">
  <span class="about-logo">⚡</span>
  <h1 class="page-title">NetBox</h1>
  <p class="page-sub">سرویس اینترنت سریع و قابل اعتماد</p>
</div>

<div class="card" style="margin-bottom:20px">
  <p style="color:var(--white-60);font-size:0.88rem;line-height:1.8;text-align:center">
    ما در NetBox با هدف ارائه اینترنت پرسرعت، امن و مطمئن فعالیت می‌کنیم. رضایت کاربران اولویت اول ماست.
  </p>
</div>

<div class="card-title" style="color:var(--gold);font-weight:700;margin-bottom:12px">📞 راه‌های ارتباطی</div>

<a href="https://t.me/netboxx" target="_blank" class="contact-card">
  <span class="contact-icon">✈️</span>
  <div>
    <div class="contact-label">ربات تلگرام</div>
    <div class="contact-value">@netboxx</div>
  </div>
  <span class="contact-arrow">←</span>
</a>

<a href="tel:09361282020" class="contact-card">
  <span class="contact-icon">📞</span>
  <div>
    <div class="contact-label">شماره تماس</div>
    <div class="contact-value" dir="ltr">09361282020</div>
  </div>
  <span class="contact-arrow">←</span>
</a>

<a href="mailto:netbox@gmail.com" class="contact-card">
  <span class="contact-icon">📧</span>
  <div>
    <div class="contact-label">ایمیل پشتیبانی</div>
    <div class="contact-value" dir="ltr">netbox@gmail.com</div>
  </div>
  <span class="contact-arrow">←</span>
</a>
