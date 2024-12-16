<?php
session_start();
include('db_connection.php');

// Check if user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Get the book_id from the URL
if (isset($_GET['id'])) {
    $book_id = $_GET['id'];

    // Step 1: Delete related rows in the cart table
    $stmt_cart = $conn->prepare("DELETE FROM cart WHERE book_id = ?");
    $stmt_cart->bind_param("i", $book_id);
    if (!$stmt_cart->execute()) {
        echo "Error deleting related cart entries.";
        exit();
    }

    // Step 2: Delete the book from the books table
    $stmt_book = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt_book->bind_param("i", $book_id);
    if ($stmt_book->execute()) {
        // Redirect back to the admin dashboard after successful deletion
        header("Location: admin_dashboard.php?message=Book deleted successfully");
        exit();
    } else {
        echo "Error deleting the book.";
        exit();
    }
} else {
    echo "Invalid book ID.";
    exit();
}
?>
