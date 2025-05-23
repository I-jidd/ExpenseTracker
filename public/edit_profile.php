<?php
require_once '../includes/header.php';

// Fetch current data
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>
<head>
    <title>ExpenseTracker - Edit Profile</title>
    <link rel="stylesheet" href="../styles/edit_profile.css">
</head>
<main class="main-content">
    <div class="container">
        <div class="profile-header">
            <h1>Edit Profile</h1>
            <?php if ($error): ?>
                <div class="alert alert-error" style="color: red"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
        </div>

        <form id="profileForm" action="../process/update_profile.php" method="POST" class="profile-form">
            <div class="form-group">
                <label for="name">First Name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="form-group">
                <label for="current_password">Current Password (leave blank to keep current)</label>
                <input type="password" id="current_password" name="current_password">
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>
            
            <button type="button" id="saveChangesBtn" class="btn btn-primary">Save Changes</button>
            <a href="profile.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</main>

<!-- Save Confirmation Modal -->
<div class="modal-overlay" id="saveModal" style="display: none;">
    <div class="modal-content">
        <h2>Confirm Changes</h2>
        <div id="changesSummary">
            <p>Are you sure you want to save the following changes?</p>
            <ul id="changesList"></ul>
        </div>
        <div class="form-actions">
            <button id="confirmSave" class="btn btn-primary">Save Changes</button>
            <button onclick="closeSaveModal()" class="btn btn-secondary">Cancel</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.getElementById('profileForm');
    const saveChangesBtn = document.getElementById('saveChangesBtn');
    const saveModal = document.getElementById('saveModal');
    const changesList = document.getElementById('changesList');
    const confirmSaveBtn = document.getElementById('confirmSave');
    
    // Store original values
    const originalValues = {
        name: document.getElementById('name').value,
        last_name: document.getElementById('last_name').value,
        username: document.getElementById('username').value,
        current_password: '',
        new_password: '',
        confirm_password: ''
    };

    function closeSaveModal() {
        saveModal.style.display = 'none';
    }

    function detectChanges() {
        const changes = [];
        const currentValues = {
            name: document.getElementById('name').value.trim(),
            last_name: document.getElementById('last_name').value.trim(),
            username: document.getElementById('username').value.trim(),
            current_password: document.getElementById('current_password').value,
            new_password: document.getElementById('new_password').value,
            confirm_password: document.getElementById('confirm_password').value
        };

        // Check for basic field changes
        if (currentValues.name !== originalValues.name) {
            changes.push(`First Name: "${originalValues.name}" → "${currentValues.name}"`);
        }
        
        if (currentValues.last_name !== originalValues.last_name) {
            changes.push(`Last Name: "${originalValues.last_name}" → "${currentValues.last_name}"`);
        }
        
        if (currentValues.username !== originalValues.username) {
            changes.push(`Username: "${originalValues.username}" → "${currentValues.username}"`);
        }

        // Check for password change
        const isPasswordChange = currentValues.current_password || currentValues.new_password || currentValues.confirm_password;
        if (isPasswordChange) {
            if (currentValues.new_password && currentValues.confirm_password) {
                if (currentValues.new_password === currentValues.confirm_password) {
                    changes.push('Password will be updated');
                } else {
                    changes.push('⚠️ New passwords do not match');
                }
            } else if (currentValues.current_password && (!currentValues.new_password || !currentValues.confirm_password)) {
                changes.push('⚠️ New password fields are incomplete');
            }
        }

        return changes;
    }

    function validateForm() {
        const currentValues = {
            name: document.getElementById('name').value.trim(),
            last_name: document.getElementById('last_name').value.trim(),
            username: document.getElementById('username').value.trim(),
            current_password: document.getElementById('current_password').value,
            new_password: document.getElementById('new_password').value,
            confirm_password: document.getElementById('confirm_password').value
        };

        // Basic validation
        if (!currentValues.name || !currentValues.last_name || !currentValues.username) {
            alert('Please fill in all required fields (Name, Last Name, Username).');
            return false;
        }

        // Password validation
        const isPasswordChange = currentValues.current_password || currentValues.new_password || currentValues.confirm_password;
        if (isPasswordChange) {
            if (!currentValues.current_password || !currentValues.new_password || !currentValues.confirm_password) {
                alert('If changing password, all password fields must be filled.');
                return false;
            }
            
            if (currentValues.new_password !== currentValues.confirm_password) {
                alert('New passwords do not match.');
                return false;
            }
            
            if (currentValues.new_password.length < 8) {
                alert('New password must be at least 8 characters long.');
                return false;
            }
        }

        return true;
    }

    // Handle save button click
    saveChangesBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Validate form first
        if (!validateForm()) {
            return;
        }

        const changes = detectChanges();
        
        if (changes.length === 0) {
            alert('No changes detected.');
            return;
        }

        // Populate changes list
        changesList.innerHTML = '';
        changes.forEach(change => {
            const li = document.createElement('li');
            li.textContent = change;
            if (change.includes('⚠️')) {
                li.style.color = 'var(--danger-color)';
            }
            changesList.appendChild(li);
        });

        // Show modal
        saveModal.style.display = 'flex';
    });

    // Handle confirm save
    confirmSaveBtn.addEventListener('click', function() {
        // Submit the form
        profileForm.submit();
    });

    // Close modal when clicking outside
    saveModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeSaveModal();
        }
    });

    // Make closeSaveModal available globally
    window.closeSaveModal = closeSaveModal;
});
</script>

<?php include_once '../includes/footer.php'; ?>