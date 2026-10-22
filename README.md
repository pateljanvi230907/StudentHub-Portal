# StudentHub Portal

## 1. Project Overview

StudentHub Portal is a student portal project developed using HTML, CSS, JavaScript, PHP, and MySQL. It brings common student-related pages and functions together in one website, such as courses, assignments, attendance, study materials, results, profile, settings, and events.

This project is intended for learning and demonstrating front-end development, PHP integration, database operations, authentication, and local deployment.

## 2. Technologies Used

- **HTML** — page structure
- **CSS** — styling and responsive layout
- **JavaScript** — client-side interactions
- **PHP** — server-side processing
- **MySQL** — database storage
- **Apache and MySQL through XAMPP** — local development environment
- **GitHub** — source-code hosting and submission

## 3. Main Project Areas

The project includes pages and modules for:

- Login and user registration
- Home page and dashboard
- Courses
- Assignments
- Attendance
- Study materials
- Results
- Student profile and settings
- Events and event registration
- About and FAQ pages
- Administrator dashboard and event management, where configured

The availability and behavior of individual modules depend on the files and database included in the submitted project.

## 4. Requirements

Install the following software:

- XAMPP with Apache, PHP, MySQL/MariaDB, and phpMyAdmin
- A modern web browser
- A code editor such as Visual Studio Code (optional)
- GitHub account for source-code submission

## 5. Run the Project Locally

1. Install and open XAMPP.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Copy the project folder into the XAMPP web directory:

   `C:\xampp\htdocs\STUDENTHUB\`

4. Open phpMyAdmin:

   `http://localhost/phpmyadmin/`

5. Create or select the database named `studenthub_db`.
6. Import the project's SQL export (for example, `studenthub_db.sql`) using the **Import** tab. If the SQL file is not included, export the database from the existing local project before setting up another environment.
7. Check the database settings in `db_mysqli.php` and any other database connection file used by the project. For a standard local XAMPP setup, the default configuration is often host `localhost`, user `root`, and an empty password; use the values configured on your own machine.
8. Open the project in your browser:

   `http://localhost/STUDENTHUB/`

9. Use an account already registered in the database, or register through the project's registration page if it is working.

**Note:** The project uses PHP and MySQL, so opening the HTML files directly from the file system will not run the PHP functionality. Open the project through `localhost` with Apache running.

## 6. Database

The project uses a MySQL database named `studenthub_db`. Depending on the modules included, the database may contain tables for students, events, event registrations, registrations, and audit logs.

Import the SQL dump supplied with the project to recreate its database structure and any included sample data. Do not publish real student information, passwords, or other private data in a public repository.

## 7. Testing Checklist

Complete these checks and record the actual result before submission:

- [ ] Apache and MySQL start successfully in XAMPP.
- [ ] The project opens at `http://localhost/STUDENTHUB/`.
- [ ] Navigation links open the correct pages.
- [ ] Registration validates required fields and handles duplicate accounts as expected.
- [ ] Login succeeds with valid credentials and rejects invalid credentials.
- [ ] Logout ends the session.
- [ ] Student accounts cannot access administrator-only pages.
- [ ] Database operations work for the modules being demonstrated.
- [ ] Forms display useful validation or error messages.
- [ ] Pages remain usable at desktop and mobile screen sizes.
- [ ] No database passwords, secret credentials, or real personal data are exposed in the repository.

Mark a check only after testing it. Record any failed checks and the fix applied in the practical test report.

## 8. Security Notes

- Passwords should be stored as hashes using PHP password-hashing functions, not as plain text.
- Server-side validation should be used in addition to browser-side validation.
- Database queries that use user input should use prepared statements.
- Pages that require authentication or administrator privileges should check the session and user role on the server.
- Keep database credentials and private information out of public repositories.
- Use only test accounts and sample data when demonstrating the project.

## 9. GitHub Submission

The GitHub repository should contain the project source code, this `README.md`, the database SQL dump if it is safe to share, and the practical test report and screenshots if required by the instructor.

To submit, copy the URL of the repository page and provide it to the instructor. GitHub hosts the source code; it does not automatically run this PHP/MySQL application. The application can be demonstrated locally using XAMPP unless it is separately deployed to a PHP-compatible hosting service.

## 10. Project Details

- **Project name:** StudentHub Portal
- **Project type:** Student portal web application
- **Front end:** HTML, CSS, JavaScript
- **Back end:** PHP
- **Database:** MySQL
- **Local server:** Apache through XAMPP
- **Source-code hosting:** GitHub

---

*Prepared for Practical 15: Final Integration, Testing, Documentation, GitHub Submission, and Local Deployment.*
