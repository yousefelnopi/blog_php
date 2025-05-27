<?php

require_once '../config/db.php';

session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $errors[] = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Invalid credentials.";
        }
    }
}
?><!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<h2>Login</h2>
<?php foreach ($errors as $error): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endforeach; ?>
<form method="POST">
    <input type="text" name="username" placeholder="Username"><br><br>
    <input type="password" name="password" placeholder="Password"><br><br>
    <button type="submit">Login</button>
    <button><a href="register.php" style="text-decoration: none; color: inherit;">Register</a></button>

</form>
</body>
</html>