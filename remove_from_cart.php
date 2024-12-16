<?php
session_start();
include('db_connection.php');

// تحقق من أن المستخدم مسجل الدخول
if (!isset($_SESSION['user_id'])) {
    echo "من فضلك سجل الدخول أولاً.";
    exit;
}

// الحصول على معرّف العنصر الذي تريد حذفه
$cart_id = $_GET['cart_id'];

// استعلام لحذف العنصر من العربة
$sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $cart_id, $_SESSION['user_id']);
$stmt->execute();

echo "تم حذف الكتاب من العربة!";
?>
