<?php
require __DIR__ . '/config.php';
$code=trim((string)($_GET['code']??''));
$stmt=db()->prepare('SELECT o.*,p.title FROM orders o JOIN products p ON p.id=o.product_id WHERE o.order_code=?');
$stmt->execute([$code]); $o=$stmt->fetch();
if(!$o){http_response_code(404);header_html('Không tìm thấy đơn');echo '<div class="empty"><h1>Không tìm thấy đơn hàng</h1><a href="index.php" class="btn secondary">Về cửa hàng</a></div>';footer_html();exit;}
header_html('Đơn hàng '.$o['order_code']);
$bankName=getenv('STORE_BANK_NAME') ?: 'THAY_TEN_NGAN_HANG';
$bankAccount=getenv('STORE_BANK_ACCOUNT') ?: 'THAY_SO_TAI_KHOAN';
$bankOwner=getenv('STORE_BANK_OWNER') ?: 'THAY_TEN_CHU_TAI_KHOAN';
?>
<div class="order-panel"><div class="eyebrow">ORDER CONFIRMATION</div><h1>Đơn hàng <span><?=e($o['order_code'])?></span></h1><p class="muted">Lưu mã đơn hàng này để tra cứu trạng thái thanh toán.</p>
<div class="order-summary"><div><span>Sản phẩm</span><b><?=e($o['title'])?></b></div><div><span>Tổng tiền</span><b class="price-large"><?=money($o['total_amount'])?></b></div><div><span>Trạng thái</span><b><span class="order-status <?=e($o['status'])?>"><?=e(match($o['status']){'pending'=>'Chờ thanh toán','paid'=>'Đã xác nhận thanh toán','delivered'=>'Đã bàn giao','cancelled'=>'Đã hủy',default=>$o['status']})?></span></b></div></div>
<?php if($o['status']==='pending'): ?>
<h2>Thông tin chuyển khoản</h2><div class="bank-box"><div><span>Ngân hàng</span><b><?=e($bankName)?></b></div><div><span>Số tài khoản</span><b><?=e($bankAccount)?></b></div><div><span>Chủ tài khoản</span><b><?=e($bankOwner)?></b></div><div><span>Số tiền</span><b><?=money($o['total_amount'])?></b></div><div><span>Nội dung chuyển khoản (bắt buộc)</span><b class="transfer-note"><?=e($o['transfer_note'])?></b></div></div>
<p class="small warning">Thông tin ngân hàng đang là giá trị mẫu. Chủ cửa hàng phải cấu hình tài khoản thật trước khi công khai website. Chỉ chuyển khoản sau khi xác minh thông tin người nhận.</p>
<p class="muted">Sau khi chuyển khoản, quản trị viên sẽ kiểm tra giao dịch và cập nhật trạng thái. Website không tự xác nhận thanh toán.</p>
<?php endif; ?>
<div class="order-actions"><a class="btn secondary" href="orders.php">Tra cứu đơn hàng</a><a class="btn primary" href="index.php">Về cửa hàng</a></div></div>
<?php footer_html(); ?>
