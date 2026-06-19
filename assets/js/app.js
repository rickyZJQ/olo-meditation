/**
 * OLO 冥想 - 前端交互逻辑
 * 支持：分类切换、后台播放、Media Session API、进度控制
 */

(function () {
    'use strict';

    // ========== 状态管理 ==========
    const state = {
        allMusic: [],
        currentGroup: 'front', // 'front' = 前10首(助眠), 'back' = 后10首(疗愈)
        filteredList: [],
        currentIndex: -1,
        isPlaying: false,
        audio: null,
    };

    // ========== DOM 元素 ==========
    const dom = {
        musicList: document.getElementById('musicList'),
        loading: document.getElementById('loading'),
        nowPlaying: document.getElementById('nowPlaying'),
        playerCover: document.getElementById('playerCover'),
        playerTitle: document.getElementById('playerTitle'),
        playerDesc: document.getElementById('playerDesc'),
        progressFill: document.getElementById('progressFill'),
        currentTime: document.getElementById('currentTime'),
        totalTime: document.getElementById('totalTime'),
        btnPlay: document.getElementById('btnPlay'),
        btnPrev: document.getElementById('btnPrev'),
        btnNext: document.getElementById('btnNext'),
        progressBar: document.getElementById('progressBar'),
        tabFront: document.getElementById('tabFront'),
        tabBack: document.getElementById('tabBack'),
        sectionTitle: document.getElementById('sectionTitle'),
    };

    // ========== 初始化 ==========
    function init() {
        createStarfield();
        loadMusicList();
        bindEvents();
    }

    // ========== 星空背景 ==========
    function createStarfield() {
        const container = document.getElementById('starfield');
        if (!container) return;

        const starCount = 80;
        for (let i = 0; i < starCount; i++) {
            const star = document.createElement('div');
            star.className = 'star' + (Math.random() > 0.85 ? ' large' : '');
            star.style.left = Math.random() * 100 + '%';
            star.style.top = Math.random() * 100 + '%';
            star.style.setProperty('--duration', (3 + Math.random() * 5) + 's');
            star.style.setProperty('--max-opacity', (0.3 + Math.random() * 0.7).toString());
            star.style.animationDelay = Math.random() * 5 + 's';
            container.appendChild(star);
        }

        for (let i = 0; i < 3; i++) {
            const meteor = document.createElement('div');
            meteor.className = 'shooting-star';
            meteor.style.top = (10 + Math.random() * 40) + '%';
            meteor.style.left = (20 + Math.random() * 60) + '%';
            meteor.style.animationDelay = (3 + Math.random() * 8) + 's';
            container.appendChild(meteor);
        }
    }

    // ========== 加载音乐列表 ==========
    async function loadMusicList() {
        try {
            const resp = await fetch('api/music.php?action=list');
            const json = await resp.json();

            if (json.code === 0 && json.data.length > 0) {
                state.allMusic = json.data;
                switchGroup('front');
            } else {
                dom.loading.innerHTML = '<p>暂无冥想音乐</p>';
            }
        } catch (err) {
            console.error('加载音乐列表失败:', err);
            dom.loading.innerHTML = '<p>加载失败，请刷新重试</p>';
        }
    }

    // ========== 切换分类 ==========
    function switchGroup(group) {
        state.currentGroup = group;
        state.filteredList = state.allMusic.filter(m => m.group === group);

        // 更新标签样式
        if (dom.tabFront && dom.tabBack) {
            dom.tabFront.classList.toggle('active', group === 'front');
            dom.tabBack.classList.toggle('active', group === 'back');
        }

        // 更新标题
        const sectionTitle = document.getElementById('sectionTitle');
        if (sectionTitle) {
            sectionTitle.textContent = group === 'front' ? '助眠放松' : '疗愈冥想';
        }

        // 重新渲染列表
        renderMusicList();

        // 如果正在播放的音乐不在当前分组，停止播放
        if (state.currentIndex >= 0) {
            const currentItem = state.filteredList[state.currentIndex];
            if (!currentItem || state.audio) {
                // 检查当前播放的是否在当前列表中
                const allIndex = state.allMusic.findIndex(m => m.id === (currentItem ? currentItem.id : -1));
                if (allIndex === -1 || state.allMusic[allIndex].group !== group) {
                    stopPlayback();
                }
            }
        }
    }

    function stopPlayback() {
        if (state.audio) {
            state.audio.pause();
            state.audio.src = '';
        }
        state.isPlaying = false;
        state.currentIndex = -1;
        dom.nowPlaying.classList.remove('active');
        updatePlayButton();
    }

    // ========== 渲染音乐列表 ==========
    function renderMusicList() {
        dom.musicList.innerHTML = '';
        dom.loading.style.display = 'none';

        const icons = ['🌙', '🌿', '🧘', '✨', '💫', '🎵', '🌊', '🔥', '🍃', '⭐'];

        state.filteredList.forEach((item, index) => {
            const card = document.createElement('div');
            card.className = 'music-card';
            card.dataset.index = index;
            card.innerHTML = `
                <div class="card-icon">
                    <span class="card-emoji">${icons[index % icons.length]}</span>
                    <div class="playing-indicator">
                        <span></span><span></span><span></span>
                    </div>
                </div>
                <div class="card-info">
                    <div class="card-title">${escapeHtml(item.title)}</div>
                    <div class="card-meta">
                        <span class="card-duration">${item.duration} 分钟</span>
                        ${item.description ? '<span>' + escapeHtml(item.description.substring(0, 20)) + '</span>' : ''}
                    </div>
                </div>
                <button class="card-play-btn" aria-label="播放">▶</button>
            `;

            card.addEventListener('click', () => playMusic(index));
            dom.musicList.appendChild(card);
        });

        // 恢复当前播放卡片的激活状态
        if (state.currentIndex >= 0 && state.isPlaying) {
            updateActiveCard(state.currentIndex);
        }
    }

    // ========== 播放音乐 ==========
    function playMusic(index) {
        const item = state.filteredList[index];
        if (!item) return;

        if (state.currentIndex === index && state.isPlaying) {
            togglePlay();
            return;
        }

        state.currentIndex = index;

        if (!state.audio) {
            state.audio = new Audio();
            state.audio.preload = 'metadata';

            state.audio.addEventListener('timeupdate', updateProgress);
            state.audio.addEventListener('loadedmetadata', onMetadataLoaded);
            state.audio.addEventListener('ended', onPlayEnded);
            state.audio.addEventListener('error', onAudioError);
        }

        state.audio.src = item.audio;
        state.audio.load();

        updatePlayerUI(item);
        updateActiveCard(index);
        dom.nowPlaying.classList.add('active');

        state.audio.play().then(() => {
            state.isPlaying = true;
            updatePlayButton();
            dom.playerCover.classList.add('playing');
            setupMediaSession(item);
        }).catch(err => {
            console.warn('自动播放被阻止:', err);
            state.isPlaying = false;
            updatePlayButton();
        });
    }

    // ========== 播放/暂停切换 ==========
    function togglePlay() {
        if (!state.audio) return;

        if (state.isPlaying) {
            state.audio.pause();
            state.isPlaying = false;
            dom.playerCover.classList.remove('playing');
        } else {
            state.audio.play().then(() => {
                state.isPlaying = true;
                dom.playerCover.classList.add('playing');
            }).catch(err => {
                console.warn('播放失败:', err);
            });
        }
        updatePlayButton();
    }

    // ========== 上一首/下一首 ==========
    function playPrev() {
        if (state.filteredList.length === 0) return;
        const prev = (state.currentIndex - 1 + state.filteredList.length) % state.filteredList.length;
        playMusic(prev);
    }

    function playNext() {
        if (state.filteredList.length === 0) return;
        const next = (state.currentIndex + 1) % state.filteredList.length;
        playMusic(next);
    }

    // ========== 进度更新 ==========
    function updateProgress() {
        if (!state.audio || !state.audio.duration) return;
        const pct = (state.audio.currentTime / state.audio.duration) * 100;
        dom.progressFill.style.width = pct + '%';
        dom.currentTime.textContent = formatTime(state.audio.currentTime);
    }

    function onMetadataLoaded() {
        const duration = state.audio.duration;
        if (duration && !isNaN(duration) && isFinite(duration)) {
            dom.totalTime.textContent = formatTime(duration);
        } else {
            const item = state.filteredList[state.currentIndex];
            if (item && item.duration) {
                dom.totalTime.textContent = formatTime(item.duration * 60);
            }
        }
    }

    function onPlayEnded() {
        state.isPlaying = false;
        updatePlayButton();
        dom.playerCover.classList.remove('playing');
        dom.progressFill.style.width = '0%';
        dom.currentTime.textContent = '0:00';
    }

    function onAudioError() {
        console.error('音频加载失败');
    }

    // ========== 进度条点击跳转 ==========
    function seekTo(e) {
        if (!state.audio || !state.audio.duration) return;
        const rect = dom.progressBar.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const pct = x / rect.width;
        state.audio.currentTime = pct * state.audio.duration;
    }

    // ========== Media Session API ==========
    function setupMediaSession(item) {
        if (!('mediaSession' in navigator)) return;

        navigator.mediaSession.metadata = new MediaMetadata({
            title: item.title,
            artist: 'OLO 冥想',
            album: '冥想音乐',
            artwork: item.cover ? [{ src: item.cover, sizes: '512x512', type: 'image/jpeg' }] : [],
        });

        navigator.mediaSession.setActionHandler('play', () => {
            state.audio.play();
            state.isPlaying = true;
            updatePlayButton();
            dom.playerCover.classList.add('playing');
        });

        navigator.mediaSession.setActionHandler('pause', () => {
            state.audio.pause();
            state.isPlaying = false;
            updatePlayButton();
            dom.playerCover.classList.remove('playing');
        });

        navigator.mediaSession.setActionHandler('previoustrack', playPrev);
        navigator.mediaSession.setActionHandler('nexttrack', playNext);
    }

    // ========== UI 更新 ==========
    function updatePlayerUI(item) {
        dom.playerTitle.textContent = item.title;
        dom.playerDesc.textContent = item.description || item.duration + ' 分钟冥想';
        dom.progressFill.style.width = '0%';
        dom.currentTime.textContent = '0:00';
        dom.totalTime.textContent = formatTime(item.duration * 60);
    }

    function updatePlayButton() {
        dom.btnPlay.textContent = state.isPlaying ? '⏸' : '▶';
    }

    function updateActiveCard(index) {
        document.querySelectorAll('.music-card').forEach((card, i) => {
            card.classList.toggle('active', i === index);
        });
    }

    // ========== 事件绑定 ==========
    function bindEvents() {
        dom.btnPlay.addEventListener('click', togglePlay);
        dom.btnPrev.addEventListener('click', playPrev);
        dom.btnNext.addEventListener('click', playNext);
        dom.progressBar.addEventListener('click', seekTo);

        if (dom.tabFront) {
            dom.tabFront.addEventListener('click', () => switchGroup('front'));
        }
        if (dom.tabBack) {
            dom.tabBack.addEventListener('click', () => switchGroup('back'));
        }
    }

    // ========== 工具函数 ==========
    function formatTime(seconds) {
        if (!seconds || isNaN(seconds)) return '0:00';
        const m = Math.floor(seconds / 60);
        const s = Math.floor(seconds % 60);
        return m + ':' + (s < 10 ? '0' : '') + s;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // ========== 防止页面休眠 ==========
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible' && state.isPlaying) {
            if ('wakeLock' in navigator) {
                navigator.wakeLock.request('screen').catch(() => {});
            }
        }
    });

    // ========== 启动 ==========
    document.addEventListener('DOMContentLoaded', init);
})();
