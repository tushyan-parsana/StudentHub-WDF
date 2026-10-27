window.studentHubApi = async function requestJson(url, options = {}) {
    const headers = {
        Accept: 'application/json',
        ...(options.headers || {})
    };

    if (options.body && !(options.body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
    }

    let response;
    try {
        response = await fetch(url, {
            credentials: 'include',
            ...options,
            headers
        });
    } catch (error) {
        throw new Error('The server could not be reached.');
    }

    let data;
    try {
        data = await response.json();
    } catch (error) {
        throw new Error(`The server returned an invalid response (${response.status}).`);
    }

    if (!response.ok || data.success === false) {
        const apiError = new Error(data.message || `Request failed (${response.status}).`);
        apiError.status = response.status;
        throw apiError;
    }

    return data;
};
