<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $postId = $_POST['post_id'];
    $comment = trim($_POST['comment']);

    if (empty($comment)) {
        die("Comment cannot be empty.");
    }

    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->execute([$postId, $userId, $comment]);

    header('Location: index.php');
    exit;
}
