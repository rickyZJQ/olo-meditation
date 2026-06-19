/**
 * 本地开发服务器（Node.js）
 * 模拟PHP API接口，用于本地测试前台页面
 * 运行: node dev-server.js
 */

const http = require('http');
const fs = require('fs');
const path = require('path');

const PORT = 3000;
const ROOT = __dirname;

// 20首冥想轻音乐数据
// 前10首：助眠放松类 (sleep)
// 后10首：疗愈冥想类 (heal)
const meditations = [
    // ===== 前10首：助眠放松类 =====
    { id: 1, title: '夜雨安眠', description: '模拟雨夜的宁静氛围，帮助快速入眠', duration: 5, audio: '/assets/audio/track_01_night_rain.wav', cover: null, category: 'sleep', group: 'front' },
    { id: 2, title: '深海呼吸', description: '低频海浪般的节奏，引导深度放松', duration: 5, audio: '/assets/audio/track_02_deep_ocean.wav', cover: null, category: 'sleep', group: 'front' },
    { id: 3, title: '月光摇篮', description: '温柔的月光旋律，安抚心灵', duration: 5, audio: '/assets/audio/track_03_moonlight.wav', cover: null, category: 'sleep', group: 'front' },
    { id: 4, title: '森林梦境', description: '林间微风般的轻柔音色', duration: 5, audio: '/assets/audio/track_04_forest_dream.wav', cover: null, category: 'sleep', group: 'front' },
    { id: 5, title: '星光闪烁', description: '遥远的星光，宁静而深邃', duration: 5, audio: '/assets/audio/track_05_starlight.wav', cover: null, category: 'sleep', group: 'front' },
    { id: 6, title: '轻柔波浪', description: '如海浪般起伏的舒缓旋律', duration: 5, audio: '/assets/audio/track_06_gentle_waves.wav', cover: null, category: 'sleep', group: 'front' },
    { id: 7, title: '宁静山谷', description: '山谷回声般的空灵音色', duration: 5, audio: '/assets/audio/track_07_peaceful_valley.wav', cover: null, category: 'sleep', group: 'front' },
    { id: 8, title: '温暖怀抱', description: '如被温暖包裹的安全感', duration: 5, audio: '/assets/audio/track_08_warm_blanket.wav', cover: null, category: 'sleep', group: 'front' },
    { id: 9, title: '随波逐流', description: '让思绪随音乐漂流远去', duration: 5, audio: '/assets/audio/track_09_drift_away.wav', cover: null, category: 'sleep', group: 'front' },
    { id: 10, title: '寂静之夜', description: '最深沉的宁静，彻底放松', duration: 5, audio: '/assets/audio/track_10_silent_night.wav', cover: null, category: 'sleep', group: 'front' },
    // ===== 后10首：疗愈冥想类 =====
    { id: 11, title: '晨露清新', description: '清晨第一缕阳光般的清新旋律', duration: 5, audio: '/assets/audio/track_11_morning_dew.wav', cover: null, category: 'heal', group: 'back' },
    { id: 12, title: '日出霞光', description: '温暖明亮的日出氛围', duration: 5, audio: '/assets/audio/track_12_sunrise_glow.wav', cover: null, category: 'heal', group: 'back' },
    { id: 13, title: '禅意花园', description: '东方禅意的宁静与和谐', duration: 5, audio: '/assets/audio/track_13_zen_garden.wav', cover: null, category: 'heal', group: 'back' },
    { id: 14, title: '水晶澄明', description: '如水晶般清澈透明的音色', duration: 5, audio: '/assets/audio/track_14_crystal_clear.wav', cover: null, category: 'heal', group: 'back' },
    { id: 15, title: '内在光芒', description: '唤醒内在力量的温暖旋律', duration: 5, audio: '/assets/audio/track_15_inner_light.wav', cover: null, category: 'heal', group: 'back' },
    { id: 16, title: '神圣空间', description: '庄严而宁静的神圣氛围', duration: 5, audio: '/assets/audio/track_16_sacred_space.wav', cover: null, category: 'heal', group: 'back' },
    { id: 17, title: '流水禅心', description: '如溪水般流动的冥想旋律', duration: 5, audio: '/assets/audio/track_17_flowing_water.wav', cover: null, category: 'heal', group: 'back' },
    { id: 18, title: '天使之声', description: '空灵飘渺的天籁之音', duration: 5, audio: '/assets/audio/track_18_angelic_voice.wav', cover: null, category: 'heal', group: 'back' },
    { id: 19, title: '宇宙漂流', description: '浩瀚宇宙中的宁静漂浮', duration: 5, audio: '/assets/audio/track_19_cosmic_drift.wav', cover: null, category: 'heal', group: 'back' },
    { id: 20, title: '永恒宁静', description: '最深层次的内心平静', duration: 5, audio: '/assets/audio/track_20_eternal_peace.wav', cover: null, category: 'heal', group: 'back' },
];

// MIME类型
const MIME = {
    '.html': 'text/html; charset=utf-8',
    '.css': 'text/css; charset=utf-8',
    '.js': 'application/javascript; charset=utf-8',
    '.json': 'application/json; charset=utf-8',
    '.php': 'text/html; charset=utf-8',
    '.mp3': 'audio/mpeg',
    '.wav': 'audio/wav',
    '.ogg': 'audio/ogg',
    '.png': 'image/png',
    '.jpg': 'image/jpeg',
    '.jpeg': 'image/jpeg',
    '.webp': 'image/webp',
    '.svg': 'image/svg+xml',
    '.ico': 'image/x-icon',
    '.txt': 'text/plain; charset=utf-8',
};

const server = http.createServer((req, res) => {
    let url = req.url.split('?')[0];

    // API 路由
    if (url === '/api/music.php' || url === '/api/music') {
        const result = meditations.map(item => ({
            id: item.id,
            title: item.title,
            description: item.description,
            duration: item.duration,
            audio: item.audio,
            cover: item.cover,
            category: item.category,
            group: item.group,
        }));
        res.writeHead(200, { 'Content-Type': 'application/json; charset=utf-8', 'Access-Control-Allow-Origin': '*' });
        res.end(JSON.stringify({ code: 0, data: result }));
        return;
    }

    // 默认首页
    if (url === '/') {
        url = '/index.php';
    }

    // 文件服务
    const filePath = path.join(ROOT, url);

    // 安全检查
    if (!filePath.startsWith(ROOT)) {
        res.writeHead(403);
        res.end('Forbidden');
        return;
    }

    fs.readFile(filePath, (err, data) => {
        if (err) {
            if (err.code === 'ENOENT') {
                res.writeHead(404);
                res.end('Not Found: ' + url);
            } else {
                res.writeHead(500);
                res.end('Internal Server Error');
            }
            return;
        }

        const ext = path.extname(filePath).toLowerCase();
        const contentType = MIME[ext] || 'application/octet-stream';
        res.writeHead(200, { 'Content-Type': contentType });
        res.end(data);
    });
});

server.listen(PORT, '0.0.0.0', () => {
    console.log('');
    console.log('  ========================================');
    console.log('  OLO 冥想 - 本地开发服务器');
    console.log('  ========================================');
    console.log('');
    console.log('  前台页面: http://localhost:' + PORT);
    console.log('  API接口:  http://localhost:' + PORT + '/api/music.php');
    console.log('');
    console.log('  音乐数量: 20首');
    console.log('  前10首: 助眠放松类');
    console.log('  后10首: 疗愈冥想类');
    console.log('');
    console.log('  按 Ctrl+C 停止服务器');
    console.log('');
});
