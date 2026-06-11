/**
 * Dashboard JavaScript
 * Loads statistics and renders dashboard widgets
 */

document.addEventListener('DOMContentLoaded', function() {
    requireAuth();
    updateUserUI();
    loadDashboardStats();
});

async function loadDashboardStats() {
    try {
        const response = await api.get('/dashboard/stats.php');

        if (response.data.status) {
            const { stats, recent_students, department_stats } = response.data;

            // Update stat cards
            document.getElementById('stat-total').textContent = stats.total_students;
            document.getElementById('stat-male').textContent = stats.male_students;
            document.getElementById('stat-female').textContent = stats.female_students;
            document.getElementById('stat-depts').textContent = stats.total_departments;

            // Recent students
            const recentList = document.getElementById('recent-students-list');
            if (recent_students && recent_students.length > 0) {
                recentList.innerHTML = recent_students.map(s => `
                    <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50">
                        <div class="w-9 h-9 bg-slate-200 rounded-full flex items-center justify-center text-sm font-bold text-slate-600">
                            ${escapeHtml(s.full_name).charAt(0).toUpperCase()}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-700 truncate">${escapeHtml(s.full_name)}</p>
                            <p class="text-xs text-slate-400">${escapeHtml(s.department_name || 'N/A')}</p>
                        </div>
                        <span class="text-xs text-slate-400">${escapeHtml(s.student_id)}</span>
                    </div>
                `).join('');
            } else {
                recentList.innerHTML = '<p class="text-sm text-slate-400 text-center py-4">No students yet</p>';
            }

            // Department distribution
            const deptDist = document.getElementById('dept-distribution');
            if (department_stats && department_stats.length > 0) {
                const maxCount = Math.max(...department_stats.map(d => parseInt(d.student_count)));
                deptDist.innerHTML = department_stats.map(d => {
                    const count = parseInt(d.student_count);
                    const pct = maxCount > 0 ? (count / maxCount) * 100 : 0;
                    return `
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm text-slate-600">${escapeHtml(d.department_name)}</span>
                                <span class="text-sm font-medium text-slate-700">${count}</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2">
                                <div class="bg-accent h-2 rounded-full" style="width: ${pct}%"></div>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                deptDist.innerHTML = '<p class="text-sm text-slate-400 text-center py-4">No departments yet</p>';
            }

            // Show content
            document.getElementById('loading').classList.add('hidden');
            document.getElementById('stats-container').classList.remove('hidden');
            document.getElementById('dashboard-tables').classList.remove('hidden');
        }
    } catch (error) {
        console.error('Dashboard load error:', error);
        document.getElementById('loading').innerHTML = '<p class="text-red-500">Failed to load dashboard data</p>';
    }
}
