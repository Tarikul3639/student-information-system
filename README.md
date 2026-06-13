# Student Information System (SIS)

A full-stack web application for managing student records, built as a Cyber Security Lab assignment.

## Tech Stack

- **Frontend:** HTML5, Tailwind CSS, JavaScript, Axios
- **Backend:** PHP 8+, MySQL
- **Architecture:** REST API with session-based authentication

## Features

- ✅ User Registration & Login with password hashing
- ✅ Session management with timeout
- ✅ CSRF protection on all state-changing requests
- ✅ SQL injection protection via PDO prepared statements
- ✅ XSS protection via output sanitization
- ✅ Dashboard with statistics
- ✅ Full CRUD for Students (with photo upload)
- ✅ Full CRUD for Departments
- ✅ Profile management & password change
- ✅ Search, pagination, sorting
- ✅ CSV export
- ✅ Responsive design with sidebar navigation

## Installation

### Prerequisites

- PHP 8.0+
- MySQL 5.7+
- Apache/Nginx web server

### Steps

1. **Clone/Copy the project** to your web server root:

   ```bash
   cp -r student-information-system /var/www/html/
   ```

2. **Create the database**:

   ```bash
   sudo mysql -u root < database/sis.sql
   sudo mysql
   SHOW DATABASES;
   USE sis_db;
   SHOW TABLES;
   SELECT * FROM users;
   php -S localhost:8000 -t ../
   sqlmap -u "http://localhost:8000/student-information-system/backend/api/students/get_student_vuln.php?id=1" --dbs --batch --flush-session
   sqlmap -u "http://localhost:8000/student-information-system/backend/api/students/get_student_vuln.php?id=1" -D sis_db -T users --columns --batch --flush-session
   sqlmap -u "http://localhost:8000/student-information-system/backend/api/students/get_student_vuln.php?id=1" -D sis_db -T users --dump --batch --flush-session

   ```

## Cookies checking is valid or not

```bash
curl -v "http://localhost:8000/student-information-system/backend/api/auth/check.php" \
--cookie "SIS_SESSION=ed6699eab11a11634d8ab8e15ba5fc0f"
```

3. **Configure database credentials** in `backend/config/database.php`:

   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'sis_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

4. **Set upload directory permissions**:

   ```bash
   chmod 755 student-information-system/backend/uploads/
   ```

5. **Access the application**:
   ```
   http://localhost/student-information-system/
   ```

### Default Login

- **Username:** `admin`
- **Password:** `password`

## API Endpoints

### Authentication

| Method | Endpoint                 | Description       |
| ------ | ------------------------ | ----------------- |
| POST   | `/api/auth/register.php` | Register new user |
| POST   | `/api/auth/login.php`    | Login             |
| POST   | `/api/auth/logout.php`   | Logout            |
| GET    | `/api/auth/check.php`    | Check session     |
| GET    | `/api/auth/csrf.php`     | Get CSRF token    |

### Students

| Method | Endpoint                   | Description                                     |
| ------ | -------------------------- | ----------------------------------------------- |
| GET    | `/api/students/read.php`   | List students (with search, filter, pagination) |
| POST   | `/api/students/create.php` | Add new student                                 |
| PUT    | `/api/students/update.php` | Update student                                  |
| DELETE | `/api/students/delete.php` | Delete student                                  |

### Departments

| Method | Endpoint                      | Description       |
| ------ | ----------------------------- | ----------------- |
| GET    | `/api/departments/read.php`   | List departments  |
| POST   | `/api/departments/create.php` | Add department    |
| PUT    | `/api/departments/update.php` | Update department |
| DELETE | `/api/departments/delete.php` | Delete department |

### Profile

| Method | Endpoint                           | Description      |
| ------ | ---------------------------------- | ---------------- |
| GET    | `/api/profile/get.php`             | Get user profile |
| POST   | `/api/profile/update.php`          | Update profile   |
| POST   | `/api/profile/change-password.php` | Change password  |

### Dashboard

| Method | Endpoint                   | Description              |
| ------ | -------------------------- | ------------------------ |
| GET    | `/api/dashboard/stats.php` | Get dashboard statistics |

## Security Features

1. **Password Hashing** — `password_hash()` / `password_verify()`
2. **SQL Injection Protection** — PDO prepared statements throughout
3. **XSS Protection** — `htmlspecialchars()` on all output
4. **CSRF Protection** — Token-based validation on POST/PUT/DELETE
5. **Session Security** — HTTP-only cookies, strict mode, periodic regeneration
6. **Session Timeout** — 30-minute inactivity timeout
7. **File Upload Security** — MIME type verification, size limits, safe filenames
8. **Input Validation** — Server-side and client-side validation
9. **Security Headers** — X-Content-Type-Options, X-Frame-Options, X-XSS-Protection

## File Structure

```
student-information-system/
├── index.html
├── database/
│   └── sis.sql
├── backend/
│   ├── config/
│   │   ├── database.php
│   │   ├── cors.php
│   │   └── session.php
│   ├── middleware/
│   │   ├── auth.php
│   │   ├── csrf.php
│   │   └── validator.php
│   ├── api/
│   │   ├── auth/
│   │   ├── students/
│   │   ├── departments/
│   │   ├── profile/
│   │   └── dashboard/
│   └── uploads/
└── frontend/
    ├── pages/
    │   ├── login.html
    │   ├── register.html
    │   ├── dashboard.html
    │   ├── students.html
    │   ├── add-student.html
    │   ├── edit-student.html
    │   ├── departments.html
    │   └── profile.html
    └── assets/
        ├── css/style.css
        └── js/
            ├── axios-config.js
            ├── auth.js
            ├── dashboard.js
            ├── students.js
            ├── departments.js
            └── profile.js
```

## Running with PHP Built-in Server

```bash
 php -S localhost:8000 -t ../
```

## License

This project is developed for educational purposes as part of a Cyber Security Lab assignment.
