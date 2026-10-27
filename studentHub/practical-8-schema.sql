CREATE DATABASE IF NOT EXISTS StudentHub
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE StudentHub;

CREATE TABLE IF NOT EXISTS students (
    student_id VARCHAR(30) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    mobile VARCHAR(10) NOT NULL,
    gender ENUM('Female', 'Male', 'Other') NOT NULL,
    role ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (student_id),
    UNIQUE KEY uq_students_email (email),
    CONSTRAINT chk_students_mobile CHECK (mobile REGEXP '^[0-9]{10}$')
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS student_login (
    student_id VARCHAR(30) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    remember_token_hash CHAR(64) NULL,
    remember_expires_at TIMESTAMP NULL DEFAULT NULL,
    last_login TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (student_id),
    UNIQUE KEY uq_student_login_email (email),
    CONSTRAINT fk_student_login_student
        FOREIGN KEY (student_id) REFERENCES students (student_id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS events (
    event_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    event_title VARCHAR(100) NOT NULL,
    event_organiser VARCHAR(80) NOT NULL,
    event_date DATE NOT NULL,
    event_fees DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (event_id),
    CONSTRAINT chk_events_fees CHECK (event_fees >= 0)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS event_registration (
    registration_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id VARCHAR(30) NOT NULL,
    event_id INT UNSIGNED NOT NULL,
    registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (registration_id),
    UNIQUE KEY uq_student_event (student_id, event_id),
    CONSTRAINT fk_event_registration_student
        FOREIGN KEY (student_id) REFERENCES students (student_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_event_registration_event
        FOREIGN KEY (event_id) REFERENCES events (event_id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO students (student_id, full_name, email, mobile, gender, role)
VALUES ('DEMO-001', 'Demo Student', 'demo.student@example.com', '9876543210', 'student')
ON DUPLICATE KEY UPDATE student_id = student_id;

INSERT INTO students (student_id, full_name, email, mobile, gender, role)
VALUES ('ADMIN-001', 'Admin User', 'admin@example.com', '9876543211', 'Other', 'admin')
ON DUPLICATE KEY UPDATE student_id = student_id;

INSERT INTO student_login (student_id, email, password_hash)
SELECT 'DEMO-001', 'demo.student@example.com',
    '$2y$10$/H9L8F3wduWFdlw07Z5ZkudVDr3cH2p3zYngcwta3zLPfCwYJYIXi'
WHERE NOT EXISTS (
    SELECT 1 FROM student_login WHERE student_id = 'DEMO-001'
);

INSERT INTO student_login (student_id, email, password_hash)
SELECT 'ADMIN-001', 'admin@example.com',
    '$2y$10$/H9L8F3wduWFdlw07Z5ZkudVDr3cH2p3zYngcwta3zLPfCwYJYIXi'
WHERE NOT EXISTS (
    SELECT 1 FROM student_login WHERE student_id = 'ADMIN-001'
);

INSERT INTO events (event_title, event_organiser, event_date, event_fees)
SELECT 'Web Workshop', 'Student Club', '2026-11-15', 250.00
WHERE NOT EXISTS (
    SELECT 1
    FROM events
    WHERE event_title = 'Web Workshop'
      AND event_date = '2026-11-15'
);

INSERT IGNORE INTO event_registration (student_id, event_id)
SELECT 'DEMO-001', event_id
FROM events
WHERE event_title = 'Web Workshop'
  AND event_date = '2026-11-15'
LIMIT 1;
