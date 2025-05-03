<?php
session_start();
require_once '../includes/connection.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login-page.php");
    exit();
}

// Get current date information
$currentDate = date("F j, Y");  // Format: "Month day, Year" (e.g., "May 3, 2025")
$currentMonthNum = date("n");    // Month as number (1-12)
$currentYearNum = date("Y");     // Year as 4-digit number

// Fetch expenses for current user and month
$sql = "SELECT e.id, e.date, e.amount, e.description, 
               c.name AS category_name 
        FROM expenses e
        JOIN categories c ON e.category_id = c.id
        JOIN users u ON e.user_id = u.id
        WHERE u.username = ?
        AND MONTH(e.date) = ?
        AND YEAR(e.date) = ?
        ORDER BY e.date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $_SESSION['username'], $currentMonthNum, $currentYearNum);

if (!$stmt->execute()) {
    die("Query failed: " . $stmt->error);
}

$expenses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$totalExpenses = array_sum(array_column($expenses, 'amount'));
$formattedTotalExpenses = number_format($totalExpenses, 2);

// Include header
include_once '../includes/header.html';
?>

<main class="main-content">
    <div class="container">
        <div class="greeting-section">
            <h1>Hi, <?= strtoupper(htmlspecialchars($_SESSION['username'])) ?>!</h1>
            <p class="current-date"><?= $currentDate ?></p>
        </div>
        
        <div class="summary-card">
            <div class="summary-item">
                <h2>This Month's Expenses</h2>
                <p class="amount">₱<?= $formattedTotalExpenses ?></p>
            </div>
        </div>
        
        <div class="section-header">
            <h2>Expenses for <?= date('F') ?> <?= $currentYearNum ?></h2>
            <a href="add_expense.php" class="btn btn-primary">Add New Expense</a>
        </div>
        
        <div class="expenses-section">
            <div class="table-container">
                <table class="expenses-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Amount (₱)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($expenses) > 0): ?>
                            <?php foreach ($expenses as $expense): ?>
                                <tr>
                                    <td><?= date("M d, Y", strtotime($expense['date'])) ?></td>
                                    <td><?= htmlspecialchars($expense['category_name']) ?></td>
                                    <td><?= htmlspecialchars($expense['description']) ?></td>
                                    <td>₱<?= number_format($expense['amount'], 2) ?></td>
                                    <td class="actions-column">
                                        <a href="edit_expense.php?id=<?= $expense['id'] ?>" class="btn btn-edit">Edit</a>
                                        <a href="delete_expense.php?id=<?= $expense['id'] ?>" class="btn btn-delete">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="no-expenses">No expenses found for this month.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include_once '../includes/footer.php'; ?>