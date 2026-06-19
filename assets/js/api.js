const API_BASE = '';

async function apiCall(endpoint, method = 'GET', body = null) {
    if (!endpoint.startsWith('/api/')) {
        endpoint = '/api' + endpoint;
    }
    const url = `${API_BASE}${endpoint}`;
    const options = {
        method,
        headers: { 'Content-Type': 'application/json' }
    };
    if (body && (method === 'POST' || method === 'PUT' || method === 'DELETE')) {
        options.body = JSON.stringify(body);
    }
    const resp = await fetch(url, options);
    if (!resp.ok) {
        const text = await resp.text();
        let errorData;
        try {
            errorData = JSON.parse(text);
        } catch (e) {
            errorData = { error: text };
        }
        throw new Error(errorData.error || `HTTP ${resp.status}`);
    }
    return resp.json();
}

async function searchItunes(term, entity) {
    let url = `/search?term=${encodeURIComponent(term)}&limit=200&country=US`;
    if (entity !== 'all' && entity) {
        url += `&entity=${entity}`;
    } else {
        url += `&media=music`;
    }
    const data = await apiCall(url);
    return data.results || [];
}

async function fetchChildren(item, offset = 0, limit = 200) {
    try {
        let endpoint, params;
        const id = (item.artistId || item.collectionId || item.trackId).toString().replace(/^it_/, '');
        if (item.wrapperType === 'artist') {
            endpoint = '/lookup';
            params = `id=${id}&entity=album&limit=${limit}`;
        } else if (item.wrapperType === 'collection') {
            endpoint = '/lookup';
            params = `id=${id}&entity=song&limit=${limit}`;
        } else {
            return [];
        }
        const data = await apiCall(`${endpoint}?${params}`);
        const results = data.results || [];
        const childType = item.wrapperType === 'artist' ? 'collection' : 'track';
        return results.filter(r => r.wrapperType === childType);
    } catch (e) {
        console.warn('Hierarchy fetch error', e);
        return [];
    }
}

async function fetchAllChildren(item) {
    let all = [];
    let offset = 0;
    const pageSize = 200;
    let hasMore = true;
    while (hasMore) {
        try {
            const batch = await fetchChildren(item, offset, pageSize);
            if (batch.length === 0) {
                hasMore = false;
            } else {
                all = all.concat(batch);
                offset += pageSize;
                if (batch.length < pageSize) hasMore = false;
            }
        } catch (e) {
            console.warn('Batch error', e);
            hasMore = false;
        }
    }
    return all;
}
