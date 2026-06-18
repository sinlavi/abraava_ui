// ============================================================
//  CONFIG
// ============================================================
const PROXY_URL = '/ui/audio_proxy.php'; // PHP proxy endpoint - needs to be updated or handled

// ============================================================
//  STATE
// ============================================================
let currentTreeItems = [];
let selectedTreeIds = new Set();
let selectedQueueIds = new Set();
let globalLookupMap = new Map();
let expandedNodes = new Set();
let currentQueueItems = [];
let filteredQueueItems = [];
let modalResolve = null;

// Audio player state
let audioPlayer = null;
let currentTrack = null; // { trackId, trackName, artistName, artworkUrl, audioUrl }
let isPlaying = false;

// ============================================================
//  DOM refs
// ============================================================
const menuToggle = document.getElementById('menuToggle');
const propsToggle = document.getElementById('propsToggle');
const leftSidebar = document.getElementById('leftSidebar');
const rightPanel = document.getElementById('rightPanel');
const layoutOverlay = document.getElementById('layoutOverlay');

const apArtwork = document.getElementById('apArtwork');
const apTitle = document.getElementById('apTitle');
const apArtist = document.getElementById('apArtist');
const apPlayBtn = document.getElementById('apPlayBtn');
const apProgress = document.getElementById('apProgress');
const apCurrentTime = document.getElementById('apCurrentTime');
const apDuration = document.getElementById('apDuration');
const apVolume = document.getElementById('apVolume');
const apCloseBtn = document.getElementById('apCloseBtn');
const apPrevBtn = document.getElementById('apPrevBtn');
const apNextBtn = document.getElementById('apNextBtn');

// ============================================================
//  TOKEN HELPER
// ============================================================
function replaceTokenInUrl(url) {
    return url;
}

function getProxiedAudioUrl(rawUrl) {
    if (!rawUrl) return null;
    const fixed = replaceTokenInUrl(rawUrl);
    return PROXY_URL + '?url=' + encodeURIComponent(fixed);
}

// ============================================================
//  TOAST / MODAL
// ============================================================
function showToast(msg, isError = false, duration = 3000) {
    const toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.style.background = isError ? '#b91c1c' : '#333';
    toast.classList.add('show');
    clearTimeout(toast._timeout);
    toast._timeout = setTimeout(() => toast.classList.remove('show'), duration);
}

function showModal(title, body) {
    return new Promise((resolve) => {
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalBody').innerHTML = body;
        document.getElementById('modalOverlay').classList.add('active');
        modalResolve = resolve;
    });
}

// Event listeners for modal (moved from global scope to init if needed, but keeping logic here)
document.getElementById('modalConfirmBtn').addEventListener('click', () => {
    document.getElementById('modalOverlay').classList.remove('active');
    if (modalResolve) modalResolve(true);
});
document.getElementById('modalCancelBtn').addEventListener('click', () => {
    document.getElementById('modalOverlay').classList.remove('active');
    if (modalResolve) modalResolve(false);
});
document.getElementById('modalOverlay').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) {
        document.getElementById('modalOverlay').classList.remove('active');
        if (modalResolve) modalResolve(false);
    }
});

// ============================================================
//  CACHE / HELPERS
// ============================================================
function escapeHtml(str) { if (!str) return ''; return str.replace(/[&<>]/g, m => ({ '&': '&amp;', '<': '&lt;',
    '>': '&gt;' } [m])); }

function cacheItem(item) {
    if (!item) return;
    if (item.artistId) globalLookupMap.set(`artist:${item.artistId}`, item);
    if (item.collectionId) globalLookupMap.set(`collection:${item.collectionId}`, item);
    if (item.trackId) globalLookupMap.set(`track:${item.trackId}`, item);
}

// ============================================================
//  TREE RENDER
// ============================================================
function renderTree(parentElement, items) {
    currentTreeItems = [];
    selectedTreeIds.clear();
    expandedNodes.clear();
    const ul = document.createElement('ul');
    ul.className = 'tree-node';

    const filterType = document.getElementById('resultFilter').value;
    let filteredItems = items;
    if (filterType !== 'all') {
        filteredItems = items.filter(item => item.wrapperType === filterType);
    }

    document.getElementById('treeTotalCount').textContent = filteredItems.length + ' items';

    function buildTree(itemsArr, container, depth = 0) {
        for (const item of itemsArr) {
            cacheItem(item);
            const li = document.createElement('li');
            const hasChildren = (item.wrapperType === 'artist' || item.wrapperType === 'collection');
            const displayName = item.trackName || item.collectionName || item.artistName || 'Untitled';
            const icon = item.wrapperType === 'artist' ? 'fas fa-user' : (item.wrapperType === 'collection' ?
                'fas fa-dot-circle' : 'fas fa-music');
            const itemKey = `${item.wrapperType}:${item.artistId || item.collectionId || item.trackId}`;

            let extraInfo = '';
            let countInfo = '';
            if (item.wrapperType === 'artist') {
                if (item.primaryGenreName) extraInfo = `<i class="fas fa-tag"></i> ${escapeHtml(item.primaryGenreName)}`;
            } else if (item.wrapperType === 'collection') {
                const year = item.releaseDate ? new Date(item.releaseDate).getFullYear() : '';
                const count = item.trackCount || 0;
                extraInfo = `${year ? year + ' · ' : ''}${count} tracks`;
                countInfo = count > 0 ? `<span class="node-count">${count} tracks</span>` : '';
            } else if (item.wrapperType === 'track') {
                const duration = item.trackTimeMillis ?
                    `${Math.floor(item.trackTimeMillis/60000)}:${Math.floor((item.trackTimeMillis%60000)/1000).toString().padStart(2,'0')}` :
                    '';
                extraInfo = duration ? `⏱ ${duration}` : '';
            }

            currentTreeItems.push({ key: itemKey, item, type: item.wrapperType, id: item.artistId || item
                    .collectionId || item.trackId });

            const toggleSpan = document.createElement('div');
            toggleSpan.className = 'tree-toggle';
            toggleSpan.setAttribute('data-node-key', itemKey);
            toggleSpan.innerHTML = `
                <input type="checkbox" class="tree-checkbox" data-key="${itemKey}" aria-label="Select ${escapeHtml(displayName)}">
                <i class="${icon}"></i>
                <span class="node-label">
                    <span>${escapeHtml(displayName)}</span>
                    ${extraInfo ? `<span class="sub-info">${extraInfo}</span>` : ''}
                </span>
                ${countInfo}
                <span class="node-actions">
                    <button class="tree-add-btn" data-key="${itemKey}" title="Add to queue" aria-label="Add to queue"><i class="fas fa-plus-circle"></i></button>
                    <button class="tree-expand-btn" data-key="${itemKey}" title="Expand" aria-label="Expand"><i class="fas fa-chevron-right"></i></button>
                    ${item.wrapperType === 'track' && item.mirrorUrls?.audioUrl ? `<button class="tree-play-btn" data-key="${itemKey}" title="Play" aria-label="Play"><i class="fas fa-play"></i></button>` : ''}
                </span>
            `;
            li.appendChild(toggleSpan);

            // Play button for tracks
            const playBtn = toggleSpan.querySelector('.tree-play-btn');
            if (playBtn) {
                playBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    playTrackFromItem(item);
                });
            }

            if (hasChildren) {
                const childrenContainer = document.createElement('ul');
                childrenContainer.className = 'tree-children';
                li.appendChild(childrenContainer);

                const expandBtn = toggleSpan.querySelector('.tree-expand-btn');
                expandBtn.addEventListener('click', async (e) => {
                    e.stopPropagation();
                    const isOpen = li.classList.contains('open');
                    if (!isOpen) {
                        const nodeKey = itemKey;
                        if (!expandedNodes.has(nodeKey)) {
                            childrenContainer.innerHTML =
                                '<div class="tree-loading"><i class="fas fa-spinner fa-pulse"></i> Loading...</div>';
                            const kids = await fetchAllChildren(item);
                            childrenContainer.innerHTML = '';
                            if (kids.length) {
                                buildTree(kids, childrenContainer, depth + 1);
                                expandedNodes.add(nodeKey);
                            } else {
                                childrenContainer.innerHTML =
                                    '<div style="padding:3px 10px; color:#999; font-size:9px;">↳ No child items found</div>';
                                expandedNodes.add(nodeKey);
                            }
                        }
                        li.classList.add('open');
                    } else {
                        li.classList.remove('open');
                    }
                });

                toggleSpan.addEventListener('click', async (e) => {
                    if (e.target.type === 'checkbox' || e.target.closest('.node-label') || e.target
                        .closest('.node-actions')) return;
                    e.stopPropagation();
                    const isOpen = li.classList.contains('open');
                    if (!isOpen) {
                        const nodeKey = itemKey;
                        if (!expandedNodes.has(nodeKey)) {
                            childrenContainer.innerHTML =
                                '<div class="tree-loading"><i class="fas fa-spinner fa-pulse"></i> Loading...</div>';
                            const kids = await fetchAllChildren(item);
                            childrenContainer.innerHTML = '';
                            if (kids.length) {
                                buildTree(kids, childrenContainer, depth + 1);
                                expandedNodes.add(nodeKey);
                            } else {
                                childrenContainer.innerHTML =
                                    '<div style="padding:3px 10px; color:#999; font-size:9px;">↳ No child items found</div>';
                                expandedNodes.add(nodeKey);
                            }
                        }
                        li.classList.add('open');
                    } else {
                        li.classList.remove('open');
                    }
                });
            }

            const cb = toggleSpan.querySelector('.tree-checkbox');
            cb.addEventListener('change', (e) => {
                e.stopPropagation();
                if (cb.checked) selectedTreeIds.add(itemKey);
                else selectedTreeIds.delete(itemKey);
                updateTreeBulkBar();
            });

            const addBtn = toggleSpan.querySelector('.tree-add-btn');
            addBtn.addEventListener('click', async (e) => {
                e.stopPropagation();
                await addSingleItemToQueue(item);
            });

            const labelSpan = toggleSpan.querySelector('.node-label');
            labelSpan.addEventListener('click', (e) => {
                e.stopPropagation();
                activateStructuralNode(itemKey, item);
                closeAllDrawers();
            });
            container.appendChild(li);
        }
    }

    buildTree(filteredItems, ul);
    parentElement.innerHTML = '';
    parentElement.appendChild(ul);
    updateTreeBulkBar();
}

