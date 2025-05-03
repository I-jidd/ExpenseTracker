<?php
session_start();
require_once '../includes/connection.php';

// Validate logged-in user
if (!isset($_SESSION['username'])) {
    header("Location: ../public/login-page.php");
    exit();
}

// Sanitize inputs
$name = htmlspecialchars($_POST['name']);
$last_name = htmlspecialchars($_POST['last_name']);
$username = htmlspecialchars($_POST['username']);

// Check if new username exists (if changed)
if ($username !== $_SESSION['username']) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        header("Location: ../public/edit_profile.php?error=Username+already+taken");
        exit();
    }
}

// Update profile
$stmt = $conn->prepare("UPDATE users SET name = ?, last_name = ?, username = ? WHERE username = ?");
$stmt->bind_param("ssss", $name, $last_name, $username, $_SESSION['username']);

if ($stmt->execute()) {
    $_SESSION['username'] = $username; // Update session if username changed
    header("Location: ../public/profile.php?success=Profile+updated");
} else {
    header("Location: ../public/edit_profile.php?error=Update+failed");
}
exit();
?>