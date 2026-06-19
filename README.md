# OLO 冥想音乐
> 找到内心的宁静

OLO品牌冥想音乐应用，暗色星空主题，支持20首冥想轻音乐分类播放。

## 功能特点

- 暗色星空主题背景（CSS动画）
- 20首冥想轻音乐，分两类展示
  - 🌙 助眠放松（前10首）
  - ✨ 疗愈冥想（后10首）
- 音乐播放器（播放/暂停/上一首/下一首/进度条）
- Media Session API（支持手机熄屏播放）
- Wake Lock API（防止设备休眠）
- PHP后台管理（上传/删除/启停音乐）

## 技术栈

- 前端：HTML5 + CSS3 + JavaScript
- 后端：PHP + MySQL
- 本地开发：Node.js 模拟服务器

## 部署说明

### 环境要求
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx

### 部署步骤
1. 修改 `config.php` 中的数据库连接信息和管理员密码
2. 上传到Web服务器
3. 访问 `init_db.php` 初始化数据库
4. **删除 `init_db.php`**
5. 访问 `admin/` 登录后台管理

### 本地开发
```bash
node dev-server.js
# 访问 http://localhost:3000
```

## 项目结构
```
├── index.php          # 前台首页
├── config.php         # 配置文件
├── init_db.php        # 数据库初始化
├── dev-server.js      # Node.js开发服务器
├── api/
│   └── music.php      # 音乐API
├── admin/
│   ├── index.php      # 后台登录
│   ├── dashboard.php  # 后台管理
│   └── upload.php     # 上传处理
└── assets/
    ├── css/style.css  # 样式
    ├── js/app.js      # 前端逻辑
    ├── audio/         # 音乐文件
    └── images/        # 图片
```

## License
MIT
