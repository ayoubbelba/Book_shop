<?php 
session_start();
include('db_connection.php');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Add to cart logic
if (isset($_GET['add_to_cart'])) {
    $book_id = $_GET['add_to_cart'];
    $user_id = $_SESSION['user_id'];
    $image_path = $_SESSION['image_path'];
    $quantity = 1;

    // Check if the book already exists in the cart
    $query_check = "SELECT * FROM cart WHERE user_id = '$user_id' AND book_id = '$book_id'";
    $result_check = mysqli_query($conn, $query_check);
    if (mysqli_num_rows($result_check) > 0) {
        // Update the quantity if the book already exists in the cart
        $query_update = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = '$user_id' AND book_id = '$book_id'";
        mysqli_query($conn, $query_update);
    } else {
        // Insert the book into the cart
        $query_add = "INSERT INTO cart (user_id, book_id, quantity, added_at) VALUES ('$user_id', '$book_id', '$quantity', NOW())";
        mysqli_query($conn, $query_add);
    }
    header("Location: cart.php");
    exit();
}

// Remove item from cart logic
if (isset($_GET['remove'])) {
    $book_id = $_GET['remove'];
    $user_id = $_SESSION['user_id'];

    // Delete the item from the cart
    $query_remove = "DELETE FROM cart WHERE user_id = '$user_id' AND book_id = '$book_id'";
    mysqli_query($conn, $query_remove);

    header("Location: cart.php");
    exit();
}

// Update quantity logic (AJAX)
if (isset($_POST['update_quantity'])) {
    $book_id = $_POST['book_id'];
    $user_id = $_SESSION['user_id'];
    $change = $_POST['change']; // It will be either 'increase' or 'decrease'

    // Get the current quantity from the cart
    $query_current_quantity = "SELECT quantity, price FROM cart INNER JOIN books ON cart.book_id = books.id WHERE cart.user_id = '$user_id' AND cart.book_id = '$book_id'";
    $result_quantity = mysqli_query($conn, $query_current_quantity);
    $row = mysqli_fetch_assoc($result_quantity);

    $current_quantity = $row['quantity'];
    $price_per_item = $row['price'];

    // Update quantity based on change
    if ($change == 'increase') {
        $new_quantity = $current_quantity + 1;
    } elseif ($change == 'decrease' && $current_quantity > 1) {
        $new_quantity = $current_quantity - 1;
    } else {
        $new_quantity = $current_quantity; // Do nothing if quantity is already 1 and user wants to decrease
    }

    // Update the cart with the new quantity
    $query_update_quantity = "UPDATE cart SET quantity = $new_quantity WHERE user_id = '$user_id' AND book_id = '$book_id'";
    mysqli_query($conn, $query_update_quantity);

    // Recalculate the total price
    $query_total_price = "SELECT SUM(cart.quantity * books.price) AS total FROM cart INNER JOIN books ON cart.book_id = books.id WHERE cart.user_id = '$user_id'";
    $result_total_price = mysqli_query($conn, $query_total_price);
    $total_price = mysqli_fetch_assoc($result_total_price)['total'];

    // Return the new quantity and total price as JSON
    echo json_encode([
        'new_quantity' => $new_quantity,
        'total' => $total_price
    ]);
    exit();
}

// Check if "Order Now" is clicked and cart is not empty
if (isset($_GET['order_now'])) {
    $user_id = $_SESSION['user_id'];

    // Check if there are items in the user's cart
    $query_check_cart = "SELECT COUNT(*) AS cart_count FROM cart WHERE user_id = '$user_id'";
    $result_check_cart = mysqli_query($conn, $query_check_cart);
    $cart_count = mysqli_fetch_assoc($result_check_cart)['cart_count'];

    if ($cart_count > 0) {
        // Fetch the cart details to be passed to checkout2.php
        $query_cart_details = "SELECT cart.book_id, cart.quantity, books.title, books.price FROM cart 
                               INNER JOIN books ON cart.book_id = books.id WHERE cart.user_id = '$user_id'";
        $result_cart_details = mysqli_query($conn, $query_cart_details);
        
        // Save the cart details in session variables
        $_SESSION['cart_items'] = [];
        while ($row = mysqli_fetch_assoc($result_cart_details)) {
            $_SESSION['cart_items'][] = $row;
        }

        // Redirect to checkout2.php
        header("Location: checkout2.php");
        exit();
    } else {
        // If the cart is empty, show an error message
        echo "<script>alert('Your cart is empty. Please add items to your cart before proceeding to checkout.'); window.location='cart.php';</script>";
        exit();
    }
}

