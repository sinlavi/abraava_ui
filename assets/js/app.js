// ============================================================
//  CONFIG & STATE
// ============================================================
const PROXY_URL = '/ui/audio_proxy.php';

let currentTreeItems = [];
let selectedTreeIds = new Set();
let selectedQueueIds = new Set();
let globalLookupMap = new Map();
let expandedNodes = new Set();
let currentQueueItems = [];
let filteredQueueItems = [];
let modalResolve = null;

let audioPlayer = null;
let currentTrack = null;
let isPlaying = false;

// ============================================================
//  DOM REFS
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

// ============================================================
//  HELPERS
// ============================================================
function getProxiedAudioUrl(rawUrl) {
    if (!rawUrl) return null;
    return rawUrl.startsWith('http') ? PROXY_URL + '?url=' + encodeURIComponent(rawUrl) : rawUrl;
}

function escapeHtml(str) {
    if (!str) return '';
    return str.toString().replace(/[&<>]/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[m]));
}

function cleanId(id) {
    return id ? id.toString().replace(/^it_/, '') : null;
}

function cacheItem(item) {
    if (!item) return;
    if (item.artistId) globalLookupMap.set(`artist:${item.artistId}`, item);
    if (item.collectionId) globalLookupMap.set(`collection:${item.collectionId}`, item);
    if (item.trackId) globalLookupMap.set(`track:${item.trackId}`, item);
}

function showModal(title, body) {
    return new Promise((resolve) => {
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalBody').innerHTML = body;
        document.getElementById('modalOverlay').classList.add('active');
        modalResolve = resolve;
    });
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
            const icon = item.wrapperType === 'artist' ? 'fas fa-user' : (item.wrapperType === 'collection' ? 'fas fa-dot-circle' : 'fas fa-music');
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
                    `${Math.floor(item.trackTimeMillis/60000)}:${Math.floor((item.trackTimeMillis%60000)/1000).toString().padStart(2,'0')}` : '';
                extraInfo = duration ? `⏱ ${duration}` : '';
            }

            currentTreeItems.push({ key: itemKey, item, type: item.wrapperType, id: item.artistId || item.collectionId || item.trackId });

            const toggleSpan = document.createElement('div');
            toggleSpan.className = 'tree-toggle';
            toggleSpan.setAttribute('data-node-key', itemKey);
            toggleSpan.innerHTML = `
                <input type="checkbox" class="tree-checkbox" data-key="${itemKey}" aria-label="Select ${escapeHtml(displayName)}">
                <i class="${icon}" aria-hidden="true"></i>
                <span class="node-label">
                    <span>${escapeHtml(displayName)}</span>
                    ${extraInfo ? `<span class="sub-info">${extraInfo}</span>` : ''}
                </span>
                ${countInfo}
                <span class="node-actions">
                    <button class="tree-add-btn" data-key="${itemKey}" aria-label="Add ${escapeHtml(displayName)} to queue" title="Add to queue"><i class="fas fa-plus-circle" aria-hidden="true"></i></button>
                    <button class="tree-expand-btn" data-key="${itemKey}" aria-label="Expand ${escapeHtml(displayName)}" title="Expand"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
                </span>
            `;
            li.appendChild(toggleSpan);

            if (hasChildren) {
                const childrenContainer = document.createElement('ul');
                childrenContainer.className = 'tree-children';
                li.appendChild(childrenContainer);

                const expandBtn = toggleSpan.querySelector('.tree-expand-btn');
                expandBtn.onclick = async (e) => {
                    e.stopPropagation();
                    const isOpen = li.classList.contains('open');
                    if (!isOpen) {
                        if (!expandedNodes.has(itemKey)) {
                            childrenContainer.innerHTML = '<div class="tree-loading"><i class="fas fa-spinner fa-pulse"></i> Loading...</div>';
                            const kids = await fetchAllChildren(item);
                            childrenContainer.innerHTML = '';
                            if (kids.length) {
                                buildTree(kids, childrenContainer, depth + 1);
                                expandedNodes.add(itemKey);
                            } else {
                                childrenContainer.innerHTML = '<div style="padding:3px 10px; color:#999; font-size:9px;">↳ No child items found</div>';
                                expandedNodes.add(itemKey);
                            }
                        }
                        li.classList.add('open');
                        expandBtn.innerHTML = '<i class="fas fa-chevron-down"></i>';
                    } else {
                        li.classList.remove('open');
                        expandBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
                    }
                };
            }

            toggleSpan.onclick = (e) => {
                if (e.target.type === 'checkbox' || e.target.closest('.node-actions')) return;
                activateStructuralNode(itemKey, item);
            };

            const cb = toggleSpan.querySelector('.tree-checkbox');
            cb.onchange = (e) => {
                if (cb.checked) selectedTreeIds.add(itemKey);
                else selectedTreeIds.delete(itemKey);
                updateTreeBulkBar();
            };

            const addBtn = toggleSpan.querySelector('.tree-add-btn');
            addBtn.onclick = (e) => {
                e.stopPropagation();
                addSingleItemToQueue(item, addBtn);
            };

            container.appendChild(li);
        }
    }

    buildTree(filteredItems, ul);
    parentElement.innerHTML = '';
    parentElement.appendChild(ul);
    updateTreeBulkBar();
}

