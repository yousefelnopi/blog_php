<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_id'])) {
    $commentId = filter_var($_POST['comment_id'], FILTER_VALIDATE_INT);

    if ($commentId) {
        // تحقق من وجود التعليق
        $stmt = $pdo->prepare("SELECT * FROM comments WHERE id = ?");
        $stmt->execute([$commentId]);
        $comment = $stmt->fetch();

        if ($comment) {
            // تحقق هل المستخدم هو صاحب التعليق أو صاحب البوست
            $postStmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
            $postStmt->execute([$comment['post_id']]);
            $post = $postStmt->fetch();

            if ($comment['user_id'] == $_SESSION['user_id'] || $post['user_id'] == $_SESSION['user_id']) {
                $delete = $pdo->prepare("DELETE FROM comments WHERE id = ?");
                $delete->execute([$commentId]);
            }
        }
    }
}

$redirectUrl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
header("Location: $redirectUrl");
exit;
