<?php
session_start();
include('db_connection.php');

// التحقق من وجود المستخدم في الجلسة
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_id = $_GET['cart_id'];
$action = $_GET['action'];

// التعامل مع العمليات المختلفة
if ($action == 'increment') {
    $sql = "UPDATE cart SET quantity = quantity + 1 WHERE id = ? AND user_id = ?";
} elseif ($action == 'decrement') {
    $sql = "UPDATE cart SET quantity = quantity - 1 WHERE id = ? AND user_id = ? AND quantity > 1";
} elseif ($action == 'remove') {
    $sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
}

if (isset($sql)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $cart_id, $user_id);
    $stmt->execute();
}

// إعادة التوجيه إلى صفحة السلة بعد التعديل
header('Location: cart.php');
exit();
?>
