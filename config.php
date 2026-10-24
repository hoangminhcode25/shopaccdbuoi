<?php
declare(strict_types=1);
session_start();

/*
 * Local XAMPP defaults. Change these values for your hosting environment.
 * Never upload real database credentials to a public repository.
 */
const DB_HOST = '127.0.0.1';
const DB_NAME = 'lienquan_store';
const DB_USER = 'root';
const DB_PASS = '';

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    return $pdo;
}
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function money(int|float|string $value): string {
    return number_format((float)$value, 0, ',', '.') . '₫';
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verify_csrf(): void {
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(403);
        exit('Yêu cầu không hợp lệ (CSRF). Hãy tải lại trang và thử lại.');
    }
}
function is_admin(): bool {
    return !empty($_SESSION['admin_id']);
}
function require_admin(): void {
    if (!is_admin()) {
        header('Location: admin_login.php');
        exit;
    }
}
function flash(string $message, string $type='info'): void {
    $_SESSION['flash'] = ['message'=>$message, 'type'=>$type];
}
function get_flash(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($f) ? $f : null;
}
function header_html(string $title): void {
    $flash = get_flash();
    echo '<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<meta name="theme-color" content="#09090d"><title>'.e($title).' | HUYẾT ẢNH STORE</title><link rel="stylesheet" href="assets/style.css"></head><body>';
    echo '<header class="topbar"><a class="brand" href="index.php"><span class="brand-mark">LQ</span><span>HUYẾT ẢNH <b>STORE</b><small>ARENA OF VALOR ACCOUNT MARKET</small></span></a><nav><a href="index.php">Cửa hàng</a><a href="orders.php">Tra cứu đơn</a><a href="admin_login.php">Quản trị</a></nav></header><main class="container">';
    if ($flash) echo '<div class="flash '.e($flash['type']).'">'.e($flash['message']).'</div>';
}
function footer_html(): void {
    echo '</main><footer class="footer"><span>HUYẾT ẢNH STORE</span><span>Website demo bán hàng số · Chuyển khoản xác nhận thủ công</span></footer></body></html>';
}
