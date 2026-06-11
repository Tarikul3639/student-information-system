/**
 * Students Management JavaScript
 * Handles CRUD, search, pagination, sorting, CSV export
 */

let currentPage = 1;
let currentSort = 'created_at';
let currentSortOrder = 'DESC';
let currentSearch = '';
let currentDeptFilter = '';
let deleteStudentId = null;

document.addEventListener('DOMContentLoaded', function() {
    requireAuth();
    updateUserUI();
    loadDepartments();
    loadStudents();

    // Search debounce
    let searchTimeout;
    document.getElementById('search-input').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentSearch = this.value.trim();
            currentPage = 1;
            loadStudents();
        }, 300);
    });

    // Department filter
    document.getElementById('dept-filter').addEventListener('change', function() {
        currentDeptFilter = this.value;
        currentPage = 1;
        loadStudents();
    });
});

async function loadStudents() {
    const loading = document.getElementById('loading');
    const tableContainer = document.getElementById('students-table-container');
    const emptyState = document.getElementById('empty-state');

    loading.classList.remove('hidden');
    tableContainer.classList.add('hidden');
    emptyState.classList.add('hidden');

    try {
        const params = new URLSearchParams({
            page: currentPage,
            per_page: 10,
            sort_by: currentSort,
            sort_order: currentSortOrder,
        });

        if (currentSearch) params.append('search', currentSearch);
        if (currentDeptFilter) params.append('department', currentDeptFilter);

        const response = await api.get(`/students/read.php?${params}`);

        if (response.data.status) {
            const { students, pagination } = response.data;

            if (students.length === 0) {
                emptyState.classList.remove('hidden');
            } else {
                renderStudents(students);
                renderPagination(pagination);
                tableContainer.classList.remove('hidden');
            }
        }
    } catch (error) {
        console.error('Load students error:', error);
        showToast('Failed to load students', 'error');
    } finally {
        loading.classList.add('hidden');
    }
}

