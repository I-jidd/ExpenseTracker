<?php
    session_start();
    require_once '../includes/connection.php';



    if (!isset($_SESSION['username'])) {
        header("Location: ../public/login-page.php");
        exit();
    }

    // Validate and sanitize inputs
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $date = $_POST['date'] ?? '';
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $description = htmlspecialchars(trim($_POST['description'] ?? ''));
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);

    // Basic validation
    if (!$id || !$date || !$category_id || !$description || !$amount) {
        header("Location: ../public/index.php?error=Invalid+input");
        exit();
    }

    // Update the expense
    $stmt = $conn->prepare("UPDATE expenses SET date=?, category_id=?, description=?, amount=? WHERE id=?");
    $stmt->bind_param("sisdi", $date, $category_id, $description, $amount, $id);

    if ($stmt->execute()) {
        header("Location: ../public/index.php?success=Expense+updated+successfully");
    } else {
        header("Location: ../public/index.php?error=Error+updating+expense");
    }
    exit();