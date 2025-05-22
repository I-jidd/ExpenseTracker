<?php
session_start();
require_once '../includes/connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../public/login-page.php");
    exit();
}

$categoryId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if(!$categoryId){
    header("Location: ../public/categories.php?error=Invalid+category+ID");
    exit();
}

// Get user ID
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Verify category belongs to current user
$stmt = $conn->prepare("SELECT id FROM categories WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $categoryId, $user['id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../public/categories.php?error=Category+not+found+or+not+authorized");
    exit();
}

// Check if category is being used in expenses
$stmt = $conn->prepare("SELECT COUNT(*) as expense_count FROM expenses WHERE category_id = ?");
$stmt->bind_param("i", $categoryId);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc();

if ($count['expense_count'] > 0) {
    header("Location: ../public/categories.php?error=Cannot+delete+category+with+existing+expenses");
    exit();
}

// Delete the category
$deleteStmt = $conn->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
$deleteStmt->bind_param("ii", $categoryId, $user['id']);

if ($deleteStmt->execute()) {
    header("Location: ../public/categories.php?success=Category+deleted+successfully");
} else {
    header("Location: ../public/categories.php?error=Error+deleting+category");
}
exit();
?>