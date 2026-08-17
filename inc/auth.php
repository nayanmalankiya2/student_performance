<?php
/**
 * Authentication & Role-Based Access Control helpers
 * Roles: admin, faculty, student
 */

// Require the user to be logged in
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "/login.php");
        exit();
    }
}

// Require admin role
function require_admin() {
    require_login();
    if ($_SESSION['role'] !== 'admin') {
        header("Location: " . BASE_URL . "/index.php");
        exit();
    }
}

// Require faculty role only (not admin)
function require_faculty() {
    require_login();
    if (!is_faculty()) {
        header("Location: " . BASE_URL . "/index.php");
        exit();
    }
}

// Require admin or faculty
function require_faculty_or_admin() {
    require_login();
    if (!in_array($_SESSION['role'], ['admin', 'faculty'])) {
        header("Location: " . BASE_URL . "/index.php");
        exit();
    }
}

// Check if user is admin
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Check if user is faculty
function is_faculty() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'faculty';
}

// Check if user is student
function is_student() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

// Get current user's linked student id (for students)
function get_student_id() {
    return isset($_SESSION['student_id']) ? (int)$_SESSION['student_id'] : 0;
}

// Redirect based on role after login
function redirect_by_role() {
    if (is_admin() || is_faculty()) {
        header("Location: " . BASE_URL . "/index.php");
    } else {
        header("Location: " . BASE_URL . "/students/performance.php?from=login");
    }
    exit();
}
?>
