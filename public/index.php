<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}



// جلب بيانات المستخدم
$stmtUser = $pdo->prepare("SELECT username, profile_img FROM users WHERE id = ?");
$stmtUser->execute([$_SESSION['user_id']]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

// معالجة تسجيل لايك أو ديسلايك أو تعليق
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = $_POST['post_id'] ?? null;

    // تسجيل لايك
    if (isset($_POST['like'])) {
        $check = $pdo->prepare("SELECT * FROM reactions WHERE user_id = ? AND post_id = ?");
        $check->execute([$_SESSION['user_id'], $postId]);
        $exists = $check->fetch();

        if ($exists) {
            $update = $pdo->prepare("UPDATE reactions SET reaction = 'like' WHERE user_id = ? AND post_id = ?");
            $update->execute([$_SESSION['user_id'], $postId]);
        } else {
            $insert = $pdo->prepare("INSERT INTO reactions (user_id, post_id, reaction) VALUES (?, ?, 'like')");
            $insert->execute([$_SESSION['user_id'], $postId]);
        }
    }

    // تسجيل ديسلايك
    if (isset($_POST['dislike'])) {
        $check = $pdo->prepare("SELECT * FROM reactions WHERE user_id = ? AND post_id = ?");
        $check->execute([$_SESSION['user_id'], $postId]);
        $exists = $check->fetch();

        if ($exists) {
            $update = $pdo->prepare("UPDATE reactions SET reaction = 'dislike' WHERE user_id = ? AND post_id = ?");
            $update->execute([$_SESSION['user_id'], $postId]);
        } else {
            $insert = $pdo->prepare("INSERT INTO reactions (user_id, post_id, reaction) VALUES (?, ?, 'dislike')");
            $insert->execute([$_SESSION['user_id'], $postId]);
        }
    }

    // تسجيل تعليق
    if (isset($_POST['comment_text']) && trim($_POST['comment_text']) !== '') {
        $commentText = trim($_POST['comment_text']);
        $insertComment = $pdo->prepare("INSERT INTO comments (user_id, post_id, comment, created_at) VALUES (?, ?, ?, NOW())");
        $insertComment->execute([$_SESSION['user_id'], $postId, $commentText]);
    }

    // بعد العملية، رجع للصفحة لتحديث البيانات وتجنب إعادة إرسال النموذج
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// جلب جميع البوستات مع اسم المستخدم
$stmt = $pdo->query("SELECT posts.*, users.username, users.id AS user_id_fk FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.created_at DESC");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// جلب عدد اللايكات والديسلايكات لكل بوست دفعة واحدة
$reactionsStmt = $pdo->query("SELECT post_id, reaction, COUNT(*) as count FROM reactions GROUP BY post_id, reaction");
$reactionsData = [];
foreach ($reactionsStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $reactionsData[$row['post_id']][$row['reaction']] = $row['count'];
}

// جلب التعليقات لكل بوست
// $commentsStmt = $pdo->query("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id ORDER BY comments.created_at ASC");
// $commentsData = [];
// foreach ($commentsStmt->fetchAll(PDO::FETCH_ASSOC) as $comment) {
//     $commentsData[$comment['post_id']][] = $comment;
// }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Blog Home</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .reaction-btn {
            background-color: transparent;
            color: teal;
            border: none;
            padding: 6px 8px;
            margin-right: 10px;
            cursor: pointer;
            font-size: 20px;
            vertical-align: middle;
        }

        .reaction-btn.dislike {
            color: red;
        }

        .reaction-wrapper {
            margin-top: 8px;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .comment-section {
            background-color: rgba(255, 255, 255, 0.1);
            color: teal;
            padding: 10px;
            border-radius: 8px;
            display: none;
            /* مخفي افتراضياً */
            position: relative;
            max-height: 300px;
            /* تقدر تغير حسب حاجتك */
            overflow-y: auto;
            padding-bottom: 60px;
            /* مساحة كافية للفورم */
            position: relative;

        }

        .comment-form {
            position: absolute;
            bottom: 10px;
            right: 10px;
            /* بدل left خليها right عشان تثبت على اليمين */
            display: flex;
            gap: 10px;
            background-color: rgba(255, 255, 255, 0.3);
            /* شفافية 30% */
            padding: 6px;
            border-radius: 5px;
            box-sizing: border-box;
            align-items: center;
            max-width: 600px;
            /* مساحة متوسطة */
            /* حذف margin: 0 auto; */
        }


        .comment-form textarea {
            flex-grow: 1;
            resize: none;
            height: 36px;
            /* أقل شوية عشان متوسطة */
            padding: 6px 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
            background-color: rgba(242, 245, 245, 0.99);
            /* خلفية شفافة للنص */
            color: black;
            /* لون النص */
        }

        .comment-submit-btn {
            padding: 7px 14px;
            border: none;
            background-color: teal;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .comment-submit-btn:hover {
            background-color: #006666;
        }



        .toggle-comments-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 24px;
            color: teal;
            vertical-align: middle;
            margin-top: 10px;
        }

        .post-content {
            margin-bottom: 10px;
        }

        .text-primary {
            color: #007bff;
            /* أزرق */
        }
    </style>

</head>

<body>

    <h1 style="position: relative; left: 200px;"><em>Blogaly.Coer</em></h1>

    <!-- عرض صورة البروفايل -->
    <?php if (!empty($user['profile_img'])): ?>
        <img src="../<?= htmlspecialchars($user['profile_img']) ?>"
            alt="Profile Image"
            style="border-radius: 50%; object-fit: cover; border: 4px solid teal; margin: 20px;"
            width="150px" height="150px">
    <?php else: ?>
        <p>No Profile Image</p>
    <?php endif; ?>

    <!-- روابط المستخدم -->
    <?php if (isset($_SESSION['username'])): ?>
        <p style="margin-right: 200px;">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!<br>
            <a href="profile.php">
                <button style="text-decoration: none; color: teal; background-color: aqua;">
                    <i class="fas fa-user-circle" style="font-size: 25px; margin-right: 5px;"></i>
                </button>
            </a><br><br>
            <a href="create_post.php">
                <button style="background-color: teal; color: white; border: none; padding: 8px 16px; border-radius: 10px;">+ New Post</button>
            </a>
            <a href="logout.php">
                <button style="background-color: red; color: white; border: none; padding: 8px 16px; border-radius: 10px;">Logout</button>
            </a>
        </p><br>
    <?php else: ?>
        <p><a href="login.php">Login</a> | <a href="register.php">Register</a></p>
    <?php endif; ?>

    <hr>
    
    <!-- عرض البوستات -->
    <?php foreach ($posts as $post): ?>
        
        <div class="title">
        
        </div>
        <div class="post" style="margin-bottom: 30px;">
            <p>
                <a href="prof2.php?id=<?= $post['user_id_fk'] ?>" style="text-decoration: none; color: teal;">
                    <?= htmlspecialchars($post['username']) ?>
                    <i class="fas fa-user-circle" style="font-size: 25px; margin-right: 5px;"></i>
                </a>
            </p>
            
            <?php if ($_SESSION['user_id'] == $post['user_id']): ?>
                <p>
                    <a href="edit_post.php?id=<?= $post['id'] ?>">
                        <button style="background-color: teal; color: white; border: none; padding: 8px 16px; border-radius: 10px;">Edit</button>
                    </a>
                    <a href="delete_post.php?id=<?= $post['id'] ?>">
                        <button style="background-color: red; color: white; border: none; padding: 8px 16px; border-radius: 10px;">Delete</button>
                    </a>
                </p>
                <?php endif; ?>
                
                <!-- صورة البوست -->
                <?php if (!empty($post['image'])): ?>
                    <img src="../<?= htmlspecialchars($post['image']) ?>" alt="Post Image" width="300px" style="display:block; margin-bottom: 8px;">

                <!-- أزرار اللايك والديسلايك تحت الصورة -->
                <div class="reaction-wrapper">
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <button type="submit" name="like" class="reaction-btn" title="Like">
                            <i class="fas fa-thumbs-up"></i>
                        </button>
                        <span><?= $reactionsData[$post['id']]['like'] ?? 0 ?></span>
                    </form>
                    
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <button type="submit" name="dislike" class="reaction-btn dislike" title="Dislike">
                            <i class="fas fa-thumbs-down"></i>
                        </button>
                        <span><?= $reactionsData[$post['id']]['dislike'] ?? 0 ?></span>
                    </form>
                </div>
                <?php else: ?>
                <!-- لو مفيش صورة: خلي أزرار اللايك والديسلايك تحت محتوى البوست -->
                <div class="reaction-wrapper" style="margin-bottom: 10px;">
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <button type="submit" name="like" class="reaction-btn" title="Like">
                            <i class="fas fa-thumbs-up"></i>
                        </button>
                        <span><?= $reactionsData[$post['id']]['like'] ?? 0 ?></span>
                    </form>
                    
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <button type="submit" name="dislike" class="reaction-btn dislike" title="Dislike">
                            <i class="fas fa-thumbs-down"></i>
                        </button>
                        <span><?= $reactionsData[$post['id']]['dislike'] ?? 0 ?></span>
                    </form>
                </div>
            <?php endif; ?>

                    <div class="post-content">
            <h3><?= htmlspecialchars($post['title']) ?></h3>
            <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
        </div>


            <!-- زر لإظهار وإخفاء التعليقات -->
            <button class="toggle-comments-btn" title="View Comments">
                <a href="show_comment.php?post_id=<?= $post['id'] ?>" style="color: inherit; text-decoration: none;">
                    <i class="fa-regular fa-comment-dots"></i>
                </a>
            </button>
            
            
            <?php if ($_SESSION['user_id']) : ?>
    <button class="open-comment-btn" style="background: none; border: none; cursor: pointer;">
        <i class="fas fa-bolt" style="color: rgb(0, 255, 170); font-size: 24px;"></i>
    </button>

    <form method="post" class="comment-form" style="margin-top: 10px; display: none;">
        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
        <textarea name="comment_text" rows="2" placeholder="Add a comment..." required style="width: 100%;"></textarea>
        <button type="submit" class="comment-submit-btn">Send</button>
    </form>

<?php endif; ?>

            </div>
            
        </div>
        <hr>
    <?php endforeach; ?>

<script>
    document.querySelectorAll('.open-comment-btn').forEach((btn) => {
        btn.addEventListener('click', function(event) {
            event.preventDefault();
            const form = this.nextElementSibling; // يفترض الفورم جاي بعد الزر مباشرة
            if (form && form.classList.contains('comment-form')) {
                form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
            }
        });
    });
</script>

</body>

</html>