<?php
// edit_comment.php

session_start();

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to edit comments.");
}

$servername = "localhost";
$dbname = "blog_php";
$username = "root";
$password = "";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// تأكد من وجود comment_id في الرابط
if (!isset($_GET['comment_id']) && !isset($_POST['comment_id'])) {
    die("Comment ID is missing.");
}

// نحدد الcomment_id من GET او POST
$comment_id = isset($_GET['comment_id']) ? intval($_GET['comment_id']) : intval($_POST['comment_id']);

// معالجة إرسال النموذج لتحديث التعليق
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_comment = trim($_POST['comment_text']);

    if (empty($new_comment)) {
        $error = "Comment text cannot be empty.";
    } else {
        // تحقق أن هذا المستخدم هو صاحب التعليق
        $sqlCheck = "SELECT user_id FROM comments WHERE id = $comment_id";
        $resultCheck = $conn->query($sqlCheck);

        if ($resultCheck->num_rows == 0) {
            die("Comment not found.");
        }

        $row = $resultCheck->fetch_assoc();

        if ($row['user_id'] != $_SESSION['user_id']) {
            die("You are not authorized to edit this comment.");
        }

        // تحديث التعليق في قاعدة البيانات
        $stmt = $conn->prepare("UPDATE comments SET comment = ? WHERE id = ?");
        $stmt->bind_param("si", $new_comment, $comment_id);

        if ($stmt->execute()) {
            // بعد التحديث، ارجع لصفحة عرض الكومنتات للبوست
            header("Location: show_comment.php?post_id=" . $_POST['post_id']);
            exit();
        } else {
            $error = "Failed to update comment. Please try again.";
        }
    }
}

// جلب بيانات التعليق لعرضها في النموذج
$sql = "SELECT comment, post_id, user_id FROM comments WHERE id = $comment_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Comment not found.");
}

$commentData = $result->fetch_assoc();

// تحقق أن المستخدم هو صاحب التعليق قبل عرض الصفحة
if ($commentData['user_id'] != $_SESSION['user_id']) {
    die("You are not authorized to edit this comment.");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Edit Comment</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
body {
    font-family: Arial, sans-serif;
    background-color: #000; /* خلفية سودة */
    padding: 20px;
    /* شوية نقوش خفيفة خلفية */
    background-image: radial-gradient(rgba(19, 16, 230, 0.24) 1px, transparent 1px);
    background-size: 20px 20px;
}

.edit-form {
    background-color:rgb(130, 151, 151); /* تيل مزرق خفيف */
    padding: 20px;
    border-radius: 8px;
    max-width: 600px;
    margin: auto;
    box-shadow: 0 0 10px rgba(0, 128, 128, 0.5);
}

textarea {
    width: 100%;
    height: 120px;
    padding: 10px;
    font-size: 16px;
    border-radius: 6px;
    border: 1px solid #ccc;
    resize: vertical;
    color: black; /* لون الخط داخل التيكست اريا أسود */
    background-color: #7f8c8c; /* رمادي غامق للتيكست اريا */
}

button {
    background-color: teal; /* لون الزر تيل */
    color: black; /* الكلمة داخل الزر أسود */
    border: none;
    padding: 10px 18px;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 10px;
    font-size: 16px;
    transition: background-color 0.3s ease, color 0.3s ease; /* سلاسة التحول */
}

.save-button:hover {
    background-color: #006400; /* أخضر غامق */
    color: white; /* خلي الكلمة باللون الأبيض عشان تظهر كويس */
}

/* زرار Back */
.back-button {
    background-color: teal;
    color: black;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    transition: background-color 0.3s ease, color 0.3s ease;
}
.back-button:hover {
    background-color: red; /* اللون الأحمر */
    color: white; /* خلي الكلمة باللون الأبيض عشان تظهر كويس */
}



    </style>
</head>
<body>

<div class="edit-form">
    <h2 style="color: teal;">Edit Your Comment</h2>

    <?php if (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="edit_comment.php">
        <input type="hidden" name="comment_id" value="<?= $comment_id ?>">
        <input type="hidden" name="post_id" value="<?= $commentData['post_id'] ?>">
        <textarea name="comment_text" required><?= htmlspecialchars($commentData['comment']) ?></textarea>
        <button type="submit" class="save">Save Changes</button>
    </form>
    <a href="show_comment.php?post_id=<?= $commentData['post_id'] ?>" class="back-link"><button>Back</button></a>

</div>

</body>
</html>