// ============================================================
//  TREE ACTIONS
// ============================================================
function updateTreeBulkBar() {
    const bar = document.getElementById('treeBulkBar');
    const count = selectedTreeIds.size;
    document.getElementById('treeSelectedCount').innerText = count;
    bar.style.display = count > 0 ? 'flex' : 'none';
}

function treeSelectAll() {
    document.querySelectorAll('.tree-checkbox').forEach(cb => {
        cb.checked = true;
        selectedTreeIds.add(cb.dataset.key);
    });
    updateTreeBulkBar();
}

function treeSelectNone() {
    selectedTreeIds.clear();
    document.querySelectorAll('.tree-checkbox').forEach(cb => cb.checked = false);
    updateTreeBulkBar();
}

function treeExpandAll() {
    document.querySelectorAll('.tree-node li').forEach(li => li.classList.add('open'));
}

function treeCollapseAll() {
    document.querySelectorAll('.tree-node li').forEach(li => li.classList.remove('open'));
}

function closeAllDrawers() {
    leftSidebar.classList.remove('open-drawer');
    rightPanel.classList.remove('open-drawer');
    layoutOverlay.classList.remove('active');
}

// ============================================================
//  ACTIVATE NODE / BROWSE
// ============================================================
function activateStructuralNode(itemKey, item) {
    document.querySelectorAll('.tree-toggle').forEach(el => el.classList.remove('active-node'));
    const element = document.querySelector(`[data-node-key="${itemKey}"]`);
    if (element) {
        element.classList.add('active-node');
        let parentLi = element.parentElement;
        while (parentLi && parentLi.tagName === 'LI') {
            parentLi.classList.add('open');
            parentLi = parentLi.parentElement ? parentLi.parentElement.parentElement : null;
        }
        element.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }
    setSelectedMetadata(item);
    renderWorkspacePage(item);
    switchTab('browse');
}

window.routeToInternalLink = async function(type, id) {
    const lookupKey = `${type}:${id}`;
    let cachedItem = globalLookupMap.get(lookupKey);
    if (!cachedItem) {
        const container = document.getElementById('browseWorkspace');
        container.innerHTML = '<div class="empty-msg"><i class="fas fa-spinner fa-pulse"></i> Loading...</div>';
        switchTab('browse');
        try {
            const lookup = await apiCall(`/lookup?id=${cleanId(id)}`);
            if (lookup.results && lookup.results.length) {
                cachedItem = lookup.results[0];
                cacheItem(cachedItem);
            }
        } catch (e) {
            container.innerHTML = `<div class="empty-msg">❌ Error: ${e.message}</div>`;
            return;
        }
    }
    if (cachedItem) {
        activateStructuralNode(lookupKey, cachedItem);
    } else {
        showToast('Error: Item not found', true);
    }
};

function switchTab(target) {
    const tabs = document.querySelectorAll('.tab');
    const panes = {
        queue: document.getElementById('queue-tab'),
        browse: document.getElementById('browse-tab'),
        stats: document.getElementById('stats-tab'),
        admin: document.getElementById('admin-tab')
    };
    tabs.forEach(t => t.classList.remove('active'));
    const targetTab = document.querySelector(`[data-tab="${target}"]`);
    if (targetTab) targetTab.classList.add('active');
    Object.values(panes).forEach(p => p.classList.remove('active-pane'));
    if (panes[target]) panes[target].classList.add('active-pane');
    if (target === 'stats') loadStats();
    if (target === 'admin') loadUsersList();
}

// ============================================================
//  RENDER MIRROR BUTTONS (with play support)
// ============================================================
function renderMirrorButtons(mirrorUrls, trackItem) {
    if (!mirrorUrls) return '';
    let html = '<div class="mirror-group">';
    const audioRaw = mirrorUrls.audioUrl?.url || mirrorUrls.audioUrl;
    if (audioRaw) {
        const proxied = getProxiedAudioUrl(audioRaw);
        if (proxied) {
            html +=
                `<button class="mirror-btn" onclick="playAudioUrl('${escapeHtml(proxied)}', '${trackItem ? escapeHtml(trackItem.trackName || '') : ''}', '${trackItem ? escapeHtml(trackItem.artistName || '') : ''}', '${trackItem ? escapeHtml(trackItem.artworkUrl100 || trackItem.artworkUrl60 || '') : ''}')" title="Play" aria-label="Play Mirror"><i class="fas fa-play"></i></button>`;
            html +=
                `<a href="${escapeHtml(proxied)}" target="_blank" class="mirror-btn" title="Download" aria-label="Download Mirror"><i class="fas fa-download"></i></a>`;
        }
    }
    if (mirrorUrls.artworkUrl?.url) {
        html +=
            `<a href="${escapeHtml(mirrorUrls.artworkUrl.url)}" target="_blank" class="mirror-btn" aria-label="View Artwork"><i class="fas fa-image"></i></a>`;
    }
    if (mirrorUrls.previewUrl?.url) {
        const preview = replaceTokenInUrl(mirrorUrls.previewUrl.url);
        html +=
            `<a href="${escapeHtml(preview)}" target="_blank" class="mirror-btn" aria-label="Play Preview"><i class="fas fa-play-circle"></i></a>`;
    }
    html += '</div>';
    return html;
}

