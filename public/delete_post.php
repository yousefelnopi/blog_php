<?php
require_once '../config/db.php';
session_start();

// تحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    die("Access Denied");
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$postId = $_GET['id'];

// لما يضغط المستخدم على "Confirm Delete"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['DEL']) && $_POST['DEL'] == 1) {
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
    $stmt->execute([$postId, $_SESSION['user_id']]);
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/style.css">
    <title>Delete Post</title>
</head>
<body>
    <h2>Are you sure you want to delete this post?</h2>

        <div class="post">
    <form method="POST" >
        <input type="hidden" name="DEL" value="1">
        <button type="submit">Confirm Delete</button>
        <a href="index.php">Cancel</a>
    </form>
    </div>
</body>
</html>
