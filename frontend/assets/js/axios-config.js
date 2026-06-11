/**
 * Axios Configuration
 * Base API configuration with CSRF token interceptor
 */

const API_BASE = '/student-information-system/backend/api';

// Create axios instance with defaults
const api = axios.create({
    baseURL: API_BASE,
    headers: {
        'Content-Type': 'application/json',
    },
    timeout: 30000,
});

// Request interceptor — attach CSRF token
api.interceptors.request.use(
    (config) => {
        const csrfToken = localStorage.getItem('csrf_token');
        if (csrfToken) {
            config.headers['X-CSRF-Token'] = csrfToken;
        }
        return config;
    },
    (error) => Promise.reject(error)
);

// Response interceptor — handle auth errors
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 401) {
            localStorage.removeItem('sis_user');
            localStorage.removeItem('csrf_token');
            if (!window.location.pathname.includes('login.html') && !window.location.pathname.includes('register.html')) {
                window.location.href = '/student-information-system/frontend/pages/login.html';
            }
        }
        return Promise.reject(error);
    }
);
