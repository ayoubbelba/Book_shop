<?php
session_start();
include('db_connection.php');

// Check if book_id is passed
if (isset($_GET['book_id'])) {
    $book_id = $_GET['book_id'];
    
    // Fetch book details from the database
    $query = "SELECT * FROM books WHERE id = '$book_id'";
    $result = mysqli_query($conn, $query);
    $book = mysqli_fetch_assoc($result);
    
    if (!$book) {
        echo "Book not found.";
        exit();
    }
} else {
    echo "No book selected.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="styles.css">
    <style>
    /* General styles */
   /* General styles */
/* General styles */
/* General styles */
body {
    font-family: 'Helvetica Neue', Arial, sans-serif;
    background-color: #f4f4f4;
    padding: 0;
    margin: 0;
    color: #333;
}

.container {
    width: 80%;
    margin: auto;
    padding: 20px;
    background-color: #fff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}

/* Book details section */
.book-details {
    max-width: 800px;
    margin: 30px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.book-details h2 {
    text-align: center;
    margin-bottom: 20px;
    font-size: 28px;
    color: #555;
}

.book-details p {
    margin: 10px 0;
    line-height: 1.6;
    color: #666;
}

.book-details .price {
    font-size: 24px;
    font-weight: bold;
    color: #28a745;
    margin-top: 10px;
}

.buy-btn {
    display: block;
    width: 100%;
    padding: 15px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 5px;
    text-align: center;
    cursor: pointer;
    font-size: 18px;
    transition: background-color 0.3s ease;
}

.buy-btn:hover {
    background-color: #218838;
}

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    overflow: auto;
    transition: opacity 0.3s ease;
}

.modal.active {
    display: block;
    opacity: 1;
}

.modal-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 20px;
    border-radius: 10px;
    width: 500px;
    position: relative;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transform: translateY(-50px);
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.modal.active .modal-content {
    transform: translateY(0);
    opacity: 1;
}

.modal-content h2 {
    text-align: center;
    margin-bottom: 20px;
    font-size: 24px;
    color: #555;
}

.modal-content input,
.modal-content select {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border-radius: 5px;
    border: 1px solid #ddd;
    font-size: 16px;
}

.modal-content .btn-submit {
    width: 100%;
    padding: 15px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 18px;
    transition: background-color 0.3s ease;
}

.modal-content .btn-submit:hover {
    background-color: #0056b3;
}

.close {
    position: absolute;
    top: 10px;
    right: 10px;
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
}

/* For a smoother modal opening and closing */
.modal {
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
}

.modal.active {
    opacity: 1;
    pointer-events: auto;
}
.image{
    padding: 2px 50px ;
    width: 150px; /* Fixed width */
    height: 200px; 
}
.field{
    width: 480px
}
    </style>
</head>
<body>

    <!-- Book details section -->
    <div class="book-details">
        <h2>Book Checkout</h2>
        <p><strong>Cover:</strong></p>
        <img class="image" src="<?php echo $book['image_path'];?>">
        <p><strong>Title:</strong> <?php echo $book['title']; ?></p>
        <p><strong>Author:</strong> <?php echo $book['author']; ?></p>
        <p><strong>Description:</strong> <?php echo $book['description']; ?></p>
        <p class="price">Price: $<?php echo number_format($book['price'], 2); ?></p>
        <!-- Buy button to trigger modal -->
        <button class="buy-btn" id="buyBtn">Buy Now</button>
    </div>

    <!-- Modal form for user information -->
    <div id="checkoutModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h2>Enter Your Details</h2>
            <form action="process_order.php" method="POST">
                <div class="field">
                <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                <input type="text" name="name" placeholder="Your Name" required>
                <input type="email" name="email" placeholder="Your Email" required>
                <input type="text" name="address" placeholder="Your Address" required>
                <input type="number" name="phone" placeholder="Your Phone Number" required>
                <input type="number" name="quantity" placeholder="Quantity" min="1" required>
                </div>
                <button type="submit" class="btn-submit">Submit Order</button>
            </form>
        </div>
    </div>

    <script>
    // Get the modal
    var modal = document.getElementById("checkoutModal");
    // Get the button that opens the modal
    var buyBtn = document.getElementById("buyBtn");
    // Get the <span> element that closes the modal
    var closeModal = document.getElementById("closeModal");

    // When the user clicks the button, open the modal
    buyBtn.onclick = function() {
        modal.classList.add("active");
    }

    // When the user clicks on <span> (x), close the modal
    closeModal.onclick = function() {
        modal.classList.remove("active");
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.classList.remove("active");
        }
    }
</script>

</body>
</html>
