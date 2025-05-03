<?php
session_start();
require_once '../includes/connection.php';
require_once '../includes/header.html';

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login-page.php");
    exit();
}

// Fetch categories for dropdown
$stmt = $conn->prepare("SELECT id, name FROM categories");
$stmt->execute();
$categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>
<head>
    <title>ExpenseTracker - Add Expense</title>
    <link rel="stylesheet" href="../styles/add_expense.css">
</head>
<main class="main-content">
    <div class="container">
        <div class="section-header">
            <h1>Add New Expense</h1>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
        </div>

        <form action="../process/add_expense_process.php" method="POST" class="expense-form">
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" id="date" name="date" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category_id" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>">
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <input type="text" id="description" name="description" required>
            </div>

            <div class="form-group">
                <label for="amount">Amount (₱)</label>
                <input type="number" id="amount" name="amount" min="0.01" step="0.01" required>
            </div>

            <button type="submit" class="btn btn-primary">Add Expense</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</main>

<?php include_once '../includes/footer.php'; ?>