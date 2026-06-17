================================================================
  STUDENT FEEDBACK MANAGEMENT SYSTEM
  B.Tech AI & Data Science Portfolio Project
================================================================

PROJECT OVERVIEW
----------------
A complete web-based Student Feedback Management System built
with PHP, MySQL, HTML, CSS, JavaScript, and Bootstrap 5.
Runs on XAMPP (local server).

TECH STACK
----------
- Backend  : PHP 8.x (with MySQLi, prepared statements)
- Database : MySQL 5.7+ / MariaDB
- Frontend : HTML5, CSS3, Bootstrap 5, JavaScript
- Charts   : Chart.js 4.x
- Icons    : Font Awesome 6
- Server   : Apache (XAMPP)

FOLDER STRUCTURE
----------------
Student_Feedback_System/
├── index.php                  ← Student Feedback Form (public)
├── config/
│   └── db.php                 ← Database connection & helpers
├── admin/
│   ├── login.php              ← Admin Login
│   ├── dashboard.php          ← Admin Dashboard with charts
│   ├── feedbacks.php          ← View / Search / Filter / Delete
│   ├── reports.php            ← Analytics & Charts
│   └── logout.php             ← Logout
├── assets/
│   ├── css/style.css          ← Main stylesheet
│   └── js/main.js             ← Client-side JS & validation
├── database/
│   └── student_feedback_system.sql  ← Full database SQL
└── README.txt                 ← This file

================================================================
  SETUP INSTRUCTIONS (XAMPP)
================================================================

STEP 1 – Install XAMPP
-----------------------
1. Download XAMPP from https://www.apachefriends.org
2. Install it (default path: C:\xampp on Windows)
3. Open XAMPP Control Panel
4. Start "Apache" and "MySQL" modules

STEP 2 – Copy Project Files
-----------------------------
1. Copy the entire "Student_Feedback_System" folder to:
   Windows : C:\xampp\htdocs\
   Mac/Linux: /opt/lampp/htdocs/

   Final path: C:\xampp\htdocs\Student_Feedback_System\

STEP 3 – Import Database
--------------------------
1. Open browser → go to: http://localhost/phpmyadmin
2. Click "New" on the left sidebar
3. Create database named: student_feedback_system
4. Click on the new database
5. Click "Import" tab at the top
6. Click "Choose File" → select:
   Student_Feedback_System/database/student_feedback_system.sql
7. Click "Go" to import

STEP 4 – Configure Database (if needed)
-----------------------------------------
Open: config/db.php
Edit these lines if your MySQL settings differ:

  define('DB_HOST', 'localhost');   // Usually 'localhost'
  define('DB_USER', 'root');        // Your MySQL username
  define('DB_PASS', '');            // Your MySQL password (blank by default)
  define('DB_NAME', 'student_feedback_system');

STEP 5 – Run the Project
--------------------------
Open browser and go to:
  http://localhost/Student_Feedback_System/

Admin Panel:
  http://localhost/Student_Feedback_System/admin/login.php

================================================================
  DEFAULT LOGIN CREDENTIALS
================================================================

  Username : admin
  Password : admin123

IMPORTANT: Change the password after first login in production!

================================================================
  FEATURES LIST
================================================================

STUDENT (PUBLIC) MODULE:
  ✅ Feedback Form with all required fields
  ✅ 6-criteria Star Rating System (1–5 stars)
  ✅ Anonymous Feedback option
  ✅ Acknowledgement Number generation
  ✅ Client-side JS validation
  ✅ Server-side PHP validation
  ✅ SQL Injection protection (prepared statements)
  ✅ XSS Prevention (htmlspecialchars)
  ✅ Dark / Light Mode toggle
  ✅ Fully Responsive (Bootstrap 5)

ADMIN MODULE:
  ✅ Secure Login with session management
  ✅ Dashboard with stat cards & charts
  ✅ View all feedbacks in paginated table
  ✅ Search by name, faculty, acknowledgement no
  ✅ Filter by Department / Faculty / Subject
  ✅ Sort by date (newest/oldest)
  ✅ View full feedback in modal
  ✅ Delete feedback
  ✅ Export to CSV
  ✅ Reports & Analytics (4 Chart.js charts)
    - Faculty Rating Analysis (bar chart)
    - Department Distribution (doughnut)
    - Subject-wise Feedback (horizontal bar)
    - Monthly Trend (line chart)
  ✅ Faculty detailed rating table
  ✅ Pagination (15 per page)

================================================================
  FUTURE ENHANCEMENTS (AI Ideas for Portfolio)
================================================================

1. AI Sentiment Analysis
   → Use Python (TextBlob/VADER) or Hugging Face API
   → Analyze "strengths", "improvements", "feedback" text
   → Auto-tag as Positive / Neutral / Negative

2. Faculty Performance Predictor
   → Train ML model on rating data
   → Predict improvement areas

3. NLP Word Cloud
   → Extract keywords from feedback text
   → Display as word cloud visualization

4. Email Notifications
   → Send acknowledgement email to students
   → Weekly report email to admin

5. QR Code Feedback
   → Generate QR code per faculty/subject
   → Students scan to open pre-filled form

================================================================
  TROUBLESHOOTING
================================================================

Problem: "Connection refused" or blank page
Solution: Make sure Apache and MySQL are running in XAMPP

Problem: "Table doesn't exist" error
Solution: Re-import the SQL file from Step 3

Problem: Admin password not working
Solution: The SQL uses password_hash(). If it fails, open
          config/db.php - there's a plain-text fallback for 'admin123'

Problem: CSS/JS not loading
Solution: Make sure the folder is directly inside htdocs/
          not in a subfolder like htdocs/projects/Student_...

Problem: Port conflict (Apache/MySQL won't start)
Solution: Change Apache port to 8080 in XAMPP config
          Then access: http://localhost:8080/Student_Feedback_System/

================================================================
  VS CODE SETUP (RECOMMENDED)
================================================================

1. Install VS Code: https://code.visualstudio.com
2. Install extensions:
   - PHP Intelephense (PHP intellisense)
   - PHP Debug (for debugging)
   - Live Server (for HTML preview)
   - MySQL (database browser)
   - Prettier (code formatting)
3. Open the Student_Feedback_System folder in VS Code
4. Use XAMPP to run — VS Code is just the editor

================================================================
  CREDITS
================================================================

Built by  : B.Tech AI & Data Science Student
Framework : Bootstrap 5 by Twitter
Charts    : Chart.js
Icons     : Font Awesome 6
Server    : XAMPP (Apache + MySQL + PHP)

================================================================
