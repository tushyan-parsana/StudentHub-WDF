const ADMIN_DASHBOARD_API = 'http://localhost/studentHub/admin-dashboard-api.php';
const adminStatus = document.querySelector('#admin-status');

function escapeText(value) {
    return String(value).replace(/[&<>'"]/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[character]));
}

async function loadAdminDashboard() {
    try {
        const data = await window.studentHubApi(ADMIN_DASHBOARD_API);
        document.querySelector('#student-count').textContent = data.counts.students;
        document.querySelector('#event-count').textContent = data.counts.events;
        document.querySelector('#registration-count').textContent = data.counts.registrations;
        document.querySelector('#open-event-count').textContent = data.counts.open_events;

        document.querySelector('#latest-registrations').innerHTML = data.latestRegistrations.length
            ? data.latestRegistrations.map((item) => `<tr><td>${escapeText(item.full_name)}</td><td>${escapeText(item.event_title)}</td><td>${escapeText(item.registered_at)}</td></tr>`).join('')
            : '<tr><td colspan="3">No registrations yet.</td></tr>';

        document.querySelector('#audit-rows').innerHTML = data.auditLogs.length
            ? data.auditLogs.map((item) => `<tr><td><span class="audit-action ${escapeText(item.action)}">${escapeText(item.action)}</span></td><td>${escapeText(item.entity_type)} #${escapeText(item.entity_id)}</td><td>${escapeText(item.actor_student_id)}</td><td>${escapeText(item.created_at)}</td></tr>`).join('')
            : '<tr><td colspan="4">No audit actions yet.</td></tr>';

        adminStatus.textContent = 'Dashboard updated from the database.';
        adminStatus.classList.remove('error');
    } catch (error) {
        adminStatus.textContent = error.message;
        adminStatus.classList.add('error');
    }
}

loadAdminDashboard();
