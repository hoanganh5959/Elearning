# Nền tảng E-learning

Đây là hệ thống quản lý học tập trực tuyến (E-learning) được phát triển bằng PHP thuần, nhằm cung cấp một môi trường học tập trực tuyến hiệu quả và bảo mật.

## Cấu trúc dự án

```
├── classes/               # Các class chính của hệ thống
├── config.php             # File cấu hình chính
├── .env                   # Biến môi trường (không được commit)
├── .env-example           # Mẫu file .env
├── functions/             # Các chức năng và thư viện hỗ trợ
├── includes/              # Các file hỗ trợ và tiện ích
│   ├── auth.php           # Chức năng xác thực
│   ├── bootstrap.php      # Khởi tạo ứng dụng
│   └── helpers.php        # Các hàm tiện ích
├── public/                # Thư mục công khai cho web server
│   ├── assets/            # Tài nguyên tĩnh: CSS, JS, images
│   ├── includes/          # Các thành phần giao diện: header, footer
│   └── *.php              # Các trang web
└── vendor/                # Thư viện từ Composer
```

## Cài đặt

1. **Clone repository**:
   ```bash
   git clone https://your-repository-url.git
   cd elearning_restructured_updated
   ```

2. **Cài đặt các dependencies bằng Composer**:
   ```bash
   composer install
   ```

3. **Tạo file `.env` từ `.env-example`**:
   ```bash
   cp .env-example .env
   ```

4. **Cấu hình môi trường trong file `.env`**:
   ```env
   DB_HOST=localhost
   DB_USER=root
   DB_PASS=your_password
   DB_NAME=elearning
   ```

5. **Import database từ file SQL**:
   ```bash
   mysql -u root -p elearning < elearning.sql
   ```

6. **Cấu hình web server (Apache) để trỏ đến thư mục `public/`.**

## Tính năng

- Đăng nhập/Đăng ký người dùng
- Đăng nhập bằng Google
- Quản lý khóa học
- Quản lý bài học
- Thông tin cá nhân
- Và nhiều tính năng khác...

## Bảo mật

Dự án được cấu trúc với sự tập trung vào bảo mật:
- Tách biệt cấu trúc thư mục, với thư mục `public/` dành cho web
- Sử dụng file `.env` cho các biến môi trường nhạy cảm
- Các chức năng xác thực được tách riêng
- Bảo vệ chống CSRF, XSS

## Liên hệ

Vui lòng liên hệ [hoanganh5923@gmail.com] nếu có câu hỏi hoặc thắc mắc. 