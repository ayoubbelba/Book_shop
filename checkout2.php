<?php
session_start();
include('db_connection.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
    
}



// Fetch cart data
$user_id = $_SESSION['user_id'];
$query_cart = "SELECT * FROM cart INNER JOIN books ON cart.book_id = books.id WHERE cart.user_id = '$user_id'";
$result_cart = mysqli_query($conn, $query_cart);
$cart_books = mysqli_fetch_all($result_cart, MYSQLI_ASSOC);

// Get the total price
$query_total_price = "SELECT SUM(cart.quantity * books.price) AS total FROM cart INNER JOIN books ON cart.book_id = books.id WHERE cart.user_id = '$user_id'";
$result_total_price = mysqli_query($conn, $query_total_price);
$total_price = mysqli_fetch_assoc($result_total_price)['total'];

// Handle order submission
if (isset($_POST['submit_order'])) {
    // Sanitize user input
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $payment_method = $_POST['payment_method'];

    // Validate input
    if (empty($full_name) || empty($address) || empty($phone_number) || empty($email) || empty($payment_method)) {
        echo "All fields are required.";
        exit();
    }

    // Insert the order details into the 'orders' table
    $query_order = "INSERT INTO orders (user_id, order_date, status, total_amount, shipping_address, payment_method, full_name, email,phone_number, address) 
                    VALUES ('" . $_SESSION['user_id'] . "', NOW(), 'Pending', '$total_price', '$address', '$payment_method', '$full_name', '$email',$phone_number, '$address')";
    if (mysqli_query($conn, $query_order)) {
        // Get the order ID of the inserted order
        $order_id = mysqli_insert_id($conn);

        // Insert each book in the cart into the 'order_items' table
        foreach ($cart_books as $book) {
            $book_id = $book['book_id'];
            $quantity = $book['quantity'];
            $price = $book['price'];

            $query_order_item = "INSERT INTO order_items (order_id, book_id, quantity, price) 
                                 VALUES ('$order_id', '$book_id', '$quantity', '$price')";
            mysqli_query($conn, $query_order_item);
        }

        // Clear the cart after successful order submission
        $query_clear_cart = "DELETE FROM cart WHERE user_id = '$user_id'";
        mysqli_query($conn, $query_clear_cart);

        // Display a confirmation message and redirect back to index.php
        echo "<h2>Your order has been placed successfully!</h2>";
        echo "<p>Your order will be processed shortly. You will be redirected to the homepage in a few seconds...</p>";
        echo "<script>setTimeout(function(){ window.location.href = 'index.php'; }, 5000);</script>";

        exit();
    } else {
        echo "Error placing order. Please try again.";
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Review Order</title>
    <style>
        /* CSS styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .cart-table th, .cart-table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .cart-table th {
            background-color: #f2f2f2;
        }
        .cart-total {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .btn {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #218838;
        }
        .payment-methods {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .payment-methods label {
            cursor: pointer;
        }
    </style>
</head>
    <body>

<div class="container">
    <h1>Review Your Order</h1>

    <!-- Cart Items Table -->
    <table class="cart-table">
        <thead>
            <tr>
                <th>Book Title</th>
                <th>Quantity</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cart_books as $book): ?>
                <tr>
                    <td><?= $book['title']; ?></td>
                    <td><?= $book['quantity']; ?></td>
                    <td>$<?= number_format($book['price'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Cart Total -->
    <div class="cart-total">
        Total: $<?= number_format($total_price, 2); ?>
    </div>

    <!-- Shipping Information Form -->
    <form action="checkout2.php" method="POST">
        <h2>Shipping Information</h2>

        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" name="full_name" id="full_name" required>
        </div>

        <div class="form-group">
            <label for="address">Shipping Address</label>
            <input type="text" name="address" id="address" required>
        </div>

        <div class="form-group">
            <label for="phone_number">Phone Number</label>
            <input type="text" name="phone_number" id="phone_number" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>
        </div>

        <!-- Payment Method -->
        <div class="payment-methods">
            <div>
                <input type="radio" name="payment_method" value="Credit Card" required> Credit Card
            </div>
            <div>
                <input type="radio" name="payment_method" value="PayPal" required> PayPal
            </div>
        </div>

        <!-- Submit Order Button -->
        <button type="submit" name="submit_order" class="btn">Confirm and Pay</button>
    </form>
</div>

</body>
</html>
