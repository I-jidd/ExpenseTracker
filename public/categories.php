<?php
$pageTitle = "Categories";
include_once '../includes/header.php';

// Get user ID
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch categories for current user only
$stmt = $conn->prepare("SELECT * FROM categories WHERE user_id = ? ORDER BY name ASC");
$stmt->bind_param("i", $user['id']);
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
                        $stmt = $conn->prepare("SELECT * FROM categories WHERE name LIKE ? AND user_id = ? ORDER BY name ASC");
                        $searchTerm = "%$search%";
                        $stmt->bind_param("si", $searchTerm, $user['id']);
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
                                    <button class="btn btn-delete" data-id="<?= $category['id'] ?>" data-name="<?= htmlspecialchars($category['name']) ?>">Delete</button>
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

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModal" style="display: none;">
    <div class="modal-content">
        <h2>Confirm Deletion</h2>
        <p id="deleteMessage">Are you sure you want to delete this category?</p>
        <div class="form-actions">
            <button id="confirmDelete" class="btn btn-danger">Delete</button>
            <button onclick="closeDeleteModal()" class="btn btn-secondary">Cancel</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addBtn = document.getElementById('add-category-btn');
    const cancelBtn = document.getElementById('cancel-add');
    const formContainer = document.getElementById('new-category-form');
    let currentCategoryIdToDelete = null;

    // Add category form toggle
    if (addBtn && cancelBtn && formContainer) {
        addBtn.addEventListener('click', function(e) {
            e.preventDefault();
            formContainer.style.display = 'block';
        });

        cancelBtn.addEventListener('click', function() {
            formContainer.style.display = 'none';
        });
    }

    // Delete confirmation functionality
    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
        currentCategoryIdToDelete = null;
    }

    // Set up delete button handlers
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            currentCategoryIdToDelete = this.getAttribute('data-id');
            const categoryName = this.getAttribute('data-name');
            
            // Update the modal message with category name
            document.getElementById('deleteMessage').textContent = 
                `Are you sure you want to delete the category "${categoryName}"?`;
            
            document.getElementById('deleteModal').style.display = 'flex';
        });
    });

    // Confirm delete button
    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (currentCategoryIdToDelete) {
            window.location.href = `../process/delete_category.php?id=${currentCategoryIdToDelete}`;
        }
    });

    // Close modal when clicking outside
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });

    // Make closeDeleteModal available globally
    window.closeDeleteModal = closeDeleteModal;
});
</script>

<?php include_once '../includes/footer.php'; ?>