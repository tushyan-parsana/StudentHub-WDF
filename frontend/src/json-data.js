document.addEventListener("DOMContentLoaded", function () {
    const pageSize = 6;
    const state = {
        dataset: "events",
        records: [],
        page: 1
    };
    const list = document.querySelector("#data-list");
    const status = document.querySelector("#data-status");
    const search = document.querySelector("#data-search");
    const filter = document.querySelector("#data-filter");
    const sort = document.querySelector("#data-sort");
    const pageSummary = document.querySelector("#page-summary");
    const previousPage = document.querySelector("#previous-page");
    const nextPage = document.querySelector("#next-page");

    const escapeHtml = function (value) {
        return String(value).replace(/[&<>"']/g, function (character) {
            return {"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#039;"}[character];
        });
    };

    const getCategory = function (record) {
        return record.category || record.course || "Other";
    };

    const getSortValue = function (record) {
        return String(record.date || record.title || record.name || record.question || "").toLowerCase();
    };

    const renderRecord = function (record) {
        if (state.dataset === "events") {
            return '<article class="json-card"><span class="json-badge">' + escapeHtml(record.category) +
                '</span><h3>' + escapeHtml(record.title) + '</h3><p><strong>Date:</strong> ' +
                escapeHtml(record.date) + '</p><p><strong>Location:</strong> ' + escapeHtml(record.location) + '</p></article>';
        }
        if (state.dataset === "students") {
            return '<article class="json-card"><h3>' + escapeHtml(record.name) + '</h3><p>' +
                escapeHtml(record.course) + ' · Year ' + escapeHtml(record.year) + '</p><p>' +
                escapeHtml(record.department) + ' · <strong>' + escapeHtml(record.status) + '</strong></p></article>';
        }
        return '<article class="json-card"><span class="json-badge">' + escapeHtml(record.category) +
            '</span><h3>' + escapeHtml(record.question) + '</h3><p>' + escapeHtml(record.answer) + '</p></article>';
    };

    const updateFilterOptions = function () {
        const categories = [...new Set(state.records.map(getCategory))].sort();
        filter.innerHTML = '<option value="all">All categories</option>' +
            categories.map(category => '<option value="' + escapeHtml(category) + '">' + escapeHtml(category) + '</option>').join("");
    };

    const render = function () {
        const query = search.value.trim().toLowerCase();
        const selectedCategory = filter.value;
        let records = state.records.filter(function (record) {
            const text = JSON.stringify(record).toLowerCase();
            return text.includes(query) && (selectedCategory === "all" || getCategory(record) === selectedCategory);
        });
        if (sort.value !== "default") {
            records.sort(function (first, second) {
                const firstValue = getSortValue(first);
                const secondValue = getSortValue(second);
                return sort.value === "asc" ? firstValue.localeCompare(secondValue) : secondValue.localeCompare(firstValue);
            });
        }
        const totalPages = Math.max(1, Math.ceil(records.length / pageSize));
        state.page = Math.min(state.page, totalPages);
        const start = (state.page - 1) * pageSize;
        list.innerHTML = records.slice(start, start + pageSize).map(renderRecord).join("");
        status.textContent = records.length + " record" + (records.length === 1 ? "" : "s") + " found.";
        pageSummary.textContent = "Page " + state.page + " of " + totalPages;
        previousPage.disabled = state.page === 1;
        nextPage.disabled = state.page === totalPages;
    };

    const loadDataset = function (dataset) {
        state.dataset = dataset;
        state.page = 1;
        list.innerHTML = "";
        status.textContent = "Loading " + dataset + "...";
        fetch("data/" + dataset + ".json")
            .then(function (response) {
                if (!response.ok) throw new Error("The server returned " + response.status + ".");
                return response.json();
            })
            .then(function (records) {
                if (!Array.isArray(records)) throw new Error("The JSON response is not an array.");
                state.records = records;
                updateFilterOptions();
                render();
            })
            .catch(function (error) {
                state.records = [];
                list.innerHTML = "";
                status.textContent = "Unable to load " + dataset + ".json. Run this page through a local server and try again.";
                console.error("JSON data loading failed:", error);
            });
    };

    document.querySelectorAll(".data-tab").forEach(function (tab) {
        tab.addEventListener("click", function () {
            document.querySelectorAll(".data-tab").forEach(item => {
                item.classList.remove("active");
                item.setAttribute("aria-selected", "false");
            });
            this.classList.add("active");
            this.setAttribute("aria-selected", "true");
            filter.value = "all";
            search.value = "";
            sort.value = "default";
            loadDataset(this.dataset.dataset);
        });
    });
    [search, filter, sort].forEach(control => control.addEventListener("input", function () {
        state.page = 1;
        render();
    }));
    previousPage.addEventListener("click", function () {
        state.page--;
        render();
    });
    nextPage.addEventListener("click", function () {
        state.page++;
        render();
    });
    loadDataset(state.dataset);
});
