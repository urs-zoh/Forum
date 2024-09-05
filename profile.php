<?php
session_start();
require_once 'Database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Fetch the current user's data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    // Only set role if the user is an admin and role is provided in POST
    $role = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && isset($_POST['role'])) ? $_POST['role'] : $user['role'];
    $avatar = $user['avatar'];

    // Handle password change
    if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        // Verify current password
        if (password_verify($currentPassword, $user['password'])) {
            if ($newPassword === $confirmPassword) {
                $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$newHashedPassword, $user['id']]);
                echo "<div class='alert alert-success'>Password updated successfully.</div>";
            } else {
                echo "<div class='alert alert-danger'>New password and confirmation do not match.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Current password is incorrect.</div>";
        }
    }

    // Handle avatar upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        // Maximum file size: 2MB (adjust as needed)
        $maxFileSize = 2 * 1024 * 1024; // 2MB
        if ($_FILES['avatar']['size'] <= $maxFileSize) {
            // Validate the uploaded file type
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($_FILES['avatar']['type'], $allowedMimeTypes)) {
                $avatar = base64_encode(file_get_contents($_FILES['avatar']['tmp_name']));
            } else {
                echo "<div class='alert alert-danger'>Invalid avatar file type. Allowed types: JPEG, PNG, GIF.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Avatar file size exceeds the 2MB limit.</div>";
        }
    }

    // Update the user profile (except password)
    $sql = "UPDATE users SET email = ?, role = ?, avatar = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    try {
        $stmt->execute([$email, $role, $avatar, $user['id']]);
        echo "<div class='alert alert-success'>Profile updated successfully.</div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error updating profile: " . htmlspecialchars($e->getMessage()) . "</div>";
    }

    // Refresh the user data
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <div class="container mt-5">
        <h2 class="mb-4">User Profile</h2>

        <!-- Display user avatar -->
        <?php if ($user['avatar']): ?>
        <div class="mb-3">
            <img src="data:image/jpeg;base64,<?= $user['avatar'] ?>" alt="User Avatar" class="rounded-circle"
                style="width: 150px; height: 150px;">
        </div>
        <?php endif; ?>

        <!-- Profile update form -->
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="avatar" class="form-label">Upload Avatar:</label>
                <input type="file" class="form-control" name="avatar" accept="image/*">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                    required>
            </div>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div class="mb-3">
                <label for="role" class="form-label">Role:</label>
                <select class="form-select" name="role">
                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <?php endif; ?>

            <h3 class="mt-4">Change Password</h3>
            <div class="mb-3">
                <label for="current_password" class="form-label">Current Password:</label>
                <input type="password" class="form-control" name="current_password">
            </div>

            <div class="mb-3">
                <label for="new_password" class="form-label">New Password:</label>
                <input type="password" class="form-control" name="new_password">
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password:</label>
                <input type="password" class="form-control" name="confirm_password">
            </div>

            <button type="submit" class="btn btn-primary">Update Profile</button>
            <a href="index.php" class="btn btn-secondary">Back to Forum</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>