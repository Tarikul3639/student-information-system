/**
 * Authentication JavaScript
 * Handles login, register, logout, and CSRF token management
 */

// ========================
// Toast Notifications
// ========================
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
    const icon = type === 'success'
        ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
        : type === 'error'
        ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>'
        : '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';

    toast.className = `toast flex items-center gap-3 px-4 py-3 ${bgColor} text-white rounded-lg shadow-lg min-w-[280px]`;
    toast.innerHTML = `${icon}<span class="text-sm font-medium">${escapeHtml(message)}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('hiding');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ========================
// Error Display
// ========================
function showErrors(errors) {
    const container = document.getElementById('error-container');
    const list = document.getElementById('error-list');
    if (!container || !list) return;

    list.innerHTML = '';
    if (Array.isArray(errors)) {
        errors.forEach(err => {
            const li = document.createElement('li');
            li.textContent = err;
            list.appendChild(li);
        });
    } else {
        const li = document.createElement('li');
        li.textContent = errors;
        list.appendChild(li);
    }
    container.classList.remove('hidden');
}

function hideErrors() {
    const container = document.getElementById('error-container');
    if (container) container.classList.add('hidden');
}

// ========================
// HTML Escape (XSS prevention)
// ========================
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ========================
// Login Handler
// ========================
const loginForm = document.getElementById('login-form');
if (loginForm) {
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        hideErrors();

        const loginInput = document.getElementById('login-input').value.trim();
        const password = document.getElementById('password').value;
        const btn = document.getElementById('login-btn');
        const spinner = document.getElementById('login-spinner');

        // Get CSRF token first
        try {
            const csrfRes = await api.get('/auth/csrf.php');
            if (csrfRes.data.csrf_token) {
                localStorage.setItem('csrf_token', csrfRes.data.csrf_token);
            }
        } catch (err) {
            console.log('CSRF fetch failed, continuing...');
        }

        btn.disabled = true;
        btn.querySelector('span').textContent = 'Signing in...';
        spinner.classList.remove('hidden');

        try {
            const response = await api.post('/auth/login.php', {
                email: loginInput,
                password: password,
            });

            if (response.data.status) {
                localStorage.setItem('sis_user', JSON.stringify(response.data.user));
                if (response.data.csrf_token) {
                    localStorage.setItem('csrf_token', response.data.csrf_token);
                }
                showToast('Login successful! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = '/student-information-system/frontend/pages/dashboard.html';
                }, 500);
            }
        } catch (error) {
            btn.disabled = false;
            btn.querySelector('span').textContent = 'Sign In';
            spinner.classList.add('hidden');

            if (error.response && error.response.data) {
                const data = error.response.data;
                if (data.errors) {
                    showErrors(data.errors);
                } else {
                    showErrors([data.message || 'Login failed']);
                }
            } else {
                showErrors(['Network error. Please try again.']);
            }
        }
    });
}

// ========================
// Register Handler
// ========================
const registerForm = document.getElementById('register-form');
if (registerForm) {
    registerForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        hideErrors();

        const data = {
            full_name: document.getElementById('full_name').value.trim(),
            username: document.getElementById('username').value.trim(),
            email: document.getElementById('email').value.trim(),
            password: document.getElementById('password').value,
            confirm_password: document.getElementById('confirm_password').value,
        };

        const btn = document.getElementById('register-btn');
        const spinner = document.getElementById('register-spinner');

        // Client-side validation
        const errors = [];
        if (data.password.length < 8) errors.push('Password must be at least 8 characters');
        if (data.password !== data.confirm_password) errors.push('Passwords do not match');
        if (!data.full_name) errors.push('Full name is required');
        if (!data.username) errors.push('Username is required');
        if (!data.email) errors.push('Email is required');

        if (errors.length > 0) {
            showErrors(errors);
            return;
        }

        // Get CSRF token first
        try {
            const csrfRes = await api.get('/auth/csrf.php');
            if (csrfRes.data.csrf_token) {
                localStorage.setItem('csrf_token', csrfRes.data.csrf_token);
            }
        } catch (err) {
            console.log('CSRF fetch failed, continuing...');
        }

        btn.disabled = true;
        btn.querySelector('span').textContent = 'Creating Account...';
        spinner.classList.remove('hidden');

        try {
            const response = await api.post('/auth/register.php', data);

            if (response.data.status) {
                showToast('Registration successful! Redirecting to login...', 'success');
                setTimeout(() => {
                    window.location.href = '/student-information-system/frontend/pages/login.html';
                }, 1500);
            }
        } catch (error) {
            btn.disabled = false;
            btn.querySelector('span').textContent = 'Create Account';
            spinner.classList.add('hidden');

            if (error.response && error.response.data) {
                const data = error.response.data;
                if (data.errors) {
                    showErrors(data.errors);
                } else {
                    showErrors([data.message || 'Registration failed']);
                }
            } else {
                showErrors(['Network error. Please try again.']);
            }
        }
    });
}

// ========================
// Logout Handler
// ========================
async function logout() {
    try {
        await api.post('/auth/logout.php');
    } catch (err) {
        console.log('Logout API error:', err);
    }

    localStorage.removeItem('sis_user');
    localStorage.removeItem('csrf_token');
    window.location.href = '/student-information-system/frontend/pages/login.html';
}

// ========================
// Auth Guard — for protected pages
// ========================
function requireAuth() {
    const user = localStorage.getItem('sis_user');
    if (!user) {
        window.location.href = '/student-information-system/frontend/pages/login.html';
        return null;
    }
    return JSON.parse(user);
}

// ========================
// Update UI with User Info
// ========================
function updateUserUI() {
    const user = JSON.parse(localStorage.getItem('sis_user') || '{}');
    const nameEl = document.getElementById('user-name');
    const usernameEl = document.getElementById('user-username');
    if (nameEl && user.full_name) nameEl.textContent = user.full_name;
    if (usernameEl && user.username) usernameEl.textContent = '@' + user.username;
}
