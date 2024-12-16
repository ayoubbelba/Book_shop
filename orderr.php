<?php
session_start();
include('db_connection.php');
require_once('vendor/autoload.php'); // Make sure you install Stripe PHP SDK

// Stripe API keys (use test keys for development)
\Stripe\Stripe::setApiKey('sk_test_yourStripeSecretKey');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the cart and order details from the database
$query_cart = "SELECT * FROM cart INNER JOIN books ON cart.book_id = books.id WHERE cart.user_id = '$user_id'";
$result_cart = mysqli_query($conn, $query_cart);
$cart_books = mysqli_fetch_all($result_cart, MYSQLI_ASSOC);

$query_total_price = "SELECT SUM(cart.quantity * books.price) AS total FROM cart INNER JOIN books ON cart.book_id = books.id WHERE cart.user_id = '$user_id'";
$result_total_price = mysqli_query($conn, $query_total_price);
$total_price = mysqli_fetch_assoc($result_total_price)['total'];

// Check if user is submitting the payment
if (isset($_POST['submit_payment'])) {
    $token = $_POST['stripeToken']; // The token received from Stripe
    $full_name = $_POST['full_name'];
    $address = $_POST['address'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];

    try {
        // Create a new Stripe charge
        $charge = \Stripe\PaymentIntent::create([
            'amount' => $total_price * 100, // Convert to cents
            'currency' => 'usd', // Change to your desired currency
            'payment_method' => $token,
            'confirmation_method' => 'manual',
            'confirm' => true,
        ]);

        // Insert order details into the database
        $query_order = "INSERT INTO orders (user_id, full_name, address, phone_number, email, total_price, payment_method, created_at) 
                        VALUES ('$user_id', '$full_name', '$address', '$phone_number', '$email', '$total_price', 'Stripe', NOW())";
        mysqli_query($conn, $query_order);

        $order_id = mysqli_insert_id($conn); // Get the order ID

        // Insert order items into 'order_items' table
        foreach ($cart_books as $book) {
            $book_id = $book['book_id'];
            $quantity = $book['quantity'];
            $query_order_item = "INSERT INTO order_items (order_id, book_id, quantity, price) 
                                 VALUES ('$order_id', '$book_id', '$quantity', '{$book['price']}')";
            mysqli_query($conn, $query_order_item);
        }

        // Clear the cart after successful payment
        $query_clear_cart = "DELETE FROM cart WHERE user_id = '$user_id'";
        mysqli_query($conn, $query_clear_cart);

        // Send confirmation email (optional)
        // You can use PHPMailer or any email library to send a confirmation email.

        // Redirect to the confirmation page
        header("Location: order_confirmation.php");
        exit();
    } catch (Exception $e) {
        // Handle payment failure
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm and Pay</title>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { width: 80%; margin: 0 auto; padding: 20px; }
        .cart-table { width: 100%; border-collapse: collapse; }
        .cart-table th, .cart-table td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        .form-group { margin-bottom: 15px; }
        .form-group input { width: 100%; padding: 10px; }
        .btn { background-color: #28a745; color: white; padding: 10px 20px; border: none; }
    </style>
</head>
<body>

<div class="container">
    <h1>Confirm Your Order</h1>
    <h3>Order Details</h3>

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

    <h3>Total Price: $<?= number_format($total_price, 2); ?></h3>

    <!-- Shipping Information Form -->
    <form action="confirm_and_pay_order.php" method="POST" id="payment-form">
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

        <!-- Stripe Payment Integration -->
        <div class="form-group">
            <label for="card-element">Credit or Debit Card</label>
            <div id="card-element">
                <!-- A Stripe Element will be inserted here. -->
            </div>
            <div id="card-errors" role="alert"></div>
        </div>

        <!-- Submit Payment Button -->
        <button type="submit" name="submit_payment" class="btn">Pay Now</button>
    </form>
</div>

<script>
    // Set up Stripe.js and Elements to handle the payment process
    var stripe = Stripe('pk_test_yourStripePublicKey');
    var elements = stripe.elements();
    var style = {
        base: {
            color: '#32325d',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': {
                color: '#aab7c4'
            }
        }
    };

    var card = elements.create('card', {style: style});
    card.mount('#card-element');

    // Handle form submission and create a payment method
    var form = document.getElementById('payment-form');
    form.addEventListener('submit', function(event) {
        event.preventDefault();

        stripe.createToken(card).then(function(result) {
            if (result.error) {
                // Inform the user if there was an error
                var errorElement = document.getElementById('card-errors');
                errorElement.textContent = result.error.message;
            } else {
                // Send the token to your server for processing the payment
                var token = result.token.id;

                // Add the token to the form before submitting
                var hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'stripeToken');
                hiddenInput.setAttribute('value', token);
                form.appendChild(hiddenInput);

                form.submit();
            }
        });
    });
</script>

</body>
</html>
