<?php
session_start();
require_once '../includes/connection.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../public/login-page.php");
    exit();
}

// Get user ID
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: ../public/login-page.php?error=User+not+found");
    exit();
}

if (empty($_POST['category_name'])) {
    header("Location: ../public/categories.php?error=Category+name+is+required");
    exit();
}

$category_name = htmlspecialchars(trim($_POST['category_name']));

// Check if category already exists for this user
$stmt = $conn->prepare("SELECT id FROM categories WHERE name = ? AND user_id = ?");
$stmt->bind_param("si", $category_name, $user['id']);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();

if ($existing) {
    header("Location: ../public/categories.php?error=Category+already+exists");
    exit();
}

// Insert new category
$stmt = $conn->prepare("INSERT INTO categories (name, user_id) VALUES (?, ?)");
$stmt->bind_param("si", $category_name, $user['id']);

if ($stmt->execute()) {
    header("Location: ../public/categories.php?success=Category+added+successfully");
} else {
    header("Location: ../public/categories.php?error=Error+adding+category");
}
exit();
?>