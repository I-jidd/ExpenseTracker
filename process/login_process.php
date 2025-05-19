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
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];

            if(isset($_POST['remember-me'])){
                $token = bin2hex(random_bytes(32));
                $expiry = time() + (86400 * 7); // 1 week

                $stmt = $conn->prepare("UPDATE users SET remember_token = ?,
                                        token_expires_at = FROM_UNIXTIME(?)
                                        WHERE id = ?");

                $stmt->bind_param('ssi', $token, $expiry, $row['id']);
                $stmt->execute();
                
                setcookie('remember_me', $token, [
                    'expires' => $expiry,
                    'path' => '/',
                    'secure' => true,      
                    'httponly' => true,        
                ]);
            }
            header('Location: ../public/index.php?login=success');
            exit();
        }
        else{
            header('Location: ../public/login-page.php?error=Invalid username or password!');
            exit();
        }
    }
    else{
        header('Location: ../public/login-page.php?error=Invalid username or password!');
        exit();
    }
    $conn->close();