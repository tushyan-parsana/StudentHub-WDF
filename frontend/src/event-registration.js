document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("#event-registration-form");
    const status = document.querySelector("#event-registration-status");

    if (!form || !status) return;

    const dateInput = form.elements.eventDate;
    dateInput.addEventListener("input", function () {
        dateInput.setCustomValidity("");
    });

    form.addEventListener("submit", function (event) {
        if (!/^\d{4}-\d{2}-\d{2}$/.test(dateInput.value)) {
            dateInput.setCustomValidity("Enter the date as YYYY-MM-DD.");
        } else {
            dateInput.setCustomValidity("");
        }

        if (!form.checkValidity()) {
            event.preventDefault();
            status.className = "event-registration-status error";
            status.textContent = "Please enter a complete date (YYYY-MM-DD) and complete all required fields.";
        }
    });
});