function updateTreeBulkBar() {
    const bar = document.getElementById('treeBulkBar');
    const count = selectedTreeIds.size;
    document.getElementById('treeSelectedCount').innerText = count;
    bar.style.display = count > 0 ? 'flex' : 'none';
}

function activateStructuralNode(itemKey, item) {
    document.querySelectorAll('.tree-toggle').forEach(el => el.classList.remove('active-node'));
    const element = document.querySelector(`[data-node-key="${itemKey}"]`);
    if (element) {
        element.classList.add('active-node');
        element.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }
    setSelectedMetadata(item);
    renderWorkspacePage(item);
    switchTab('browse');
}

// ============================================================
//  WORKSPACE & METADATA
// ============================================================
async function renderWorkspacePage(item) {
    const container = document.getElementById('browseWorkspace');
    const type = item.wrapperType;
    let freshItem = item;

    container.innerHTML = '<div class="empty-msg"><i class="fas fa-spinner fa-pulse"></i> Loading details...</div>';

    try {
        const id = cleanId(item.artistId || item.collectionId || item.trackId);
        const lookup = await apiCall(`/lookup?id=${id}`);
        if (lookup.results && lookup.results.length) freshItem = lookup.results[0];
    } catch (e) { console.warn('Lookup failed', e); }

    const mirrorHtml = renderMirrorButtons(freshItem.mirrorUrls || {}, freshItem);

    if (type === 'artist') {
        container.innerHTML = `
            <div class="pma-header">
                <h2><i class="fas fa-user"></i> ${escapeHtml(freshItem.artistName)}</h2>
                <button class="btn-sm btn-success" onclick="addAllToQueueByType('artist', '${cleanId(freshItem.artistId)}')" style="margin-left:auto;"><i class="fas fa-download"></i> Add All</button>
            </div>
            <div class="pma-doc-box">
                <div class="profile-container">
                    <div style="text-align:center; background:#e1e1e1; padding:20px; border:1px solid #ccc; display:flex; align-items:center; justify-content:center; width:100%; max-width:100px;">
                        <i class="fas fa-user-tie fa-4x" style="color:#888;"></i>
                    </div>
                    <div class="profile-meta-grid">
                        <div class="meta-block"><label>ID</label><div>${freshItem.artistId}</div></div>
                        <div class="meta-block"><label>Genre</label><div>${escapeHtml(freshItem.primaryGenreName || 'Unknown')}</div></div>
                        <div class="meta-block"><label>Link</label><div><a href="${freshItem.artistLinkUrl}" target="_blank">View Profile</a></div></div>
                    </div>
                </div>
            </div>
            <div class="pma-header"><h3><i class="fas fa-boxes"></i> Albums</h3></div>
            <div style="padding:0 10px 10px; overflow-y:auto; flex:1;">
                <table class="data-table" id="subAlbumTable">
                    <thead><tr><th>Album</th><th>Year</th><th>Tracks</th><th>Action</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        `;
        const kids = await fetchAllChildren(freshItem);
        const tbody = container.querySelector('#subAlbumTable tbody');
        tbody.innerHTML = kids.map(alb => `
            <tr>
                <td><span class="clickable-link" onclick="routeToInternalLink('collection', '${alb.collectionId}')">${escapeHtml(alb.collectionName)}</span></td>
                <td>${alb.releaseDate ? new Date(alb.releaseDate).getFullYear() : '—'}</td>
                <td>${alb.trackCount || 0}</td>
                <td><button class="btn-sm btn-success" onclick="addAllToQueueByType('album', '${alb.collectionId}')"><i class="fas fa-download"></i></button></td>
            </tr>
        `).join('') || '<tr><td colspan="4" class="empty-msg">No albums found.</td></tr>';

    } else if (type === 'collection') {
        container.innerHTML = `
            <div class="pma-header">
                <h2><i class="fas fa-dot-circle"></i> ${escapeHtml(freshItem.collectionName)}</h2>
                <button class="btn-sm btn-success" onclick="addAllToQueueByType('album', '${cleanId(freshItem.collectionId)}')" style="margin-left:auto;"><i class="fas fa-download"></i> Add All</button>
            </div>
            <div class="pma-doc-box">
                <div class="profile-container">
                    <div><img src="${freshItem.artworkUrl100 || ''}" style="width:100px; border:1px solid #ccc; padding:2px;" alt="Artwork"></div>
                    <div class="profile-meta-grid">
                        <div class="meta-block"><label>Artist</label><div><span class="clickable-link" onclick="routeToInternalLink('artist', '${freshItem.artistId}')">${escapeHtml(freshItem.artistName)}</span></div></div>
                        <div class="meta-block"><label>Tracks</label><div>${freshItem.trackCount || 0}</div></div>
                        <div class="meta-block"><label>Release</label><div>${freshItem.releaseDate ? new Date(freshItem.releaseDate).toLocaleDateString() : '—'}</div></div>
                    </div>
                </div>
            </div>
            <div class="pma-header"><h3><i class="fas fa-list-ol"></i> Tracks</h3></div>
            <div style="padding:0 10px 10px; overflow-y:auto; flex:1;">
                <table class="data-table" id="subTrackTable">
                    <thead><tr><th>#</th><th>Track</th><th>Duration</th><th>Action</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        `;
        const kids = await fetchAllChildren(freshItem);
        const tbody = container.querySelector('#subTrackTable tbody');
        tbody.innerHTML = kids.map(trk => `
            <tr>
                <td>${trk.trackNumber || 1}</td>
                <td><span class="clickable-link" onclick="routeToInternalLink('track', '${trk.trackId}')">${escapeHtml(trk.trackName)}</span></td>
                <td>${trk.trackTimeMillis ? Math.floor(trk.trackTimeMillis/60000) + ':' + Math.floor((trk.trackTimeMillis%60000)/1000).toString().padStart(2,'0') : '—'}</td>
                <td><button class="btn-sm btn-success" onclick="addAllToQueueByType('track', '${trk.trackId}')"><i class="fas fa-download"></i></button></td>
            </tr>
        `).join('') || '<tr><td colspan="4" class="empty-msg">No tracks found.</td></tr>';
    } else if (type === 'track') {
        container.innerHTML = `
            <div class="pma-header">
                <h2><i class="fas fa-music"></i> ${escapeHtml(freshItem.trackName)}</h2>
                <button class="btn-sm btn-success" onclick="addAllToQueueByType('track', '${cleanId(freshItem.trackId)}')" style="margin-left:auto;"><i class="fas fa-download"></i> Add to Queue</button>
            </div>
            <div class="pma-doc-box">
                <div class="profile-container">
                    <div><img src="${freshItem.artworkUrl100 || ''}" style="width:120px; border:1px solid #ccc; padding:2px;" alt="Artwork"></div>
                    <div class="profile-meta-grid">
                        <div class="meta-block"><label>Artist</label><div><span class="clickable-link" onclick="routeToInternalLink('artist', '${freshItem.artistId}')">${escapeHtml(freshItem.artistName)}</span></div></div>
                        <div class="meta-block"><label>Album</label><div><span class="clickable-link" onclick="routeToInternalLink('collection', '${freshItem.collectionId}')">${escapeHtml(freshItem.collectionName || 'Single')}</span></div></div>
                        <div class="meta-block"><label>Genre</label><div>${escapeHtml(freshItem.primaryGenreName)}</div></div>
                        ${mirrorHtml ? `<div class="meta-block" style="grid-column: 1/-1;"><label>Mirrors</label><div>${mirrorHtml}</div></div>` : ''}
                    </div>
                </div>
            </div>
        `;
    }
}

