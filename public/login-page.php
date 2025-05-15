<?php
  session_start();
  if(isset($_SESSION['username'])){
    header('Location: index.php');
    exit();
  }
  $error = $_GET['error'] ?? '';
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
        <?php if (!empty($error)): ?>
            <div class="error-message" id="errorMessage" style="color:red;">
                <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>

        <form
          action="../process/login_process.php"
          method="post"
          class="login-form">
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
              <input type="checkbox" id="remember" name="remember-me" />
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
  <script>
        // Auto-hide error message after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const errorMessage = document.getElementById('errorMessage');
            if (errorMessage) {
                setTimeout(() => {
                    errorMessage.style.opacity = '0';
                    setTimeout(() => {
                        errorMessage.remove();
                    }, 500); // Matches the CSS transition time
                }, 3000); // 3 seconds
            }
        });
    </script>
  </body>
</html>
