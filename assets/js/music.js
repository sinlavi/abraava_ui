// ============================================================
//  CONFIG & STATE
// ============================================================
const API_BASE = '';
let currentTrack = null;
let audioPlayer = new Audio();
let isPlaying = false;

// ============================================================
//  CORE UTILS
// ============================================================
async function apiCall(endpoint, method = 'GET', body = null) {
    if (!endpoint.startsWith('/api/')) endpoint = '/api' + endpoint;
    const options = { method, headers: { 'Content-Type': 'application/json' } };
    if (body) options.body = JSON.stringify(body);
    const resp = await fetch(endpoint, options);
    if (!resp.ok) throw new Error(await resp.text() || `HTTP ${resp.status}`);
    return resp.json();
}

function showToast(msg, isError = false) {
    const toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.style.background = isError ? '#b91c1c' : '#333';
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[m]));
}

// ============================================================
//  AUTH
// ============================================================
async function checkAuthStatus() {
    try {
        const res = await apiCall('/auth/status');
        if (res.logged_in) {
            document.getElementById('userNameDisplay').textContent = res.user.username;
            loadPlaylists();
        } else {
            window.location.href = 'login.html';
        }
    } catch (e) {
        window.location.href = 'login.html';
    }
}

document.getElementById('logoutBtn').onclick = async () => {
    await apiCall('/auth/logout', 'POST');
    window.location.href = 'login.html';
};

// ============================================================
//  PLAYER LOGIC
// ============================================================
function playTrack(item) {
    const audioUrl = item.mirrorUrls?.audioUrl?.url || item.previewUrl;
    if (!audioUrl) {
        showToast('No audio available', true);
        return;
    }

    currentTrack = item;
    audioPlayer.src = audioUrl;
    audioPlayer.play();
    isPlaying = true;

    updatePlayerUI();
    loadComments(item.trackId);
}

function updatePlayerUI() {
    if (!currentTrack) return;
    document.getElementById('apTitle').textContent = currentTrack.trackName;
    document.getElementById('apArtist').textContent = currentTrack.artistName;
    document.getElementById('apPlayBtn').innerHTML = isPlaying ? '<i class="fas fa-pause"></i>' : '<i class="fas fa-play"></i>';

    const artwork = currentTrack.artworkUrl100 || currentTrack.artworkUrl60;
    const apArtwork = document.getElementById('apArtwork');
    if (artwork) {
        apArtwork.innerHTML = '';
        apArtwork.style.backgroundImage = `url(${artwork})`;
        apArtwork.style.backgroundSize = 'cover';
    }

    document.getElementById('commentSection').style.display = 'block';
}

document.getElementById('apPlayBtn').onclick = () => {
    if (!audioPlayer.src) return;
    if (isPlaying) {
        audioPlayer.pause();
        isPlaying = false;
    } else {
        audioPlayer.play();
        isPlaying = true;
    }
    updatePlayerUI();
};

audioPlayer.ontimeupdate = () => {
    if (audioPlayer.duration) {
        const pct = (audioPlayer.currentTime / audioPlayer.duration) * 1000;
        document.getElementById('apProgress').value = pct;
        document.getElementById('apCurrentTime').textContent = formatTime(audioPlayer.currentTime);
        document.getElementById('apDuration').textContent = formatTime(audioPlayer.duration);
    }
};

function formatTime(secs) {
    const m = Math.floor(secs / 60);
    const s = Math.floor(secs % 60);
    return `${m}:${s.toString().padStart(2, '0')}`;
}

// ============================================================
//  PLAYLISTS
// ============================================================
async function loadPlaylists() {
    const res = await apiCall('/playlists');
    const list = document.getElementById('playlistList');
    list.innerHTML = res.playlists.map(p => `
        <div class="tree-toggle" onclick="loadPlaylistTracks(${p.id})">
            <i class="fas fa-list"></i>
            <span class="node-label">${escapeHtml(p.name)}</span>
        </div>
    `).join('');
}

document.getElementById('createPlaylistBtn').onclick = async () => {
    const name = prompt('Playlist Name:');
    if (name) {
        await apiCall('/playlists', 'POST', { name });
        loadPlaylists();
    }
};

async function loadPlaylistTracks(id) {
    const res = await apiCall(`/playlists/tracks?playlist_id=${id}`);
    if (res.tracks.length === 0) {
        showToast('Playlist is empty');
        return;
    }
    // Lookup tracks and display in workspace
    const tracksRes = await apiCall(`/lookup?id=${res.tracks.join(',')}`);
    renderTrackList(tracksRes.results);
}

function renderTrackList(tracks) {
    const container = document.getElementById('browseWorkspace');
    container.innerHTML = `
        <table class="data-table">
            <thead><tr><th>#</th><th>Track</th><th>Artist</th><th>Action</th></tr></thead>
            <tbody>
                ${tracks.map((t, i) => `
                    <tr>
                        <td>${i+1}</td>
                        <td onclick='playTrack(${JSON.stringify(t)})' style="cursor:pointer; color:#235a81; font-weight:bold;">${escapeHtml(t.trackName)}</td>
                        <td>${escapeHtml(t.artistName)}</td>
                        <td>
                            <button onclick='addToPlaylistPrompt(${t.trackId})' class="btn-sm"><i class="fas fa-plus"></i></button>
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

window.addToPlaylistPrompt = async (trackId) => {
    const res = await apiCall('/playlists');
    if (res.playlists.length === 0) {
        showToast('Create a playlist first', true);
        return;
    }
    const pid = prompt("Enter Playlist ID:\n" + res.playlists.map(p => `${p.id}: ${p.name}`).join('\n'));
    if (pid) {
        await apiCall('/playlists/tracks', 'POST', { playlist_id: pid, track_id: trackId });
        showToast('Added to playlist');
    }
};

// ============================================================
//  SEARCH
// ============================================================
document.getElementById('doSearchBtn').onclick = async () => {
    const term = document.getElementById('searchTerm').value;
    const res = await apiCall(`/search?term=${encodeURIComponent(term)}&entity=song`);
    renderTrackList(res.results);
};

// ============================================================
//  COMMENTS
// ============================================================
async function loadComments(trackId) {
    const res = await apiCall(`/comments?track_id=${trackId}`);
    const list = document.getElementById('commentList');
    list.innerHTML = res.comments.map(c => `
        <div class="comment-item">
            <div class="comment-user">${escapeHtml(c.username)} <span style="font-weight:normal; color:#888;">${c.created_at}</span></div>
            <div class="comment-text">${escapeHtml(c.content)}</div>
        </div>
    `).join('');
}

document.getElementById('submitCommentBtn').onclick = async () => {
    if (!currentTrack) return;
    const content = document.getElementById('commentInput').value;
    if (!content) return;
    await apiCall('/comments', 'POST', { track_id: currentTrack.trackId, content });
    document.getElementById('commentInput').value = '';
    loadComments(currentTrack.trackId);
};

// ============================================================
//  BOOT
// ============================================================
checkAuthStatus();
