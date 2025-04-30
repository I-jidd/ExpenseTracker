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
    <title>ExpenseTracker - Sign up</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="../styles/main.css" />
    <link rel="stylesheet" href="../styles/signup.css" />
  </head>
  <body>
    
    <div class="signup-container">
      <div class="signup-card">
        <!-- header -->
        <div class="signup-header">
          <img src="../icons/logo.svg" alt="logo" class="logo" />
          <h1>Create an Account</h1>
          <p>Join us to manage your expenses effectively.</p>
          <?php if (!empty($error)): ?>
            <div class="error-message" id="errorMessage" style="color:red;">
                <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>
        </div>
        <!-- form -->
        <form
          action="../process/signup_process.php"
          method="post"
          class="signup-form"
        >
          <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required />
          </div>
          <div class="form-group">
            <label for="lastname">Last name</label>
            <input type="text" id="lastname" name="lastname" required />
          </div>
          <div class="form-group">
            <label for="username">username</label>
            <input type="text" id="username" name="username" required />
          </div>
          <div class="form-group">
            <label for="password">password</label>
            <input type="password" id="password" name="password" required />
          </div>
          <div class="form-group">
            <label for="confirm-password">Confirm Password</label>
            <input
              type="password" id="confirm-password" name="confirm-password" required />
          </div>
          <button type="submit" class="btn btn-primary signup-primary">
            Sign up
          </button>
        </form>
        <!-- footer -->
        <div class="signup-footer">
          <p>Already have an account? <a href="login-page.php">Login</a></p>
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
