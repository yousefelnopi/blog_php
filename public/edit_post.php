<?php
require_once '../config/db.php';
session_start();

$errors = [];

// تحقق من وجود ID في الرابط
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$postId = $_GET['id'];

// جلب بيانات البوست الحالي مع التأكد أنه يخص المستخدم الحالي
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$postId, $_SESSION['user_id']]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

// التحقق من وجود البوست وصلاحية المستخدم
if (!$post) {
    echo "Post not found or you do not have permission.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $imagePath = $post['image']; // حفظ مسار الصورة القديمة

    // تنظيف المدخلات من الوسوم لمنع XSS
    $title = strip_tags($title);
    $content = strip_tags($content);

    // التعامل مع رفع صورة جديدة إذا تم رفعها
// التعامل مع رفع صورة جديدة إذا تم رفعها
if (!empty($_FILES['image']['name'])) {
    $uploadDir = __DIR__ . '/../upload/';
    $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
    $targetFilePath = $uploadDir . $fileName;

    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            // ✅ حذف الصورة القديمة إذا كانت موجودة
            if (!empty($post['image'])) {
                $oldImagePath = __DIR__ . '/../' . $post['image'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $imagePath = 'upload/' . $fileName;
        } else {
            $errors[] = "Failed to upload image.";
        }
    } else {
        $errors[] = "Only JPG, PNG, and GIF files are allowed.";
    }
}


    // التحقق من أن العنوان والمحتوى غير فارغين
    if (empty($title) || empty($content)) {
        $errors[] = "Both title and content are required.";
    }

    // تحديث البيانات في قاعدة البيانات إذا لا يوجد أخطاء
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, image = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $content, $imagePath, $postId, $_SESSION['user_id']]);
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Post</title>
    <link rel="stylesheet" href="../assets/style.css" />
</head>
<body>
    <h2>Edit Post</h2>

    <!-- عرض الأخطاء إذا وجدت -->
    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- نموذج التعديل -->
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Post Title" value="<?= htmlspecialchars($post['title']) ?>" required maxlength="150" /><br><br>
        <textarea name="content" placeholder="Post Content" rows="5" cols="30" required><?= htmlspecialchars($post['content']) ?></textarea><br><br>

        <!-- عرض الصورة الحالية إن وجدت -->
        <?php if (!empty($post['image'])): ?>
            <p>Current Image:</p>
            <img src="../<?= htmlspecialchars($post['image']) ?>" alt="Post Image" width="200" /><br><br>
        <?php endif; ?>

        <input type="file" name="image" hidden id="image" />
        <label for="image" style="background-color: teal; color: white; padding: 8px 16px; border-radius: 10px; cursor: pointer;">
            Upload New Image
        </label><br><br>

        <button type="submit">Save Changes</button>
    </form>

    <p><a href="index.php">← Back to Home</a></p>
</body>
</html>