// ============================================================
//  AUDIO PLAYER
// ============================================================
function initAudioPlayer() {
    audioPlayer = new Audio();
    audioPlayer.volume = parseInt(apVolume.value) / 100;

    audioPlayer.addEventListener('timeupdate', () => {
        if (audioPlayer.duration) {
            const pct = (audioPlayer.currentTime / audioPlayer.duration) * 1000;
            apProgress.value = Math.min(pct, 1000);
            apCurrentTime.textContent = formatTime(audioPlayer.currentTime);
        }
    });

    audioPlayer.addEventListener('loadedmetadata', () => {
        apDuration.textContent = formatTime(audioPlayer.duration);
        apProgress.value = 0;
        apCurrentTime.textContent = '0:00';
    });

    audioPlayer.addEventListener('ended', () => {
        isPlaying = false;
        apPlayBtn.innerHTML = '<i class="fas fa-play"></i>';
    });

    audioPlayer.addEventListener('play', () => {
        isPlaying = true;
        apPlayBtn.innerHTML = '<i class="fas fa-pause"></i>';
    });

    audioPlayer.addEventListener('pause', () => {
        isPlaying = false;
        apPlayBtn.innerHTML = '<i class="fas fa-play"></i>';
    });

    audioPlayer.addEventListener('error', (e) => {
        showToast('❌ Audio playback error', true);
        console.warn('Audio error', e);
        isPlaying = false;
        apPlayBtn.innerHTML = '<i class="fas fa-play"></i>';
    });

    // Progress seek
    apProgress.addEventListener('input', () => {
        if (audioPlayer.duration) {
            const pct = parseInt(apProgress.value) / 1000;
            audioPlayer.currentTime = pct * audioPlayer.duration;
        }
    });

    // Volume
    apVolume.addEventListener('input', () => {
        const val = parseInt(apVolume.value) / 100;
        audioPlayer.volume = val;
    });

    // Play/Pause
    apPlayBtn.addEventListener('click', () => {
        if (!audioPlayer.src) {
            showToast('No track loaded. Select a track to play.', false, 2000);
            return;
        }
        if (isPlaying) {
            audioPlayer.pause();
        } else {
            audioPlayer.play().catch(e => {
                showToast('❌ Cannot play: ' + e.message, true);
            });
        }
    });

    // Close
    apCloseBtn.addEventListener('click', () => {
        audioPlayer.pause();
        audioPlayer.src = '';
        currentTrack = null;
        isPlaying = false;
        apPlayBtn.innerHTML = '<i class="fas fa-play"></i>';
        apTitle.textContent = 'No track loaded';
        apArtist.textContent = '—';
        apArtwork.innerHTML = '<i class="fas fa-music"></i>';
        apArtwork.style.background = '#ccc';
        apArtwork.style.backgroundImage = 'none';
        apCurrentTime.textContent = '0:00';
        apDuration.textContent = '0:00';
        apProgress.value = 0;
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        if (e.code === 'Space') {
            e.preventDefault();
            apPlayBtn.click();
        }
        if (e.code === 'ArrowRight' && audioPlayer.duration) {
            audioPlayer.currentTime = Math.min(audioPlayer.currentTime + 5, audioPlayer.duration);
        }
        if (e.code === 'ArrowLeft' && audioPlayer.duration) {
            audioPlayer.currentTime = Math.max(audioPlayer.currentTime - 5, 0);
        }
        if (e.code === 'ArrowUp') {
            e.preventDefault();
            const v = Math.min(100, parseInt(apVolume.value) + 5);
            apVolume.value = v;
            audioPlayer.volume = v / 100;
        }
        if (e.code === 'ArrowDown') {
            e.preventDefault();
            const v = Math.max(0, parseInt(apVolume.value) - 5);
            apVolume.value = v;
            audioPlayer.volume = v / 100;
        }
    });
}

function formatTime(seconds) {
    if (!seconds || isNaN(seconds)) return '0:00';
    const m = Math.floor(seconds / 60);
    const s = Math.floor(seconds % 60);
    return `${m}:${s.toString().padStart(2, '0')}`;
}

function playAudioUrl(url, trackName, artistName, artworkUrl) {
    if (!url) { showToast('❌ No audio URL available', true); return; }
    try {
        const proxied = url.startsWith('http') ? url : PROXY_URL + '?url=' + encodeURIComponent(url);
        audioPlayer.src = proxied;
        audioPlayer.load();
        audioPlayer.play().catch(e => {
            showToast('❌ Cannot play: ' + e.message, true);
        });
        currentTrack = { trackName, artistName, artworkUrl };
        apTitle.textContent = trackName || 'Track';
        apArtist.textContent = artistName || '—';
        if (artworkUrl) {
            apArtwork.innerHTML = '';
            apArtwork.style.background = '#fff';
            apArtwork.style.backgroundImage = `url(${escapeHtml(artworkUrl)})`;
            apArtwork.style.backgroundSize = 'cover';
            apArtwork.style.backgroundPosition = 'center';
        } else {
            apArtwork.innerHTML = '<i class="fas fa-music"></i>';
            apArtwork.style.background = '#ccc';
            apArtwork.style.backgroundImage = 'none';
        }
        showToast(`▶️ Playing: ${trackName || 'Track'}`, false, 1500);
    } catch (e) {
        showToast('❌ Playback error: ' + e.message, true);
    }
}

function playTrackFromItem(item) {
    if (!item) return;
    const audioRaw = item.mirrorUrls?.audioUrl?.url || item.mirrorUrls?.audioUrl;
    if (!audioRaw) { showToast('No audio available for this track', true); return; }
    const proxied = (audioRaw);
    if (!proxied) { showToast('Invalid audio URL', true); return; }
    const trackName = item.trackName || 'Track';
    const artistName = item.artistName || '—';
    const artwork = item.artworkUrl100 || item.artworkUrl60 || '';
    playAudioUrl(proxied, trackName, artistName, artwork);
}

// Expose to inline onclick
window.playAudioUrl = playAudioUrl;