function setSelectedMetadata(item) {
    const panel = document.getElementById('rightPanel');
    const type = item.wrapperType;
    const title = item.trackName || item.collectionName || item.artistName || '—';
    const artist = item.artistName || '—';
    const artwork = item.artworkUrl100 || item.artworkUrl60 || null;

    panel.innerHTML = `
        <div class="props-card">
            ${artwork ? `<img src="${artwork}" class="props-artwork" alt="cover">` : `<div style="height:80px; background:#e1e1e1; margin-bottom:6px; display:flex;align-items:center;justify-content:center;"><i class="fas fa-music fa-2x" style="color:#aaa;"></i></div>`}
            <div class="props-title" onclick="routeToInternalLink('${type === 'collection' ? 'collection' : type}', '${item.collectionId || item.artistId || item.trackId}')">${escapeHtml(title)}</div>
            <div class="props-row"><span>Type</span><strong>${type.toUpperCase()}</strong></div>
            <div class="props-row"><span>Artist</span><strong>${escapeHtml(artist)}</strong></div>
            <hr>
            <button class="btn btn-success" style="width:100%; justify-content:center;" onclick="addAllToQueueByType('${type === 'collection' ? 'album' : type}', '${cleanId(item.artistId || item.collectionId || item.trackId)}')"><i class="fas fa-download"></i> Add to Queue</button>
        </div>
    `;
}

