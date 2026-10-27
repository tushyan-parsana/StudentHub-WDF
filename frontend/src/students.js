const API_URL = 'http://localhost/studentHub/students-api.php';

const rows = document.querySelector('#student-rows');
const searchInput = document.querySelector('#search-input');
const genderFilter = document.querySelector('#gender-filter');
const clearFilters = document.querySelector('#clear-filters');
const statusMessage = document.querySelector('#status-message');
const dialog = document.querySelector('#student-dialog');
const form = document.querySelector('#student-form');
const dialogTitle = document.querySelector('#dialog-title');
let editingStudentId = null;

function showStatus(message, isError = false) {
    statusMessage.textContent = message;
    statusMessage.classList.toggle('error', isError);
}

async function requestApi(url = API_URL, options = {}) {
    return window.studentHubApi(url, options);
}

function studentFromForm() {
    return Object.fromEntries(new FormData(form).entries());
}

function renderRows(students) {
    rows.replaceChildren();
    if (!students.length) {
        rows.innerHTML = '<tr><td class="empty" colspan="7">No student records found.</td></tr>';
        return;
    }

    students.forEach((student) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${escapeHtml(student.student_id)}</td>
            <td>${escapeHtml(student.full_name)}</td>
            <td>${escapeHtml(student.email)}</td>
            <td>${escapeHtml(student.mobile)}</td>
            <td>${escapeHtml(student.gender)}</td>
            <td><span class="role ${student.role}">${escapeHtml(student.role)}</span></td>
            <td class="actions">
                <button type="button" data-action="edit">Edit</button>
                <button type="button" data-action="delete">Delete</button>
            </td>`;
        row.querySelector('[data-action="edit"]').addEventListener('click', () => openEdit(student));
        row.querySelector('[data-action="delete"]').addEventListener('click', () => deleteStudent(student.student_id));
        rows.append(row);
    });
}

function escapeHtml(value) {
    return String(value).replace(/[&<>'"]/g, (character) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
    }[character]));
}

async function loadStudents() {
    showStatus('Loading records...');
    const params = new URLSearchParams({
        search: searchInput.value.trim(),
        gender: genderFilter.value
    });

    try {
        const data = await requestApi(`${API_URL}?${params}`);
        renderRows(data.students);
        showStatus(`${data.students.length} record(s) found.`);
    } catch (error) {
        showStatus(error.message, true);
        if (error.status === 401 || error.status === 403) {
            window.location.replace('dashboard.html');
        }
    }
}

let searchTimer;
function queueStudentSearch() {
    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(loadStudents, 300);
}

function openNew() {
    editingStudentId = null;
    dialogTitle.textContent = 'Add student';
    form.reset();
    document.querySelector('#student-id').disabled = false;
    dialog.showModal();
}

function openEdit(student) {
    editingStudentId = student.student_id;
    dialogTitle.textContent = 'Edit student';
    Object.entries(student).forEach(([key, value]) => {
        const field = form.elements[key];
        if (field) field.value = value;
    });
    document.querySelector('#student-id').disabled = true;
    dialog.showModal();
}

async function saveStudent(event) {
    event.preventDefault();
    const payload = studentFromForm();
    const method = editingStudentId ? 'PUT' : 'POST';

    try {
        await requestApi(API_URL, { method, body: JSON.stringify(payload) });
        dialog.close();
        showStatus('Student saved successfully.');
        await loadStudents();
    } catch (error) {
        showStatus(error.message, true);
    }
}

async function deleteStudent(studentId) {
    if (!window.confirm(`Delete student ${studentId}?`)) return;

    try {
        await requestApi(`${API_URL}?student_id=${encodeURIComponent(studentId)}`, { method: 'DELETE' });
        showStatus('Student deleted successfully.');
        await loadStudents();
    } catch (error) {
        showStatus(error.message, true);
    }
}

document.querySelector('#new-student-button').addEventListener('click', openNew);
document.querySelector('#close-dialog').addEventListener('click', () => dialog.close());
document.querySelector('#cancel-dialog').addEventListener('click', () => dialog.close());
form.addEventListener('submit', saveStudent);
searchInput.addEventListener('input', queueStudentSearch);
genderFilter.addEventListener('change', loadStudents);
clearFilters.addEventListener('click', () => {
    searchInput.value = '';
    genderFilter.value = '';
    loadStudents();
});
loadStudents();
