<?php
session_start();
require_once '../includes/connection.php';
$pageTitle = "Categories";
include_once '../includes/header.php';

// Fetch all categories
$stmt = $conn->prepare("SELECT * FROM categories ORDER BY name ASC");
$stmt->execute();
$categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$isSearchActive = isset($_GET['search']) && !empty($_GET['search']);
?>
<head>
    <link rel="stylesheet" href="../styles/categories.css">
    <link rel="stylesheet" href="../styles/main.css">
</head>
<main class="main-content">
    <div class="container">
        <div class="section-header">
            <div>
                <h1>Manage Categories</h1>
                <p>Diversify your categories</p>
            </div>
            <!-- Search and Add New -->
            <div class="category-actions">
                <form method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search categories..." 
                    value = "<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" required>
                    <button type="submit" class="search-btn">
                        <img src="../icons/Vector.png" alt="search" class="search-icon">
                    </button>
                    <?php if($isSearchActive):?>
                        <a href="categories.php" class="btn btn-secondary">Show All</a>
                    <?php endif; ?>
                    <button id="add-category-btn" class="btn btn-primary">
                        + Add New
                    </button>
                </form>
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
    
        <div class="table-container">
            <table class="category-table">
                <thead>
                    <tr>
                        <th>Category Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if(isset($_GET['search']) && $search = htmlspecialchars($_GET['search'])){
                        $stmt = $conn->prepare("SELECT * FROM categories WHERE name LIKE ? ORDER BY name ASC");
                        $searchTerm = "%$search%";
                        $stmt->bind_param("s", $searchTerm);
                        $stmt->execute();
                        $categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    }
                    ?>
                    <?php if(count($categories) > 0){?>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?= htmlspecialchars($category['name']) ?></td>
                                <td>
                                    <a href="../process/edit_category.php?id=<?= $category['id'] ?>" class="btn btn-edit">Edit</a>
                                    <a href="../process/delete_category.php?id=<?= $category['id'] ?>" class="btn btn-delete">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach;
                    }else{?>
                        <tr class="no-data">
                            <td colspan="2" class="no-data">No categories found.</td>
                        </tr>
                    <?php }?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addBtn = document.getElementById('add-category-btn');
    const cancelBtn = document.getElementById('cancel-add');
    const formContainer = document.getElementById('new-category-form');

    if (addBtn && cancelBtn && formContainer) {
        addBtn.addEventListener('click', function(e) {
            e.preventDefault();
            formContainer.style.display = 'block';
        });

        cancelBtn.addEventListener('click', function() {
            formContainer.style.display = 'none';
        });
    }
});
</script>

<?php include_once '../includes/footer.php'; ?>