<?php
session_start();


// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login-page.php");
    exit();
}

// Current user information (static for now)
$username = $_SESSION['username'];
$currentDate = date("F j, Y");
$currentMonth = date("F");
$currentYear = date("Y");

// Static expense data (would come from database in a real app)
$expenses = [
    [
        'id' => 1,
        'date' => '2025-04-01',
        'category' => 'Groceries',
        'description' => 'Weekly groceries',
        'amount' => 1250.75,
    ],
    [
        'id' => 2,
        'date' => '2025-04-05',
        'category' => 'Transportation',
        'description' => 'Fuel',
        'amount' => 750.00,
    ],
    [
        'id' => 3,
        'date' => '2025-04-10',
        'category' => 'Utilities',
        'description' => 'Electricity bill',
        'amount' => 2200.50,
    ],
    [
        'id' => 4,
        'date' => '2025-04-15',
        'category' => 'Entertainment',
        'description' => 'Movie tickets',
        'amount' => 500.00,
    ],
    [
        'id' => 5,
        'date' => '2025-04-20',
        'category' => 'Dining',
        'description' => 'Dinner with friends',
        'amount' => 1850.25,
    ],
    [
        'id' => 6,
        'date' => '2025-04-25',
        'category' => 'Shopping',
        'description' => 'New clothes',
        'amount' => 3200.00,
    ],
];

// Calculate total expenses for current month
$totalExpenses = 0;
foreach ($expenses as $expense) {
    $expenseDate = strtotime($expense['date']);
    $expenseMonth = date('F', $expenseDate);
    $expenseYear = date('Y', $expenseDate);
    
    if ($expenseMonth === $currentMonth && $expenseYear === $currentYear) {
        $totalExpenses += $expense['amount'];
    }
}

// Format total expenses with comma for thousands
$formattedTotalExpenses = number_format($totalExpenses, 2);

// Include header
include_once '../includes/header.html';
?>

<main class="main-content">
    <div class="container">
        <div class="greeting-section">
            <h1>Hi, <?= strtoupper($username); ?>!</h1>
            <p class="current-date"><?= $currentDate; ?></p>
        </div>
        
        <div class="summary-card">
            <div class="summary-item">
                <h2>This Month's Expenses</h2>
                <p class="amount">₱<?= $formattedTotalExpenses; ?></p>
            </div>
        </div>
        
        <div class="expenses-section">
            <div class="section-header">
                <h2>Expenses for <?= $currentMonth; ?> <?= $currentYear; ?></h2>
                <a href="add_expense.php" class="btn btn-primary">Add New Expense</a>
            </div>
            
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
                                <?php 
                                $expenseDate = date("M d, Y", strtotime($expense['date']));
                                ?>
                                <tr>
                                    <td><?= $expenseDate; ?></td>
                                    <td><?= $expense['category']; ?></td>
                                    <td><?= $expense['description']; ?></td>
                                    <td class="amount-column"><?= number_format($expense['amount'], 2); ?></td>
                                    <td class="actions-column">
                                        <a href="edit_expense.php?id=<?= $expense['id']; ?>" class="btn btn-edit">Edit</a>
                                        <a href="delete_expense.php?id=<?= $expense['id']; ?>" class="btn btn-delete">Delete</a>
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