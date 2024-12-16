<?php
session_start();
include('db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If the user is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];  // Get the user_id from the session

// Fetch the user's favorite books
$query_get_favorites = "
    SELECT b.id, b.title, b.description, b.author, b.price, b.image_path
    FROM favorites f
    JOIN books b ON f.book_id = b.id
    WHERE f.user_id = '$user_id'
";
$result_favorites = mysqli_query($conn, $query_get_favorites);

// Check if the user has any favorite books
if (mysqli_num_rows($result_favorites) > 0) {
    $favorite_books = mysqli_fetch_all($result_favorites, MYSQLI_ASSOC);
} else {
    $favorite_books = [];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Favorites</title>
    <link rel="stylesheet" href="styles.css">
</head>
<style>
/* Apply background image to the entire page */
html, body {
    background-image: url('index.jpg');
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
    overflow-x: hidden; /* Disable horizontal scrolling */
}

/* Apply background image to the entire page */
body {
    font-family: Arial, sans-serif;
    background-image: url('images/book.jpg'); /* Add the path to your image */
    background-size: cover; /* Ensure the background covers the entire page */
    background-position: center center; /* Center the image */
    background-attachment: fixed; /* Keep the background fixed while scrolling */
    color: #333;
    
}
header {
    padding: 20px;
    text-align: center;
    background-color: blueviolet;
    background-color: rgba(20, 14, 100, 0.25); 
    color: white;
    border-bottom: 2px solid #fff;
    position: relative; /* Added for positioning the 'Back to Library' button */
}

header h1 {
    margin: 0;
    font-size: 36px;
}

header .btn-back {
    background-color: #FF6347;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 5px;
    margin-top: 10px;
}

header .btn-back:hover {
    background-color: #FF4500;
}

/* Positioning the 'Back to Library' Button to the top-right corner */
header .btn-back {
    position: absolute;
    top: 20px;
    left: 20px;
}

/* Details Button - Styling the 'Details' button in each book card */
.book-card .content .btn {
    display: inline-block;
    background-color: #1E90FF; /* Blue color for the details button */
    color: white;
    padding: 8px 15px;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
    margin-top: 10px;
    text-align: center;
    transition: background-color 0.3s ease, transform 0.3s ease;
}

/* Hover effect for the 'Details' button */
.book-card .content .btn:hover {
    background-color: #1C86EE; /* Slightly darker blue for hover effect */
    transform: scale(1.1);
}

/* Main content area */
.container {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-around;
    padding: 20px;
    z-index: 1; /* Ensure this content is on top of the background */
}

/* Styling for book cards */
.book-card {
    width: 250px;
    margin: 15px;
    background-color: rgba(255, 255, 255, 0.8); /* Slightly transparent white */
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: transform 0.3s ease;
}

.book-card img {
    width: 100%;
    height: 300px;
    object-fit: cover;
}

.book-card:hover {
    transform: scale(1.05);
}

.book-card .content {
    padding: 15px;
    text-align: center;
}

/* Book title styling */
.book-card .content h3 {
    font-size: 18px;
    color: #333;
    margin-bottom: 10px;
}

/* Book description styling */
.book-card .content p {
    font-size: 14px;
    color: #666;
    margin-bottom: 10px;
}

/* Author name styling */
.book-card .content .author {
    font-size: 14px;
    color: #1E90FF;
    font-weight: bold;
}

/* Price styling */
.book-card .content .price {
    font-size: 16px;
    font-weight: bold;
    color: #28a745;
    margin-top: 10px;
}

/* Footer styles */
footer {
    background-color: rgba(0, 0, 0, 0.7);
    color: white;
    text-align: center;
    padding: 20px;
    font-size: 20px;
    position: relative;
    bottom: 0;
    margin-top: auto;
    width: 100%;
}

footer p {
    margin: 0;
}


</style>
<body>
    <header>
        <h1>Your Favorite Books</h1>
        <a href="index.php" class="btn-back">Back to Library</a>
    </header>

    <div class="container">
        
       <?php if (!empty($favorite_books)) :?>
         <?php    foreach ($favorite_books as $book) : ?>

               <div class='book-card'>
                
                        <div class='content'>
                        <img src="uploads/<?php echo htmlspecialchars($book['image_path']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
           <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($book['description'], 0, 100)) . '...'; ?></p>
                    <p class="author">by <?php echo htmlspecialchars($book['author']); ?></p>
                    <p class="price">Price: $<?php echo number_format($book['price'], 2); ?></p>
                    <a href="book_details.php?id=<?php echo $book['id']; ?>" class="btn">Details</a>
                        </div>
                    </div>;
                    
                    <?php endforeach; ?>

        <?php else: ?> 
            echo "<p>You don't have any favorite books yet!</p>";
            <?php endif; ?>

    </div>

    <footer>
        <p>Book Library &copy; 2024</p>
    </footer>
</body>
</html>