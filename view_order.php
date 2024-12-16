<?php
session_start();
include('db_connection.php');

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle finishing the order
if (isset($_POST['finish_order']) && isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    
    // Update order status to finished
    $update_query = "UPDATE orders SET order_status = 'Finished' WHERE order_id = '$order_id'";
    if (mysqli_query($conn, $update_query)) {
        // Redirect to avoid form resubmission and refresh the page with updated status
        header("Location: orders.php?order_id=$order_id&order_status=success " );
        exit();
    } else {
        // Handle potential error
        $error_message = "Failed to update order status: " . mysqli_error($conn);
    }
}

// Check if the order ID is provided
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    // Fetch order details from order_items and books tables
    $query_order_details = "SELECT oi.order_item_id, oi.quantity, oi.price, b.title 
                            FROM order_items oi 
                            INNER JOIN books b ON oi.book_id = b.id 
                            WHERE oi.order_id = '$order_id'";
    $result_order_details = mysqli_query($conn, $query_order_details);

    // Fetch the order info (user, date, status)
    $query_order_info = "SELECT o.order_date, o.order_status, u.username 
                         FROM orders o 
                         INNER JOIN users u ON o.user_id = u.id 
                         WHERE o.order_id = '$order_id'";
    $result_order_info = mysqli_query($conn, $query_order_info);
    $order_info = mysqli_fetch_assoc($result_order_info);

} else {
    // Redirect if no order ID is specified
    header("Location: orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <style>
       body {
    font-family: 'Roboto', 'Arial', sans-serif;
    background-color: #f0f2f5;
    margin: 0;
    padding: 20px;
    line-height: 1.6;
    color: #333;
}

.container {
    max-width: 1000px;
    margin: 0 auto;
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    padding: 30px;
}

h2 {
    text-align: center;
    color: #2c3e50;
    margin-bottom: 30px;
    font-size: 2.5rem;
    font-weight: 300;
    border-bottom: 3px solid #3498db;
    padding-bottom: 15px;
}

.order-info {
    display: flex;
    justify-content: space-between;
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
}

.order-info p {
    margin: 10px 0;
    font-size: 1rem;
    color: #495057;
}

.order-info p strong {
    color: #2c3e50;
    min-width: 120px;
    display: inline-block;
}

table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 20px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    border-radius: 10px;
    overflow: hidden;
}

table thead {
    background-color: #3498db;
    color: white;
}

table th {
    padding: 15px;
    text-align: left;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 1px;
}

table td {
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
    transition: background-color 0.3s ease;
}

table tbody tr:nth-child(even) {
    background-color: #f8f9fa;
}

table tbody tr:hover {
    background-color: #e9ecef;
}

.total-price {
    text-align: right;
    margin-top: 20px;
    font-size: 1.2rem;
    font-weight: bold;
    color: #2c3e50;
    padding: 15px;
    background-color: #f1f3f5;
    border-radius: 8px;
}

.finish-order-btn {
    display: block;
    width: 200px;
    margin: 20px auto;
    padding: 10px 20px;
    background-color: #2ecc71;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.finish-order-btn:hover {
    background-color: #27ae60;
}

.finish-order-btn:disabled {
    background-color: #95a5a6;
    cursor: not-allowed;
}

.success-message {
    background-color: #2ecc71;
    color: white;
    padding: 10px;
    text-align: center;
    border-radius: 5px;
    margin-bottom: 20px;
}

@media screen and (max-width: 768px) {
    .container {
        padding: 15px;
    }

    .order-info {
        flex-direction: column;
    }

    table {
        font-size: 0.9rem;
    }

    table th, table td {
        padding: 10px;
    }
}
    </style>
</head>
<body>
<div class="container">
    <?php 
    // Display success message if order was just finished
    if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
        <div class="success-message">Order Successfully Finished!</div>
    <?php endif; ?>

    <h2>Order Details</h2>

    <!-- Display Order Information -->
    <div class="order-info">
        <div>
            <p><strong>Order ID:</strong> <?php echo $order_id; ?></p>
            <p><strong>User:</strong> <?php echo $order_info['username']; ?></p>
            <p><strong>Order Date:</strong> <?php echo date("Y-m-d H:i:s", strtotime($order_info['order_date'])); ?></p>
            <p><strong>Status:</strong> <?php echo ucfirst($order_info['order_status']); ?></p>
        </div>
    </div>

    <h3>Order Items</h3>
    <?php if (mysqli_num_rows($result_order_details) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Book Title</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_order_price = 0;
                while ($row = mysqli_fetch_assoc($result_order_details)):
                    $total_price = $row['quantity'] * $row['price'];
                    $total_order_price += $total_price;
                ?>
                    <tr>
                        <td><?php echo $row['title']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td><?php echo number_format($row['price'], 2); ?> USD</td>
                        <td><?php echo number_format($total_price, 2); ?> USD</td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <div class="total-price">
            Total Order Price: <?php echo number_format($total_order_price, 2); ?> USD
        </div>

        <!-- Finish Order Button -->
        <?php if ($order_info['order_status'] !== 'Finished'): ?>
            <form method="POST" action="">
                <button type="submit" name="finish_order" class="finish-order-btn">
                    Finish Order
                </button>
            </form>
        <?php else: ?>
            <div class="success-message">This order is already finished.</div>
        <?php endif; ?>

    <?php else: ?>
        <p>No items in this order.</p>
    <?php endif; ?>
</div>
</body>
</html>