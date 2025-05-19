<?php
session_start();
require_once '../includes/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../public/edit_profile.php?error=Invalid request method");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login-page.php");
    exit();
}

// Get form data
$name = $_POST['name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$username = $_POST['username'] ?? '';
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Basic validation
if (empty($name) || empty($last_name) || empty($username)) {
    header("Location: ../public/edit_profile.php?error=All fields are required");
    exit();
}

// Check if password fields are filled
$change_password = !empty($current_password) || !empty($new_password) || !empty($confirm_password);

if ($change_password) {
    // All password fields must be filled
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        header("Location: ../public/edit_profile.php?error=All password fields are required to change password");
        exit();
    }
    
    // New passwords must match
    if ($new_password !== $confirm_password) {
        header("Location: ../public/edit_profile.php?error=New passwords do not match");
        exit();
    }
    
    // Password strength check (optional)
    if (strlen($new_password) < 8) {
        header("Location: ../public/edit_profile.php?error=Password must be at least 8 characters long");
        exit();
    }
    
    // Verify current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!password_verify($current_password, $user['password'])) {
        header("Location: ../public/edit_profile.php?error=Current password is incorrect");
        exit();
    }
    
    // Hash new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
}

try {
    // Update user data
    if ($change_password) {
        $stmt = $conn->prepare("UPDATE users SET name = ?, last_name = ?, username = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $last_name, $username, $hashed_password, $_SESSION['user_id']);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, last_name = ?, username = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $last_name, $username, $_SESSION['user_id']);
    }
    
    $stmt->execute();
    
    // Update session username if changed
    $_SESSION['username'] = $username;
    
    header("Location: ../public/edit_profile.php?success=Profile updated successfully");
    exit();
} catch (mysqli_sql_exception $e) {
    // Check for duplicate username
    if ($e->getCode() === 1062) {
        header("Location: ../public/edit_profile.php?error=Username already exists");
        exit();
    }
    header("Location: ../public/edit_profile.php?error=An error occurred");
    exit();
}