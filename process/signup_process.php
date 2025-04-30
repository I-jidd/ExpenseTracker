<?php
    session_start();
    require_once '../includes/connection.php';

    if($_POST['password'] !== $_POST['confirm-password']){
        header('Location: ../public/signup-page.php?error=Passwords do not match!');
        exit();
    }
    $name = $_POST['name'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    //check if the username already exists in the database
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        header('Location: ../signup-page.php?error=Username already exists!');
        exit();
    }

    // Insert new user into the database
    $sql = "INSERT INTO users (name, last_name, username, password) VALUES ('$name', '$lastname', '$username', '$password')";

    if($conn->query($sql) === TRUE) {
        header('Location: ../public/login-page.php?success=Account created successfully! Please log in.');
        exit();
    } else {
        header('Location: ../signup-page.php?error=Error creating account!');
        exit();
    }