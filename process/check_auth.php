<?php
    session_start();
    require_once '../includes/connection.php';

    if(isset($_SESSION['user_id'])){
        return;
    }

    if(isset($_COOKIE['remember_me'])){
        $token = $_COOKIE['remember_me'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE remember_token = ?");
        $stmt -> bind_param("s", $token);
        $stmt -> execute();
        $result = $stmt -> get_result();

        if($result->num_rows > 0){
            $row = $result->fetch_assoc();
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            
        } else{
            // Invalid toke -clear the cookie
            setcookie('remember_me', '', time() - 3600, "/");
            header("Location: ../public/login-page.php");
            exit();
        } 
    } else{
            header("Location: ../public/login-page.php")
            exit();
        }
?>