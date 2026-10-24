<?php
require __DIR__ . '/config.php';
$q = trim((string)($_GET['q'] ?? ''));
$min = filter_input(INPUT_GET, 'min', FILTER_VALIDATE_INT);
$max = filter_input(INPUT_GET, 'max', FILTER_VALIDATE_INT);
$sql = "SELECT * FROM products WHERE status='available'";
$params = [];
if ($q !== '') { $sql .= ' AND (title LIKE ? OR description LIKE ?)'; $params[]='%'.$q.'%'; $params[]='%'.$q.'%'; }
if ($min !== false && $min !== null && $min >= 0) { $sql .= ' AND price >= ?'; $params[]=$min; }
if ($max !== false && $max !== null && $max >= 0) { $sql .= ' AND price <= ?'; $params[]=$max; }
$sql .= ' ORDER BY created_at DESC';
$stmt = db()->prepare($sql); $stmt->execute($params); $products = $stmt->fetchAll();
header_html('Cửa hàng tài khoản');
?>
<section class="hero">
  <div class="hero-copy"><div class="eyebrow"><span class="pulse"></span> MARKETPLACE • AOV</div>
    <h1>SỞ HỮU KHO TƯỚNG.<br><span>THỐNG TRỊ ĐẤU TRƯỜNG.</span></h1>
    <p>Khám phá bộ sưu tập tài khoản Liên Quân. Thông tin rõ ràng, mã đơn riêng và xác nhận thanh toán thủ công.</p>
    <a class="btn primary" href="#shop">Khám phá tài khoản <span>↘</span></a>
  </div>
  <div class="hero-art"><div class="orbit orbit-a"></div><div class="orbit orbit-b"></div><div class="hero-emblem">LQ<span>ARENA</span></div><div class="hero-tag">LEGENDARY<br><b>LOADOUT</b></div></div>
</section>
<section class="stats"><div><strong><?=count($products)?></strong><span>TÀI KHOẢN ĐANG BÁN</span></div><div><strong>24/7</strong><span>GỬI YÊU CẦU ĐẶT MUA</span></div><div><strong>SAFE</strong><span>XÁC NHẬN THỦ CÔNG</span></div></section>
<section id="shop" class="shop-section">
  <div class="section-heading"><div><div class="eyebrow">THE COLLECTION</div><h2>Tài khoản <span>nổi bật</span></h2></div><span class="muted"><?=count($products)?> kết quả</span></div>
  <form class="filters" method="get"><input name="q" value="<?=e($q)?>" placeholder="Tìm tài khoản..."><input name="min" type="number" min="0" value="<?=e($min === false || $min === null ? '' : (string)$min)?>" placeholder="Giá từ (đ)"><input name="max" type="number" min="0" value="<?=e($max === false || $max === null ? '' : (string)$max)?>" placeholder="Giá đến (đ)"><button class="btn secondary">Lọc tài khoản</button></form>
  <div class="product-grid">
  <?php foreach ($products as $p): ?>
    <article class="product-card">
      <div class="product-image">
        <?php if (!empty($p['image_url'])): ?><img src="<?=e($p['image_url'])?>" alt="<?=e($p['title'])?>" loading="lazy"><?php else: ?><div class="placeholder-art"><span>ARENA OF VALOR</span><b><?=e(str_pad((string)$p['id'], 3, '0', STR_PAD_LEFT))?></b><small>ACCOUNT COLLECTION</small></div><?php endif; ?>
        <span class="status-pill">● ĐANG BÁN</span>
      </div>
      <div class="product-body"><div class="product-id">ACCOUNT #<?=e((string)$p['id'])?></div><h3><?=e($p['title'])?></h3>
        <div class="specs"><span>♟ <?=e((string)$p['heroes_count'])?> tướng</span><span>✦ <?=e((string)$p['skins_count'])?> skin</span><span>◆ <?=e($p['rank_name'])?></span></div>
        <div class="product-bottom"><strong><?=money($p['price'])?></strong><a class="btn primary small-btn" href="product.php?id=<?=(int)$p['id']?>">Chi tiết ↗</a></div>
      </div>
    </article>
  <?php endforeach; ?>
  </div>
  <?php if (!$products): ?><div class="empty"><h3>Chưa tìm thấy tài khoản</h3><p>Thử thay đổi từ khóa hoặc mức giá.</p><a href="index.php" class="btn secondary">Xóa bộ lọc</a></div><?php endif; ?>
</section>
<section class="notice"><b>✦ QUY TRÌNH MINH BẠCH</b><p>Đặt hàng → chuyển khoản đúng nội dung → quản trị viên kiểm tra giao dịch → xác nhận đơn và liên hệ bàn giao. Không gửi mật khẩu ngân hàng hoặc mã OTP cho bất kỳ ai.</p></section>
<?php footer_html(); ?>
