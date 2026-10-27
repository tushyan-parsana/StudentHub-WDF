const EVENTS_API = 'http://localhost/studentHub/events-api.php';
const eventRows = document.querySelector('#event-rows');
const eventDialog = document.querySelector('#event-dialog');
const eventForm = document.querySelector('#event-form');
const statusMessage = document.querySelector('#event-status');
let editingEventId = null;

function showStatus(message, error = false) {
    statusMessage.textContent = message;
    statusMessage.classList.toggle('error', error);
}

async function api(url = EVENTS_API, options = {}) {
    const headers = { Accept: 'application/json' };
    if (options.body && !(options.body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
    }
    const response = await fetch(url, {
        credentials: 'include',
        headers,
        ...options
    });
    const data = await response.json();
    if (!response.ok) throw new Error(data.message || 'Request failed.');
    return data;
}

function safe(value) {
    return String(value).replace(/[&<>'"]/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[character]));
}

async function loadEvents() {
    try {
        const data = await api();
        eventRows.replaceChildren();
        data.events.forEach((event) => {
            const row = document.createElement('tr');
            const poster = event.poster_path ? `<img class="event-poster-thumb" src="http://localhost/studentHub/${safe(event.poster_path)}" alt="">` : '<span class="no-poster">None</span>';
            row.innerHTML = `<td>${poster}</td><td>${safe(event.event_title)}</td><td>${safe(event.category)}</td><td><span class="event-status ${safe(event.status)}">${safe(event.status)}</span></td><td>${safe(event.event_organiser)}</td><td>${safe(event.event_date)}</td><td>₹${Number(event.event_fees).toFixed(2)}</td><td>${event.registrations_count}</td><td class="actions"><button data-edit>Edit</button><button data-delete>Delete</button></td>`;
            row.querySelector('[data-edit]').addEventListener('click', () => openEdit(event));
            row.querySelector('[data-delete]').addEventListener('click', () => deleteEvent(event.event_id));
            eventRows.append(row);
        });
        showStatus(`${data.events.length} event(s) found.`);
    } catch (error) {
        showStatus(error.message, true);
    }
}

function openEvent(event = null) {
    editingEventId = event?.event_id || null;
    document.querySelector('#event-dialog-title').textContent = event ? 'Edit event' : 'Add event';
    eventForm.reset();
    if (event) Object.entries(event).forEach(([key, value]) => { if (eventForm.elements[key]) eventForm.elements[key].value = value; });
    eventDialog.showModal();
}

async function saveEvent(event) {
    event.preventDefault();
    const payload = new FormData(eventForm);
    payload.append('action', editingEventId ? 'update' : 'create');
    if (editingEventId) payload.append('event_id', editingEventId);
    try {
        await api(EVENTS_API, { method: 'POST', body: payload });
        eventDialog.close();
        await loadEvents();
    } catch (error) { showStatus(error.message, true); }
}

async function deleteEvent(eventId) {
    if (!confirm('Delete this event and its registrations?')) return;
    try { await api(`${EVENTS_API}?event_id=${eventId}`, { method: 'DELETE' }); await loadEvents(); }
    catch (error) { showStatus(error.message, true); }
}

document.querySelector('#new-event-button').addEventListener('click', () => openEvent());
document.querySelector('#close-event-dialog').addEventListener('click', () => eventDialog.close());
document.querySelector('#cancel-event-dialog').addEventListener('click', () => eventDialog.close());
eventForm.addEventListener('submit', saveEvent);
loadEvents();
