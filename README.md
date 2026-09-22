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
| Logic     | JavaScript   |

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
- **Registration** (`registration.html`) — Accessible student registration form with HTML5 input types, regular-expression validation, password strength feedback, confirmation matching, and terms acceptance.
- **Forgot Password** (`forgot.html`) — Simple email-entry form that sends a password-reset link. Includes a back-to-login navigation.

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