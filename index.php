<?php
/**
 * OLO 冥想 - 前台首页
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#0a0e1a">
    <title>OLO 冥想 - 内心的宁静</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- 星空背景 -->
    <div class="starfield" id="starfield"></div>

    <!-- 光晕效果 -->
    <div class="glow-orb orb1"></div>
    <div class="glow-orb orb2"></div>
    <div class="glow-orb orb3"></div>

    <!-- 主容器 -->
    <div class="app-container">

        <!-- 头部 -->
        <header class="app-header">
            <span class="app-logo">🧘</span>
            <h1 class="app-title">OLO 冥想</h1>
            <p class="app-subtitle">找到内心的宁静</p>
        </header>

        <!-- 正在播放 -->
        <section class="now-playing" id="nowPlaying">
            <div class="player-cover" id="playerCover">
                <div class="breath-circle"></div>
                <span class="player-cover-icon">🎵</span>
            </div>
            <div class="player-title" id="playerTitle">选择一首冥想音乐</div>
            <div class="player-desc" id="playerDesc">点击下方列表开始</div>

            <div class="progress-wrapper">
                <div class="progress-bar" id="progressBar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <div class="progress-time">
                    <span id="currentTime">0:00</span>
                    <span id="totalTime">0:00</span>
                </div>
            </div>

            <div class="player-controls">
                <button class="btn-control" id="btnPrev" aria-label="上一首">⏮</button>
                <button class="btn-control btn-play" id="btnPlay" aria-label="播放">▶</button>
                <button class="btn-control" id="btnNext" aria-label="下一首">⏭</button>
            </div>
        </section>

        <!-- 分类切换 -->
        <section class="category-tabs">
            <button class="tab-btn active" id="tabFront">
                <span class="tab-icon">🌙</span>
                <span class="tab-text">助眠放松</span>
                <span class="tab-count">10首</span>
            </button>
            <button class="tab-btn" id="tabBack">
                <span class="tab-icon">✨</span>
                <span class="tab-text">疗愈冥想</span>
                <span class="tab-count">10首</span>
            </button>
        </section>

        <!-- 音乐列表 -->
        <section>
            <h2 class="section-title" id="sectionTitle">助眠放松</h2>
            <div class="music-list" id="musicList"></div>
            <div class="loading" id="loading">
                <div class="loading-spinner"></div>
                <p>正在加载...</p>
            </div>
        </section>

        <!-- 底部 -->
        <footer class="app-footer">
            OLO MEDITATION &copy; 2024
        </footer>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
