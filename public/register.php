<?php 

require_once '../config/db.php';

$errors = [];


if($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if(empty($username) || empty($password) || empty($confirm))
    {
        $errors[] = "All Fields Are Required.";
    }else if($password <> $confirm)
    {
        $errors[] = "Password Do Not Match ";
    }else{
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if($stmt->fetch())
        {
            $errors[] = "Username Alreaedy exists.";
        }else{
            $hashedpassword = password_hash($password,PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users(username,password)values(?, ?)");
            $stmt->execute([$username, $hashedpassword]);
            header("Location: index.php");
            exit;
        }
    }

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<h2>Register</h2>
<?php foreach ($errors as $error): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endforeach; ?>
<form method="POST">
    <input type="text" name="username" placeholder="Username"><br><br>
    <input type="password" name="password" placeholder="Password"><br><br>
    <input type="password" name="confirm" placeholder="Confirm Password"><br><br>
    <button type="submit">Register</button>
</form>
</body>
</html>