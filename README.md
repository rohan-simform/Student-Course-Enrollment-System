# Student Course Enrollment System

A PHP-based student enrollment platform for managing students, courses, instructors, and course registrations. The app uses an OOP structure with dedicated classes for database access, CRUD operations, enrollment rules, authentication, and email notifications.

## Overview

This project was built to manage the full enrollment flow in one place:

- add, update, and remove students and courses
- enroll a student in a course from DB-driven dropdowns
- cancel enrollments and manage enrollment status
- show all enrollments with student and course details through JOIN queries
- calculate remaining seats per course
- prevent duplicate enrollments and overbooking

The application also includes authentication, CAPTCHA protection, CSRF tokens, role-based dashboards, and an email queue worker for asynchronous mail delivery.

## Features

- Student CRUD
- Course CRUD
- Enrollment create, cancel, list, and lookup actions
- Duplicate enrollment prevention on the PHP side
- Seat availability checks before enrollment
- Confirmation prompts before destructive actions in the UI
- Role support for `admin`, `instructor`, and `student`
- Email queue processing with a background worker

## Tech Stack

- PHP 7.4 / 8.2 / 8.4
- MySQL / MariaDB
- Composer
- PHPMailer
- vlucas/phpdotenv

## Database

The main database is `enrollment_db`. The schema includes the core tables used by the system:

- `users`
- `students`
- `instructors`
- `courses`
- `courses_instructors`
- `enrollments`
- `email_queue`

The enrollment flow is centered around `students`, `courses`, and `enrollments`, with supporting tables for roles, instructor assignments, and email jobs.

## Project Structure

- `classes/` - data access and entity classes such as `Database`, `Student`, `Course`, and `Enrollment`
- `service/` - business logic for auth, users, courses, enrollments, and mail
- `handlers/` - request handlers for forms and AJAX actions
- `helpers/` - shared utilities for validation, logging, CSRF, mail, permissions, and query helpers
- `public/` - web entry points and UI pages
- `config/` - configuration bootstrap and constants
- `database/` - SQL schema and dump files
- `worker/` - background email worker

## Main Requirements Covered

This project covers the original assignment requirements:

- OOP database connection through a `Database` class
- CRUD methods for `Student` and `Course`
- `Enrollment` methods such as `enroll()`, `cancel()`, `getByStudent()`, and `getAll()`
- enrollment status tracking
- multi-table JOINs to list enrollments with names instead of IDs
- validation for duplicate enrollments and maximum course capacity
- UI confirmation before cancellation or deletion

## Setup

### 1. Install dependencies

```bash
composer install
```

### 2. Create your environment file

Create a `.env` file in the project root and add the database and mail settings used by the app:

```env
DB_HOST=127.0.0.1
DB_USERNAME=root
DB_PASSWORD=
DB_NAME=enrollment_db
DB_PORT=3306

MAIL_HOST=smtp.example.com
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your-password
MAIL_SMTP_AUTH=true
MAIL_SMTP_SECURE=tls
MAIL_PORT=587
MAIL_NAME=Enrollment System
```

Adjust these values to match your local or production environment.

### 3. Import the database

Import `database/enrollment_db.sql` into MySQL and make sure the target database is named `enrollment_db`.

```bash
mysql -u root -p enrollment_db < database/enrollment_db.sql
```

### 4. Configure your web server

Point your web server document root to the `public/` directory so the application can serve its pages correctly.

### 5. Run the email worker when needed

The email worker reads pending jobs from `email_queue` and sends them in the background:

```bash
php worker/emailWorker.php
```

## Test Users & Credentials

All test users have the password: **`pass123`**

### Students
| Email | Password | Status |
|-------|----------|--------|
| rahul@example.com | pass123 | active |
| priya@example.com | pass123 | active |
| amit@example.com | pass123 | active |

### Instructors
| Email | Password | Status |
|-------|----------|--------|
| john@example.com | pass123 | active |
| sara@example.com | pass123 | active |

### Admins
| Email | Password | Status |
|-------|----------|--------|
| admin@example.com | pass123 | active |
