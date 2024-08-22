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
    <div class="container">
        <h1>Register</h1>
        <form method="post">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>
            <div class="mb-3">
                <label for="cpassword" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="cpassword" name="cpassword">
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $cpassword = $_POST['cpassword'];

        if ($password == $cpassword) {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Insert into the database
            $sql = "INSERT INTO users (email, password) VALUES ('$email', '$hash')";
            if (mysqli_query($conn, $sql)) {
                // Save the user to a file
                $file = __DIR__ . '/users.txt';
                $user_data = "Email: $email, Password: $hash" . PHP_EOL;
                echo '<div class="alert alert-info" role="alert">Attempting to write to file</div>';
                file_put_contents($file, $user_data, FILE_APPEND | LOCK_EX);
                if (file_put_contents($file, $user_data, FILE_APPEND | LOCK_EX) === false) {
                    echo '<div class="alert alert-danger" role="alert">Error writing to file</div>';
                }


                echo '<div class="alert alert-success" role="alert">Account created successfully</div>';
            } else {
                echo '<div class="alert alert-danger" role="alert">Unable to create account</div>';
            }
        } else {
            echo '<div class="alert alert-danger" role="alert">Passwords do not match</div>';
        }
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>