// Authentication and Session Management
async function checkAuthStatus() {
    try {
        const res = await apiCall('/auth/status');
        const isLoginPage = window.location.pathname.endsWith('index.html') || window.location.pathname.endsWith('/');

        if (res.logged_in) {
            if (isLoginPage) {
                window.location.href = 'music.html';
            }
            return res.user;
        } else {
            if (!isLoginPage) {
                window.location.href = 'index.html';
            }
        }
    } catch (e) {
        console.error('Auth status check failed', e);
    }
    return null;
}

async function login(username, password, button) {
    if (button) {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Logging in...';
    }
    try {
        const res = await apiCall('/auth/login', 'POST', { username, password });
        if (res.success) {
            window.location.href = 'music.html';
        }
    } catch (e) {
        showToast(e.message, true);
    } finally {
        if (button) {
            button.disabled = false;
            button.textContent = 'Login';
        }
    }
}

async function signup(username, password, button) {
    if (button) {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Signing up...';
    }
    try {
        await apiCall('/auth/signup', 'POST', { username, password });
        showToast('Signup successful! Please login.');
        const switchToLogin = document.getElementById('switchToLogin');
        if (switchToLogin) switchToLogin.click();
    } catch (e) {
        showToast(e.message, true);
    } finally {
        if (button) {
            button.disabled = false;
            button.textContent = 'Signup';
        }
    }
}

async function logout() {
    await apiCall('/auth/logout', 'POST');
    window.location.href = 'index.html';
}

function showToast(msg, isError = false, duration = 3000) {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.textContent = msg;
    toast.style.background = isError ? '#b91c1c' : '#333';
    toast.classList.add('show');
    clearTimeout(toast._timeout);
    toast._timeout = setTimeout(() => toast.classList.remove('show'), duration);
}

// Initialize Auth listeners if on index.html
document.addEventListener('DOMContentLoaded', () => {
    const loginBtn = document.getElementById('loginBtn');
    const signupBtn = document.getElementById('signupBtn');

    if (loginBtn) {
        loginBtn.onclick = () => {
            const u = document.getElementById('loginUsername').value;
            const p = document.getElementById('loginPassword').value;
            login(u, p, loginBtn);
        };
    }

    if (signupBtn) {
        signupBtn.onclick = () => {
            const u = document.getElementById('signupUsername').value;
            const p = document.getElementById('signupPassword').value;
            signup(u, p, signupBtn);
        };
    }

    const switchToSignup = document.getElementById('switchToSignup');
    const switchToLogin = document.getElementById('switchToLogin');
    const loginBox = document.getElementById('loginBox');
    const signupBox = document.getElementById('signupBox');

    if (switchToSignup) {
        switchToSignup.onclick = () => {
            loginBox.style.display = 'none';
            signupBox.style.display = 'block';
        };
    }
    if (switchToLogin) {
        switchToLogin.onclick = () => {
            signupBox.style.display = 'none';
            loginBox.style.display = 'block';
        };
    }
});

const authPromise = checkAuthStatus();