// ============================================================
//  RENDER WORKSPACE PAGE (with play buttons)
// ============================================================
async function renderWorkspacePage(item) {
    const container = document.getElementById('browseWorkspace');
    const type = item.wrapperType;
    let freshItem = item;
    if (type === 'track' && item.trackId) {
        try {
            const lookup = await apiCall(`/lookup?id=${cleanId(item.trackId)}`);
            if (lookup.results && lookup.results.length) freshItem = lookup.results[0];
        } catch (e) {}
    } else if (type === 'collection' && item.collectionId) {
        try {
            const lookup = await apiCall(`/lookup?id=${cleanId(item.collectionId)}`);
            if (lookup.results && lookup.results.length) freshItem = lookup.results[0];
        } catch (e) {}
    } else if (type === 'artist' && item.artistId) {
        try {
            const lookup = await apiCall(`/lookup?id=${cleanId(item.artistId)}`);
            if (lookup.results && lookup.results.length) freshItem = lookup.results[0];
        } catch (e) {}
    }

    const mirrorHtml = renderMirrorButtons(freshItem.mirrorUrls || {}, freshItem);
    const lyricsHtml = (type === 'track' && freshItem.lyrics) ? renderLyrics(freshItem.lyrics) : '';

    // Helper to add play button to tables
    const playBtnHtml = (track) => {
        const audio = track.mirrorUrls?.audioUrl?.url || track.mirrorUrls?.audioUrl;
        if (!audio) return '—';
        const proxied = getProxiedAudioUrl(audio);
        if (!proxied) return '—';
        return `<button class="btn-sm btn-play" onclick="playAudioUrl('${escapeHtml(proxied)}', '${escapeHtml(track.trackName || '')}', '${escapeHtml(track.artistName || '')}', '${escapeHtml(track.artworkUrl100 || track.artworkUrl60 || '')}')" aria-label="Play Track"><i class="fas fa-play"></i></button>`;
    };

    if (type === 'artist') {
        container.innerHTML = `
            <div class="pma-header">
                <h2><i class="fas fa-user"></i> ${escapeHtml(freshItem.artistName)}</h2>
                <button class="btn-sm btn-success" onclick="addAllToQueueByType('artist', '${cleanId(freshItem.artistId)}', '${escapeHtml(freshItem.artistName)}')" style="margin-left:auto;"><i class="fas fa-download"></i> Add All</button>
            </div>
            <div class="pma-doc-box">
                <div class="profile-container">
                    <div style="text-align:center; background:#e1e1e1; padding:20px; border:1px solid #ccc; display:flex; align-items:center; justify-content:center; width:100%; max-width:100px;">
                        <i class="fas fa-user-tie fa-4x" style="color:#888;"></i>
                    </div>
                    <div class="profile-meta-grid">
                        <div class="meta-block"><label>ID</label><div>${freshItem.artistId}</div></div>
                        <div class="meta-block"><label>Genre</label><div>${escapeHtml(freshItem.primaryGenreName || 'Unknown')}</div></div>
                        <div class="meta-block"><label>Link</label><div style="font-size:9px;"><a href="${freshItem.artistLinkUrl}" target="_blank">View Profile</a></div></div>
                        ${mirrorHtml ? `<div class="meta-block"><label>Mirrors</label><div>${mirrorHtml}</div></div>` : ''}
                    </div>
                </div>
            </div>
            <div class="pma-header"><h3><i class="fas fa-boxes"></i> Albums (${freshItem.albumCount || '?'})</h3></div>
            <div style="padding:0 10px 10px; overflow-y:auto; flex:1;">
                <table class="data-table" id="subAlbumTable">
                    <thead><tr><th>Album</th><th>Year</th><th>Tracks</th><th>Mirrors</th><th>Action</th></tr></thead>
                    <tbody><tr><td colspan="5" class="empty-msg"><i class="fas fa-spinner fa-pulse"></i> Loading...</td></tr></tbody>
                </table>
            </div>
        `;
        try {
            const subItems = await fetchAllChildren(freshItem);
            const subBody = container.querySelector('#subAlbumTable tbody');
            if (!subItems.length) { subBody.innerHTML =
                '<tr><td colspan="5" class="empty-msg">No albums found.</td></tr>'; return; }
            let html = '';
            for (const alb of subItems) {
                cacheItem(alb);
                const subMirror = renderMirrorButtons(alb.mirrorUrls || {}, alb);
                html += `<tr>
                    <td data-label="Album"><span class="clickable-link" onclick="routeToInternalLink('collection', '${alb.collectionId}')">${escapeHtml(alb.collectionName)}</span></td>
                    <td data-label="Year">${alb.releaseDate ? new Date(alb.releaseDate).getFullYear() : 'N/A'}</td>
                    <td data-label="Tracks">${alb.trackCount || 0}</td>
                    <td data-label="Mirrors">${subMirror}</td>
                    <td data-label="Action"><button class="btn-sm btn-success" onclick="addAllToQueueByType('album', '${cleanId(alb.collectionId)}', '${escapeHtml(alb.collectionName)}')" aria-label="Add Album to Queue"><i class="fas fa-download"></i></button></td>
                </tr>`;
            }
            subBody.innerHTML = html;
        } catch (err) { console.error(err); }

    } else if (type === 'collection') {
        container.innerHTML = `
            <div class="pma-header">
                <h2><i class="fas fa-dot-circle"></i> ${escapeHtml(freshItem.collectionName)}</h2>
                <button class="btn-sm btn-success" onclick="addAllToQueueByType('album', '${cleanId(freshItem.collectionId)}', '${escapeHtml(freshItem.collectionName)}')" style="margin-left:auto;"><i class="fas fa-download"></i> Add All</button>
            </div>
            <div class="pma-doc-box">
                <div class="profile-container">
                    <div><img src="${freshItem.artworkUrl100 || 'https://via.placeholder.com/100'}" style="width:100px; border:1px solid #ccc; padding:2px; background:#fff; border-radius:2px;" alt="Artwork"></div>
                    <div class="profile-meta-grid">
                        <div class="meta-block"><label>ID</label><div>${freshItem.collectionId}</div></div>
                        <div class="meta-block"><label>Artist</label><div><span class="clickable-link" onclick="routeToInternalLink('artist', '${freshItem.artistId}')">${escapeHtml(freshItem.artistName)}</span></div></div>
                        <div class="meta-block"><label>Tracks</label><div>${freshItem.trackCount || 0}</div></div>
                        <div class="meta-block"><label>Copyright</label><div style="font-size:9px; font-weight:normal;">${escapeHtml(freshItem.copyright || 'None')}</div></div>
                        ${mirrorHtml ? `<div class="meta-block"><label>Mirrors</label><div>${mirrorHtml}</div></div>` : ''}
                    </div>
                </div>
            </div>
            <div class="pma-header"><h3><i class="fas fa-list-ol"></i> Tracks</h3></div>
            <div style="padding:0 10px 10px; overflow-y:auto; flex:1;">
                <table class="data-table" id="subTrackTable">
                    <thead><tr><th>#</th><th>Track</th><th>Duration</th><th>Play</th><th>Mirrors</th><th>Action</th></tr></thead>
                    <tbody><tr><td colspan="6" class="empty-msg"><i class="fas fa-spinner fa-pulse"></i> Loading...</td></tr></tbody>
                </table>
            </div>
        `;
        try {
            const subTracks = await fetchAllChildren(freshItem);
            const subBody = container.querySelector('#subTrackTable tbody');
            if (!subTracks.length) { subBody.innerHTML =
                '<tr><td colspan="6" class="empty-msg">No tracks found.</td></tr>'; return; }
            let html = '';
            for (const trk of subTracks) {
                cacheItem(trk);
                const duration = trk.trackTimeMillis ?
                    `${Math.floor(trk.trackTimeMillis/60000)}:${Math.floor((trk.trackTimeMillis%60000)/1000).toString().padStart(2,'0')}` :
                    '—';
                const subMirror = renderMirrorButtons(trk.mirrorUrls || {}, trk);
                const playBtn = playBtnHtml(trk);
                html += `<tr>
                    <td data-label="#"><code>${trk.trackNumber || 1}</code></td>
                    <td data-label="Track"><span class="clickable-link" onclick="routeToInternalLink('track', '${trk.trackId}')">${escapeHtml(trk.trackName)}</span></td>
                    <td data-label="Duration">${duration}</td>
                    <td data-label="Play">${playBtn}</td>
                    <td data-label="Mirrors">${subMirror}</td>
                    <td data-label="Action"><button class="btn-sm btn-success" onclick="addAllToQueueByType('track', '${cleanId(trk.trackId)}', '${escapeHtml(trk.trackName)}')" aria-label="Add Track to Queue"><i class="fas fa-download"></i></button></td>
                </tr>`;
            }
            subBody.innerHTML = html;
        } catch (e) { console.error(e); }

    } else if (type === 'track') {
        const duration = freshItem.trackTimeMillis ?
            `${Math.floor(freshItem.trackTimeMillis/60000)}:${Math.floor((freshItem.trackTimeMillis%60000)/1000).toString().padStart(2,'0')}` :
            '—';
        const audioRaw = freshItem.mirrorUrls?.audioUrl?.url || freshItem.mirrorUrls?.audioUrl;
        const playBtn = audioRaw ?
            `<button class="btn-sm btn-play" onclick="playAudioUrl('${escapeHtml(getProxiedAudioUrl(audioRaw))}', '${escapeHtml(freshItem.trackName || '')}', '${escapeHtml(freshItem.artistName || '')}', '${escapeHtml(freshItem.artworkUrl100 || freshItem.artworkUrl60 || '')}')" aria-label="Play Track"><i class="fas fa-play"></i> Play</button>` :
            '—';

        container.innerHTML = `
            <div class="pma-header">
                <h2><i class="fas fa-music"></i> ${escapeHtml(freshItem.trackName)}</h2>
                <div style="display:flex; gap:4px; margin-left:auto;">
                    ${playBtn}
                    <button class="btn-sm btn-success" onclick="addAllToQueueByType('track', '${cleanId(freshItem.trackId)}', '${escapeHtml(freshItem.trackName)}')" aria-label="Add Track to Queue"><i class="fas fa-download"></i> Add</button>
                </div>
            </div>
            <div class="pma-doc-box">
                <div class="profile-container">
                    <div><img src="${freshItem.artworkUrl100 || 'https://via.placeholder.com/100'}" style="width:100px; border:1px solid #ccc; padding:2px; background:#fff; border-radius:2px;" alt="Artwork"></div>
                    <div class="profile-meta-grid">
                        <div class="meta-block"><label>ID</label><div>${freshItem.trackId}</div></div>
                        <div class="meta-block"><label>Artist</label><div><span class="clickable-link" onclick="routeToInternalLink('artist', '${freshItem.artistId}')">${escapeHtml(freshItem.artistName)}</span></div></div>
                        <div class="meta-block"><label>Album</label><div><span class="clickable-link" onclick="routeToInternalLink('collection', '${freshItem.collectionId}')">${escapeHtml(freshItem.collectionName || 'Single')}</span></div></div>
                        <div class="meta-block"><label>Duration</label><div>${duration}</div></div>
                        <div class="meta-block"><label>Track #</label><div>${freshItem.trackNumber || 1} of ${freshItem.trackCount || 1}</div></div>
                        ${mirrorHtml ? `<div class="meta-block"><label>Mirrors</label><div>${mirrorHtml}</div></div>` : ''}
                        ${lyricsHtml ? `<div class="meta-block" style="grid-column:1/-1;"><label>Lyrics</label><div>${lyricsHtml}</div></div>` : ''}
                    </div>
                </div>
            </div>
        `;
    }
}

