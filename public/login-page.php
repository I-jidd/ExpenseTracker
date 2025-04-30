<?php
  session_start();
  if(isset($_SESSION['username'])){
    header('Location: index.php');
    exit();
  }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ExpenseTracker - Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="../styles/main.css" />
    <link rel="stylesheet" href="../styles/login.css" />
  </head>
  <body>
    <div class="login-container">
      <div class="login-card">
        <div class="login-header">
          <img src="../icons/logo.svg" alt="logo" class="logo" />
          <h1>Welcome Back</h1>
          <p>Please sign in to your account</p>
        </div>

        <form
          action="../process/login_process.php"
          method="post"
          class="login-form"
        >
          <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required />
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required />
          </div>

          <div class="form-options">
            <div class="remember-me">
              <input type="checkbox" id="remember" name="remember" />
              <label for="remember">Remember me</label>
            </div>
            <a href="forgot_password.php" class="forgot-password"
              >Forgot password?</a
            >
          </div>

          <button type="submit" class="btn btn-primary login-btn">Login</button>
        </form>

        <div class="login-footer">
          <p>Don't have an account? <a href="signup-page.php">Sign up</a></p>
        </div>
      </div>
    </div>
  </body>
</html>
