<?php
include_once '../includes/header.php';

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
                                <tr data-id ="<?= $expense['id'] ?>">
                                    <td data-date="<?= date('Y-m-d', strtotime($expense['date'])) ?>">
                                        <?= date("M d, Y", strtotime($expense['date'])) ?>
                                    </td>
                                    <td data-category-id="<?= $expense['id'] ?>">
                                        <?= htmlspecialchars($expense['category_name']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($expense['description']) ?></td>
                                    <td>₱<?= number_format($expense['amount'], 2) ?></td>
                                    <td class="actions-column">
                                        <button onclick="openEditModal(<?= $expense['id'] ?>)" class="btn btn-edit">Edit</button>
                                        <button class="btn btn-delete" data-id="<?= $expense['id'] ?>">Delete</button>
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
    <div class="modal-overlay" id="expenseModal" style="display: none;">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h2>Edit Expense</h2>
            <form id="expenseForm" method="POST" action="../process/update_expense.php">
                <input type="hidden" name="id" id="expense_id">
                
                <div class="form-group">
                    <label for="modal-date">Date</label>
                    <input type="date" id="modal-date" name="date" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="modal-category">Category</label>
                    <select id="modal-category" name="category_id" class="form-input" required>
                        <?php 
                        $categories = $conn->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);
                        foreach ($categories as $category): ?>
                            <option value="<?php $category['id'] ?>">
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="modal-description">Description</label>
                    <input type="text" id="modal-description" name="description" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="modal-amount">Amount (₱)</label>
                    <input type="number" id="modal-amount" name="amount" min="0.01" step="0.01" class="form-input" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
        <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal" style="display: none;">
        <div class="modal-content">
            <h2>Confirm Deletion</h2>
            <p>Are you sure you want to delete this expense?</p>
            <div class="form-actions">
                <button id="confirmDelete" class="btn btn-danger">Delete</button>
                <button onclick="closeDeleteModal()" class="btn btn-secondary">Cancel</button>
            </div>
        </div>
    </div>
   <script>
        let currentExpenseIdToDelete = null;

        // Function to open edit modal
        function openEditModal(expenseId) {
            const row = document.querySelector(`tr[data-id="${expenseId}"]`);
            
            if (row) {
                // Set form values
                document.getElementById('expense_id').value = expenseId;
                document.getElementById('modal-date').value = row.cells[0].getAttribute('data-date');
                document.getElementById('modal-category').value = row.cells[1].getAttribute('data-category-id');
                document.getElementById('modal-description').value = row.cells[2].textContent.trim();
                document.getElementById('modal-amount').value = row.cells[3].textContent.replace('₱', '').replace(',', '').trim();
                
                // Show modal
                document.getElementById('expenseModal').style.display = 'flex';
            }
        }

        function closeModal() {
            document.getElementById('expenseModal').style.display = 'none';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            currentExpenseIdToDelete = null;
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Set up delete button handlers
            document.querySelectorAll('.btn-delete').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    currentExpenseIdToDelete = this.getAttribute('data-id') || 
                                            this.getAttribute('href').split('=')[1];
                    document.getElementById('deleteModal').style.display = 'flex';
                });
            });

            // Confirm delete button
            document.getElementById('confirmDelete').addEventListener('click', function() {
                if (currentExpenseIdToDelete) {
                    window.location.href = `../process/delete_expense.php?id=${currentExpenseIdToDelete}`;
                }
            });

            // Close modals when clicking outside
            document.getElementById('expenseModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });

            document.getElementById('deleteModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeDeleteModal();
                }
            });
        });
    </script>
</main>

<?php include_once '../includes/footer.php'; ?>