# 📚 Student Performance Index (SPI)

A comprehensive **Student Performance Index** web application built with **PHP & MySQL** for tracking, analyzing, and managing student academic performance with role-based access control.

---

## 🎯 Features

### Role-Based Access Control
- **Admin** – Full access to all modules (students, subjects, marks, users, performance reports)
- **Faculty** – Manage students and marks (add, edit, delete marks and student information)
- **Student** – View-only access (personal profile, marks, performance index)

### Core Functionality
- ✅ **User Authentication** – Secure login with role-based redirects
- ✅ **Student Management** – Add/Edit/Delete student information with login credentials
- ✅ **Subject Management** – Define subjects with subject codes and max marks
- ✅ **Marks Entry** – Record internal & external marks with automatic grade calculation
- ✅ **Performance Reports** – Visual ranking of students based on performance index (CGPA)
- ✅ **Responsive Design** – Works on desktop, tablet, and mobile devices
- ✅ **User Management** – Admin can manage faculty and student user accounts

### Technology Stack
- **Frontend**: HTML5, CSS3, Bootstrap 5, Font Awesome Icons
- **Backend**: PHP 7.0+
- **Database**: MySQL 5.7+
- **Server**: Apache (via XAMPP)

---

## 📋 Prerequisites

Before starting, ensure you have:

### Required Software
1. **XAMPP** (Apache + MySQL + PHP)
   - Download: https://www.apachefriends.org/
   - Includes PHP, Apache, MySQL, and phpMyAdmin

2. **Git** (optional, for cloning)
   - Download: https://git-scm.com/

3. **Text Editor / IDE**
   - Visual Studio Code (recommended)
   - Or any PHP-compatible editor

### System Requirements
- Windows 10/11, macOS, or Linux
- At least 500 MB free disk space
- PHP 7.0 or higher
- MySQL 5.7 or higher

---

## 🚀 Installation & Setup Guide

### Step 1️⃣ – Start XAMPP

1. Open **XAMPP Control Panel**
2. Click **Start** next to Apache
3. Click **Start** next to MySQL
4. Wait for both to show green status

```
Status:
✅ Apache - Running
✅ MySQL - Running
```

### Step 2️⃣ – Clone or Copy Project

#### Option A: Clone from GitHub
```bash
git clone https://github.com/nayanmalankiya2/student_performance.git
cd student_performance
```

