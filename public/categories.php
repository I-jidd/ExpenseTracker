<?php
session_start();
require_once '../includes/connection.php';
$pageTitle = "Categories";
include_once '../includes/header.php';

// Fetch all categories
$stmt = $conn->prepare("SELECT * FROM categories ORDER BY name ASC");
$stmt->execute();
$categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<main class="main-content">
    <div class="container">
        <div class="section-header">
            <h1>Manage Categories</h1>
            
            <!-- Search and Add New -->
            <div class="category-actions">
                <form method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search categories...">
                </form>
                <button id="add-category-btn" class="btn btn-primary">
                    + Add New
                </button>
            </div>
        </div>

        <!-- Add New Category Form (Initially Hidden) -->
        <div id="new-category-form" style="display: none;">
            <form action="../process/add_category.php" method="POST" class="category-form">
                <input type="text" name="category_name" placeholder="Category name" required>
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" id="cancel-add" class="btn btn-secondary">Cancel</button>
            </form>
        </div>

        <!-- Categories List -->
        <div class="categories-list">
            <?php if (count($categories) > 0): ?>
                <?php foreach ($categories as $category): ?>
                    <div class="category-card">
                        <span class="category-name"><?= htmlspecialchars($category['name']) ?></span>
                        <div class="category-actions">
                            <a href="edit_category.php?id=<?= $category['id'] ?>" class="btn btn-edit">Edit</a>
                            <a href="../process/delete_category.php?id=<?= $category['id'] ?>" 
                               class="btn btn-delete"
                               onclick="return confirm('Delete this category?')">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-categories">No categories found. Add your first one!</p>
            <?php endif; ?>
        </div>
        <h1>wa pa nahuman!</h1>
    </div>
</main>

<script>
    // Toggle add category form
    document.getElementById('add-category-btn').addEventListener('click', function() {
        document.getElementById('new-category-form').style.display = 'block';
    });
    document.getElementById('cancel-add').addEventListener('click', function() {
        document.getElementById('new-category-form').style.display = 'none';
    });
</script>

<?php include_once '../includes/footer.php'; ?>