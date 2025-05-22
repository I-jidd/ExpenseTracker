
<?php
session_start();
require_once '../includes/connection.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../public/login-page.php");
    exit();
}

// Get user ID
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: ../public/login-page.php?error=User+not+found");
    exit();
}

$categoryId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$categoryId) {
    header("Location: ../public/categories.php?error=Invalid+category+ID");
    exit();
}

// Handle form submission (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = htmlspecialchars(trim($_POST['category_name'] ?? ''));
    
    if (empty($categoryName)) {
        header("Location: edit_category.php?id=$categoryId&error=Category+name+is+required");
        exit();
    }
    
    // Check if category name already exists for this user (excluding current category)
    $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ? AND user_id = ? AND id != ?");
    $stmt->bind_param("sii", $categoryName, $user['id'], $categoryId);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    
    if ($existing) {
        header("Location: edit_category.php?id=$categoryId&error=Category+name+already+exists");
        exit();
    }
    
    // Update the category
    $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $categoryName, $categoryId, $user['id']);
    
    if ($stmt->execute()) {
        header("Location: ../public/categories.php?success=Category+updated+successfully");
    } else {
        header("Location: edit_category.php?id=$categoryId&error=Error+updating+category");
    }
    exit();
}

// Get category data for editing (GET request)
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $categoryId, $user['id']);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();

if (!$category) {
    header("Location: ../public/categories.php?error=Category+not+found+or+not+authorized");
    exit();
}

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExpenseTracker - Edit Category</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/main.css">
    <link rel="stylesheet" href="../styles/edit_profile.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <div class="logo">
                    <a href="../public/index.php">
                        <span class="logo-icon">
                            <img src="../icons/arrow.png" alt="arrow" class="logo-arrow" style="width: 18px">
                        </span>
                        <span class="logo-text">ExpenseTracker</span>
                    </a>
                </div>
                <ul class="nav-links">
                    <li><a href="../public/index.php">Home</a></li>
                    <li><a href="../public/profile.php">Profile</a></li>
                    <li><a href="../public/add_expense.php">Add Expense</a></li>
                    <li><a href="../public/categories.php" class="active">Categories</a></li>
                    <li><a href="../process/logout.php" class="logout-link">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="profile-header">
                <h1>Edit Category</h1>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
            </div>

            <form method="POST" class="profile-form">
                <div class="form-group">
                    <label for="category_name">Category Name</label>
                    <input type="text" id="category_name" name="category_name" 
                           value="<?= htmlspecialchars($category['name']) ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="../public/categories.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y'); ?> ExpenseTracker. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>