function renderLyrics(lyrics) {
    if (!lyrics) return '';
    if (typeof lyrics === 'string') return `<div class="lyrics-box">${escapeHtml(lyrics)}</div>`;
    if (Array.isArray(lyrics)) return `<div class="lyrics-box">${lyrics.map(l => escapeHtml(l)).join('\n')}</div>`;
    if (typeof lyrics === 'object') return `<div class="lyrics-box">${escapeHtml(JSON.stringify(lyrics, null, 2))}</div>`;
    return '';
}

// ============================================================
//  QUEUE / DOWNLOAD FUNCTIONS
// ============================================================
async function addSingleItemToQueue(item) {
    try {
        let payload = {};
        if (item.wrapperType === 'track') {
            payload.trackId = cleanId(item.trackId);
        } else if (item.wrapperType === 'collection') {
            payload.albumId = cleanId(item.collectionId);
        } else if (item.wrapperType === 'artist') {
            payload.artistId = cleanId(item.artistId);
        } else return;
        payload.quality = '192';
        payload.skipExisting = true;
        const result = await apiCall('/download/add', 'POST', payload);
        const added = result.added_count || 0;
        showToast(`✅ Added ${added} item${added > 1 ? 's' : ''} to queue`);
        loadQueue();
    } catch (e) {
        showToast('❌ Error: ' + e.message, true);
    }
}

window.addAllToQueueByType = async function(type, id, nameHint) {
    try {
        let payload = {};
        if (type === 'artist') payload.artistId = id;
        else if (type === 'album' || type === 'collection') payload.albumId = id;
        else if (type === 'track') payload.trackId = id;
        else { showToast('❌ Invalid entity type', true); return; }
        payload.quality = '192';
        payload.skipExisting = true;
        const result = await apiCall('/download/add', 'POST', payload);
        const addedCount = result.added_count || 0;
        const skippedCount = result.skipped_count || 0;
        showToast(`✅ Added ${addedCount} item${addedCount > 1 ? 's' : ''} to queue${skippedCount > 0 ? ` (${skippedCount} already queued)` : ''}`);
        loadQueue();
    } catch (err) {
        showToast('❌ Error: ' + err.message, true);
    }
};

async function addSelectedToQueue() {
    if (selectedTreeIds.size === 0) return;
    let addedTotal = 0;
    for (const key of selectedTreeIds) {
        const found = currentTreeItems.find(t => t.key === key);
        if (!found) continue;
        const item = found.item;
        try {
            let payload = {};
            if (item.wrapperType === 'track') payload.trackId = cleanId(item.trackId);
            else if (item.wrapperType === 'collection') payload.albumId = cleanId(item.collectionId);
            else if (item.wrapperType === 'artist') payload.artistId = cleanId(item.artistId);
            else continue;
            payload.quality = '192';
            payload.skipExisting = true;
            const result = await apiCall('/download/add', 'POST', payload);
            addedTotal += result.added_count || 0;
        } catch (e) { console.warn(e); }
    }
    showToast(`✅ Added ${addedTotal} item${addedTotal > 1 ? 's' : ''} to queue.`);
    loadQueue();
    selectedTreeIds.clear();
    document.querySelectorAll('.tree-checkbox').forEach(cb => cb.checked = false);
    updateTreeBulkBar();
}

// ============================================================
//  QUEUE TABLE
// ============================================================
function filterQueueItems() {
    const statusFilter = document.getElementById('queueStatusFilter').value;
    const searchFilter = document.getElementById('queueSearchFilter').value.toLowerCase().trim();
    const sortBy = document.getElementById('queueSortBy').value;

    filteredQueueItems = currentQueueItems.filter(item => {
        if (statusFilter !== 'all') {
            const status = item.download_status || item.status || 'pending';
            if (status !== statusFilter) return false;
        }
        if (searchFilter) {
            const trackName = (item.trackName || '').toLowerCase();
            const artistName = (item.artistName || '').toLowerCase();
            const trackId = (item.trackId || '').toLowerCase();
            if (!trackName.includes(searchFilter) &&
                !artistName.includes(searchFilter) &&
                !trackId.includes(searchFilter)) {
                return false;
            }
        }
        return true;
    });

    filteredQueueItems.sort((a, b) => {
        switch (sortBy) {
            case 'id':
                return (a.download_id || 0) - (b.download_id || 0);
            case 'status':
                return (a.download_status || '').localeCompare(b.download_status || '');
            case 'track':
                return (a.trackName || '').localeCompare(b.trackName || '');
            case 'added':
                return new Date(a.added_at || 0) - new Date(b.added_at || 0);
            default:
                return 0;
        }
    });

    document.getElementById('queueFilterCount').textContent = `Showing: ${filteredQueueItems.length} / ${currentQueueItems.length}`;
    renderQueueTable(filteredQueueItems);
    updateQueueStats();
}