function renderMirrorButtons(mirrors, item) {
    let html = '<div class="mirror-group">';
    for (const [type, data] of Object.entries(mirrors)) {
        const url = data.url || data;
        if (type === 'audioUrl') {
            html += `<button class="mirror-btn" onclick="playAudioUrl('${escapeHtml(url)}', '${escapeHtml(item.trackName)}', '${escapeHtml(item.artistName)}', '${escapeHtml(item.artworkUrl60)}')" aria-label="Play audio mirror"><i class="fas fa-play"></i></button>`;
        }
        html += `<a href="${escapeHtml(url)}" target="_blank" class="mirror-btn" aria-label="Open ${type} mirror"><i class="fas fa-external-link-alt"></i> ${type}</a>`;
    }
    html += '</div>';
    return mirrors && Object.keys(mirrors).length ? html : '';
}

// ============================================================
//  AUDIO PLAYER
// ============================================================
function initAudioPlayer() {
    audioPlayer = new Audio();
    audioPlayer.volume = parseInt(apVolume.value) / 100;

    audioPlayer.ontimeupdate = () => {
        if (audioPlayer.duration) {
            const pct = (audioPlayer.currentTime / audioPlayer.duration) * 1000;
            apProgress.value = Math.min(pct, 1000);
            apCurrentTime.textContent = formatTime(audioPlayer.currentTime);
        }
    };

    audioPlayer.onloadedmetadata = () => {
        apDuration.textContent = formatTime(audioPlayer.duration);
    };

    audioPlayer.onplay = () => { isPlaying = true; apPlayBtn.innerHTML = '<i class="fas fa-pause"></i>'; };
    audioPlayer.onpause = () => { isPlaying = false; apPlayBtn.innerHTML = '<i class="fas fa-play"></i>'; };
    audioPlayer.onended = () => { isPlaying = false; apPlayBtn.innerHTML = '<i class="fas fa-play"></i>'; };

    apPlayBtn.onclick = () => {
        if (!audioPlayer.src) return;
        isPlaying ? audioPlayer.pause() : audioPlayer.play();
    };

    apProgress.oninput = () => {
        if (audioPlayer.duration) audioPlayer.currentTime = (apProgress.value / 1000) * audioPlayer.duration;
    };

    apVolume.oninput = () => { audioPlayer.volume = apVolume.value / 100; };

    apCloseBtn.onclick = () => {
        audioPlayer.pause();
        audioPlayer.src = '';
        apTitle.textContent = 'No track loaded';
        apArtist.textContent = '—';
        apArtwork.innerHTML = '<i class="fas fa-music"></i>';
        apArtwork.style.backgroundImage = 'none';
    };
}

