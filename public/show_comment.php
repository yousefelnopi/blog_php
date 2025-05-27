<?php
// اتصال بقاعدة البيانات
$servername = "localhost";      
$dbname = "blog_php"; 
$username = "root";
$password = "";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}   

session_start();        
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view comments.");
}

if (!isset($_GET['post_id']) || empty($_GET['post_id'])) {
    die("Post ID is missing.");
}

$post_id = intval($_GET['post_id']);

$sqlPost = "SELECT * FROM posts WHERE id = $post_id";
$resultPost = $conn->query($sqlPost);
if ($resultPost->num_rows == 0) {
    die("Post not found.");
}
$post = $resultPost->fetch_assoc();

$sqlComments = "SELECT c.id AS comment_id, c.comment, c.user_id AS comment_user_id, u.username 
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.post_id = $post_id
                ORDER BY c.created_at DESC";

$resultComments = $conn->query($sqlComments);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Comments for Post #<?= htmlspecialchars($post_id) ?></title>
    <style>
/* خلفية الصفحة */
body {
    font-family: 'Arial', sans-serif;
    background-color: #000; /* أسود */
    background-image: 
        linear-gradient(45deg, rgba(255,255,255,0.03) 25%, transparent 25%),
        linear-gradient(-45deg, rgba(255,255,255,0.03) 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, rgba(15, 221, 221, 0.03) 75%),
        linear-gradient(-45deg, transparent 75%, rgba(255,255,255,0.03) 75%);
    background-size: 40px 40px;
    padding: 20px;
    color: white;
}

/* كل تعليق */
.comment {
    background: rgba(255, 255, 255, 0.1);
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 15px;
    box-shadow: 0 4px 10px rgba(0, 255, 255, 0.1);
    color: #ffffff;
    backdrop-filter: blur(4px);
}

/* اسم المستخدم داخل التعليق */
.comment strong {
    color: #00ffff;
}

/* نموذج إضافة تعليق */
.comment-form {
    margin-top: 30px;
    display: flex;
    gap: 10px;
}

/* صندوق الكتابة */
.comment-form textarea {
    flex-grow: 1;
    resize: none;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #00b3b3;
    background-color: #111;
    color: #fff;
    font-size: 15px;
}

/* زر الإرسال */
.comment-submit-btn {
    padding: 10px 20px;
    background-color: #008080;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.comment-submit-btn:hover {
    background-color: #00b3b3;
}

    </style>
</head>
<body>
<h1 style="text-align: center; color: teal;">Comments for Post #<?= htmlspecialchars($post_id) ?></h1>
<!-- زر الصفحة الرئيسية -->
<a href="index.php" class="icon-button">
    <i class="fas fa-home"></i>
</a>

<!-- زر الرجوع -->
<a href="javascript:history.back()" class="icon-button">
    <i class="fas fa-arrow-left"></i>
</a>

<!-- تنسيقات CSS -->
<style>
.icon-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background: linear-gradient(145deg,rgb(3, 92, 128),rgb(11, 55, 173)); /* تدرج أخضر */
    border-radius: 50%;
    box-shadow: 4px 4px 8pxrgb(2, 73, 114), -4px -4px 8pxrgb(2, 70, 134);
    text-decoration: none;
    margin: 10px;
    transition: all 0.3s ease;
}

.icon-button i {
    font-size: 30px;
    color: white;
    transition: transform 0.3s ease;
}

.icon-button:hover {
    box-shadow: inset 4px 4px 8pxrgb(2, 95, 138), inset -4px -4px 8pxrgb(61, 104, 72);
}

.icon-button:hover i {
    transform: scale(1.1);
}
</style>

  <i class="fas fa-arrow-left"></i>
</a>

<style>
.back-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  background: linear-gradient(145deg, #6c73d5, #4a50b5);
  border-radius: 50%;
  box-shadow: 4px 4px 8px #3d3d3d55, -4px -4px 8px #ffffff99;
  text-decoration: none;
  transition: all 0.3s ease;
}

.back-button i {
  font-size: 24px;
  color: white;
  transition: transform 0.3s ease;
}

.back-button:hover {
  box-shadow: inset 4px 4px 8px #3d3d3d55, inset -4px -4px 8px #ffffff99;
}

.back-button:hover i {
  transform: translateX(-3px);
}
</style>

<br>
<h2>Comments for Post #<?= htmlspecialchars($post_id) ?></h2>

<div class="comments-list">
    <?php if ($resultComments->num_rows > 0): ?>
        <?php while ($comment = $resultComments->fetch_assoc()): ?>
            <div class="comment">
                <strong><?= htmlspecialchars($comment['username']) ?>:</strong>
                <?= nl2br(htmlspecialchars($comment['comment'])) ?>

                <?php if ($_SESSION['user_id'] == $comment['comment_user_id'] || $_SESSION['user_id'] == $post['user_id']): ?>
                    <form method="POST" action="delete_comment.php" style="display:inline;">
                        <input type="hidden" name="comment_id" value="<?= $comment['comment_id'] ?>">
                        <button type="submit" style="background-color: red; color: white; border: none; padding: 5px 10px; border-radius: 4px; margin-left: 10px; cursor: pointer;">
                            Del
                        </button>
                    </form>

                    <?php if ($_SESSION['user_id'] == $comment['comment_user_id']): ?>
                        <a href="edit_comment.php?comment_id=<?= $comment['comment_id'] ?>&Text=<?= urlencode($comment['comment']) ?>" 
                        style="background-color: teal; color: white; padding: 5px 10px; border-radius: 4px; margin-left: 10px; text-decoration: none;">
                            Edit
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="color: red;">No comments yet.</p>
    <?php endif; ?>
</div>

</body>
</html>
