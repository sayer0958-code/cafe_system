<?php
session_start();
include 'db.php';
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE username = '$username' AND password = '$password'");
    if ($result && $result->num_rows > 0) {
        $_SESSION['admin_logged'] = true;
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit();
    } else {
        $msg = "اسم المستخدم أو كلمة المرور غير صحيحة!";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول للإدارة</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background: #0f172a; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: #fff; padding: 30px; border-radius: 16px; width: 320px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
        h2 { text-align: center; color: #1e293b; margin-bottom: 20px; }
        label { display: block; margin-bottom: 6px; font-weight: bold; color: #475569; }
        input { width: 100%; padding: 10px; margin-bottom: 15px; border: 2px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #3b82f6; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; }
        .error { color: #ef4444; font-size: 14px; text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="login-card">
    <h2>تسجيل دخول الإدارة</h2>
    <?php if($msg != "") { echo "<div class='error'>$msg</div>"; } ?>
    <form method="POST" action="">
        <label>اسم المستخدم:</label>
        <input type="text" name="username" required autocomplete="off">
        <label>كلمة المرور:</label>
        <input type="password" name="password" required autocomplete="off">
        <button type="submit">دخول</button>
    </form>
</div>
</body>
</html>