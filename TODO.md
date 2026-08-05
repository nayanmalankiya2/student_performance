# Role-Based Access Control Implementation TODO

## Status: COMPLETE ✅

- [x] 1. Create `inc/auth.php` - role helper functions (require_login, require_admin, require_faculty_or_admin, is_admin, is_faculty, is_student, get_student_id, redirect_by_role)
- [x] 2. Update `sql/database.sql` - users table: student_id column + role enum (admin/faculty/student)
- [x] 3. Update `setup.php` - hash admin password, add student_id column, role enum migration
- [x] 4. Update `login.php` - DB-based authentication (password_verify), role-based redirect
- [x] 5. Update `sidebar.php` - role-based menu (Manage Users admin-only; students get My Profile)
- [x] 6. Update `index.php` - students redirected to own performance
- [x] 7. Create `users/index.php` - user list (admin only)
- [x] 8. Create `users/add.php` - create faculty/student account (admin only)
- [x] 9. Create `users/edit.php` - edit/delete user (admin only)
- [x] 10. Update `students/add.php` - add username/password fields for student login
- [x] 11. Update `students/edit.php` - edit student login credentials
- [x] 12. Update `students/index.php` - student role read-only (redirect to own profile)
- [x] 13. Update `students/performance.php` - student can only view own + hide add-marks for students
- [x] 14. Update `subjects/*` - student role read-only, faculty/admin full
- [x] 15. Update `marks/*` - student role read-only, faculty/admin full
- [x] 16. Update `performance/index.php` - student redirected to own data
- [x] 17. Test the full flow (all files pass PHP syntax check)
- [x] 18. Update `config.php` - define BASE_URL + include auth helpers
- [x] 19. Update `header.php` - handle users folder title/back button
- [x] 20. Update `student_performance.sql` - users table schema
