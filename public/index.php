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
include_once '../includes/header.php';
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
                                    <td data-date="<?= date('Y-m-d', strtotime($expense['date'])) ?>">
                                        <?= date("M d, Y", strtotime($expense['date'])) ?>
                                    </td>
                                    <td data-category-id="<?= $expense['id'] ?>">
                                        <?= htmlspecialchars($expense['category_name']) ?>
                                    </td>
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
    <div class="class-overlay" id="edit-expense-modal" style="display: none;">
        <div class="modal-content">
            <span class="class-modal" onclick="closeModal()">&times;</span>
            <h2>Edit Expense</h2>
        </div>
        <form id="edit-expense-form" class="expense-form">
            <input type="hidden" id="edit-expense-id">
            <div class="form-group">
                <label for="edit-date">Date</label>
                <input type="date" id="edit-date" name="date" required>
            </div>
            <div class="form-group">
                <label for="edit-category">Category</label>
                <select id="edit-category" name="category_id" required>
                    <?php 
                    $categories = $conn->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);
                    foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>">
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="edit-description">Description</label>
                <input type="text" id="edit-description" name="description" required>
            </div>
            <div class="form-group">
                <label for="edit-amount">Amount (₱)</label>
                <input type="number" id="edit-amount" name="amount" min="0.01" step="0.01" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
    <script>
        function openEditModal(expenseId, date, categoryId, description, amount){
            document.getElementById('edit-expense-id').value = expenseId;
            document.getElementById('edit-date').value = date;
            document.getElementById('edit-category').value = categoryId;
            document.getElementById('edit-description').value = description;
            document.getElementById('edit-amount').value = amount;
            
            document.getElementById('edit-expense-modal').style.display = 'block';
        }
        function closeModal(){
            document.getElementById('edit-expense-modal').style = 'flex';
        }
        
        document.getElementById('edit-expense-form').addEventListener('submit', function(e){
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('expense_id', document.getElementById('edit-expense-id').value);

            fetch('../process/update_expense.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Expense updated successfully!');
                    window.location.reload();
                } else {
                    alert('Error updating expense: ' + data.error);
                }
            })
            .catch(error =>{
                console.error('Error:', error);
            })
        });
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const row = this.closest('tr');
                const expenseId = this.getAttribute('href').split('=')[1];
                const date = row.cells[0].getAttribute('data-date'); // Add data-date attribute to your date cell
                const category = row.cells[1].textContent;
                const description = row.cells[2].textContent;
                const amount = row.cells[3].textContent.replace('₱', '').replace(',', '');
                
                // You'll need to get categoryId from somewhere - could add data-category-id to the row
                const categoryId = row.cells[1].getAttribute('data-category-id');
                
                openEditModal(expenseId, date, categoryId, description, amount);
            });
        });
    </script>
</main>

<?php include_once '../includes/footer.php'; ?>