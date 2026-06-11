/**
 * Profile Management JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    requireAuth();
    updateUserUI();
    loadProfile();

    // Profile form
    document.getElementById('profile-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        hideErrors('profile-error', 'profile-error-list');

        const data = {
            full_name: document.getElementById('full_name').value.trim(),
            username: document.getElementById('username').value.trim(),
            email: document.getElementById('email').value.trim(),
        };

        try {
            const res = await api.post('/profile/update.php', data);
            if (res.data.status) {
                showToast('Profile updated!', 'success');
                // Update local storage
                const user = JSON.parse(localStorage.getItem('sis_user') || '{}');
                user.full_name = data.full_name;
                user.username = data.username;
                user.email = data.email;
                localStorage.setItem('sis_user', JSON.stringify(user));
                updateUserUI();
                loadProfile();
            }
        } catch (error) {
            if (error.response && error.response.data) {
                const data = error.response.data;
                if (data.errors) showErrors('profile-error', 'profile-error-list', data.errors);
                else showErrors('profile-error', 'profile-error-list', [data.message]);
            }
        }
    });

    // Password form
    document.getElementById('password-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        hideErrors('password-error', 'password-error-list');

        const data = {
            current_password: document.getElementById('current_password').value,
            new_password: document.getElementById('new_password').value,
            confirm_password: document.getElementById('confirm_password').value,
        };

        if (data.new_password !== data.confirm_password) {
            showErrors('password-error', 'password-error-list', ['Passwords do not match']);
            return;
        }

        try {
            const res = await api.post('/profile/change-password.php', data);
            if (res.data.status) {
                showToast('Password changed!', 'success');
                document.getElementById('password-form').reset();
            }
        } catch (error) {
            if (error.response && error.response.data) {
                const data = error.response.data;
                if (data.errors) showErrors('password-error', 'password-error-list', data.errors);
                else showErrors('password-error', 'password-error-list', [data.message]);
            }
        }
    });
});

async function loadProfile() {
    try {
        const res = await api.get('/profile/get.php');
        if (res.data.status) {
            const user = res.data.user;
            document.getElementById('profile-name').textContent = user.full_name;
            document.getElementById('profile-username').textContent = '@' + user.username;
            document.getElementById('profile-avatar').textContent = user.full_name.charAt(0).toUpperCase();
            document.getElementById('user-avatar').textContent = user.full_name.charAt(0).toUpperCase();

            document.getElementById('full_name').value = user.full_name;
            document.getElementById('username').value = user.username;
            document.getElementById('email').value = user.email;

            const date = new Date(user.created_at);
            document.getElementById('created_at').textContent = date.toLocaleDateString('en-US', {
                year: 'numeric', month: 'long', day: 'numeric'
            });
        }
    } catch (error) {
        showToast('Failed to load profile', 'error');
    }
}

function showErrors(containerId, listId, errors) {
    const container = document.getElementById(containerId);
    const list = document.getElementById(listId);
    if (!container || !list) return;
    list.innerHTML = '';
    errors.forEach(err => {
        const li = document.createElement('li');
        li.textContent = err;
        list.appendChild(li);
    });
    container.classList.remove('hidden');
}

function hideErrors(containerId, listId) {
    const container = document.getElementById(containerId);
    if (container) container.classList.add('hidden');
}