function renderQueueTable(items) {
    const tbody = document.getElementById('queueBody');
    if (!items.length) {
        tbody.innerHTML = '<tr><td colspan="9" class="empty-msg">No items match the current filters.</td></tr>';
        updateQueueSelection();
        return;
    }

    let html = '';
    for (const item of items) {
        const trackName = item.trackName || item.trackId || 'Unknown';
        const artist = item.artistName || '—';
        const status = item.download_status || item.status || 'pending';
        const statusClass = `status-${status}`;
        const quality = item.quality || '192';
        const mirrorHtml = renderMirrorButtons(item.mirrorUrls || {}, item);
        const progress = item.progress || 0;
        const hasProgress = status === 'downloading' && progress > 0;

        // Play button for completed tracks
        let playBtn = '—';
        if (status === 'completed' && item.file_path) {
            // If we have a file path, try to play it
            // For now, use mirror if available
            const audioRaw = item.mirrorUrls?.audioUrl?.url || item.mirrorUrls?.audioUrl;
            if (audioRaw) {
                const proxied = getProxiedAudioUrl(audioRaw);
                if (proxied) {
                    playBtn =
                        `<button class="btn-sm btn-play" onclick="playAudioUrl('${escapeHtml(proxied)}', '${escapeHtml(trackName)}', '${escapeHtml(artist)}', '${escapeHtml(item.artworkUrl100 || item.artworkUrl60 || '')}')" aria-label="Play Track"><i class="fas fa-play"></i></button>`;
                }
            }
        } else if (item.mirrorUrls?.audioUrl) {
            const audioRaw = item.mirrorUrls.audioUrl.url || item.mirrorUrls.audioUrl;
            if (audioRaw) {
                const proxied = getProxiedAudioUrl(audioRaw);
                if (proxied) {
                    playBtn =
                        `<button class="btn-sm btn-play" onclick="playAudioUrl('${escapeHtml(proxied)}', '${escapeHtml(trackName)}', '${escapeHtml(artist)}', '${escapeHtml(item.artworkUrl100 || item.artworkUrl60 || '')}')" aria-label="Play Track"><i class="fas fa-play"></i></button>`;
                }
            }
        }

        html += `<tr data-id="${item.download_id}" class="queue-row">
            <td class="checkbox-col"><input type="checkbox" class="queue-checkbox" value="${item.download_id}" aria-label="Select Track"></td>
            <td data-label="ID"><code>${item.download_id}</code></td>
            <td data-label="Track"><strong><span class="clickable-link" onclick="routeToInternalLink('track', '${cleanId(item.trackId)}')">${escapeHtml(trackName)}</span></strong></td>
            <td data-label="Artist">${escapeHtml(artist)}</td>
            <td data-label="Quality">${quality}</td>
            <td data-label="Status">
                <span class="status-badge ${statusClass}">${status}</span>
                ${hasProgress ? `<div class="progress-bar"><div class="progress-fill" style="width:${Math.min(progress, 100)}%"></div></div>` : ''}
            </td>
            <td data-label="Actions" class="action-buttons">
                ${playBtn !== '—' ? playBtn : ''}
                ${status === 'pending' ? `<button onclick="updateSingleStatus(${item.download_id}, 'downloading')" aria-label="Start Download"><i class="fas fa-play"></i></button>` : ''}
                ${status === 'downloading' ? `<button onclick="updateSingleStatus(${item.download_id}, 'paused')" aria-label="Pause Download"><i class="fas fa-pause"></i></button>` : ''}
                ${status === 'paused' ? `<button onclick="updateSingleStatus(${item.download_id}, 'downloading')" aria-label="Resume Download"><i class="fas fa-play"></i></button>` : ''}
                ${(status === 'failed' || status === 'stopped') ? `<button onclick="updateSingleStatus(${item.download_id}, 'pending')" aria-label="Retry Download"><i class="fas fa-sync"></i></button>` : ''}
                ${(status === 'downloading' || status === 'pending') ? `<button onclick="updateSingleStatus(${item.download_id}, 'stopped')" aria-label="Stop Download"><i class="fas fa-stop"></i></button>` : ''}
                <button onclick="deleteSingleDownload(${item.download_id})" aria-label="Delete Task"><i class="fas fa-trash-alt"></i></button>
            </td>
            <td data-label="Mirrors">${mirrorHtml}</td>
            <td data-label="Info">${item.error_message ? `<span class="error-msg">${escapeHtml(item.error_message)}</span>` : item.file_path ? '✅ Downloaded' : '—'}</td>
        </tr>`;
    }
    tbody.innerHTML = html;

    document.querySelectorAll('.queue-checkbox').forEach(cb => cb.addEventListener('change', (e) => {
        e.stopPropagation();
        updateQueueSelection();
    }));

    // Palette: Row-level selection
    document.querySelectorAll('.queue-row').forEach(row => row.addEventListener('click', (e) => {
        if (e.target.closest('button') || e.target.closest('a') || e.target.closest('.clickable-link') || e.target.type === 'checkbox') return;
        const cb = row.querySelector('.queue-checkbox');
        cb.checked = !cb.checked;
        updateQueueSelection();
    }));

    const selectAll = document.getElementById('queueSelectAll');
    if (selectAll) {
        const checkboxes = document.querySelectorAll('.queue-checkbox');
        const checked = document.querySelectorAll('.queue-checkbox:checked');
        selectAll.checked = checkboxes.length > 0 && checkboxes.length === checked.length;
        selectAll.onchange = (e) => {
            document.querySelectorAll('.queue-checkbox').forEach(cb => cb.checked = e.target.checked);
            updateQueueSelection();
        };
    }
    updateQueueSelection();
    document.getElementById('queueTabBadge').textContent = currentQueueItems.filter(i => i.download_status ===
        'pending' || i.download_status === 'downloading').length;
}

function updateQueueStats() {
    const stats = { pending: 0, downloading: 0, paused: 0, completed: 0, failed: 0 };
    currentQueueItems.forEach(item => {
        const status = item.download_status || item.status || 'pending';
        if (stats[status] !== undefined) stats[status]++;
    });
    document.getElementById('statPending').textContent = stats.pending;
    document.getElementById('statDownloading').textContent = stats.downloading;
    document.getElementById('statPaused').textContent = stats.paused;
    document.getElementById('statCompleted').textContent = stats.completed;
    document.getElementById('statFailed').textContent = stats.failed;
    document.getElementById('queueStats').textContent = `${currentQueueItems.length} total`;
}

async function loadQueue() {
    try {
        const data = await apiCall('/download/queue?limit=500');
        currentQueueItems = data.items || [];
        filterQueueItems();
    } catch (e) {
        const tbody = document.getElementById('queueBody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="9" class="empty-msg">⚠️ Error: ${e.message}</td></tr>`;
        }
        console.error(e);
    }
}

function updateQueueSelection() {
    selectedQueueIds.clear();
    document.querySelectorAll('.queue-checkbox:checked').forEach(cb => selectedQueueIds.add(parseInt(cb.value)));
    const count = selectedQueueIds.size;
    document.getElementById('queueSelectedCount').innerText = count;
    document.getElementById('queueBulkBar').style.display = count > 0 ? 'flex' : 'none';

    const selectAll = document.getElementById('queueSelectAll');
    if (selectAll) {
        const checkboxes = document.querySelectorAll('.queue-checkbox');
        const checked = document.querySelectorAll('.queue-checkbox:checked');
        selectAll.checked = checkboxes.length > 0 && checkboxes.length === checked.length;
    }
}

function queueSelectAllVisible() {
    document.querySelectorAll('.queue-checkbox').forEach(cb => cb.checked = true);
    updateQueueSelection();
}

function queueSelectNone() {
    selectedQueueIds.clear();
    document.querySelectorAll('.queue-checkbox').forEach(cb => cb.checked = false);
    updateQueueSelection();
}

function queueInvertSelection() {
    document.querySelectorAll('.queue-checkbox').forEach(cb => cb.checked = !cb.checked);
    updateQueueSelection();
}

async function bulkUpdate(status) {
    if (selectedQueueIds.size === 0) return;
    try {
        const ids = Array.from(selectedQueueIds);
        await apiCall('/download/update', 'POST', { id: ids, status });
        showToast(`✅ ${ids.length} tasks updated to "${status}"`);
        loadQueue();
    } catch (e) {
        showToast('❌ Update failed: ' + e.message, true);
    }
}

async function bulkDelete() {
    if (selectedQueueIds.size === 0) return;
    const confirmed = await showModal('Delete Tasks', `Are you sure you want to delete ${selectedQueueIds.size} selected tasks?`);
    if (!confirmed) return;
    try {
        const ids = Array.from(selectedQueueIds);
        await apiCall('/download/delete', 'DELETE', { id: ids });
        showToast(`🗑️ ${ids.length} tasks deleted`);
        loadQueue();
    } catch (e) {
        showToast('❌ Delete failed: ' + e.message, true);
    }
}

window.updateSingleStatus = async (id, status) => {
    try {
        await apiCall('/download/update', 'POST', { id: [id], status });
        showToast(`✅ Task ${id} updated to "${status}"`);
        loadQueue();
    } catch (e) {
        showToast('❌ Update failed: ' + e.message, true);
    }
};

window.deleteSingleDownload = async (id) => {
    const confirmed = await showModal('Delete Task', `Are you sure you want to delete task ${id}?`);
    if (!confirmed) return;
    try {
        await apiCall('/download/delete', 'DELETE', { id: [id] });
        showToast(`🗑️ Task ${id} deleted`);
        loadQueue();
    } catch (e) {
        showToast('❌ Delete failed: ' + e.message, true);
    }
};

async function clearFailed() {
    const failed = currentQueueItems.filter(i => i.download_status === 'failed' || i.download_status === 'stopped');
    if (failed.length === 0) { showToast('No failed/stopped tasks to clear'); return; }
    const confirmed = await showModal('Clear Failed', `Delete ${failed.length} failed/stopped tasks?`);
    if (!confirmed) return;
    try {
        await apiCall('/download/delete', 'DELETE', { status: 'failed' });
        await apiCall('/download/delete', 'DELETE', { status: 'stopped' });
        showToast(`✅ ${failed.length} failed/stopped tasks cleared`);
        loadQueue();
    } catch (e) {
        showToast('❌ Clear failed: ' + e.message, true);
    }
}

async function retryFailed() {
    const failed = currentQueueItems.filter(i => i.download_status === 'failed');
    if (failed.length === 0) { showToast('No failed tasks to retry'); return; }
    try {
        const ids = failed.map(i => i.download_id);
        await apiCall('/download/update', 'POST', { id: ids, status: 'pending' });
        showToast(`✅ ${ids.length} failed tasks set to pending`);
        loadQueue();
    } catch (e) {
        showToast('❌ Retry failed: ' + e.message, true);
    }
}