function formatTime(secs) {
    const m = Math.floor(secs / 60);
    const s = Math.floor(secs % 60);
    return `${m}:${s.toString().padStart(2, '0')}`;
}

function playAudioUrl(url, title, artist, artwork) {
    audioPlayer.src = getProxiedAudioUrl(url);
    audioPlayer.play().catch(e => showToast('Playback failed: ' + e.message, true));
    apTitle.textContent = title;
    apArtist.textContent = artist;
    if (artwork) {
        apArtwork.innerHTML = '';
        apArtwork.style.backgroundImage = `url(${artwork})`;
        apArtwork.style.backgroundSize = 'cover';
    }
}

// ============================================================
//  QUEUE MANAGEMENT
// ============================================================
async function loadQueue() {
    try {
        const data = await apiCall('/download/queue');
        currentQueueItems = data.items || [];
        filterQueueItems();
    } catch (e) {
        console.error('Queue load failed', e);
    }
}

function filterQueueItems() {
    const status = document.getElementById('queueStatusFilter').value;
    const search = document.getElementById('queueSearchFilter').value.toLowerCase();

    filteredQueueItems = currentQueueItems.filter(item => {
        if (status !== 'all' && item.status !== status) return false;
        if (search && !((item.trackName || '').toLowerCase().includes(search) || (item.artistName || '').toLowerCase().includes(search))) return false;
        return true;
    });

    renderQueueTable(filteredQueueItems);
}

function renderQueueTable(items) {
    const tbody = document.getElementById('queueBody');
    tbody.innerHTML = items.map(item => `
        <tr data-id="${item.id}">
            <td class="checkbox-col"><input type="checkbox" class="queue-checkbox" value="${item.id}" aria-label="Select task ${item.id}" ${selectedQueueIds.has(item.id) ? 'checked' : ''}></td>
            <td><code>${item.id}</code></td>
            <td><strong>${escapeHtml(item.trackName || item.trackId)}</strong></td>
            <td>${escapeHtml(item.artistName || '—')}</td>
            <td>${item.quality}</td>
            <td><span class="status-badge status-${item.status}">${item.status}</span></td>
            <td class="action-buttons">
                <button onclick="updateSingleStatus(${item.id}, 'downloading')" aria-label="Start"><i class="fas fa-play"></i></button>
                <button onclick="updateSingleStatus(${item.id}, 'paused')" aria-label="Pause"><i class="fas fa-pause"></i></button>
                <button onclick="deleteSingleDownload(${item.id})" aria-label="Delete"><i class="fas fa-trash-alt"></i></button>
            </td>
            <td>—</td>
            <td>${item.errorMessage ? `<span class="error-msg">${escapeHtml(item.errorMessage)}</span>` : '—'}</td>
        </tr>
    `).join('') || '<tr><td colspan="9" class="empty-msg">No items in queue.</td></tr>';

    tbody.querySelectorAll('.queue-checkbox').forEach(cb => {
        cb.onchange = () => {
            const id = parseInt(cb.value);
            if (cb.checked) selectedQueueIds.add(id);
            else selectedQueueIds.delete(id);
            updateQueueBulkBar();
        };
    });

    updateQueueStats();
    updateQueueBulkBar();
}

function updateQueueBulkBar() {
    const bar = document.getElementById('queueBulkBar');
    if (!bar) return;
    const count = selectedQueueIds.size;
    document.getElementById('queueSelectedCount').innerText = count;
    bar.style.display = count > 0 ? 'flex' : 'none';
}

function updateQueueStats() {
    const stats = { pending: 0, downloading: 0, paused: 0, completed: 0, failed: 0 };
    currentQueueItems.forEach(i => stats[i.status] ? stats[i.status]++ : (stats[i.status] = 1));
    document.getElementById('statPending').textContent = stats.pending || 0;
    document.getElementById('statDownloading').textContent = stats.downloading || 0;
    document.getElementById('statPaused').textContent = stats.paused || 0;
    document.getElementById('statCompleted').textContent = stats.completed || 0;
    document.getElementById('statFailed').textContent = stats.failed || 0;
    document.getElementById('queueTabBadge').textContent = (stats.pending || 0) + (stats.downloading || 0);
}

