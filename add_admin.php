<?php
// تضمين الاتصال بقاعدة البيانات
include('db_connection.php');

// تحديد تفاصيل المدير
$id=10;
$admin_username = 'ayoub';  // استبدل باسم المدير الذي ترغب في إضافته
$admin_password = '111';  // استبدل بكلمة المرور التي ترغب فيها

// تشفير كلمة المرور باستخدام password_hash
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

// إدخال المستخدم في قاعدة البيانات
$sql = "INSERT INTO users (id,username, password, role) 
        VALUES ('$id',$admin_username', '$hashed_password', 'admin')";

if ($conn->query($sql) === TRUE) {
    echo "تم إنشاء حساب المدير بنجاح.";
} else {
    echo "خطأ: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
