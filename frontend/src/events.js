const EVENTS_API = 'http://localhost/studentHub/events-api.php';
const cards = document.querySelector('#event-cards');
const statusMessage = document.querySelector('#event-status');
const registrationDialog = document.querySelector('#registration-dialog');
const registrationForm = document.querySelector('#registration-form');
const newEventDialog = document.querySelector('#new-event-dialog');
const newEventForm = document.querySelector('#new-event-form');
let events = [];

function showStatus(message, error = false) {
    statusMessage.textContent = message;
    statusMessage.classList.toggle('error', error);
}

async function api(url = EVENTS_API, options = {}) {
    const response = await fetch(url, {
        credentials: 'include',
        headers: { Accept: 'application/json', ...(options.body ? { 'Content-Type': 'application/json' } : {}) },
        ...options
    });
    const data = await response.json();
    if (!response.ok) throw new Error(data.message || 'Request failed.');
    return data;
}

function safe(value) {
    return String(value).replace(/[&<>'"]/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[character]));
}

function renderEvents() {
    cards.replaceChildren();
    events.forEach((event) => {
        const card = document.createElement('article');
        const registered = Number(event.registered) === 1;
        card.className = 'event-card';
        card.innerHTML = `<p class="eyebrow">${safe(event.event_date)}</p><h2>${safe(event.event_title)}</h2><p>${safe(event.event_organiser)} · ₹${Number(event.event_fees).toFixed(2)}</p><p>${event.registrations_count} registration(s)</p><div class="event-actions"><button class="primary-button" data-register>${registered ? 'Registered' : 'Register'}</button>${registered ? '<button class="secondary-button" data-edit>Edit registration</button><button class="danger-button" data-delete>Cancel registration</button>' : ''}</div>`;
        card.querySelector('[data-register]').disabled = registered;
        card.querySelector('[data-register]').addEventListener('click', () => registerEvent(event.event_id));
        card.querySelector('[data-edit]')?.addEventListener('click', () => openEdit(event));
        card.querySelector('[data-delete]')?.addEventListener('click', () => deleteRegistration(event.registration_id));
        cards.append(card);
    });
}

async function loadEvents() {
    try {
        const data = await api();
        events = data.events;
        renderEvents();
        showStatus(`${events.length} event(s) available.`);
    } catch (error) { showStatus(error.message, true); }
}

async function registerEvent(eventId) {
    try { await api(EVENTS_API, { method: 'POST', body: JSON.stringify({ event_id: eventId }) }); await loadEvents(); }
    catch (error) { showStatus(error.message, true); }
}

function openNewEvent() {
    newEventForm.reset();
    newEventDialog.showModal();
}

async function createEvent(event) {
    event.preventDefault();
    const payload = Object.fromEntries(new FormData(newEventForm).entries());

    try {
        await api(EVENTS_API, {
            method: 'POST',
            body: JSON.stringify(payload)
        });
        newEventDialog.close();
        showStatus('Your event was created and registered successfully.');
        await loadEvents();
    } catch (error) {
        showStatus(error.message, true);
    }
}

function openEdit(event) {
    document.querySelector('#registration-id').value = event.registration_id;
    const select = document.querySelector('#registration-event');
    select.replaceChildren(...events.map((item) => new Option(item.event_title, item.event_id)));
    select.value = event.event_id;
    registrationDialog.showModal();
}

async function updateRegistration(event) {
    event.preventDefault();
    try {
        await api(EVENTS_API, { method: 'PUT', body: JSON.stringify({ registration_id: Number(document.querySelector('#registration-id').value), event_id: Number(document.querySelector('#registration-event').value) }) });
        registrationDialog.close();
        await loadEvents();
    } catch (error) { showStatus(error.message, true); }
}

async function deleteRegistration(registrationId) {
    if (!confirm('Cancel your registration for this event?')) return;
    try { await api(`${EVENTS_API}?registration_id=${registrationId}`, { method: 'DELETE' }); await loadEvents(); }
    catch (error) { showStatus(error.message, true); }
}

document.querySelector('#close-registration-dialog').addEventListener('click', () => registrationDialog.close());
document.querySelector('#cancel-registration-dialog').addEventListener('click', () => registrationDialog.close());
registrationForm.addEventListener('submit', updateRegistration);
document.querySelector('#new-event-button').addEventListener('click', openNewEvent);
document.querySelector('#close-new-event-dialog').addEventListener('click', () => newEventDialog.close());
document.querySelector('#cancel-new-event-dialog').addEventListener('click', () => newEventDialog.close());
newEventForm.addEventListener('submit', createEvent);
loadEvents();
