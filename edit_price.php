<?php
session_start();
include('db_connection.php');

// Check if user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $book_id = mysqli_real_escape_string($conn, $_GET['id']);

    // Fetch the current book details
    $sql = "SELECT * FROM books WHERE id = $book_id";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $book = mysqli_fetch_assoc($result);
    } else {
        die("Book not found.");
    }
}

// Handle price update
if (isset($_POST['update_price'])) {
    $new_price = mysqli_real_escape_string($conn, $_POST['price']);

    $update_sql = "UPDATE books SET price = '$new_price' WHERE id = $book_id";
    if (mysqli_query($conn, $update_sql)) {
        header('Location: admin_dashboard.php');
        exit();
    } else {
        die("Error updating price: " . mysqli_error($conn));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book Price</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            color: #333;
        }
        label {
            font-weight: bold;
        }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }
        .button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Edit Price for "<?php echo htmlspecialchars($book['title']); ?>"</h1>
        
        <form action="edit_price.php?id=<?php echo $book['id']; ?>" method="POST">
            <label for="price">New Price:</label>
            <input type="number" id="price" name="price" value="<?php echo $book['price']; ?>" required>
            
            <button type="submit" name="update_price" class="button">Update Price</button>
        </form>
    </div>

</body>
</html>
