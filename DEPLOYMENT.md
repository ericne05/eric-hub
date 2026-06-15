# Hướng dẫn Deploy lên Hosting

## Chuẩn bị

### 1. Cấu trúc thư mục
```
your-domain.com/
├── .htaccess (rename từ .htaccess.production)
├── app/
├── data/
├── public/
└── index.php (optional, redirect to public/)
```

### 2. Các bước deploy

#### Bước 1: Upload files
- Upload toàn bộ thư mục lên hosting
- Đảm bảo cấu trúc thư mục giữ nguyên

#### Bước 2: Cấu hình .htaccess
```bash
# Đổi tên file
mv .htaccess.production .htaccess
```

Hoặc tạo file `.htaccess` mới với nội dung:
```apache
RewriteEngine On

# Redirect to public folder
RewriteCond %{REQUEST_URI} !^/public/
RewriteRule ^(.*)$ public/$1 [L]
```

#### Bước 3: Phân quyền thư mục
```bash
chmod 755 app/
chmod 755 data/
chmod 755 public/
chmod 644 public/index.php
```

#### Bước 4: Kiểm tra PHP version
- Yêu cầu: PHP >= 7.4
- Kiểm tra: `php -v`

### 3. Cấu hình cho subdomain/subfolder

#### Nếu deploy vào subfolder (ví dụ: domain.com/english-test/)

Sửa file `public/.htaccess`:
```apache
RewriteEngine On
RewriteBase /english-test/

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

Sửa file `app/Views/layout/header.php`:
```php
<link rel="stylesheet" href="/english-test/css/style.css">
```

### 4. Kiểm tra sau khi deploy

Truy cập:
- `https://your-domain.com/` → Trang chủ (Listening Test 1)
- `https://your-domain.com/?skill=reading&test=1` → Reading Test 1
- `https://your-domain.com/?skill=writing&test=1` → Writing Test 1

### 5. Troubleshooting

#### Lỗi 500 Internal Server Error
- Kiểm tra file `.htaccess` có đúng format không
- Kiểm tra PHP error log: `tail -f /path/to/error.log`
- Tắt RewriteEngine để test: Comment dòng `RewriteEngine On`

#### CSS/JS không load
- Kiểm tra đường dẫn trong `header.php`
- Kiểm tra phân quyền thư mục `public/css/`
- Clear browser cache

#### Không hiển thị test data
- Kiểm tra thư mục `data/` có đầy đủ file không
- Kiểm tra phân quyền đọc file: `chmod 644 data/*/*.txt`

### 6. Tối ưu cho production

#### Enable caching
Thêm vào `.htaccess`:
```apache
# Cache static files
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
</IfModule>
```

#### Compress files
```apache
# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css application/javascript
</IfModule>
```

### 7. Bảo mật

#### Ẩn thư mục app/ và data/
Tạo file `.htaccess` trong `app/` và `data/`:
```apache
Deny from all
```

#### Disable directory listing
Thêm vào `.htaccess` root:
```apache
Options -Indexes
```

## Mobile Support

Website đã được tối ưu cho mobile với:
- Responsive design (breakpoints: 768px, 576px)
- Hamburger menu cho mobile
- Touch-friendly buttons và inputs
- Optimized font sizes

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Liên hệ

Nếu gặp vấn đề, kiểm tra:
1. PHP error log
2. Browser console (F12)
3. Network tab để xem request nào fail
