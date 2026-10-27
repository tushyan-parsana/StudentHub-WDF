document.addEventListener("DOMContentLoaded", function () {
    const body = document.body;

    // Add shared controls without changing the existing page layout.
    const header = document.querySelector(".header");
    const sidebar = document.querySelector(".sidebar");
    const headerIcons = document.querySelector(".header-icons");

    if (header && sidebar && headerIcons) {
        const menuButton = document.createElement("button");
        menuButton.className = "menu-toggle";
        menuButton.type = "button";
        menuButton.setAttribute("aria-label", "Toggle navigation menu");
        menuButton.textContent = "☰";
        header.insertBefore(menuButton, header.firstElementChild);
        menuButton.addEventListener("click", function () {
            sidebar.classList.toggle("open");
        });

        const themeButton = headerIcons.querySelector(".theme-toggle") ||
            document.createElement("button");
        if (!themeButton.parentElement) {
            themeButton.className = "theme-toggle";
            themeButton.type = "button";
            themeButton.textContent = "🌙";
            themeButton.setAttribute("aria-label", "Switch to dark theme");
            headerIcons.insertBefore(themeButton, headerIcons.firstElementChild);
        }
        themeButton.addEventListener("click", function () {
            const dark = body.classList.toggle("dark-theme");
            localStorage.setItem("studenthub-theme", dark ? "dark" : "light");
            themeButton.textContent = dark ? "☀️" : "🌙";
            themeButton.setAttribute("aria-label", dark ? "Switch to light theme" : "Switch to dark theme");
        });
        if (localStorage.getItem("studenthub-theme") === "dark") {
            body.classList.add("dark-theme");
            themeButton.textContent = "☀️";
            themeButton.setAttribute("aria-label", "Switch to light theme");
        }
    }

    document.querySelectorAll(".dropdown-btn").forEach(function (button) {
        button.addEventListener("click", function () {
            this.closest(".dropdown").classList.toggle("active");
        });
    });

    const showBanner = function (message) {
        let banner = document.querySelector(".notification-banner");
        if (!banner) {
            banner = document.createElement("div");
            banner.className = "notification-banner";
            banner.setAttribute("role", "status");
            document.body.appendChild(banner);
        }
        banner.textContent = message;
        banner.classList.add("visible");
        window.setTimeout(function () {
            banner.classList.remove("visible");
        }, 3500);
    };

    const notificationButton = document.querySelector(".notification-btn");
    if (notificationButton) {
        notificationButton.addEventListener("click", function () {
            showBanner("You are all caught up! No new notifications.");
        });
    }

    const modal = document.createElement("div");
    modal.className = "modal-backdrop";
    modal.innerHTML = '<div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">' +
        '<button class="modal-close" type="button" aria-label="Close dialog">×</button>' +
        '<h3 id="modal-title">Student Profile</h3><p>Welcome back, Alex. Your profile is up to date.</p></div>';
    body.appendChild(modal);

    const closeModal = function () {
        modal.classList.remove("visible");
    };
    const profileButton = document.querySelector(".profile-btn");
    if (profileButton) {
        profileButton.addEventListener("click", function () {
            modal.classList.add("visible");
        });
    }
    modal.querySelector(".modal-close").addEventListener("click", closeModal);
    modal.addEventListener("click", function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.querySelectorAll(".faq-question").forEach(function (question) {
        question.addEventListener("click", function () {
            const item = this.closest(".faq-item");
            const isOpen = item.classList.toggle("active");
            this.setAttribute("aria-expanded", String(isOpen));
            item.querySelector(".faq-icon").textContent = isOpen ? "−" : "+";
        });
        question.setAttribute("aria-expanded", "false");
    });

    const slides = document.querySelectorAll(".slide");
    const dots = document.querySelector(".slide-dots");
    let currentSlide = 0;
    const showSlide = function (index) {
        currentSlide = (index + slides.length) % slides.length;
        slides.forEach(function (slide, slideIndex) {
            slide.classList.toggle("active", slideIndex === currentSlide);
        });
        if (dots) {
            dots.querySelectorAll("button").forEach(function (dot, dotIndex) {
                dot.classList.toggle("active", dotIndex === currentSlide);
            });
        }
    };
    if (slides.length && dots) {
        slides.forEach(function (_, index) {
            const dot = document.createElement("button");
            dot.type = "button";
            dot.setAttribute("aria-label", "Show update " + (index + 1));
            dot.addEventListener("click", function () { showSlide(index); });
            dots.appendChild(dot);
        });
        document.querySelector(".slider-prev").addEventListener("click", function () {
            showSlide(currentSlide - 1);
        });
        document.querySelector(".slider-next").addEventListener("click", function () {
            showSlide(currentSlide + 1);
        });
        showSlide(0);
    }
});
