document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("#registration-form");
    const password = document.querySelector("#password");
    const confirmPassword = document.querySelector("#confirm-password");
    const strengthBar = document.querySelector("#strength-bar");
    const strengthLabel = document.querySelector("#password-strength");
    const successMessage = document.querySelector("#form-success");

    const rules = {
        fullName: {
            message: "Enter a name with at least 3 letters.",
            valid: value => /^[A-Za-z][A-Za-z .'-]{2,49}$/.test(value.trim())
        },
        email: {
            message: "Enter a valid email address.",
            valid: value => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(value.trim())
        },
        mobile: {
            message: "Enter a 10-digit mobile number.",
            valid: value => /^[0-9]{10}$/.test(value)
        },
        course: { message: "Select your course.", valid: value => value !== "" },
        year: { message: "Select your year.", valid: value => value !== "" },
        gender: { message: "Select your gender.", valid: () => Boolean(form.querySelector("input[name='gender']:checked")) },
        password: {
            message: "Password must include uppercase, lowercase, a number, and a special character.",
            valid: value => /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/.test(value)
        },
        confirmPassword: {
            message: "Passwords do not match.",
            valid: value => value !== "" && value === password.value
        },
        terms: { message: "Accept the terms and conditions to continue.", valid: () => form.terms.checked }
    };

    const setError = function (fieldName, message) {
        const field = form.elements[fieldName];
        const error = document.querySelector("#" + (fieldName === "gender" ? "gender" : fieldName.replace(/[A-Z]/g, match => "-" + match.toLowerCase())) + "-error");
        if (error) {
            error.textContent = message;
        }
        if (field && fieldName !== "gender") {
            field.setAttribute("aria-invalid", message ? "true" : "false");
        }
        return !message;
    };

    const validateField = function (fieldName) {
        const field = form.elements[fieldName];
        const value = fieldName === "gender" ? "" : field.value;
        const rule = rules[fieldName];
        return setError(fieldName, rule.valid(value) ? "" : rule.message);
    };

    const updateStrength = function () {
        const value = password.value;
        let score = 0;
        if (value.length >= 8) score++;
        if (/[a-z]/.test(value)) score++;
        if (/[A-Z]/.test(value)) score++;
        if (/\d/.test(value)) score++;
        if (/[^A-Za-z\d]/.test(value)) score++;
        strengthBar.style.width = (score * 20) + "%";
        strengthBar.className = score < 3 ? "weak" : score < 5 ? "medium" : "strong";
        strengthLabel.textContent = value ? ["Very weak", "Weak", "Fair", "Good", "Strong", "Strong"][score] : "";
    };

    ["fullName", "email", "mobile", "course", "year", "password", "confirmPassword"].forEach(function (fieldName) {
        const field = form.elements[fieldName];
        field.addEventListener("input", function () {
            validateField(fieldName);
            if (fieldName === "password") {
                updateStrength();
                if (confirmPassword.value) validateField("confirmPassword");
            }
            if (fieldName === "confirmPassword") validateField("confirmPassword");
        });
        field.addEventListener("blur", function () { validateField(fieldName); });
    });

    form.querySelectorAll("input[name='gender']").forEach(function (radio) {
        radio.addEventListener("change", function () { validateField("gender"); });
    });
    form.terms.addEventListener("change", function () { validateField("terms"); });

    form.addEventListener("submit", function (event) {
        event.preventDefault();
        successMessage.textContent = "";
        const fieldNames = Object.keys(rules);
        const valid = fieldNames.map(validateField).every(Boolean);
        if (valid) {
            successMessage.textContent = "Registration details are valid. You can now continue to StudentHub.";
            form.reset();
            updateStrength();
            fieldNames.forEach(function (fieldName) { setError(fieldName, ""); });
        }
    });
});
