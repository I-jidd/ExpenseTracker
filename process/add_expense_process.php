<?php
session_start();
require_once '../includes/connection.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../public/login-page.php");
    exit();
}

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: ../public/login-page.php?error=User+not+found");
    exit();
}

$required = ['date', 'description', 'amount'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        header("Location: ../public/add_expense.php?error=Missing+required+fields");
        exit();
    }
}

$date = $_POST['date'];
$description = htmlspecialchars(trim($_POST['description']));
$amount = (float)$_POST['amount'];

if (!empty($_POST['new_category'])) {
    // Process new category
    $new_category = htmlspecialchars(trim($_POST['new_category']));
    
    // Check if category already exists for this user
    $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ? AND user_id = ?");
    $stmt->bind_param("si", $new_category, $user['id']);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    
    if ($existing) {
        $category_id = $existing['id'];
    } else {
        // Insert new category for this user
        $stmt = $conn->prepare("INSERT INTO categories (name, user_id) VALUES (?, ?)");
        $stmt->bind_param("si", $new_category, $user['id']);
        $stmt->execute();
        $category_id = $stmt->insert_id;
    }
} elseif (!empty($_POST['category_id'])) {
    // Use existing category - verify it belongs to current user
    $category_id = (int)$_POST['category_id'];
    $stmt = $conn->prepare("SELECT id FROM categories WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $category_id, $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: ../public/add_expense.php?error=Invalid+category");
        exit();
    }
} else {
    header("Location: ../public/add_expense.php?error=Category+required");
    exit();
}

// Validate amount
if ($amount <= 0) {
    header("Location: ../public/add_expense.php?error=Amount+must+be+positive");
    exit();
}

// Insert expense
$stmt = $conn->prepare("
    INSERT INTO expenses (user_id, date, category_id, description, amount)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("isisd", $user['id'], $date, $category_id, $description, $amount);

if ($stmt->execute()) {
    header("Location: ../public/index.php?success=Expense+added+successfully");
} else {
    header("Location: ../public/add_expense.php?error=Database+error:".$conn->error);
}
exit();
?>