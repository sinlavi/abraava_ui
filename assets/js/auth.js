async function apiCall(endpoint, method = 'GET', body = null) {
    if (!endpoint.startsWith('/api/')) {
        endpoint = '/api' + endpoint;
    }
    const options = { method, headers: { 'Content-Type': 'application/json' } };
    if (body && (method === 'POST' || method === 'PUT' || method === 'DELETE')) {
        options.body = JSON.stringify(body);
    }
    const resp = await fetch(endpoint, options);
    if (!resp.ok) {
        const text = await resp.text();
        throw new Error(text || `HTTP ${resp.status}`);
    }
    return resp.json();
}

function showToast(msg, isError = false, duration = 3000) {
    const toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.style.background = isError ? '#b91c1c' : '#333';
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), duration);
}

const loginBtn = document.getElementById('loginBtn');
if (loginBtn) {
    loginBtn.onclick = async () => {
        const username = document.getElementById('loginUsername').value;
        const password = document.getElementById('loginPassword').value;
        try {
            const res = await apiCall('/auth/login', 'POST', { username, password });
            if (res.success) {
                window.location.href = 'index.html';
            }
        } catch (e) {
            showToast('Login failed', true);
        }
    };
}

const signupBtn = document.getElementById('signupBtn');
if (signupBtn) {
    signupBtn.onclick = async () => {
        const username = document.getElementById('signupUsername').value;
        const password = document.getElementById('signupPassword').value;
        try {
            await apiCall('/auth/signup', 'POST', { username, password });
            showToast('Signup successful! Please login.');
            setTimeout(() => window.location.href = 'login.html', 1500);
        } catch (e) {
            showToast('Signup failed: ' + e.message, true);
        }
    };
}
