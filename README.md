# 🎓 StudentHub

> A comprehensive student portal frontend built with **HTML**, **CSS**, and **JavaScript** — designed to centralize academics, events, career opportunities, community forums, and campus support into a single, intuitive dashboard.

---

## 📋 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [File Structure](#-file-structure)
- [Sitemap](#-sitemap)
- [Page Descriptions](#-page-descriptions)
- [Getting Started](#-getting-started)
- [Wireframes](#-wireframes)
- [License](#-license)

---

## ✨ Features

| Module        | Highlights                                                       |
| ------------- | ---------------------------------------------------------------- |
| **Auth**      | Login, registration, and Forgot-Password flows                   |
| **Dashboard** | Welcome banner, upcoming deadlines, today's schedule, announcements, slider, theme toggle, and mobile menu |
| **Academics** | Course catalog → Course detail → Assignments (nested navigation) |
| **Events**    | Calendar view of campus events                                   |
| **Events / Forums / HelpDesk** | Fetch JSON data for events, student discussions, notices, and FAQs with search, filters, sorting, and pagination |
| **Career**    | Job & internship portal with search, filters, and apply actions  |
| **Forums**    | Community discussion boards                                      |
| **HelpDesk**  | Campus support & ticket submission                               |
| **Profile**   | View/edit personal info, notification preferences, change password |

---

## 🛠 Tech Stack

| Layer   | Technology    |
| ------- | ------------- |
| Structure | HTML5        |
| Styling   | Vanilla CSS  |
| Logic     | JavaScript and PHP |
| Storage   | JSON files with file locking |

---

## 📁 File Structure

```
StudentHub/
│
├── README.md                          # Project documentation (this file)
│
├── WireFrame/                         # UI wireframe screenshots
│   ├── Screenshot 2026-08-05 044532.png
│   ├── Screenshot 2026-08-05 044744.png
│   ├── Screenshot 2026-08-05 044848.png
│   ├── Screenshot 2026-08-05 045423.png
│   ├── Screenshot 2026-08-05 045936.png
│   ├── Screenshot 2026-08-05 045954.png
│   ├── Screenshot 2026-08-05 050112.png
│   ├── Screenshot 2026-08-05 050119.png
│   ├── Screenshot 2026-08-05 050131.png
│   └── Screenshot 2026-08-05 050140.png
│
└── frontend/
    └── src/
        │
        ├── styles/                    # Stylesheets
        │   ├── login.css              # Login page styles
        │   ├── forgot.css             # Forgot-password page styles
        │   ├── dashboard.css          # Shared layout (header, sidebar, content)
        │   └── dashboard_updated.css  # Revised/alternate dashboard styles
        │
        ├── login.html                 # Login page (entry point)
        ├── registration.html          # Validated student registration form
        ├── forgot.html                # Forgot-password page
        ├── dashboard.html             # Main dashboard
        ├── data-render.js             # Shared Fetch API rendering logic
        ├── data/                      # Events, forum, notices, student, and FAQ JSON datasets
        ├── catalog.html               # Course catalog
        ├── course-detail.html         # Individual course details
        ├── assignments.html           # Assignments list / submission
        ├── calender.html              # Events calendar
        ├── career.html                # Job & internship portal
        ├── forums.html                # Community forums
        ├── helpdesk.html              # Campus helpdesk
        └── profile.html               # User profile & settings
```

---

## 🗺 Sitemap

```
                          ┌──────────────┐
                          │  login.html  │  (Landing / Entry Point)
                          └──────┬───────┘
                                 │
                    ┌────────────┼────────────┐
                    │            │            │
                    ▼            ▼            ▼
           ┌──────────┐  ┌────────────┐  ┌──────────────┐
           │  forgot   │  │ dashboard  │  │   profile    │
           │  .html    │  │  .html     │  │   .html      │
           └──────────┘  └─────┬──────┘  └──────────────┘
                               │
          ┌────────────┬───────┼────────┬──────────┐
          │            │       │        │          │
          ▼            ▼       ▼        ▼          ▼
   ┌────────────┐ ┌─────────┐ ┌──────┐ ┌───────┐ ┌─────────┐
   │ Academics  │ │ Events  │ │Career│ │Forums │ │HelpDesk │
   │ (dropdown) │ │calender │ │career│ │forums │ │helpdesk │
   └─────┬──────┘ │ .html   │ │.html │ │.html  │ │.html    │
         │        └─────────┘ └──────┘ └───────┘ └─────────┘
         │
         ├── catalog.html         (Course Catalog)
         ├── course-detail.html   (My Courses)
         └── assignments.html     (Assignments)
```

### Sitemap – Flat Reference

| #  | Page                | File                | Parent        | Description                              |
| -- | ------------------- | ------------------- | ------------- | ---------------------------------------- |
| 1  | **Login**           | `login.html`        | —             | Entry point; email + password auth       |
| 2  | **Forgot Password** | `forgot.html`       | Login         | Password-reset request form              |
| 3  | **Dashboard**       | `dashboard.html`    | Login         | Central hub after login                  |
| 4  | **Catalog**         | `catalog.html`      | Dashboard     | Browse available courses                 |
| 5  | **Course Detail**   | `course-detail.html`| Dashboard     | Detailed view of a single course         |
| 6  | **Assignments**     | `assignments.html`  | Dashboard     | View & manage assignment submissions     |
| 7  | **Events**          | `calender.html`     | Dashboard     | Calendar-based event listing             |
| 8  | **Career Portal**   | `career.html`       | Dashboard     | Search & apply for jobs / internships    |
| 9  | **Forums**          | `forums.html`       | Dashboard     | Community discussion threads             |
| 10 | **HelpDesk**        | `helpdesk.html`     | Dashboard     | Campus support & ticket system           |
| 11 | **Profile**         | `profile.html`      | Dashboard     | View/edit profile, notification settings |

---

## 📄 Page Descriptions

### 🔐 Authentication

- **Login** (`login.html`) — Split-panel layout with branding on the left and a login form (email + password) on the right. Successful login redirects to the Dashboard.
- **Registration** (`registration.html`) — Accessible student registration form with client-side feedback and a POST request to `registration.php`.
- **Registration processing** (`registration.php`) — Validates and sanitizes submitted fields on the server, hashes passwords, checks duplicate student IDs/emails, and stores records in `frontend/src/data/registrations.json` using file locking.
- **Storage helper** (`storage.php`) — Reads and appends JSON records with `flock()` and explicit JSON/file errors.
- **Event processing** (`event-registration.php`) — Validates event registration POST data and stores it in `frontend/src/data/event-registrations.json`.
- **Stored records** (`registration-records.php`) — Intermediate extension that displays saved registration records without exposing password hashes.
- **Forgot Password** (`forgot.html`) — Simple email-entry form that sends a password-reset link. Includes a back-to-login navigation.

To run the registration processor, serve `frontend/src` through PHP (for example, place the project under XAMPP/WAMP/Laragon `htdocs` and open `registration.html` through `http://localhost`). Opening the HTML file directly with `file://` only supports the client-side form behavior; PHP requires a web server. The JSON files must be writable by the PHP process.

### Practical 7: PHP Form Processing

**Problem:** Process submitted registration and event forms using PHP, validate and sanitize input on the server, and store records safely in CSV/JSON format.

**Implementation flow:**

1. The HTML form sends data with `method="post"` to a relative PHP processor.
2. PHP checks the request method, validates every field, trims text, normalizes email, and escapes output with `htmlspecialchars()`.
3. `password_hash()` stores only a password hash; the original password is never written to JSON.
4. `storage.php` uses `flock(LOCK_EX)` while appending records, preventing simultaneous writes from corrupting the JSON file.
5. The processor displays a clear success or error response.
6. `registration-records.php` demonstrates the intermediate extension by displaying stored records while omitting the password hash.

**Test cases:**

| Test input | Expected result |
| --- | --- |
| Valid registration with all fields | Success message and a new record in `data/registrations.json` |
| Missing student ID or invalid email | Error message; no record is written |
| Weak password or mismatched confirmation | Error message; no record is written |
| Duplicate student ID or email | Error message; no duplicate is written |
| Event fees below 0 or above 100000 | Error message; no event record is written |
| Valid event registration | Success message and a new record in `data/event-registrations.json` |

### Practical 8: MySQL Schema Design and PDO Connectivity

The SQL script [`studentHub/practical-8-schema.sql`](studentHub/practical-8-schema.sql) creates the `StudentHub` database and normalized `students`, `student_login`, `events`, and `event_registration` tables. It defines primary keys, unique constraints, foreign keys, validation checks, and safe sample data.

Run the script in phpMyAdmin by opening the **Import** tab and selecting the SQL file, or paste its contents into the **SQL** tab. The existing PDO settings in `studentHub/config.php` use the Laragon defaults (`localhost`, user `root`, blank password, database `StudentHub`).

The JSON processors from Practical 7 remain available for demonstrating file-based storage. Practical 8's SQL schema is ready for migrating those processors to prepared PDO statements.

PDO connectivity test: open `http://localhost/studentHub/pdo-connectivity.php`. The page runs `SELECT 1` to verify the connection and then runs a named-parameter prepared statement against the `events` table. A successful output is:

```text
PDO connection successful. Connected to the StudentHub database.
Prepared statement executed successfully.
```

### 📊 Dashboard (`dashboard.html`)

The main landing page after authentication. Features:
- **Upcoming Deadlines** — Checklist-style card.
- **Today's Schedule** — Time-slotted class listing.
- **Campus Announcements** — Latest notices from administration.

### 📚 Academics (Sidebar Dropdown)

- **Catalog** (`catalog.html`) — Browse and search available courses.
- **Course Detail** (`course-detail.html`) — Dive into a specific course: syllabus, schedule, resources.
- **Assignments** (`assignments.html`) — View pending/completed assignments with submission support.

### 📅 Events (`calender.html`)

Calendar view for campus events, workshops, and important dates.

### 🧩 Shared UI Interactions

### 📦 JSON-backed application pages

The existing Events, Forums, and Help Desk pages consume external JSON files with the Fetch API and dynamically display:
- campus event lists from `data/events.json`
- forum discussions from `data/forums.json`
- FAQs from `data/faqs.json`
- notices and student profile data remain available in `data/notices.json` and `data/students.json` for application views.

Each data set supports search, category filtering, sorting, and pagination with visible loading and error states.

Several pages now share a common JavaScript layer for:
- collapsible FAQ items on HelpDesk
- modal popup for the profile button
- notification banner feedback
- responsive hamburger menu on smaller screens
- light/dark theme persistence with `localStorage`
- a small content slider on the dashboard

### 💼 Career Portal (`career.html`)

Job & internship portal with:
- **Search bar** for companies and roles.
- **Filters** for job type, location, and experience level.
- **Job cards** showing company, location, stipend, and an "Apply Now" action.

### 💬 Forums (`forums.html`)

Community discussion boards for peer-to-peer help and campus conversations.

### 🛟 HelpDesk (`helpdesk.html`)

Campus support portal for submitting and tracking help tickets.

### 👤 Profile (`profile.html`)

User profile management:
- View/edit name, email, student ID, department, semester.
- Toggle email & push notification preferences.
- Change password action.

---

## 🚀 Getting Started

### Prerequisites

Any modern web browser (Chrome, Firefox, Edge, Safari).

### Run Locally

1. **Clone the repository**
   ```bash
   git clone https://github.com/tushyan-parsana/StudentHub-WDF.git
   cd StudentHub-WDF
   ```

2. **Open the entry point**
   ```
   Open  frontend/src/login.html  in your browser.
   ```

   Or use a local dev server (e.g. VS Code Live Server) for auto-reload.

---

## 🖼 Wireframes

All wireframe screenshots are stored in the [`WireFrame/`](WireFrame/) directory. They document the initial UI design for each page before implementation.

---

## 📝 License

This project is part of the **Web Development Fundamentals (WDF)** Lab coursework — B.Tech, Semester 3.

---

<p align="center">
  Made with ❤️ by <strong>Tushyan Parsana</strong>
</p>