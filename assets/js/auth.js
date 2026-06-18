async function signup(username, password) {
    return await apiCall('/auth/signup', 'POST', { username, password });
}

async function login(username, password) {
    const res = await apiCall('/auth/login', 'POST', { username, password });
    if (res.success) {
        return res.user;
    }
    throw new Error('Login failed');
}

async function logout() {
    return await apiCall('/auth/logout', 'POST');
}

async function checkAuthStatus() {
    try {
        const res = await apiCall('/auth/status');
        return res;
    } catch (e) {
        return { logged_in: false };
    }
}

async function loadUsers() {
    return await apiCall('/users');
}

async function deleteUser(id) {
    return await apiCall('/users', 'DELETE', { id });
}
