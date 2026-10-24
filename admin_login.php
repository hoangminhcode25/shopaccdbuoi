<?php
require __DIR__ . '/config.php';
if(is_admin()){header('Location: admin.php');exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 $username=trim((string)($_POST['username']??''));$password=(string)($_POST['password']??'');
 $s=db()->prepare('SELECT id,username,password_hash FROM admins WHERE username=?');$s->execute([$username]);$a=$s->fetch();
 if($a && password_verify($password,$a['password_hash'])){
  session_regenerate_id(true);$_SESSION['admin_id']=(int)$a['id'];$_SESSION['admin_username']=$a['username'];header('Location: admin.php');exit;
 }
 $error='Tên đăng nhập hoặc mật khẩu không đúng.';
}
header_html('Đăng nhập quản trị');
?>
<section class="auth-card"><div class="eyebrow">RESTRICTED ACCESS</div><h1>Admin <span>login</span></h1><p class="muted">Chỉ dành cho người quản lý cửa hàng.</p><?php if($error):?><div class="flash error"><?=e($error)?></div><?php endif;?>
<form method="post" class="form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label>Tên đăng nhập<input name="username" required autocomplete="username"></label><label>Mật khẩu<input type="password" name="password" required autocomplete="current-password"></label><button class="btn primary">Đăng nhập</button></form><p class="small">Lần đầu cài đặt? Mở <a href="setup.php">trang thiết lập quản trị</a>.</p></section>
<?php footer_html(); ?>