async function exportQueue() {
    if (filteredQueueItems.length === 0) { showToast('No items to export'); return; }
    try {
        let csv = 'ID,Track,Artist,Status,Quality,Added\n';
        for (const item of filteredQueueItems) {
            csv +=
                `${item.download_id},${item.trackName || ''},${item.artistName || ''},${item.download_status || item.status || ''},${item.quality || ''},${item.added_at || ''}\n`;
        }
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `queue_export_${new Date().toISOString().slice(0,10)}.csv`;
        a.click();
        URL.revokeObjectURL(url);
        showToast('✅ Export complete');
    } catch (e) {
        showToast('❌ Export failed: ' + e.message, true);
    }
}

// ============================================================
//  STATS
// ============================================================
async function loadStats() {
    const container = document.getElementById('statsWorkspace');
    try {
        const stats = await apiCall('/stats');
        container.innerHTML = `
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:12px;">
                <div class="meta-block"><label>Total Tracks</label><div style="font-size:18px;">${stats.track_count || 0}</div></div>
                <div class="meta-block"><label>Total Artists</label><div style="font-size:18px;">${stats.artist_count || 0}</div></div>
                <div class="meta-block"><label>Total Albums</label><div style="font-size:18px;">${stats.album_count || 0}</div></div>
                <div class="meta-block"><label>Cache Entries</label><div style="font-size:18px;">${stats.cache_entries || 0}</div></div>
                <div class="meta-block"><label>DB Size</label><div style="font-size:18px;">${(stats.db_size_bytes / 1024 / 1024).toFixed(2)} MB</div></div>
                <div class="meta-block"><label>Uptime</label><div style="font-size:18px;">${Math.floor((stats.uptime_seconds || 0) / 3600)}h ${Math.floor(((stats.uptime_seconds || 0) % 3600) / 60)}m</div></div>
            </div>
            <div style="margin-top:16px;">
                <div class="meta-block"><label>Queue Status</label>
                    <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:4px;">
                        <span>Pending: <strong id="statPending2">0</strong></span>
                        <span>Downloading: <strong id="statDownloading2">0</strong></span>
                        <span>Paused: <strong id="statPaused2">0</strong></span>
                        <span>Completed: <strong id="statCompleted2">0</strong></span>
                        <span>Failed: <strong id="statFailed2">0</strong></span>
                    </div>
                </div>
            </div>
        `;
        const stats2 = { pending: 0, downloading: 0, paused: 0, completed: 0, failed: 0 };
        currentQueueItems.forEach(item => {
            const status = item.download_status || item.status || 'pending';
            if (stats2[status] !== undefined) stats2[status]++;
        });
        document.getElementById('statPending2').textContent = stats2.pending;
        document.getElementById('statDownloading2').textContent = stats2.downloading;
        document.getElementById('statPaused2').textContent = stats2.paused;
        document.getElementById('statCompleted2').textContent = stats2.completed;
        document.getElementById('statFailed2').textContent = stats2.failed;
    } catch (e) {
        container.innerHTML = `<div class="empty-msg">❌ Error loading stats: ${e.message}</div>`;
    }
}

// ============================================================
//  SEARCH
// ============================================================
async function performSearchAndBuild() {
    const term = document.getElementById('searchTerm').value.trim();
    const entity = document.getElementById('entityType').value;
    if (!term) return;
    const container = document.getElementById('treeContainer');
    container.innerHTML = '<div class="empty-msg"><i class="fas fa-spinner fa-pulse"></i> Searching...</div>';
    try {
        const results = await searchItunes(term, entity);
        if (!results.length) { container.innerHTML = '<div class="empty-msg">No results found.</div>'; return; }
        let topLevel = results.filter(r => r.wrapperType === 'artist');
        if (entity === 'album') topLevel = results.filter(r => r.wrapperType === 'collection');
        else if (entity === 'song') topLevel = results.filter(r => r.wrapperType === 'track');
        else topLevel = [...results.filter(r => r.wrapperType === 'artist'), ...results.filter(r => r
                .wrapperType === 'collection'), ...results.filter(r => r.wrapperType === 'track')];
        renderTree(container, topLevel);
    } catch (e) {
        container.innerHTML = `<div class="empty-msg">❌ Error: ${e.message}</div>`;
        console.error(e);
    }
}

// ============================================================
//  SET SELECTED METADATA (right panel)
// ============================================================
function setSelectedMetadata(item) {
    const rightPanelElement = document.getElementById('rightPanel');
    const type = item.wrapperType;
    const title = item.trackName || item.collectionName || item.artistName || '—';
    const artist = item.artistName || (type === 'artist' ? 'Artist Profile' : '—');
    const genre = item.primaryGenreName || '—';
    const date = item.releaseDate ? new Date(item.releaseDate).toLocaleDateString() : '—';
    let artwork = item.artworkUrl100 || item.artworkUrl60 || null;
    const mirrorHtml = renderMirrorButtons(item.mirrorUrls || {}, item);
    const lyricsHtml = (type === 'track' && item.lyrics) ? renderLyrics(item.lyrics) : '';

    let addHandler = '';
    let addButtonText = 'Add to Queue';
    if (type === 'track' && item.trackId) {
        addHandler =
            `addAllToQueueByType('track', '${cleanId(item.trackId)}', '${escapeHtml(title)}')`;
        addButtonText = 'Add Track';
    } else if (type === 'collection' && item.collectionId) {
        addHandler =
            `addAllToQueueByType('album', '${cleanId(item.collectionId)}', '${escapeHtml(title)}')`;
        addButtonText = 'Add All Tracks';
    } else if (type === 'artist' && item.artistId) {
        addHandler =
            `addAllToQueueByType('artist', '${cleanId(item.artistId)}', '${escapeHtml(title)}')`;
        addButtonText = 'Add All Albums & Tracks';
    }

    // Play button
    let playHandler = '';
    let playButton = '';
    if (type === 'track' && item.mirrorUrls?.audioUrl) {
        const audioRaw = item.mirrorUrls.audioUrl.url || item.mirrorUrls.audioUrl;
        if (audioRaw) {
            const proxied = getProxiedAudioUrl(audioRaw);
            if (proxied) {
                playHandler =
                    `playAudioUrl('${escapeHtml(proxied)}', '${escapeHtml(title)}', '${escapeHtml(artist)}', '${escapeHtml(artwork || '')}')`;
                playButton =
                    `<button class="btn btn-play" style="width:100%; justify-content:center; margin-bottom:4px;" onclick="${playHandler}" aria-label="Play Track"><i class="fas fa-play"></i> Play Track</button>`;
            }
        }
    }

    rightPanelElement.innerHTML = `
        <div class="props-card">
            ${artwork ? `<img src="${artwork}" class="props-artwork" alt="cover">` : `<div style="height:80px; background:#e1e1e1; margin-bottom:6px; display:flex;align-items:center;justify-content:center;"><i class="fas fa-database fa-2x" style="color:#aaa;"></i></div>`}
            <div class="props-title" onclick="routeToInternalLink('${type === 'collection' ? 'collection' : type}', '${item.collectionId || item.artistId || item.trackId}')">${escapeHtml(title)}</div>
            ${playButton}
            <div class="props-row"><span>Type</span><strong>${type.toUpperCase()}</strong></div>
            <div class="props-row"><span>Artist</span><strong>${escapeHtml(artist)}</strong></div>
            <div class="props-row"><span>Genre</span><strong>${escapeHtml(genre)}</strong></div>
            <div class="props-row"><span>Release</span><strong>${date}</strong></div>
            ${mirrorHtml ? `<div class="props-row"><span>Mirrors</span><strong>${mirrorHtml}</strong></div>` : ''}
            ${lyricsHtml ? `<div class="props-row" style="flex-direction:column;align-items:stretch;"><span>Lyrics</span><strong>${lyricsHtml}</strong></div>` : ''}
            <hr>
            <button class="btn btn-success" style="width:100%; justify-content:center;" onclick="${addHandler}" aria-label="${addButtonText}"><i class="fas fa-download"></i> ${addButtonText}</button>
        </div>
    `;
}

