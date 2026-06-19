<?php
/**
 * 后台管理 - 音频上传处理
 */
require_once __DIR__ . '/../config.php';

// 检查登录
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$db = getDB();

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$duration = (int) ($_POST['duration'] ?? 5);
$category = trim($_POST['category'] ?? 'default');
$sort_order = (int) ($_POST['sort_order'] ?? 0);

if (empty($title) || empty($duration)) {
    header('Location: dashboard.php?msg=error');
    exit;
}

// 处理音频文件上传
$audioPath = '';
if (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION));
    $allowedExt = ['mp3', 'mpeg', 'wav', 'ogg', 'm4a'];
    if (!in_array($ext, $allowedExt)) {
        header('Location: dashboard.php?msg=error_audio');
        exit;
    }

    $filename = 'meditation_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $uploadDir = __DIR__ . '/../assets/audio/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (move_uploaded_file($_FILES['audio']['tmp_name'], $uploadDir . $filename)) {
        $audioPath = 'assets/audio/' . $filename;
    }
}

if (empty($audioPath)) {
    header('Location: dashboard.php?msg=error_upload');
    exit;
}

// 处理封面图片上传
$coverPath = '';
if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
    $allowedImg = ['jpg', 'jpeg', 'png', 'webp'];
    if (in_array($ext, $allowedImg)) {
        $imgFilename = 'cover_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $imgDir = __DIR__ . '/../assets/images/';
        if (!is_dir($imgDir)) {
            mkdir($imgDir, 0755, true);
        }
        if (move_uploaded_file($_FILES['cover']['tmp_name'], $imgDir . $imgFilename)) {
            $coverPath = 'assets/images/' . $imgFilename;
        }
    }
}

// 插入数据库
$stmt = $db->prepare("INSERT INTO meditations (title, description, duration, audio_file, cover_image, category, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$title, $description, $duration, $audioPath, $coverPath ?: null, $category, $sort_order]);

header('Location: dashboard.php?msg=uploaded');
exit;