// ============================================================
//  ACTIONS
// ============================================================
async function addSingleItemToQueue(item, button) {
    if (button) { button.disabled = true; button.innerHTML = '<i class="fas fa-spinner fa-pulse"></i>'; }
    try {
        const type = item.wrapperType === 'collection' ? 'albumId' : (item.wrapperType === 'artist' ? 'artistId' : 'trackId');
        await apiCall('/download/add', 'POST', { [type]: cleanId(item.artistId || item.collectionId || item.trackId) });
        showToast('Added to queue');
        loadQueue();
    } catch (e) { showToast(e.message, true); }
    finally { if (button) { button.disabled = false; button.innerHTML = '<i class="fas fa-plus-circle"></i>'; } }
}

window.addAllToQueueByType = async (type, id) => {
    const key = type === 'album' ? 'albumId' : (type === 'artist' ? 'artistId' : 'trackId');
    try {
        await apiCall('/download/add', 'POST', { [key]: id });
        showToast('Added to queue');
        loadQueue();
    } catch (e) { showToast(e.message, true); }
};

window.updateSingleStatus = async (id, status) => {
    await apiCall('/download/update', 'POST', { id: [id], status });
    loadQueue();
};

window.deleteSingleDownload = async (id) => {
    if (await showModal('Confirm Delete', 'Delete this task?')) {
        await apiCall('/download/delete', 'DELETE', { id: [id] });
        loadQueue();
    }
};

// ============================================================
//  INITIALIZATION
// ============================================================
function switchTab(target) {
    document.querySelectorAll('.tab').forEach(t => t.classList.toggle('active', t.dataset.tab === target));
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.toggle('active-pane', p.id === `${target}-tab`));
    if (target === 'stats') loadStats();
    if (target === 'admin') loadUsers();
}

async function loadStats() {
    const res = await apiCall('/stats');
    document.getElementById('statsWorkspace').innerHTML = `
        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:12px;">
            <div class="meta-block"><label>Tracks</label><div>${res.track_count}</div></div>
            <div class="meta-block"><label>Artists</label><div>${res.artist_count}</div></div>
            <div class="meta-block"><label>Albums</label><div>${res.album_count}</div></div>
            <div class="meta-block"><label>Users</label><div>${res.user_count}</div></div>
        </div>
    `;
}

window.routeToInternalLink = async (type, id) => {
    const lookup = await apiCall(`/lookup?id=${cleanId(id)}`);
    if (lookup.results && lookup.results[0]) {
        activateStructuralNode(`${type}:${id}`, lookup.results[0]);
    }
};

document.addEventListener('DOMContentLoaded', async () => {
    const user = await authPromise;
    if (!user) return;

    document.getElementById('userNameDisplay').textContent = user.username;
    if (user.role === 'admin') document.getElementById('adminTabHead').style.display = 'flex';

    initAudioPlayer();
    loadQueue();

    document.getElementById('doSearchBtn').onclick = async () => {
        const term = document.getElementById('searchTerm').value;
        const type = document.getElementById('entityType').value;
        const results = await searchItunes(term, type);
        renderTree(document.getElementById('treeContainer'), results);
    };

    document.querySelectorAll('.tab').forEach(t => t.onclick = () => switchTab(t.dataset.tab));

    document.getElementById('logoutBtn').onclick = logout;
    document.getElementById('refreshQueueBtn').onclick = loadQueue;

    document.getElementById('clearCacheBtn').onclick = async () => {
        await apiCall('/manage-data', 'POST');
        showToast('Cache cleared');
    };

    // Keyboard shortcuts
    document.addEventListener('keydown', e => {
        if (e.code === 'Space' && e.target.tagName !== 'INPUT') {
            e.preventDefault();
            apPlayBtn.click();
        }
    });

    // Row selection micro-UX
    document.getElementById('queueTable').onclick = (e) => {
        const row = e.target.closest('tr');
        if (row && !e.target.closest('button') && !e.target.closest('input')) {
            const cb = row.querySelector('.queue-checkbox');
            if (cb) {
                cb.checked = !cb.checked;
                cb.dispatchEvent(new Event('change'));
            }
        }
    };
});
