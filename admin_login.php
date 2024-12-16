<?php
session_start();
include('db_connection.php'); // الاتصال بقاعدة البيانات

// التحقق مما إذا كان المستخدم قد أرسل النموذج
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // التقاط البيانات من النموذج
    $username = $_POST['username'];
    $password = $_POST['password'];

    // تنظيف البيانات لتفادي هجمات SQL Injection
    $username = mysqli_real_escape_string($conn, $username);
    $password = mysqli_real_escape_string($conn, $password);

    // التحقق إذا كان المستخدم موجود في قاعدة البيانات (الدور هو admin)
    $sql = "SELECT * FROM users WHERE username = ? AND role = 'admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // إذا تم العثور على المستخدم، تحقق من كلمة المرور
    if ($user && password_verify($password, $user['password'])) {
        // حفظ بيانات المستخدم في الجلسة
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // التوجيه إلى صفحة لوحة التحكم الخاصة بالأدمين
        header('Location: admin_dashboard.php'); 
        exit();
    } else {
        echo "<p class='error-message'>اسم المستخدم أو كلمة المرور غير صحيحة!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل دخول المشرف</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #1E90FF;
        }
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            max-width: 900px;
            width: 100%;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .login-box {
            width: 45%;
            padding: 30px;
            text-align: center;
            border-radius: 15px;
        }
        .login-box h2 {
            font-size: 30px;
            color: #333;
            margin-bottom: 20px;
        }
        label {
            font-size: 16px;
            margin-bottom: 10px;
            color: #555;
            display: block;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
        }
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #4caf50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }
        .error-message {
            color: red;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <div class="login-container">

        <!-- تسجيل دخول للمشرف -->
        <div class="login-box">
            <h2>تسجيل دخول للمشرف</h2>
            <form method="POST" action="admin_login.php" autocomplete="off">
                <label for="username">اسم المستخدم (المشرف):</label>
                <input type="text" id="username" name="username" required autocomplete="off"><br>

                <label for="password">كلمة المرور:</label>
                <input type="password" id="password" name="password" required autocomplete="off"><br>

                <button type="submit">تسجيل دخول كمشرف</button>
            </form>
        </div>

    </div>

</body>
</html>