#### Option B: Manual Download & Copy
1. Download the project as ZIP
2. Extract to XAMPP htdocs folder: `C:\xampp\htdocs\student_performance\`

### Step 3️⃣ – Open Application in Browser

1. Open any web browser (Chrome, Firefox, Safari, Edge)
2. Navigate to:
   ```
   http://localhost/student_performance/setup.php
   ```

### Step 4️⃣ – Run Database Setup

1. On the **Setup Page**, you'll see:
   ```
   Database Setup
   ✅ Database and tables created successfully!
   ```

2. If setup fails, verify:
   - XAMPP MySQL is running (check green status in Control Panel)
   - Port 3306 is not in use
   - No other application is using MySQL

3. Once successful, click **"Go to Login"** button

### Step 5️⃣ – Login to Application

1. You'll be redirected to the **Login Page**
2. Use default admin credentials:
   ```
   Username: admin
   Password: admin123
   ```

3. Click **Login** button

### Step 6️⃣ – Start Using the Application

After successful login, you'll see the **Admin Dashboard** with:
- Overview cards (Total Students, Subjects, Records, Avg Performance)
- Top performer information
- Recent students list
- Quick action buttons

---

## 🔑 Demo Login Credentials

Use these credentials to test different user roles:

| Role   | Username | Password     |
|--------|----------|--------------|
| Admin  | `admin`  | `admin123`   |
| Faculty| `faculty`| `faculty123` |
| Student| `student`| `student123` |

**Note**: For student login, you must have a student record linked to the user account (done via Admin panel).

---

## 📁 Project Structure

```
student_performance/
├── config/
│   └── config.php              # Database connection & configuration
├── inc/
│   └── auth.php                # Authentication & authorization functions
├── assets/
│   └── css/
│       └── style.css           # Custom styles & Bootstrap customization
├── sql/
│   └── database.sql            # Database schema (optional)
├── users/
│   ├── index.php               # List all users (Admin only)
│   ├── add.php                 # Add new user/faculty (Admin only)
│   └── edit.php                # Edit/Delete users (Admin only)
├── students/
│   ├── index.php               # List all students
│   ├── add.php                 # Add new student
│   ├── edit.php                # Edit student information
│   ├── delete.php              # Delete student
│   └── performance.php         # View student performance details
├── subjects/
│   ├── index.php               # List all subjects
│   ├── add.php                 # Add new subject
│   ├── edit.php                # Edit subject
│   └── delete.php              # Delete subject
├── marks/
│   ├── index.php               # List all marks records
│   ├── add.php                 # Add marks entry
│   └── edit.php                # Edit marks
├── performance/
│   └── index.php               # Performance ranking report
├── header.php                  # Navigation header component
├── sidebar.php                 # Sidebar navigation component
├── login.php                   # User login page
├── logout.php                  # User logout
├── setup.php                   # Database setup wizard
└── index.php                   # Dashboard (home page)
```

---

## 🗄️ Database Schema

### Users Table
```sql
- id (INT) - Primary key
- username (VARCHAR) - Unique login name
- email (VARCHAR) - Unique email address
- password (VARCHAR) - Hashed password
- role (ENUM) - 'admin', 'faculty', 'student'
- student_id (INT) - Link to student record (for students only)
- created_at (TIMESTAMP) - Account creation time
```

### Students Table
```sql
- id (INT) - Primary key
- enrollment_no (VARCHAR) - Unique enrollment number
- first_name (VARCHAR) - Student first name
- last_name (VARCHAR) - Student last name
- email (VARCHAR) - Student email
- phone (VARCHAR) - Contact number
- gender (ENUM) - Male, Female, Other
- date_of_birth (DATE) - DOB
- address (TEXT) - Address
- semester (INT) - Current semester
- course (VARCHAR) - Course name (B.Tech, M.Tech, etc.)
- profile_image (VARCHAR) - Profile picture path
- created_by (INT) - Admin/Faculty who added student
- created_at (TIMESTAMP) - Creation timestamp
```

### Subjects Table
```sql
- id (INT) - Primary key
- subject_code (VARCHAR) - Unique subject code
- subject_name (VARCHAR) - Subject name
- semester (INT) - Semester offered
- max_marks (INT) - Maximum marks (default 100)
- created_at (TIMESTAMP) - Creation timestamp
```

### Marks Table
```sql
- id (INT) - Primary key
- student_id (INT) - Student reference
- subject_id (INT) - Subject reference
- semester (INT) - Semester
- internal_marks (DECIMAL) - Internal/Continuous marks
- external_marks (DECIMAL) - External/Final exam marks
- total_marks (DECIMAL) - Sum of internal + external
- grade (VARCHAR) - Calculated grade (A, B, C, D, F)
- exam_year (YEAR) - Year of exam
- created_by (INT) - Faculty/Admin who added marks
- created_at (TIMESTAMP) - Creation timestamp
```

---

## 🔧 Troubleshooting

### Issue 1: "Cannot connect to MySQL" on Setup Page
**Causes:**
- XAMPP MySQL is not running
- Port 3306 is already in use

**Solutions:**
```
1. Open XAMPP Control Panel
2. Make sure MySQL shows green status
3. If red, click "Start" next to MySQL
4. Wait 3-5 seconds for it to turn green
5. Refresh the setup.php page in browser
```

### Issue 2: Database not created after setup
**Solutions:**
```
1. Check XAMPP MySQL status (must be green)
2. Try phpMyAdmin: http://localhost/phpmyadmin
3. If you can access phpMyAdmin, MySQL is working
4. Click Retry Setup button on setup.php
5. If still fails, manually import sql/database.sql via phpMyAdmin
```

### Issue 3: Login page shows "Session not found" error
**Solutions:**
```
1. Make sure config.php is in config/ folder
2. Check PHP error logs in C:\xampp\apache\logs\
3. Clear browser cookies and cache
4. Try accessing via http://localhost/student_performance/ (not HTTPS)
```

### Issue 4: Images/CSS not loading properly
**Solutions:**
```
1. Make sure all files are in C:\xampp\htdocs\student_performance\
2. Check file permissions (should be readable by Apache)
3. Clear browser cache (Ctrl+Shift+Delete or Cmd+Shift+Delete)
4. Try a different browser
```

### Issue 5: PHP errors visible on page
**Solutions:**
```
1. Open config/config.php
2. Check database connection details:
   - Server: localhost
   - Username: root
   - Password: (usually blank)
   - Database: student_performance
