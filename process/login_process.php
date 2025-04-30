<?php
    session_start();
    require_once '../includes/connection.php';
    
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if($result->num_rows > 0){
        session_start();
        // Fetch the user data
        $row = $result->fetch_assoc();
        $_SESSION['username'] = $row['username'];
        header('Location: ../public/index.php?login=success');
        exit();
    }
    else{
        header('Location: ../public/login-page.php?error=Invalid username or password!');
        exit();
    }
    $conn->close();