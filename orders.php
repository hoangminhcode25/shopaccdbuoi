<?php
require __DIR__ . '/config.php';
$result=null;$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$code=trim((string)($_POST['code']??''));$email=trim((string)($_POST['email']??''));
 if($code===''||!filter_var($email,FILTER_VALIDATE_EMAIL))$error='Nhập mã đơn và email hợp lệ.';
 else {$s=db()->prepare('SELECT order_code,buyer_email,total_amount,status,created_at FROM orders WHERE order_code=? AND buyer_email=?');$s->execute([$code,$email]);$result=$s->fetch();if(!$result)$error='Không tìm thấy đơn phù hợp với mã đơn và email.';}
}
header_html('Tra cứu đơn hàng');
?>
<section class="auth-card"><div class="eyebrow">ORDER TRACKING</div><h1>Tra cứu <span>đơn hàng</span></h1><p class="muted">Nhập mã đơn và email đã dùng khi đặt mua.</p>
<?php if($error): ?><div class="flash error"><?=e($error)?></div><?php endif; ?>
<form method="post" class="form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label>Mã đơn hàng<input name="code" required placeholder="Ví dụ: LQ261023ABC123" value="<?=e((string)($_POST['code']??''))?>"></label><label>Email đặt hàng<input type="email" name="email" required value="<?=e((string)($_POST['email']??''))?>"></label><button class="btn primary">Tra cứu</button></form>
<?php if($result): ?><div class="result-box"><h3><?=e($result['order_code'])?></h3><p>Số tiền: <b><?=money($result['total_amount'])?></b></p><p>Trạng thái: <b><?=e(match($result['status']){'pending'=>'Chờ thanh toán','paid'=>'Đã xác nhận thanh toán','delivered'=>'Đã bàn giao','cancelled'=>'Đã hủy',default=>$result['status']})?></b></p><p class="small">Tạo lúc: <?=e($result['created_at'])?></p></div><?php endif; ?></section>
<?php footer_html(); ?>
