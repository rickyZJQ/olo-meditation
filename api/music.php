<?php
/**
 * 冥想音乐API接口
 * 返回音乐列表JSON数据
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config.php';

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        $db = getDB();
        $stmt = $db->query("SELECT * FROM meditations WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
        $list = $stmt->fetchAll();

        // 构造返回数据
        $result = [];
        foreach ($list as $item) {
            $result[] = [
                'id'          => (int) $item['id'],
                'title'       => $item['title'],
                'description' => $item['description'],
                'duration'    => (int) $item['duration'],
                'audio'       => BASE_URL . '/' . $item['audio_file'],
                'cover'       => $item['cover_image'] ? BASE_URL . '/' . $item['cover_image'] : null,
                'category'    => $item['category'],
            ];
        }

        echo json_encode(['code' => 0, 'data' => $result]);
        break;

    default:
        echo json_encode(['code' => 1, 'msg' => '未知操作']);
}
