<?php
session_start();
require_once '../includes/connection.php';

// Validate logged-in user
if (!isset($_SESSION['username'])) {
    header("Location: ../public/login-page.php");
    exit();
}

// Get user ID
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Validate inputs
$required_fields = ['date', 'category_id', 'description', 'amount'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        header("Location: ../public/add_expense.php?error=Missing+required+fields");
        exit();
    }
}

// Sanitize data
$date = $_POST['date'];
$category_id = (int)$_POST['category_id'];
$description = htmlspecialchars($_POST['description']);
$amount = (float)$_POST['amount'];

// Insert expense
$stmt = $conn->prepare("
    INSERT INTO expenses (user_id, date, category_id, description, amount)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("isisd", $user['id'], $date, $category_id, $description, $amount);

if ($stmt->execute()) {
    header("Location: ../public/index.php?success=Expense+added+successfully");
} else {
    header("Location: ../public/add_expense.php?error=Database+error");
}
exit();
?>