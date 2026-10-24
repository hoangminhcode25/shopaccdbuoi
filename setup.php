<?php
require __DIR__ . '/config.php';
try {
    $count = (int)db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
} catch (Throwable $e) {
    http_response_code(500);
    exit('Chưa có cơ sở dữ liệu. Hãy import install.sql trong phpMyAdmin trước.');
}
if ($count > 0) {
    header('Location: admin_login.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if (!preg_match('/^[A-Za-z0-9_.-]{4,60}$/', $username)) {
        $error = 'Tên đăng nhập phải có 4–60 ký tự chữ, số, dấu chấm, gạch ngang hoặc gạch dưới.';
    } elseif (strlen($password) < 12) {
        $error = 'Mật khẩu cần ít nhất 12 ký tự.';
    } else {
        $stmt = db()->prepare('INSERT INTO admins(username,password_hash) VALUES(?,?)');
        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)db()->lastInsertId();
        $_SESSION['admin_username'] = $username;
        header('Location: admin.php');
        exit;
    }
}
header_html('Thiết lập quản trị');
?>
<section class="auth-card"><div class="eyebrow">BƯỚC KHỞI TẠO</div><h1>Tạo tài khoản <span>quản trị</span></h1><p class="muted">Chỉ dùng một lần để tạo tài khoản admin đầu tiên.</p>
<?php if ($error): ?><div class="flash error"><?=e($error)?></div><?php endif; ?>
<form method="post" class="form">
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<label>Tên đăng nhập<input name="username" required minlength="4" maxlength="60" autocomplete="username"></label>
<label>Mật khẩu (ít nhất 12 ký tự)<input type="password" name="password" required minlength="12" autocomplete="new-password"></label>
<button class="btn primary" type="submit">Tạo quản trị viên</button>
</form><p class="small warning">Sau khi tạo xong, hãy xóa file setup.php khỏi hosting.</p></section>
<?php footer_html(); ?>
