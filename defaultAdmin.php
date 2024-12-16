<?php
include('db_connection.php');

// بيانات المستخدم الجديد
$username = 'ayoub';  // اسم المستخدم
$password = '12345678';  // كلمة المرور (غير مشفرة)
$role = 'admin';  // الدور

// تشفير كلمة المرور
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// استعلام لإدخال المستخدم الجديد
$sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";

// تحضير الاستعلام
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $username, $hashed_password, $role);

// تنفيذ الاستعلام
if ($stmt->execute()) {
    echo "تم إضافة الأدمن بنجاح!";
} else {
    echo "حدث خطأ أثناء إضافة الأدمن: " . $stmt->error;
}

// إغلاق الاتصال
$stmt->close();
$conn->close();
?>
