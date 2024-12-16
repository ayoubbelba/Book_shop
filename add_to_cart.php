<?php
session_start();
include('db_connection.php');

if (isset($_GET['book_id']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $book_id = $_GET['book_id'];

    // التحقق إذا كان الكتاب موجودًا بالفعل في السلة
    $sql_check = "SELECT * FROM cart WHERE user_id = ? AND book_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param('ii', $user_id, $book_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // إذا كان الكتاب موجودًا بالفعل، نقوم بتحديث الكمية
        $sql_update = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND book_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('ii', $user_id, $book_id);
        $stmt_update->execute();
    } else {
        // إذا لم يكن الكتاب في السلة، نقوم بإضافته
        $sql_insert = "INSERT INTO cart (user_id, book_id, quantity) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $quantity = 1;
        $stmt_insert->bind_param('iii', $user_id, $book_id, $quantity);
        $stmt_insert->execute();
    }

    // إعادة التوجيه إلى الصفحة الرئيسية بعد إضافة الكتاب
    header('Location: index.php');
    exit();
}
?>
