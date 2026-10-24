<?php
require __DIR__ . '/config.php';require_admin();
$pdo=db();
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$action=(string)($_POST['action']??'');
 try {
  if($action==='save_product'){
   $id=(int)($_POST['id']??0);$title=trim((string)($_POST['title']??''));$desc=trim((string)($_POST['description']??''));$heroes=max(0,(int)($_POST['heroes_count']??0));$skins=max(0,(int)($_POST['skins_count']??0));$rank=trim((string)($_POST['rank_name']??''));$price=(int)($_POST['price']??0);$img=trim((string)($_POST['image_url']??''));
   if($title===''||$price<1||mb_strlen($title)>180)throw new RuntimeException('Tên sản phẩm và giá bán hợp lệ là bắt buộc.');
   if($img!==''&&!filter_var($img,FILTER_VALIDATE_URL))throw new RuntimeException('URL hình ảnh không hợp lệ.');
   if($id){$s=$pdo->prepare("UPDATE products SET title=?,description=?,heroes_count=?,skins_count=?,rank_name=?,price=?,image_url=? WHERE id=? AND status<>'sold'");$s->execute([$title,$desc,$heroes,$skins,$rank,$price,$img?:null,$id]);}
   else{$s=$pdo->prepare("INSERT INTO products(title,description,heroes_count,skins_count,rank_name,price,image_url) VALUES(?,?,?,?,?,?,?)");$s->execute([$title,$desc,$heroes,$skins,$rank,$price,$img?:null]);}
   flash('Đã lưu sản phẩm.','success');
  } elseif($action==='order_status'){
   $id=(int)($_POST['id']??0);$status=(string)($_POST['status']??'');
   if(!in_array($status,['pending','paid','delivered','cancelled'],true))throw new RuntimeException('Trạng thái không hợp lệ.');
   $pdo->beginTransaction();$s=$pdo->prepare('SELECT product_id,status FROM orders WHERE id=? FOR UPDATE');$s->execute([$id]);$o=$s->fetch();if(!$o)throw new RuntimeException('Không tìm thấy đơn hàng.');
   if($o['status']==='delivered' && $status!=='delivered')throw new RuntimeException('Đơn đã bàn giao không thể đổi trạng thái từ trang này.');
   $u=$pdo->prepare('UPDATE orders SET status=? WHERE id=?');$u->execute([$status,$id]);
   if($status==='cancelled' && $o['status']!=='cancelled'){$r=$pdo->prepare("UPDATE products SET status='available' WHERE id=? AND status='reserved'");$r->execute([(int)$o['product_id']]);}
   if($status==='paid' || $status==='delivered'){$r=$pdo->prepare("UPDATE products SET status='sold' WHERE id=? AND status='reserved'");$r->execute([(int)$o['product_id']]);}
   $pdo->commit();flash('Đã cập nhật trạng thái đơn hàng.','success');
  } elseif($action==='delete_product'){
   $id=(int)($_POST['id']??0);$s=$pdo->prepare("DELETE FROM products WHERE id=? AND status='available' AND id NOT IN (SELECT product_id FROM orders)");$s->execute([$id]);flash($s->rowCount()?'Đã xóa sản phẩm.':'Không thể xóa sản phẩm đã đặt mua hoặc đã bán.',$s->rowCount()?'success':'error');
  }
 } catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();flash($e instanceof RuntimeException?$e->getMessage():'Có lỗi khi lưu dữ liệu.','error');}
 header('Location: admin.php');exit;
}
$products=$pdo->query('SELECT * FROM products ORDER BY created_at DESC')->fetchAll();
$orders=$pdo->query('SELECT o.*,p.title FROM orders o JOIN products p ON p.id=o.product_id ORDER BY o.created_at DESC LIMIT 100')->fetchAll();
$edit=null;if(isset($_GET['edit'])){$s=$pdo->prepare('SELECT * FROM products WHERE id=?');$s->execute([(int)$_GET['edit']]);$edit=$s->fetch();}
$stats=$pdo->query("SELECT COUNT(*) total, SUM(status='available') available, SUM(status='reserved') reserved, SUM(status='sold') sold FROM products")->fetch();
header_html('Quản trị cửa hàng');
?>
<div class="admin-heading"><div><div class="eyebrow">CONTROL CENTER</div><h1>Xin chào, <span><?=e((string)($_SESSION['admin_username']??'Admin'))?></span></h1><p class="muted">Quản lý sản phẩm và xác nhận thanh toán.</p></div><a class="btn secondary" href="logout.php">Đăng xuất</a></div>
<div class="admin-stats"><div><span>TỔNG SẢN PHẨM</span><b><?=e((string)$stats['total'])?></b></div><div><span>ĐANG BÁN</span><b><?=e((string)$stats['available'])?></b></div><div><span>ĐANG GIỮ</span><b><?=e((string)$stats['reserved'])?></b></div><div><span>ĐÃ BÁN</span><b><?=e((string)$stats['sold'])?></b></div></div>
<section class="admin-section"><div class="section-heading"><div><div class="eyebrow">INVENTORY</div><h2><?= $edit?'Sửa':'Thêm' ?> <span>sản phẩm</span></h2></div></div>
<form method="post" class="admin-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save_product"><input type="hidden" name="id" value="<?=e((string)($edit['id']??0))?>">
<label>Tên tài khoản<input name="title" required maxlength="180" value="<?=e($edit['title']??'')?>"></label><label>Giá (VNĐ)<input type="number" name="price" required min="1" value="<?=e((string)($edit['price']??''))?>"></label>
<label>Số tướng<input type="number" name="heroes_count" min="0" value="<?=e((string)($edit['heroes_count']??0))?>"></label><label>Số skin<input type="number" name="skins_count" min="0" value="<?=e((string)($edit['skins_count']??0))?>"></label>
<label>Rank<input name="rank_name" maxlength="80" value="<?=e($edit['rank_name']??'Cao Thủ')?>"></label><label>URL ảnh (tùy chọn)<input name="image_url" type="url" value="<?=e($edit['image_url']??'')?>"></label>
<label class="span-2">Mô tả<textarea name="description" rows="3"><?=e($edit['description']??'')?></textarea></label><div class="span-2 form-actions"><button class="btn primary">Lưu sản phẩm</button><?php if($edit):?><a class="btn secondary" href="admin.php">Hủy sửa</a><?php endif;?></div></form></section>
<section class="admin-section"><div class="section-heading"><div><div class="eyebrow">INVENTORY</div><h2>Danh sách <span>sản phẩm</span></h2></div></div>
<div class="table-wrap"><table><thead><tr><th>Mã</th><th>Tên tài khoản</th><th>Giá</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody><?php foreach($products as $p):?><tr><td>#<?=e((string)$p['id'])?></td><td><?=e($p['title'])?></td><td><?=money($p['price'])?></td><td><?=e(match($p['status']){'available'=>'Đang bán','reserved'=>'Đang giữ','sold'=>'Đã bán',default=>$p['status']})?></td><td class="actions"><a href="product.php?id=<?=(int)$p['id']?>" target="_blank">Xem</a><?php if($p['status']!=='sold'):?><a href="admin.php?edit=<?=(int)$p['id']?>">Sửa</a><?php endif;?> <form method="post" onsubmit="return confirm('Xóa sản phẩm này?')"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete_product"><input type="hidden" name="id" value="<?=(int)$p['id']?>"><button class="link danger">Xóa</button></form></td></tr><?php endforeach;?></tbody></table></div></section>
<section class="admin-section"><div class="section-heading"><div><div class="eyebrow">ORDER MANAGEMENT</div><h2>Đơn hàng <span>gần đây</span></h2></div></div>
<div class="table-wrap"><table><thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Sản phẩm</th><th>Tổng tiền</th><th>Trạng thái</th><th>Cập nhật</th></tr></thead><tbody><?php foreach($orders as $o):?><tr><td><b><?=e($o['order_code'])?></b><br><a href="order.php?code=<?=urlencode($o['order_code'])?>" target="_blank">Xem đơn</a></td><td><?=e($o['buyer_name'])?><br><small><?=e($o['buyer_email'])?><br><?=e($o['buyer_phone'])?></small></td><td><?=e($o['title'])?></td><td><?=money($o['total_amount'])?><br><small>Nội dung: <?=e($o['transfer_note'])?></small></td><td><?=e(match($o['status']){'pending'=>'Chờ thanh toán','paid'=>'Đã thanh toán','delivered'=>'Đã bàn giao','cancelled'=>'Đã hủy',default=>$o['status']})?></td><td><form method="post" class="status-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="order_status"><input type="hidden" name="id" value="<?=(int)$o['id']?>"><select name="status"><?php foreach(['pending'=>'Chờ thanh toán','paid'=>'Đã xác nhận tiền','delivered'=>'Đã bàn giao','cancelled'=>'Hủy đơn'] as $k=>$v):?><option value="<?=e($k)?>" <?=$o['status']===$k?'selected':''?>><?=e($v)?></option><?php endforeach;?></select><button class="btn secondary mini">Lưu</button></form></td></tr><?php endforeach;?></tbody></table></div><p class="small warning">Chỉ chọn “Đã xác nhận tiền” sau khi kiểm tra giao dịch thực tế trong tài khoản ngân hàng. Trạng thái thanh toán ở đây do quản trị viên nhập thủ công.</p></section>
<?php footer_html(); ?>
