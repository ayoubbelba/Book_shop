<?php
include('db_connection.php'); // تأكد من الاتصال بقاعدة البيانات

// التحقق من البيانات المرسلة عبر النموذج
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // التحقق من أن كلمة المرور والتأكيد متطابقان
    if ($password !== $confirm_password) {
        echo "كلمة المرور غير متطابقة!";
        exit();
    }

    // تعقيم المدخلات لتجنب SQL Injection
    $username = mysqli_real_escape_string($conn, $username);
    $email = mysqli_real_escape_string($conn, $email);
    $password = mysqli_real_escape_string($conn, $password);

    // تشفير كلمة المرور قبل حفظها في قاعدة البيانات
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // التحقق إذا كان اسم المستخدم أو البريد الإلكتروني موجودًا مسبقًا
    $sql_check = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param('ss', $username, $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        echo "اسم المستخدم أو البريد الإلكتروني موجود بالفعل!";
        exit();
    }

    // استعلام لإدخال البيانات في قاعدة البيانات
    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $username, $email, $hashed_password);

    // تنفيذ الاستعلام
    if ($stmt->execute()) {
        echo "تم إنشاء الحساب بنجاح!";
    } else {
        echo "حدث خطأ أثناء إنشاء الحساب.";
    }
}
?>