function renderStudents(students) {
    const tbody = document.getElementById('students-tbody');
    tbody.innerHTML = students.map(s => `
        <tr class="table-row">
            <td class="px-4 py-3 text-sm font-medium text-slate-700">${escapeHtml(s.student_id)}</td>
            <td class="px-4 py-3">
                ${s.photo
                    ? `<img src="/student-information-system/backend/uploads/${escapeHtml(s.photo)}" class="w-8 h-8 rounded-full object-cover" alt="Photo">`
                    : `<div class="w-8 h-8 bg-slate-200 rounded-full flex items-center justify-center text-xs font-bold text-slate-500">${escapeHtml(s.full_name).charAt(0).toUpperCase()}</div>`
                }
            </td>
            <td class="px-4 py-3 text-sm text-slate-700">${escapeHtml(s.full_name)}</td>
            <td class="px-4 py-3 text-sm text-slate-500">${escapeHtml(s.department_name || 'N/A')}</td>
            <td class="px-4 py-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${
                    s.gender === 'Male' ? 'bg-blue-100 text-blue-700' :
                    s.gender === 'Female' ? 'bg-pink-100 text-pink-700' :
                    'bg-slate-100 text-slate-700'
                }">${escapeHtml(s.gender)}</span>
            </td>
            <td class="px-4 py-3 text-sm text-slate-500">${escapeHtml(s.email || 'N/A')}</td>
            <td class="px-4 py-3 text-sm text-slate-500">${escapeHtml(s.phone || 'N/A')}</td>
            <td class="px-4 py-3">
                <div class="flex items-center gap-1">
                    <a href="/student-information-system/frontend/pages/edit-student.html?id=${s.id}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <button onclick="confirmDelete(${s.id})" class="p-1.5 text-red-600 hover:bg-red-50 rounded" title="Delete">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function renderPagination(pagination) {
    const info = document.getElementById('pagination-info');
    const btns = document.getElementById('pagination-btns');

    const start = (pagination.page - 1) * pagination.per_page + 1;
    const end = Math.min(pagination.page * pagination.per_page, pagination.total);
    info.textContent = `Showing ${start}-${end} of ${pagination.total} students`;

    let buttons = '';
    if (pagination.page > 1) {
        buttons += `<button onclick="goToPage(${pagination.page - 1})" class="px-3 py-1 border border-slate-300 rounded text-sm hover:bg-slate-50">Previous</button>`;
    }
    for (let i = 1; i <= pagination.total_pages; i++) {
        if (i === pagination.page) {
            buttons += `<button class="px-3 py-1 bg-primary text-white rounded text-sm">${i}</button>`;
        } else {
            buttons += `<button onclick="goToPage(${i})" class="px-3 py-1 border border-slate-300 rounded text-sm hover:bg-slate-50">${i}</button>`;
        }
    }
    if (pagination.page < pagination.total_pages) {
        buttons += `<button onclick="goToPage(${pagination.page + 1})" class="px-3 py-1 border border-slate-300 rounded text-sm hover:bg-slate-50">Next</button>`;
    }
    btns.innerHTML = buttons;
}

function goToPage(page) {
    currentPage = page;
    loadStudents();
}

function sortBy(column) {
    if (currentSort === column) {
        currentSortOrder = currentSortOrder === 'ASC' ? 'DESC' : 'ASC';
    } else {
        currentSort = column;
        currentSortOrder = 'ASC';
    }
    loadStudents();
}

async function loadDepartments() {
    try {
        const response = await api.get('/departments/read.php');
        if (response.data.status) {
            const select = document.getElementById('dept-filter');
            response.data.departments.forEach(d => {
                const option = document.createElement('option');
                option.value = d.id;
                option.textContent = d.department_name;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Load departments error:', error);
    }
}

// ========================
// Delete Modal
// ========================
function confirmDelete(id) {
    deleteStudentId = id;
    document.getElementById('delete-modal').classList.remove('hidden');
}

function closeDeleteModal() {
    deleteStudentId = null;
    document.getElementById('delete-modal').classList.add('hidden');
}

document.getElementById('confirm-delete-btn').addEventListener('click', async function() {
    if (!deleteStudentId) return;

    try {
        const response = await api.delete('/students/delete.php', {
            data: { id: deleteStudentId }
        });

        if (response.data.status) {
            showToast('Student deleted successfully', 'success');
            closeDeleteModal();
            loadStudents();
        }
    } catch (error) {
        showToast('Failed to delete student', 'error');
    }
});

// ========================
// CSV Export
// ========================
async function exportCSV() {
    try {
        const params = new URLSearchParams({
            page: 1,
            per_page: 10000,
            sort_by: currentSort,
            sort_order: currentSortOrder,
        });
        if (currentSearch) params.append('search', currentSearch);
        if (currentDeptFilter) params.append('department', currentDeptFilter);

        const response = await api.get(`/students/read.php?${params}`);
        if (response.data.status && response.data.students.length > 0) {
            const students = response.data.students;
            const headers = ['Student ID', 'Full Name', 'Department', 'Gender', 'Date of Birth', 'Email', 'Phone', 'Address'];
            const rows = students.map(s => [
                s.student_id,
                s.full_name,
                s.department_name || '',
                s.gender,
                s.dob || '',
                s.email || '',
                s.phone || '',
                s.address || ''
            ]);

            let csv = headers.join(',') + '\n';
            rows.forEach(row => {
                csv += row.map(cell => '"' + String(cell).replace(/"/g, '""') + '"').join(',') + '\n';
            });

            const blob = new Blob([csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'students_export.csv';
            a.click();
            URL.revokeObjectURL(url);

            showToast('CSV exported successfully', 'success');
        } else {
            showToast('No data to export', 'error');
        }
    } catch (error) {
        showToast('Failed to export CSV', 'error');
    }
}
