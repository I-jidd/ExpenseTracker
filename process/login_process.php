<?php
    session_start();
    require_once '../includes/connection.php';
    
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        // Fetch the user data
        $row = $result->fetch_assoc();
        if(password_verify($password, $row['password'])){
            $_SESSION['username'] = $row['username'];
            header('Location: ../public/index.php?login=success');
            exit();
        }
    }
    else{
        header('Location: ../public/login-page.php?error=Invalid username or password!');
        exit();
    }
    $conn->close();