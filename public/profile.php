<?php
require_once '../includes/header.php';


// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Calculate stats
$stmt = $conn->prepare("
    SELECT 
        COUNT(id) AS total_expenses,
        SUM(amount) AS total_spent,
        MAX(date) AS last_expense_date
    FROM expenses 
    WHERE user_id = ?
");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
?>
<head>
    <title>User Profile</title>
    <link rel="stylesheet" href="../styles/profile.css">
</head>
<main class="main-content">
    <div class="container">
        <div class="profile-header">
            <h1>Your Profile</h1>
            <p>Manage your account details</p>
        </div>

        <!-- User Details Section -->
        <div class="profile-section">
            <h2>Personal Information</h2>
            <div class="profile-details">
                <div class="detail-item">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value"><?= htmlspecialchars($user['name']) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Last Name:</span>
                    <span class="detail-value"><?= htmlspecialchars($user['last_name']) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Username:</span>
                    <span class="detail-value"><?= htmlspecialchars($user['username']) ?></span>
                </div>
            </div>
            <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
        </div>

        <!-- Statistics Section -->
        <div class="profile-section">
            <h2>Expense Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Expenses</h3>
                    <p><?= $stats['total_expenses'] ?? 0 ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Spent</h3>
                    <p>₱<?= number_format($stats['total_spent'] ?? 0, 2) ?></p>
                </div>
                <div class="stat-card">
                    <h3>Last Expense</h3>
                    <p><?= $stats['last_expense_date'] ? date("M d, Y", strtotime($stats['last_expense_date'])) : 'Never' ?></p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once '../includes/footer.php'; ?>