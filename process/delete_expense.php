<?php
session_start();
require_once '../includes/connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login-page.php");
    exit();
}

// Get expense ID from URL
$expenseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Validate ID
if (!$expenseId) {
    header("Location: ../public/index.php?error=Invalid+expense+ID");
    exit();
}

// Verify expense belongs to current user
$stmt = $conn->prepare("SELECT e.user_id FROM expenses e JOIN users u ON e.user_id = u.id WHERE e.id = ? AND u.username = ?");
$stmt->bind_param("is", $expenseId, $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../public/index.php?error=Expense+not+found+or+not+authorized");
    exit();
}

// Delete the expense
$deleteStmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
$deleteStmt->bind_param("i", $expenseId);

if ($deleteStmt->execute()) {
    header("Location: ../public/index.php?success=Expense+deleted+successfully");
} else {
    header("Location: ../public/index.php?error=Error+deleting+expense");
}
exit();