# HUYẾT ẢNH STORE — PHP + MySQL

Website demo cửa hàng tài khoản game với giao diện đen–đỏ neon, quản lý sản phẩm, đặt hàng, tra cứu đơn và quản trị viên xác nhận chuyển khoản thủ công.

## 1. Cài trên Windows bằng XAMPP

1. Cài XAMPP, mở **XAMPP Control Panel**, bấm **Start** ở Apache và MySQL.
2. Giải nén thư mục `lienquan_store` vào `C:\xampp\htdocs\lienquan_store`.
3. Mở `http://localhost/phpmyadmin`.
4. Chọn tab **Import**, chọn file `install.sql` trong thư mục dự án rồi bấm **Import**.
5. Mở `config.php` và kiểm tra DB_HOST, DB_NAME, DB_USER, DB_PASS. Mặc định phù hợp XAMPP cài mới: `127.0.0.1`, `lienquan_store`, `root`, mật khẩu rỗng.
6. Mở `http://localhost/lienquan_store/setup.php`. Tạo tài khoản quản trị với mật khẩu dài ít nhất 12 ký tự.
7. **Xóa `setup.php` ngay sau khi tạo admin thành công.**
8. Mở `http://localhost/lienquan_store/` để xem cửa hàng. Đăng nhập quản trị ở `http://localhost/lienquan_store/admin_login.php`.

## 2. Cấu hình thông tin ngân hàng

Mở `order.php`. Các giá trị mặc định là placeholder, không phải tài khoản ngân hàng thật:
- `STORE_BANK_NAME`
- `STORE_BANK_ACCOUNT`
- `STORE_BANK_OWNER`

Bạn có thể sửa trực tiếp ba giá trị mặc định trong `order.php`, hoặc cấu hình biến môi trường PHP tương ứng trên máy chủ. Không công khai website trước khi điền và kiểm tra thông tin chính xác.

## 3. Quy trình đặt hàng

- Khách mở sản phẩm và điền tên, email, số điện thoại.
- Hệ thống tạo mã đơn, lưu vào MySQL và chuyển sản phẩm sang `reserved` để tránh hai khách đặt cùng một tài khoản.
- Trang đơn hàng hiển thị nội dung chuyển khoản dạng `LQ <mã đơn>`.
- Chủ shop tự kiểm tra giao dịch trong ứng dụng ngân hàng và đổi trạng thái đơn trong trang admin.
- Khi xác nhận đã nhận tiền, sản phẩm chuyển sang trạng thái `sold`. Nếu hủy đơn, sản phẩm được trả lại trạng thái `available`.

## 4. Những điểm cần hoàn thiện trước khi đưa lên mạng

- Bật HTTPS; đổi mật khẩu database và dùng tài khoản DB riêng, không dùng `root`.
- Chỉ đăng bán tài khoản có quyền chuyển nhượng; kiểm tra điều khoản game và quy định pháp luật áp dụng.
- Sao lưu MySQL định kỳ; cấu hình log, giới hạn đăng nhập và thông báo đơn hàng.
- Thêm trang chính sách, điều khoản mua bán, hoàn tiền, quyền riêng tư và thông tin liên hệ.
- Ảnh sản phẩm: nhập URL HTTPS trong trang quản trị. Không dùng URL ảnh không rõ nguồn.
- Đây là bản nền khởi đầu, chưa tích hợp cổng thanh toán tự động, email/SMS hoặc tự động bàn giao thông tin tài khoản.

## 5. Ghi chú bảo mật

Có sử dụng PDO prepared statements, `password_hash`/`password_verify`, CSRF token, `htmlspecialchars`, kiểm tra dữ liệu đầu vào và session ID rotation sau đăng nhập. Khi triển khai công khai, nên bổ sung rate limit đăng nhập, cookie `Secure`/`HttpOnly`/`SameSite`, CSP và quy trình phục hồi mật khẩu.
