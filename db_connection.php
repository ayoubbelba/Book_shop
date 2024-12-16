<?php
$servername = "localhost";
$username = "root"; // المستخدم الافتراضي لـ XAMPP
$password = ""; // كلمة المرور الافتراضية في XAMPP
$dbname = "bookshop"; // تأكد من أن هذه هي قاعدة البيانات الصحيحة

// إنشاء الاتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}
?>
