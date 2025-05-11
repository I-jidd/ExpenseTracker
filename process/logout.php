<?php
    session_start();
    require_once '../includes/connection.php';

    $_SESSION = array();

    session_destroy();
    
    // Clear remember me cookie
    if (isset($_COOKIE['remember_me'])){
        // clear the database token
        $token = $_COOKIE['remember_me'];
        $stmt = $conn->prepare("UPDATE users SET remember_token = NULL WHERE remember_token = ?");
        $stmt->bind_param("s", $token);
        $stmt -> execute();

        setcookie('remember_me', '', time() - 3600, '/');
        

    }
    header('Location: ../public/login-page.php?logout=success');
    exit();