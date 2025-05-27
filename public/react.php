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
    $reaction = $_POST['reaction'];

    if (!in_array($reaction, ['like', 'dislike'])) {
        die("Invalid reaction.");
    }

    // تحقق إذا كان المستخدم أرسل رد فعل من قبل على نفس البوست
    $stmt = $pdo->prepare("SELECT * FROM reactions WHERE user_id = ? AND post_id = ?");
    $stmt->execute([$userId, $postId]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ($existing['reaction_type'] == $reaction) {
            // إذا نفس الرياكشن موجود بالفعل، نحذفه (لإلغاء اللايك مثلا)
            $stmt = $pdo->prepare("DELETE FROM reactions WHERE id = ?");
            $stmt->execute([$existing['id']]);
        } else {
            // تحديث الرياكشن إلى النوع الجديد
            $stmt = $pdo->prepare("UPDATE reactions SET reaction_type = ?, created_at = NOW() WHERE id = ?");
            $stmt->execute([$reaction, $existing['id']]);
        }
    } else {
        // إضافة رد فعل جديد
        $stmt = $pdo->prepare("INSERT INTO reactions (post_id, user_id, reaction_type) VALUES (?, ?, ?)");
        $stmt->execute([$postId, $userId, $reaction]);
    }
}

header('Location: index.php');
exit;
