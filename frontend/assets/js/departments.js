/**
 * Departments Management JavaScript
 */

let deleteDeptId = null;

document.addEventListener('DOMContentLoaded', function() {
    requireAuth();
    updateUserUI();
    loadDepartments();

    // Search
    document.getElementById('search-input').addEventListener('input', function() {
        loadDepartments(this.value.trim());
    });

    // Form submit
    document.getElementById('dept-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        document.getElementById('modal-error').classList.add('hidden');

        const id = document.getElementById('dept_id').value;
        const data = {
            department_name: document.getElementById('dept_name').value.trim(),
            department_code: document.getElementById('dept_code').value.trim(),
            description: document.getElementById('dept_desc').value.trim(),
        };

        try {
            if (id) {
                data.id = id;
                const res = await api.post('/departments/update.php', data);
                if (res.data.status) {
                    showToast('Department updated!', 'success');
                    closeDeptModal();
                    loadDepartments();
                }
            } else {
                const res = await api.post('/departments/create.php', data);
                if (res.data.status) {
                    showToast('Department added!', 'success');
                    closeDeptModal();
                    loadDepartments();
                }
            }
        } catch (error) {
            if (error.response && error.response.data) {
                document.getElementById('modal-error').textContent = error.response.data.message;
                document.getElementById('modal-error').classList.remove('hidden');
            }
        }
    });

    // Delete confirm
    document.getElementById('confirm-delete-btn').addEventListener('click', async function() {
        if (!deleteDeptId) return;
        try {
            const res = await api.delete('/departments/delete.php', { data: { id: deleteDeptId } });
            if (res.data.status) {
                showToast('Department deleted!', 'success');
                closeDeleteModal();
                loadDepartments();
            }
        } catch (error) {
            showToast(error.response?.data?.message || 'Failed to delete', 'error');
        }
    });
});

async function loadDepartments(search = '') {
    document.getElementById('loading').classList.remove('hidden');
    document.getElementById('departments-grid').classList.add('hidden');
    document.getElementById('empty-state').classList.add('hidden');

    try {
        let url = '/departments/read.php';
        if (search) url += '?search=' + encodeURIComponent(search);
        const res = await api.get(url);

        if (res.data.status) {
            const grid = document.getElementById('departments-grid');
            if (res.data.departments.length === 0) {
                document.getElementById('empty-state').classList.remove('hidden');
            } else {
                grid.innerHTML = res.data.departments.map(d => `
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <span class="text-purple-600 font-bold text-sm">${escapeHtml(d.department_code.substring(0, 2).toUpperCase())}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button onclick='openEditModal(${JSON.stringify(d).replace(/'/g, "&#39;")})' class="p-1.5 text-blue-600 hover:bg-blue-50 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button onclick="confirmDelete(${d.id})" class="p-1.5 text-red-600 hover:bg-red-50 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                        <h3 class="font-semibold text-slate-700 mb-1">${escapeHtml(d.department_name)}</h3>
                        <p class="text-xs text-slate-400 mb-2">Code: ${escapeHtml(d.department_code)}</p>
                        <p class="text-sm text-slate-500">${escapeHtml(d.description || 'No description')}</p>
                    </div>
                `).join('');
                grid.classList.remove('hidden');
            }
        }
    } catch (error) {
        showToast('Failed to load departments', 'error');
    } finally {
        document.getElementById('loading').classList.add('hidden');
    }
}

function openAddModal() {
    document.getElementById('modal-title').textContent = 'Add Department';
    document.getElementById('dept_id').value = '';
    document.getElementById('dept_name').value = '';
    document.getElementById('dept_code').value = '';
    document.getElementById('dept_desc').value = '';
    document.getElementById('dept-modal').classList.remove('hidden');
}

function openEditModal(dept) {
    document.getElementById('modal-title').textContent = 'Edit Department';
    document.getElementById('dept_id').value = dept.id;
    document.getElementById('dept_name').value = dept.department_name;
    document.getElementById('dept_code').value = dept.department_code;
    document.getElementById('dept_desc').value = dept.description || '';
    document.getElementById('dept-modal').classList.remove('hidden');
}

function closeDeptModal() {
    document.getElementById('dept-modal').classList.add('hidden');
    document.getElementById('modal-error').classList.add('hidden');
}

function confirmDelete(id) {
    deleteDeptId = id;
    document.getElementById('delete-modal').classList.remove('hidden');
}

function closeDeleteModal() {
    deleteDeptId = null;
    document.getElementById('delete-modal').classList.add('hidden');
}