// ============================================================
//  INIT TABS
// ============================================================
function initTabs() {
    const tabs = document.querySelectorAll('.tab');
    const panes = { queue: document.getElementById('queue-tab'), browse: document.getElementById('browse-tab'),
        stats: document.getElementById('stats-tab'), admin: document.getElementById('admin-tab') };
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const tabId = tab.dataset.tab;
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            Object.values(panes).forEach(p => p.classList.remove('active-pane'));
            if (panes[tabId]) {
                panes[tabId].classList.add('active-pane');
            }
            if (tabId === 'queue') loadQueue();
            if (tabId === 'stats') loadStats();
            if (tabId === 'admin') loadUsersList();
        });
    });
}

// ============================================================
//  KEYBOARD SHORTCUTS
// ============================================================
document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
        e.preventDefault();
        if (document.querySelector('[data-tab="queue"].active')) loadQueue();
        else if (document.querySelector('[data-tab="stats"].active')) loadStats();
    }
    if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
        e.preventDefault();
        const searchInput = document.getElementById('searchTerm');
        if (searchInput) searchInput.focus();
    }
    if (e.key === 'Escape') {
        closeAllDrawers();
    }
});

// ============================================================
//  EVENT BINDINGS
// ============================================================
function initEventBindings() {
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.onclick = async () => {
            await logout();
            window.location.href = 'index.html';
        };
    }

    const doSearchBtn = document.getElementById('doSearchBtn');
    if (doSearchBtn) doSearchBtn.addEventListener('click', performSearchAndBuild);

    const refreshQueueBtn = document.getElementById('refreshQueueBtn');
    if (refreshQueueBtn) refreshQueueBtn.addEventListener('click', loadQueue);

    const clearFailedBtn = document.getElementById('clearFailedBtn');
    if (clearFailedBtn) clearFailedBtn.addEventListener('click', clearFailed);

    const retryFailedBtn = document.getElementById('retryFailedBtn');
    if (retryFailedBtn) retryFailedBtn.addEventListener('click', retryFailed);

    const exportQueueBtn = document.getElementById('exportQueueBtn');
    if (exportQueueBtn) exportQueueBtn.addEventListener('click', exportQueue);

    const searchTerm = document.getElementById('searchTerm');
    if (searchTerm) searchTerm.addEventListener('keypress', e => { if (e.key === 'Enter') performSearchAndBuild(); });

    const treeBulkAddBtn = document.getElementById('treeBulkAddBtn');
    if (treeBulkAddBtn) treeBulkAddBtn.addEventListener('click', addSelectedToQueue);

    const treeSelectAllBtn = document.getElementById('treeSelectAllBtn');
    if (treeSelectAllBtn) treeSelectAllBtn.addEventListener('click', treeSelectAll);

    const treeSelectNoneBtn = document.getElementById('treeSelectNoneBtn');
    if (treeSelectNoneBtn) treeSelectNoneBtn.addEventListener('click', treeSelectNone);

    const treeExpandAllBtn = document.getElementById('treeExpandAllBtn');
    if (treeExpandAllBtn) treeExpandAllBtn.addEventListener('click', treeExpandAll);

    const treeCollapseAllBtn = document.getElementById('treeCollapseAllBtn');
    if (treeCollapseAllBtn) treeCollapseAllBtn.addEventListener('click', treeCollapseAll);

    const queueBulkStartBtn = document.getElementById('queueBulkStartBtn');
    if (queueBulkStartBtn) queueBulkStartBtn.addEventListener('click', () => bulkUpdate('downloading'));

    const queueBulkPauseBtn = document.getElementById('queueBulkPauseBtn');
    if (queueBulkPauseBtn) queueBulkPauseBtn.addEventListener('click', () => bulkUpdate('paused'));

    const queueBulkResumeBtn = document.getElementById('queueBulkResumeBtn');
    if (queueBulkResumeBtn) queueBulkResumeBtn.addEventListener('click', () => bulkUpdate('downloading'));

    const queueBulkStopBtn = document.getElementById('queueBulkStopBtn');
    if (queueBulkStopBtn) queueBulkStopBtn.addEventListener('click', () => bulkUpdate('stopped'));

    const queueBulkRetryBtn = document.getElementById('queueBulkRetryBtn');
    if (queueBulkRetryBtn) queueBulkRetryBtn.addEventListener('click', () => bulkUpdate('pending'));

    const queueBulkDeleteBtn = document.getElementById('queueBulkDeleteBtn');
    if (queueBulkDeleteBtn) queueBulkDeleteBtn.addEventListener('click', bulkDelete);

    const queueSelectAllBtn = document.getElementById('queueSelectAllBtn');
    if (queueSelectAllBtn) queueSelectAllBtn.addEventListener('click', queueSelectAllVisible);

    const queueSelectNoneBtn = document.getElementById('queueSelectNoneBtn');
    if (queueSelectNoneBtn) queueSelectNoneBtn.addEventListener('click', queueSelectNone);

    const queueInvertBtn = document.getElementById('queueInvertBtn');
    if (queueInvertBtn) queueInvertBtn.addEventListener('click', queueInvertSelection);

    const queueStatusFilter = document.getElementById('queueStatusFilter');
    if (queueStatusFilter) queueStatusFilter.addEventListener('change', filterQueueItems);

    const queueSearchFilter = document.getElementById('queueSearchFilter');
    if (queueSearchFilter) queueSearchFilter.addEventListener('input', filterQueueItems);

    const queueSortBy = document.getElementById('queueSortBy');
    if (queueSortBy) queueSortBy.addEventListener('change', filterQueueItems);

    const clearQueueFilters = document.getElementById('clearQueueFilters');
    if (clearQueueFilters) clearQueueFilters.addEventListener('click', () => {
        document.getElementById('queueStatusFilter').value = 'all';
        document.getElementById('queueSearchFilter').value = '';
        document.getElementById('queueSortBy').value = 'id';
        filterQueueItems();
    });

    const resultFilter = document.getElementById('resultFilter');
    if (resultFilter) resultFilter.addEventListener('change', () => {
        const term = document.getElementById('searchTerm').value.trim();
        if (term) performSearchAndBuild();
    });

    if (menuToggle) {
        menuToggle.addEventListener('click', () => {
            rightPanel.classList.remove('open-drawer');
            leftSidebar.classList.toggle('open-drawer');
            layoutOverlay.classList.toggle('active', leftSidebar.classList.contains('open-drawer'));
        });
    }

    if (propsToggle) {
        propsToggle.addEventListener('click', () => {
            leftSidebar.classList.remove('open-drawer');
            rightPanel.classList.toggle('open-drawer');
            layoutOverlay.classList.toggle('active', rightPanel.classList.contains('open-drawer'));
        });
    }

    if (layoutOverlay) layoutOverlay.addEventListener('click', closeAllDrawers);
}

// ============================================================
//  USER MANAGEMENT
// ============================================================
async function loadUsersList() {
    try {
        const res = await loadUsers();
        const tbody = document.getElementById('userTableBody');
        if (tbody) {
            tbody.innerHTML = res.users.map(u => `
                <tr>
                    <td>${u.id}</td>
                    <td>${escapeHtml(u.username)}</td>
                    <td>${u.role}</td>
                    <td>${u.created_at}</td>
                    <td>
                        ${u.role !== 'admin' ? `<button onclick="handleDeleteUser(${u.id})" class="btn-sm btn-danger" aria-label="Delete User"><i class="fas fa-trash"></i></button>` : ''}
                    </td>
                </tr>
            `).join('');
        }
    } catch (e) {
        showToast('Failed to load users', true);
    }
}

window.handleDeleteUser = async (id) => {
    if (confirm('Are you sure you want to delete this user?')) {
        await deleteUser(id);
        loadUsersList();
    }
};

// ============================================================
//  BOOT
// ============================================================
window.addEventListener('load', async () => {
    const status = await checkAuthStatus();
    if (!status.logged_in) {
        window.location.href = 'index.html';
        return;
    }

    document.getElementById('userNameDisplay').textContent = status.user.username;
    if (status.user.role === 'admin') {
        const adminTab = document.getElementById('adminTabHead');
        if (adminTab) adminTab.style.display = 'flex';
    }

    initAudioPlayer();
    initTabs();
    initEventBindings();
    performSearchAndBuild();
    loadQueue();
    setInterval(loadQueue, 15000);
    setInterval(() => {
        if (document.querySelector('[data-tab="stats"].active')) loadStats();
    }, 30000);
});
