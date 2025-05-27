<?php
require_once '../config/db.php';
session_start();

$errors = [];

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $imagePath = null;

    $title = strip_tags($title);
    $content = strip_tags($content);

    //  فحص معدل النشر لمنع السبام
    $userId = $_SESSION['user_id'];
    $limit = 5; // أقصى عدد منشورات خلال 10 دقائق
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ? AND created_at >= (NOW() - INTERVAL 10 MINUTE)");
    $stmt->execute([$userId]);
    $postCount = $stmt->fetchColumn();

    if ($postCount >= $limit) {
        $errors[] = "You have reached the posting limit. Please wait before posting again.";
    } else {
        // التعامل مع رفع الصورة
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = __DIR__ . '/../upload/';
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetFilePath = $uploadDir . $fileName;

            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($fileType, $allowedTypes)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                    $imagePath = 'upload/' . $fileName;
                } else {
                    $errors[] = "Failed to upload image.";
                }
            } else {
                $errors[] = "Only JPG, PNG, and GIF files are allowed.";
            }
        }

        // التأكد من أن العنوان والمحتوى غير فارغين
        if (empty($title) || empty($content)) {
            $errors[] = "Both title and content are required.";
        } else {
            if (empty($errors)) {
                $content = chunk_split($content, 50, "\n");
                $title = chunk_split($title, 50, "\n");

                $stmt = $pdo->prepare("INSERT INTO posts (title, content, image, user_id, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$title, $content, $imagePath, $userId]);
                header("Location: index.php");
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post</title>
    <link rel="stylesheet" href="../assets/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>
<body>
    <h2>Create a New Post... <a href="index.php"><i class="fas fa-home" style="font-size: 30px; color: #4CAF50;"></i></a></h2>

    <!-- عرض الأخطاء إذا كانت موجودة -->
    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <p style="color:red"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    <?php endif; ?>
    

    <!-- نموذج إنشاء بوست جديد -->
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Post Title" require maxlength="150"><br><br>
        <textarea name="content" placeholder="Post Content" rows="5" cols="30"></textarea><br><br>
        
        <!-- حقل رفع الصورة -->
        <input type="file" name="image" hidden id="image">
<label for="image" style="background-color: teal; color: white; border: none; padding: 8px 16px; border-radius: 10px; cursor: pointer;">
  image
</label><br><br>
        
        <button type="submit">Publish</button>
    </form>
<br>

</body>
</html>  