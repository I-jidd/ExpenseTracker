<?php
session_start();
require_once '../includes/connection.php';
require_once '../includes/header.html';

if (!isset($_SESSION['username'])) {
    header("Location: login-page.php");
    exit();
}

// Fetch current data
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>

<main class="main-content">
    <div class="container">
        <div class="profile-header">
            <h1>Edit Profile</h1>
            <?php if ($error): ?>
                <div class="alert alert-error" style="color: red"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
        </div>

        <form action="../process/update_profile.php" method="POST" class="profile-form">
            <div class="form-group">
                <label for="name">First Name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="profile.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</main>

<?php include_once '../includes/footer.php'; ?>