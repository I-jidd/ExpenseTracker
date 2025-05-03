<?php
session_start();
require_once '../includes/connection.php';

// 1. Validate logged-in user
if (!isset($_SESSION['username'])) {
    header("Location: ../public/login-page.php");
    exit();
}

// 2. Get user ID
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: ../public/login-page.php?error=User+not+found");
    exit();
}

// 3. Validate required fields
$required = ['date', 'description', 'amount'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        header("Location: ../public/add_expense.php?error=Missing+required+fields");
        exit();
    }
}

// 4. Sanitize inputs
$date = $_POST['date'];
$description = htmlspecialchars(trim($_POST['description']));
$amount = (float)$_POST['amount'];

// 5. Handle category (existing or new)
if (!empty($_POST['new_category'])) {
    // Process new category
    $new_category = htmlspecialchars(trim($_POST['new_category']));
    
    // Check if category already exists
    $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->bind_param("s", $new_category);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    
    if ($existing) {
        $category_id = $existing['id'];
    } else {
        // Insert new category
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $new_category);
        $stmt->execute();
        $category_id = $stmt->insert_id;
    }
} elseif (!empty($_POST['category_id'])) {
    // Use existing category
    $category_id = (int)$_POST['category_id'];
} else {
    header("Location: ../public/add_expense.php?error=Category+required");
    exit();
}

// 6. Validate amount
if ($amount <= 0) {
    header("Location: ../public/add_expense.php?error=Amount+must+be+positive");
    exit();
}

// 7. Insert expense
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