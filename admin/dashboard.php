<?php
/**
 * 后台管理 - 仪表盘（音乐管理）
 */
require_once __DIR__ . '/../config.php';

// 检查登录
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$db = getDB();

// 处理删除
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    // 获取文件路径
    $stmt = $db->prepare("SELECT audio_file, cover_image FROM meditations WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if ($item) {
        // 删除文件
        if ($item['audio_file'] && file_exists(__DIR__ . '/../' . $item['audio_file'])) {
            unlink(__DIR__ . '/../' . $item['audio_file']);
        }
        if ($item['cover_image'] && file_exists(__DIR__ . '/../' . $item['cover_image'])) {
            unlink(__DIR__ . '/../' . $item['cover_image']);
        }
        // 删除记录
        $db->prepare("DELETE FROM meditations WHERE id = ?")->execute([$id]);
    }
    header('Location: dashboard.php?msg=deleted');
    exit;
}

// 处理切换启用状态
if (isset($_GET['toggle'])) {
    $id = (int) $_GET['toggle'];
    $db->prepare("UPDATE meditations SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?")->execute([$id]);
    header('Location: dashboard.php');
    exit;
}

// 获取音乐列表
$musicList = $db->query("SELECT * FROM meditations ORDER BY sort_order ASC, id DESC")->fetchAll();

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OLO冥想 - 音乐管理</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'PingFang SC', 'Microsoft YaHei', sans-serif;
            background: #0a0e1a;
            color: #e8eaf6;
            min-height: 100vh;
        }
        .header {
            background: rgba(17, 24, 39, 0.95);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header h1 {
            font-size: 18px;
            font-weight: 400;
            letter-spacing: 2px;
        }
        .header a {
            color: #9ca3af;
            text-decoration: none;
            font-size: 13px;
        }
        .header a:hover { color: #e8eaf6; }
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 24px;
        }
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .toolbar h2 {
            font-size: 16px;
            font-weight: 500;
        }
        .btn {
            padding: 8px 20px;
            border-radius: 8px;
            border: none;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: opacity 0.2s;
        }
        .btn:hover { opacity: 0.85; }
        .btn-primary {
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            color: #fff;
        }
        .btn-sm {
            padding: 5px 12px;
            font-size: 12px;
            border-radius: 6px;
        }
        .btn-danger { background: #dc2626; color: #fff; }
        .btn-warning { background: #f59e0b; color: #000; }
        .btn-secondary { background: rgba(255,255,255,0.1); color: #e8eaf6; }

        .msg {
            padding: 10px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 13px;
        }
        .msg-success { background: rgba(34, 197, 94, 0.15); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3); }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(17, 24, 39, 0.8);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.06);
        }
        th, td {
            padding: 14px 16px;
            text-align: left;
            font-size: 13px;
        }
        th {
            background: rgba(255,255,255,0.03);
            color: #9ca3af;
            font-weight: 500;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        td {
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255,255,255,0.02); }

        .status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
        }
        .status-on { background: rgba(34,197,94,0.15); color: #22c55e; }
        .status-off { background: rgba(239,68,68,0.15); color: #ef4444; }

        .actions {
            display: flex;
            gap: 6px;
        }

        .empty {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }
        .empty p { margin-bottom: 16px; }

        .upload-section {
            background: rgba(17, 24, 39, 0.8);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .upload-section h3 {
            font-size: 15px;
            font-weight: 500;
            margin-bottom: 16px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }
        .form-row.full { grid-template-columns: 1fr; }
        .form-group label {
            display: block;
            font-size: 12px;
            color: #9ca3af;
            margin-bottom: 4px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: #e8eaf6;
            font-size: 13px;
            outline: none;
            font-family: inherit;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #7c3aed;
        }
        .form-group textarea { resize: vertical; min-height: 60px; }
        .form-group input[type="file"] {
            padding: 8px;
            background: transparent;
            border: 1px dashed rgba(255,255,255,0.15);
        }
        .form-group input[type="file"]::-webkit-file-upload-button {
            background: rgba(124,58,237,0.3);
            border: none;
            color: #e8eaf6;
            padding: 6px 14px;
            border-radius: 6px;
            cursor: pointer;
            margin-right: 10px;
        }
        .upload-actions {
            margin-top: 16px;
            display: flex;
            gap: 10px;
        }

        @media (max-width: 600px) {
            .form-row { grid-template-columns: 1fr; }
            .container { padding: 16px; }
            th, td { padding: 10px 12px; }
            .header { padding: 12px 16px; }
        }
    </style>
</head>
<body>

<div class="header">
    <h1>OLO 冥想 · 后台管理</h1>
    <a href="index.php?action=logout" onclick="return confirm('确定退出登录？')">退出登录</a>
</div>

<div class="container">

    <?php if ($msg === 'deleted'): ?>
        <div class="msg msg-success">删除成功</div>
    <?php endif; ?>
    <?php if ($msg === 'uploaded'): ?>
        <div class="msg msg-success">上传成功</div>
    <?php endif; ?>

    <!-- 上传区域 -->
    <div class="upload-section">
        <h3>添加冥想音乐</h3>
        <form action="upload.php" method="POST" enctype="multipart/form-data" id="uploadForm">
            <div class="form-row">
                <div class="form-group">
                    <label>音乐标题 *</label>
                    <input type="text" name="title" required placeholder="如：清晨冥想">
                </div>
                <div class="form-group">
                    <label>时长（分钟）*</label>
                    <select name="duration" required>
                        <option value="5">5 分钟</option>
                        <option value="8">8 分钟</option>
                        <option value="10">10 分钟</option>
                        <option value="30">30 分钟</option>
                    </select>
                </div>
            </div>
            <div class="form-row full">
                <div class="form-group">
                    <label>描述</label>
                    <textarea name="description" placeholder="简短描述这段冥想音乐"></textarea>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>音频文件 (MP3) *</label>
                    <input type="file" name="audio" accept="audio/mpeg,audio/mp3,.mp3" required>
                </div>
                <div class="form-group">
                    <label>封面图片（可选）</label>
                    <input type="file" name="cover" accept="image/jpeg,image/png,image/webp">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>分类</label>
                    <select name="category">
                        <option value="default">默认</option>
                        <option value="morning">清晨</option>
                        <option value="relax">放松</option>
                        <option value="focus">专注</option>
                        <option value="healing">疗愈</option>
                        <option value="sleep">睡眠</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>排序（数字越小越靠前）</label>
                    <input type="number" name="sort_order" value="0" min="0">
                </div>
            </div>
            <div class="upload-actions">
                <button type="submit" class="btn btn-primary">上传音乐</button>
            </div>
        </form>
    </div>

    <!-- 音乐列表 -->
    <div class="toolbar">
        <h2>音乐列表 (<?php echo count($musicList); ?>)</h2>
        <a href="../index.php" target="_blank" class="btn btn-secondary btn-sm">查看前台</a>
    </div>

    <?php if (empty($musicList)): ?>
        <div class="empty">
            <p>暂无冥想音乐</p>
            <a href="#uploadForm" class="btn btn-primary">添加第一首</a>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>标题</th>
                    <th>时长</th>
                    <th>分类</th>
                    <th>状态</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($musicList as $item): ?>
                <tr>
                    <td><?php echo $item['id']; ?></td>
                    <td>
                        <?php echo htmlspecialchars($item['title']); ?>
                        <?php if ($item['description']): ?>
                            <br><small style="color:#6b7280"><?php echo htmlspecialchars(mb_substr($item['description'], 0, 20)); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $item['duration']; ?> 分钟</td>
                    <td><?php echo htmlspecialchars($item['category']); ?></td>
                    <td>
                        <span class="status <?php echo $item['is_active'] ? 'status-on' : 'status-off'; ?>">
                            <?php echo $item['is_active'] ? '启用' : '停用'; ?>
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <a href="?toggle=<?php echo $item['id']; ?>" class="btn btn-sm btn-warning">
                                <?php echo $item['is_active'] ? '停用' : '启用'; ?>
                            </a>
                            <a href="?delete=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger"
                               onclick="return confirm('确定删除这首音乐？')">删除</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>

</body>
</html>
