document.addEventListener("DOMContentLoaded", function () {
    const config = {
        events: {
            file: "events.json",
            title: record => record.title,
            category: record => record.category,
            html: record => "<article class='json-card'><span class='json-badge'>" + escapeHtml(record.category) +
                "</span><h3>" + escapeHtml(record.title) + "</h3><p><strong>Date:</strong> " +
                escapeHtml(record.date) + "</p><p><strong>Location:</strong> " + escapeHtml(record.location) + "</p></article>"
        },
        forums: {
            file: "forums.json",
            title: record => record.title,
            category: record => record.category,
            html: record => "<article class='json-card'><span class='json-badge'>" + escapeHtml(record.category) +
                "</span><h3>" + escapeHtml(record.title) + "</h3><p>Started by " + escapeHtml(record.author) +
                " · " + escapeHtml(record.replies) + " replies</p><button class='listing-action' type='button'>Open discussion</button></article>"
        },
        faqs: {
            file: "faqs.json",
            title: record => record.question,
            category: record => record.category,
            html: record => "<article class='json-card faq-json-card'><button class='faq-question' type='button'>" +
                escapeHtml(record.question) + "<span class='faq-icon'>+</span></button><div class='faq-answer'><p>" +
                escapeHtml(record.answer) + "</p></div></article>"
        }
    };

    const page = document.body.dataset.jsonPage;
    if (!page || !config[page]) return;
    const options = config[page];
    const list = document.querySelector("#json-list");
    const status = document.querySelector("#json-status");
    const search = document.querySelector("#json-search");
    const filter = document.querySelector("#json-filter");
    const sort = document.querySelector("#json-sort");
    const previous = document.querySelector("#json-previous");
    const next = document.querySelector("#json-next");
    const summary = document.querySelector("#json-page-summary");
    const pageSize = 6;
    let records = [];
    let pageNumber = 1;

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, character => (
            {"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#039;"}[character]
        ));
    }

    function render() {
        const query = search.value.trim().toLowerCase();
        const category = filter.value;
        let visible = records.filter(record =>
            JSON.stringify(record).toLowerCase().includes(query) &&
            (category === "all" || options.category(record) === category)
        );
        visible.sort((a, b) => {
            if (sort.value === "default") return 0;
            const result = options.title(a).localeCompare(options.title(b));
            return sort.value === "asc" ? result : -result;
        });
        const pages = Math.max(1, Math.ceil(visible.length / pageSize));
        pageNumber = Math.min(pageNumber, pages);
        const start = (pageNumber - 1) * pageSize;
        list.innerHTML = visible.slice(start, start + pageSize).map(options.html).join("");
        status.textContent = visible.length + " record" + (visible.length === 1 ? "" : "s") + " found.";
        summary.textContent = "Page " + pageNumber + " of " + pages;
        previous.disabled = pageNumber === 1;
        next.disabled = pageNumber === pages;
        list.querySelectorAll(".faq-question").forEach(question => {
            question.addEventListener("click", function () {
                const card = this.closest(".faq-json-card");
                const open = card.classList.toggle("active");
                card.querySelector(".faq-icon").textContent = open ? "−" : "+";
            });
        });
    }

    fetch("data/" + options.file)
        .then(response => {
            if (!response.ok) throw new Error("Request failed with " + response.status);
            return response.json();
        })
        .then(data => {
            records = data;
            filter.innerHTML = '<option value="all">All categories</option>' +
                [...new Set(records.map(options.category))].sort().map(category =>
                    "<option value='" + escapeHtml(category) + "'>" + escapeHtml(category) + "</option>"
                ).join("");
            render();
        })
        .catch(error => {
            status.textContent = "Unable to load this data. Please run the site through a local server.";
            console.error("JSON loading failed:", error);
        });

    [search, filter, sort].forEach(control => control.addEventListener("input", function () {
        pageNumber = 1;
        render();
    }));
    previous.addEventListener("click", function () { pageNumber--; render(); });
    next.addEventListener("click", function () { pageNumber++; render(); });
});
