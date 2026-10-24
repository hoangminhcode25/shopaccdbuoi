<?php
require __DIR__ . '/config.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$stmt = db()->prepare('SELECT * FROM products WHERE id=? AND status=?');
$stmt->execute([$id ?: 0, 'available']); $p = $stmt->fetch();
if (!$p) { http_response_code(404); header_html('Không tìm thấy'); echo '<div class="empty"><h1>Tài khoản không tồn tại hoặc đã bán</h1><a class="btn secondary" href="index.php">Quay lại cửa hàng</a></div>'; footer_html(); exit; }
$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $name=trim((string)($_POST['buyer_name']??''));
    $email=trim((string)($_POST['buyer_email']??''));
    $phone=trim((string)($_POST['buyer_phone']??''));
    if (mb_strlen($name)<2 || mb_strlen($name)>100) $errors[]='Hãy nhập họ tên hợp lệ.';
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Email không hợp lệ.';
    if (!preg_match('/^[0-9+\s().-]{9,20}$/',$phone)) $errors[]='Số điện thoại không hợp lệ.';
    if (!$errors) {
        try {
            db()->beginTransaction();
            $lock=db()->prepare("SELECT * FROM products WHERE id=? AND status='available' FOR UPDATE");
            $lock->execute([(int)$p['id']]); $fresh=$lock->fetch();
            if (!$fresh) throw new RuntimeException('Tài khoản vừa được đặt mua. Vui lòng chọn tài khoản khác.');
            $code='LQ'.date('ymd').strtoupper(bin2hex(random_bytes(3)));
            $note='LQ '.$code;
            $ins=db()->prepare("INSERT INTO orders(order_code,product_id,buyer_name,buyer_email,buyer_phone,total_amount,transfer_note) VALUES(?,?,?,?,?,?,?)");
            $ins->execute([$code,(int)$fresh['id'],$name,$email,$phone,(int)$fresh['price'],$note]);
            $up=db()->prepare("UPDATE products SET status='reserved' WHERE id=? AND status='available'");
            $up->execute([(int)$fresh['id']]);
            if ($up->rowCount()!==1) throw new RuntimeException('Không thể giữ tài khoản này.');
            db()->commit();
            header('Location: order.php?code='.urlencode($code)); exit;
        } catch (Throwable $ex) {
            if (db()->inTransaction()) db()->rollBack();
            $errors[]=$ex instanceof RuntimeException ? $ex->getMessage() : 'Không thể tạo đơn hàng lúc này. Vui lòng thử lại.';
        }
    }
}
header_html('Chi tiết tài khoản');
?>
<div class="breadcrumbs"><a href="index.php">Cửa hàng</a> / Chi tiết tài khoản</div>
<div class="detail-layout"><section class="detail-art"><?php if($p['image_url']): ?><img src="<?=e($p['image_url'])?>" alt="<?=e($p['title'])?>"><?php else: ?><div class="placeholder-art large"><span>ARENA OF VALOR</span><b><?=e(str_pad((string)$p['id'],3,'0',STR_PAD_LEFT))?></b><small>ACCOUNT COLLECTION</small></div><?php endif; ?></section>
<section class="detail-info"><div class="eyebrow">ACCOUNT #<?=e((string)$p['id'])?> • AVAILABLE</div><h1><?=e($p['title'])?></h1><div class="price-large"><?=money($p['price'])?></div><div class="specs detail-specs"><span>♟ <?=e((string)$p['heroes_count'])?> tướng</span><span>✦ <?=e((string)$p['skins_count'])?> skin</span><span>◆ <?=e($p['rank_name'])?></span></div><p class="description"><?=nl2br(e($p['description']))?></p>
<h2>Đặt mua tài khoản</h2><p class="muted">Sau khi gửi yêu cầu, tài khoản được giữ tạm thời và đơn chuyển sang trạng thái chờ thanh toán.</p>
<?php foreach($errors as $err): ?><div class="flash error"><?=e($err)?></div><?php endforeach; ?>
<form method="post" class="form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<label>Họ và tên<input name="buyer_name" required maxlength="100" value="<?=e((string)($_POST['buyer_name']??''))?>"></label>
<label>Email liên hệ<input type="email" name="buyer_email" required maxlength="190" value="<?=e((string)($_POST['buyer_email']??''))?>"></label>
<label>Số điện thoại<input name="buyer_phone" required maxlength="20" value="<?=e((string)($_POST['buyer_phone']??''))?>"></label>
<button class="btn primary full" type="submit">Tạo đơn hàng · <?=money($p['price'])?></button></form><p class="small">Không chuyển khoản cho đến khi bạn xem đúng thông tin đơn hàng và ngân hàng.</p></section></div>
<?php footer_html(); ?>