3. Verify these settings match your MySQL setup
```

---

## 📊 Usage Guide

### For Admins

#### 1. Add a New Faculty User
1. Login with admin account
2. Click **"Manage Users"** in sidebar
3. Click **"Add New User"** button
4. Fill in details:
   - Username (e.g., `prof_smith`)
   - Email (e.g., `prof@example.com`)
   - Password
   - Select Role: **Faculty**
5. Click **"Save"**

#### 2. Add Students
1. Click **"Students"** → **"Add New Student"**
2. Fill student information:
   - Enrollment Number (unique ID)
   - Name, Email, Phone
   - Semester, Course, Gender, DOB
3. Scroll down → Check **"Create Login Credentials"**
4. Set Username & Password for student
5. Click **"Save"**

#### 3. Add Subjects
1. Click **"Subjects"** → **"Add New Subject"**
2. Enter:
   - Subject Code (e.g., `CS101`)
   - Subject Name
   - Semester
   - Max Marks
3. Click **"Save"**

#### 4. Add Marks
1. Click **"Marks"** → **"Add Marks"**
2. Select:
   - Student
   - Subject
   - Semester
   - Internal Marks (0-50)
   - External Marks (0-50)
3. Grade is **auto-calculated**
4. Click **"Save"**

#### 5. View Performance Report
1. Click **"Performance Reports"** in sidebar
2. See all students ranked by performance
3. Click **"View Details"** to see individual performance

### For Faculty

- ✅ View all students
- ✅ Add/Edit/Delete student records
- ✅ View and manage marks
- ✅ Cannot access user management or admin features

### For Students

- ✅ View personal profile
- ✅ View own marks and grades
- ✅ View own performance index
- ✅ No edit/delete access

---

## 🔐 Security Features

- ✅ **Password Hashing** – Uses PHP `password_hash()` function
- ✅ **Session Management** – User sessions with login/logout
- ✅ **Role-Based Access Control** – Menu and function-level restrictions
- ✅ **SQL Injection Protection** – Prepared statements
- ✅ **XSS Protection** – HTML special characters escaped

---

## 📝 Common Workflows

### Workflow 1: Complete Student Setup
```
1. Admin creates Student user account with login
2. Admin adds Student record and links to user
3. Faculty/Admin adds subjects
4. Faculty/Admin records marks for student
5. Student logs in and views performance
```

### Workflow 2: Check Top Performer
```
1. Login as Admin/Faculty
2. Click "Performance Reports"
3. See student ranking
4. Click crown icon (🏆) for #1 student
5. View their detailed performance breakdown
```

### Workflow 3: Update Student Marks
```
1. Click "Marks" → "Edit" for a mark record
2. Update Internal/External marks
3. Grade auto-recalculates
4. Click "Update"
5. Student sees new marks on their dashboard
```

---

## 🛠️ Server Configuration

### Apache Configuration
If you get "404 Not Found" errors:
1. Make sure project folder is: `C:\xampp\htdocs\student_performance\`
2. Or update URL to match your folder name: `http://localhost/your_folder_name/`

### PHP Configuration
If you get file upload errors:
1. Check `php.ini` upload settings
2. Default location: `C:\xampp\php\php.ini`
3. Key settings:
   ```
   upload_max_filesize = 20M
   post_max_size = 20M
   ```

### MySQL Configuration
Default connection in `config/config.php`:
```php
$server = "localhost";
$uname = "root";
$pwd = "";
$database = "student_performance";
```

---

## 📞 Support & Troubleshooting Checklist

Before contacting support, verify:
- [ ] XAMPP Apache is running (green status)
- [ ] XAMPP MySQL is running (green status)
- [ ] Project folder is in `C:\xampp\htdocs\`
- [ ] You can access `http://localhost/phpmyadmin/`
- [ ] Database `student_performance` exists in phpMyAdmin
- [ ] Browser cache is cleared (Ctrl+Shift+Delete)
- [ ] You're not using HTTPS (should be HTTP for local)

---

## 🎯 Next Steps

1. **Customize Styling** – Edit `assets/css/style.css`
2. **Add More Features** – Extend students/marks modules
3. **Deploy Online** – Host on web server with proper SSL
4. **Backup Database** – Export from phpMyAdmin regularly
5. **User Training** – Create user guides for faculty/students

---

## 📄 License

This project is open source. Feel free to use and modify for educational purposes.

---

## 👨‍💻 Author

**Nayan Malankiya**
- GitHub: https://github.com/nayanmalankiya2
- Repository: https://github.com/nayanmalankiya2/student_performance

---

## 🎉 You're All Set!

```
✅ Installation complete
✅ Database created
✅ Admin account ready
✅ Ready to add students and subjects!

Happy tracking! 📊
```

---

## 💡 Tips & Best Practices

1. **Regular Backups** – Export database weekly
2. **Change Default Password** – Update admin password after first login
3. **Test Roles** – Try each role to understand access levels
4. **Keep Records Clean** – Delete test data before production
5. **Document Changes** – Keep notes of customizations made

---

**For more help or issues, refer to the Troubleshooting section above.** 🚀
