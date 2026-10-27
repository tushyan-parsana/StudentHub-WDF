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
- [PHP and MySQL Setup](#-php-and-mysql-setup)
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
| Logic     | JavaScript, PHP, Fetch API |
| Database  | MySQL with PDO and MySQLi |
| Storage   | MySQL database and validated event poster uploads |

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
- **Registration processing** (`registration.php`) — Validates input, hashes passwords, checks duplicate student IDs/emails, and inserts records into MySQL using PDO prepared statements.
- **Authentication** (`login.php`, `auth.php`, `logout.php`) — Verifies passwords, starts sessions, enforces roles, supports timeout and remember-me tokens.
- **Student API** (`students-api.php`) — Admin-only JSON CRUD endpoint for student records.
- **Event API** (`events-api.php`) — Role-aware JSON CRUD endpoint for events, registrations, categories, statuses, and validated poster uploads.
- **Admin dashboard API** (`admin-dashboard-api.php`) — Returns database statistics, recent registrations, and audit logs.
- **Forgot Password** (`forgot.html`) — Simple email-entry form that sends a password-reset link. Includes a back-to-login navigation.

To run registration, copy the PHP backend into the server directory described in [PHP and MySQL Setup](#-php-and-mysql-setup), then open the frontend through Live Server. The frontend submits to `http://localhost/studentHub/registration.php`; PHP requires Apache or another web server and does not run from `file://` URLs.

### Practical 7: PHP Form Processing

**Problem:** Process submitted registration and event forms using PHP, validate and sanitize input on the server, and store records safely in MySQL.

**Implementation flow:**

1. The HTML form sends data with `method="post"` to a relative PHP processor.
2. PHP checks the request method, validates every field, trims text, normalizes email, and escapes output with `htmlspecialchars()`.
3. `password_hash()` stores only a password hash; the original password is never written to the database.
4. PDO prepared statements protect database inserts and duplicate checks.
5. The processor displays a clear success or error response.
6. Login uses `password_verify()` and PHP sessions to authenticate the stored account.

**Test cases:**

| Test input | Expected result |
| --- | --- |
| Valid registration with all fields | Success message and new rows in `students` and `student_login` |
| Missing student ID or invalid email | Error message; no record is written |
| Weak password or mismatched confirmation | Error message; no record is written |
| Duplicate student ID or email | Error message; no duplicate is written |
| Event fees below 0 or above 100000 | Error message; no event record is written |
| Valid event registration | Success message and a new row in `event_registration` |

### Practical 8: MySQL Schema Design and PDO Connectivity

The SQL script [`studentHub/practical-8-schema.sql`](studentHub/practical-8-schema.sql) creates the `StudentHub` database and normalized `students`, `student_login`, `events`, and `event_registration` tables. It defines primary keys, unique constraints, foreign keys, validation checks, and safe sample data.

Run the script in phpMyAdmin by opening the **Import** tab and selecting the SQL file, or paste its contents into the **SQL** tab. The existing PDO settings in `studentHub/config.php` use the Laragon defaults (`localhost`, user `root`, blank password, database `StudentHub`).

The current registration and event processors use the StudentHub MySQL tables. The JSON files under `frontend/src/data/` are retained only for static frontend content such as notices, forums, FAQs, and sample catalog data.

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

- PHP 8.1 or later
- MySQL or MariaDB
- XAMPP, WAMP, or Laragon
- VS Code Live Server (recommended for the frontend)
- A modern web browser

### PHP and MySQL Setup

The project has two separate parts:

- `frontend/src/` contains HTML, CSS, and JavaScript.
- `studentHub/` contains PHP, MySQL, API, authentication, and upload files.

Copy the contents of the repository's `studentHub/` directory into your PHP server's web root:

| Server | Backend directory |
| --- | --- |
| XAMPP | `C:\xampp\htdocs\studentHub` |
| WAMP | `C:\wamp64\www\studentHub` |
| Laragon | `C:\laragon\www\studentHub` |

The backend files must be directly inside the `studentHub` folder. This includes `config.php`, `mysqli-config.php`, `login.php`, `events-api.php`, `students-api.php`, and `practical-8-schema.sql`.

### Create the Database

1. Start Apache and MySQL from XAMPP, WAMP, or Laragon.
2. Open `http://localhost/phpmyadmin`.
3. Select **Import** and choose `studentHub/practical-8-schema.sql`.
4. Execute the import. It creates the `StudentHub` database, tables, seed students, events, and audit-log structure.
5. Confirm that `students`, `student_login`, `events`, `event_registration`, and `audit_logs` exist.

Default database settings:

```text
Host: localhost
Database: StudentHub
User: root
Password: blank
```

If your MySQL installation uses a password, update both `studentHub/config.php` and `studentHub/mysqli-config.php`.

### Serve the Frontend

Open the repository's `Lab` folder in VS Code and start Live Server from the workspace root. Use:

```text
http://localhost:5500/frontend/src/login.html
```

Do not open the HTML files with `file://`; PHP sessions, Fetch requests, CORS credentials, and database operations require HTTP.

### Important URLs

```text
Frontend login:       http://localhost:5500/frontend/src/login.html
Student events:       http://localhost:5500/frontend/src/events.html
Admin dashboard:      http://localhost:5500/frontend/src/admin.html
Admin event manager:  http://localhost:5500/frontend/src/admin-events.html
Student manager:      http://localhost:5500/frontend/src/students.html

PHP login:            http://localhost/studentHub/login.php
Students API:         http://localhost/studentHub/students-api.php
Events API:           http://localhost/studentHub/events-api.php
Admin dashboard API:  http://localhost/studentHub/admin-dashboard-api.php
PDO test:             http://localhost/studentHub/pdo-connectivity.php
```

### Test Accounts

```text
Student
Email: demo.student@example.com
Password: Student@123

Admin
Email: admin@example.com
Password: Student@123
```

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