<?php
session_start();

// Define file path
$file_path = __DIR__ . '/users.txt';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    // Check if passwords match
    if ($password == $cpassword) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Check if the user already exists
        $users = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $user_exists = false;
        foreach ($users as $user) {
            list($stored_email, ) = explode(':', trim($user));
            if ($stored_email == $email) {
                $user_exists = true;
                break;
            }
        }

        if ($user_exists) {
            $error_message = "User already registered. <a href='login.php'>Login here</a>";
        } else {
            // Save the user's email and hashed password to a file
            $user_data = "$email:$hash" . PHP_EOL;

            // Attempt to write to the file
            if (file_put_contents($file_path, $user_data, FILE_APPEND | LOCK_EX) !== false) {
                // Set session variables for the newly registered user
                $_SESSION['email'] = $email;
                $_SESSION['loggedin'] = true;

                // Redirect to index.php
                header('Location: index.php');
                exit();
            } else {
                $error_message = "Error writing to file.";
            }
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
        <form method="post">
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
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
        </script>
</body>

</html>