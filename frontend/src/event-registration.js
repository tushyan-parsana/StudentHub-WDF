document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("#event-registration-form");
    const status = document.querySelector("#event-registration-status");

    if (!form || !status) return;

    form.addEventListener("submit", function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            status.className = "event-registration-status error";
            status.textContent = "Please complete all registration fields.";
            form.reportValidity();
        }
    });
});
