<?php
session_start();
require_once 'Database.php';

$db = new Database();
$connection = $db->getConnection();

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Handle form submission (posting a comment)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['comment'])) {
    $comment = trim($_POST['comment']);

    if (!empty($comment)) {
        // Insert the comment into the database
        $sql = "INSERT INTO comments (comment, user_id, created_at) VALUES (?, ?, NOW())";
        $stmt = $connection->prepare($sql);
        $stmt->execute([$comment, $userId]);

        // Refresh the page to display the new comment
        header('Location: index.php');
        exit();
    }
}

// Fetch the current user's avatar
$sql = "SELECT avatar FROM users WHERE id = ?";
$stmt = $connection->prepare($sql);
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Fetch comments
$comments = $db->execute_query("SELECT c.comment, u.email, u.avatar FROM comments c JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <div class="container mt-md-5">
        <!-- Top-right Avatar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Welcome to the Forum</h1>
            <a href="profile.php">
                <img src="data:image/jpeg;base64,<?= $user['avatar'] ?>" alt="User Avatar" class="rounded-circle"
                    style="width: 50px; height: 50px;">
            </a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>

        <!-- Display Comments -->
        <div class="mb-4">
            <h2>Posted Comments</h2>
            <?php
            if (!empty($comments)) {
                foreach ($comments as $comment) {
                    echo '<div class="border p-3 mb-2">';
                    echo '<img src="data:image/jpeg;base64,' . $comment['avatar'] . '" alt="User Avatar" class="rounded-circle" style="width: 30px; height: 30px;"> ';
                    echo '<p><strong>' . htmlspecialchars($comment['email']) . '</strong> says:</p>';
                    echo '<p>' . htmlspecialchars($comment['comment']) . '</p>';
                    echo '</div>';
                }
            } else {
                echo '<p>No comments yet.</p>';
            }
            ?>
        </div>

        <!-- Post Comment Form -->
        <div>
            <h2>Post a Comment</h2>
            <form method="post">
                <div class="mb-3">
                    <textarea class="form-control" name="comment" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Post Comment</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>