// Fetch the books in the cart for the user
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM cart INNER JOIN books ON cart.book_id = books.id WHERE cart.user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$cart_books = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Fetch the total price for all items in the cart
$query_total_price = "SELECT SUM(cart.quantity * books.price) AS total FROM cart INNER JOIN books ON cart.book_id = books.id WHERE cart.user_id = '$user_id'";
$result_total_price = mysqli_query($conn, $query_total_price);
$total_price = mysqli_fetch_assoc($result_total_price)['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <style>
        /* General Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-image: url('library.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            margin: 0;
            padding: 0;
            color: white;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        header {
            padding: 20px;
            text-align: center;
            background: rgba(0, 0, 0, 0.6);
            color: white;
        }

        header h1 {
            margin: 0;
            font-size: 36px;
            text-transform: uppercase;
        }

        .cart-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 40px;
            padding: 20px;
            border-radius: 10px;
            background-color: rgba(0, 0, 0, 0.5);
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            width: 100%;
            background: rgba(255, 255, 255, 0.7);
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .cart-item img {
            width: 120px;
            height: 180px;
            object-fit: cover;
            margin-right: 20px;
            border-radius: 8px;
        }

        .cart-item .details {
            flex: 1;
        }

        .cart-item .details h3 {
            font-size: 20px;
            margin: 0;
            color: #333;
        }

        .cart-item .details p {
            margin: 5px 0;
            color: #555;
        }

        .cart-item .details .price {
            font-weight: bold;
            color: #28a745;
        }

        .cart-item .buttons-container {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 15px;
        }

        .cart-item .remove-btn {
            background-color: #FF6347;
            color: white;
            border: none;
            padding: 12px 20px;
            cursor: pointer;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
            font-size: 16px;
        }

        .cart-item .remove-btn:hover {
            background-color: #FF4500;
        }

        .quantity-btn {
            background-color: #007BFF;
            color: white;
            padding: 5px 10px;
            font-size: 15px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .quantity-btn:hover {
            background-color: #0056b3;
        }

        .back-to-library, .order-now {
            background-color: #4CAF50;
            color: white;
            padding: 15px 25px;
            font-size: 18px;
            border-radius: 10px;
            text-decoration: none;
            margin-top: 30px;
            display: inline-block;
            transition: background-color 0.3s ease;
        }

        .back-to-library:hover, .order-now:hover {
            background-color: #45a049;
        }

        .total-price {
            font-size: 20px;
            font-weight: bold;
            color: #28a745;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<header>
    <h1>Your Shopping Cart</h1>
</header>


<div class="cart-container">
<?php
    if (!empty($cart_books)) {
        foreach ($cart_books as $book) {
            $image_path = htmlspecialchars($book['image_path']); // Get image path for the book

            echo "<div class='cart-item' data-book-id='" . $book['book_id'] . "'>
                    <img src='" . $image_path . "' alt='" . htmlspecialchars($book['title']) . "' style='width: 100px; height: auto; object-fit: cover;'>
                    <div class='details'>
                        <h3>" . htmlspecialchars($book['title']) . "</h3>
                        <p>Author: " . htmlspecialchars($book['author']) . "</p>
                        <p class='price'>$" . number_format($book['price'], 2) . " each</p>
                    </div>
                    <div class='buttons-container'>
                        <button class='quantity-btn' onclick='updateQuantity(" . $book['book_id'] . ", \"increase\")'>+</button>
                        <span>" . $book['quantity'] . "</span>
                        <button class='quantity-btn' onclick='updateQuantity(" . $book['book_id'] . ", \"decrease\")'>-</button>
                        <a href='cart.php?remove=" . $book['book_id'] . "' class='remove-btn'>Remove</a>
                    </div>
                </div>";
        }
    } else {
        echo "<p>Your cart is empty!</p>";
    }
?>

    <a href="index.php" class="back-to-library">Back to Library</a>
    <a href="checkout2.php" class="order-now">Order Now</a>
</div>

<script>
    function updateQuantity(bookId, change) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'cart.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);

                // Update the quantity on the page
                var quantityElement = document.querySelector('div.cart-item[data-book-id="' + bookId + '"] span');
                if (quantityElement) {
                    quantityElement.innerText = response.new_quantity;
                }

                // Update the total price on the page
                document.querySelector('.total-price').innerText = 'Total: $' + response.total.toFixed(2);
            }
        };

        // Send the request to update the quantity
        xhr.send('update_quantity=1&book_id=' + bookId + '&change=' + change);
    }
    
</script>

</body>
</html>