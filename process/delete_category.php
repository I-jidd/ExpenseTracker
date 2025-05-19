<?php
    session_start();
    require_once '../includes/connection.php';

    if(!isset($_SESSION['user_id'])){
        header("Location: ../public/login-page.php");
        exit();
    }

    $categoryId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if(!$categoryId){
        header("Location: ../public/index.php?error=Invalid+category+ID");
        exit();
    }

    // Verify category belongs to current user
    $stmt = $conn->prepare("SELECT c.user_id FROM categories c JOIN users u ON c.user_id = u.id WHERE c.id = ? AND u.username = ?");
    $stmt->bind_param("is", $categoryId, $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->)