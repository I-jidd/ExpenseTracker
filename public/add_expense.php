<?php
require_once '../includes/header.php';



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
<body>
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
                        <option value="new">+ Add New Category</option>
                    </select>
                    <div id="new-category-container" style="display: none; margin-top: 0.5rem;">
                        <input 
                            type="text" 
                            id="new-category" 
                            name="new_category" 
                            placeholder="Enter new category name"
                            class="full-width-input"
                        >
                    </div>
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
    <script>
        document.getElementById('category').addEventListener('change', function() {
            const newCategoryContainer = document.getElementById('new-category-container');
            if (this.value === 'new') {
                newCategoryContainer.style.display = 'block';
                document.getElementById('category').name = ''; // Disable dropdown submission
            } else {
                newCategoryContainer.style.display = 'none';
                document.getElementById('category').name = 'category_id'; // Re-enable dropdown
            }
        });
    </script>
</body>

<?php include_once '../includes/footer.php'; ?>