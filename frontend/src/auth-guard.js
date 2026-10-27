const AUTH_ENDPOINT = 'http://localhost/studentHub/session.php';
const LOGIN_PAGE = 'login.html';

fetch(AUTH_ENDPOINT, {
    credentials: 'include',
    headers: { Accept: 'application/json' }
})
    .then((response) => {
        if (!response.ok) {
            window.location.replace(LOGIN_PAGE);
            return null;
        }
        return response.json();
    })
    .then((session) => {
        if (!session?.authenticated) {
            return;
        }

        const welcome = document.querySelector('.welcome h2');
        if (welcome && session.fullName) {
            welcome.textContent = `Welcome back, ${session.fullName}!`;
        }
    })
    .catch(() => {
        window.location.replace(LOGIN_PAGE);
    });
