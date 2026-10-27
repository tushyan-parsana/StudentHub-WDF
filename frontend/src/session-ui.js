const SESSION_ENDPOINT = 'http://localhost/studentHub/session.php';
const LOGIN_URL = 'login.html';
const LOGOUT_URL = 'http://localhost/studentHub/logout.php';

function addSessionControls(session) {
    const headerIcons = document.querySelector('.header-icons');
    if (!headerIcons) return;

    let userName = headerIcons.querySelector('.header-user-name');
    if (!userName) {
        userName = document.createElement('span');
        userName.className = 'header-user-name';
        headerIcons.prepend(userName);
    }
    userName.textContent = session.fullName;

    if (!headerIcons.querySelector('.logout-link')) {
        const logoutLink = document.createElement('a');
        logoutLink.className = 'logout-link';
        logoutLink.href = LOGOUT_URL;
        logoutLink.textContent = 'Logout';
        headerIcons.append(logoutLink);
    }

    const welcome = document.querySelector('.welcome h2');
    if (welcome) {
        welcome.textContent = `Welcome back, ${session.fullName}!`;
    }

    const profileWelcome = document.querySelector('.profile-welcome');
    if (profileWelcome) {
        profileWelcome.textContent =
            `${session.fullName}, your profile is up to date.`;
    }
}

fetch(SESSION_ENDPOINT, {
    credentials: 'include',
    headers: { Accept: 'application/json' }
})
    .then((response) => {
        if (!response.ok) {
            window.location.replace(LOGIN_URL);
            return null;
        }
        return response.json();
    })
    .then((session) => {
        if (session?.authenticated) {
            if (document.body.dataset.requiredRole &&
                document.body.dataset.requiredRole !== session.role) {
                const fallback = session.role === 'admin'
                    ? 'admin-events.html'
                    : 'dashboard.html';
                window.location.replace(fallback);
                return;
            }
            addSessionControls(session);
        }
    })
    .catch(() => {
        window.location.replace(LOGIN_URL);
    });
