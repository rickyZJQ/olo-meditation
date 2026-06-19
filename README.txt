# OLO 冥想 - 部署说明

## 环境要求
- PHP 7.4+
- MySQL 5.7+
- Apache / Nginx（支持 PHP）
- 启用 PHP 扩展：pdo_mysql, mbstring, session

## 部署步骤

### 1. 上传文件
将所有文件上传到你的 Web 服务器根目录

### 2. 配置数据库
修改 `config.php` 中的数据库连接信息：
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'olo_meditation');
define('DB_USER', 'root');
define('DB_PASS', '你的数据库密码');
```

### 3. 初始化数据库
在浏览器中访问 `http://你的域名/init_db.php`，运行数据库初始化脚本。
**初始化完成后，请务必删除 `init_db.php` 文件！**

### 4. 修改管理员密码
在 `config.php` 中修改后台登录密码：
```php
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', '你的密码');
```

### 5. 访问
- 前台页面：`http://你的域名/`
- 后台管理：`http://你的域名/admin/`

## 后台使用
1. 登录后台管理
2. 点击"上传音乐"添加冥想音频（MP3格式）
3. 可选上传封面图片
4. 设置时长、分类、排序
5. 支持启用/停用和删除操作

## 熄屏播放说明
- 使用了 Media Session API，支持手机锁屏/熄屏后继续播放
- 手机通知栏会显示播放控制（播放/暂停/上一首/下一首）
- 使用了 Wake Lock API 防止设备休眠

## 注意事项
- 音频文件建议使用 MP3 格式，兼容性最好
- 建议开启 HTTPS（部分浏览器要求 HTTPS 才能使用 Media Session API）
- `assets/audio/` 目录需要有写入权限
