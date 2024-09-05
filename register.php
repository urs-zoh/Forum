<?php
session_start();
require_once 'Database.php';

$db = new Database();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    $role = 'user'; // Default role is 'user'
    $avatar = null;

    // Handle avatar upload
    if (!empty($_FILES['avatar']['tmp_name'])) {
        $avatarData = file_get_contents($_FILES['avatar']['tmp_name']);
        $avatar = base64_encode($avatarData); // Encode avatar as base64
    }

    // Admin can set role
    if (isset($_POST['role']) && $_SESSION['role'] === 'admin') {
        $role = $_POST['role'];
    }

    if ($password == $cpassword) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Check if user already exists
        $sql = "SELECT id FROM users WHERE email = :email";
        $user_exists = $db->execute_query($sql, [':email' => $email])->fetch(PDO::FETCH_ASSOC);

        if ($user_exists) {
            $error_message = "User already registered. <a href='login.php'>Login here</a>";
        } else {
            // Insert the user into the database
            $sql = "INSERT INTO users (email, password, role, avatar) VALUES (:email, :password, :role, :avatar)";
            $db->execute_query($sql, [
                ':email' => $email,
                ':password' => $hash,
                ':role' => $role,
                ':avatar' => $avatar
            ]);


            $_SESSION['user_id'] = $db->getConnection()->lastInsertId();
            $_SESSION['loggedin'] = true;
            header('Location: index.php');
            exit();
        }
    } else {
        $error_message = "Passwords do not match.";
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <div class="container mt-md-5">
        <h1>Register</h1>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?= $error_message; ?>
            </div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="cpassword" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="cpassword" name="cpassword" required>
            </div>
            <div class="mb-3">
                <label for="avatar" class="form-label">Upload Avatar (optional)</label>
                <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
            </div>

            <?php if ($_SESSION['role'] === 'admin'): ?>
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
        </script>
</body>

</html>