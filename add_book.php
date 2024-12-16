<?php
// add_book.php

include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $author = $_POST["author"];
    $price = $_POST["price"];
    $description = $_POST["description"];
    $category = $_POST["category"];
    $stock = $_POST["stock"];

    // Image upload
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $image_name = basename($_FILES["image_path"]["name"]);
    $target_file = $target_dir . $image_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is an actual image or fake image
    $check = getimagesize($_FILES["image_path"]["tmp_name"]);
    if ($check !== false) {
        if (move_uploaded_file($_FILES["image_path"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO books (title, author, price, description, image_path, category, stock)
                    VALUES ('$title', '$author', '$price', '$description', '$image_name', '$category', '$stock')";

            if ($conn->query($sql) === TRUE) {
                echo "New record created successfully";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    } else {
        echo "File is not an image.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Book</title>
    <style>
      body {
    font-family: 'Inter', 'Segoe UI', Roboto, Arial, sans-serif;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
    padding: 20px;
    background-attachment: fixed;
}

.container {
    width: 100%;
    max-width: 500px;
    background-color: rgba(255, 255, 255, 0.9);
    border-radius: 12px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    padding: 40px;
    transition: all 0.3s ease;
}

.container:hover {
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.container h2 {
    color: #2c3e50;
    text-align: center;
    margin-bottom: 30px;
    font-weight: 600;
    letter-spacing: -0.5px;
}

.container form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.container form input,
.container form textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #bdc3c7;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.container form input:focus,
.container form textarea:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
}

.container form textarea {
    resize: vertical;
    min-height: 120px;
}

.container form input[type="file"] {
    border: 2px dashed #95a5a6;
    cursor: pointer;
}

.container form input[type="file"]:hover {
    border-color: #3498db;
}

.container form input[type="submit"] {
    background-color: #3498db;
    color: white;
    border: none;
    cursor: pointer;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.container form input[type="submit"]:hover {
    background-color: #2980b9;
    transform: translateY(-3px);
    box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
}

.container form input[type="submit"]:active {
    transform: translateY(1px);
}

@media (max-width: 600px) {
    .container {
        width: 95%;
        padding: 20px;
    }
}
    </style>
</head>
<body>

<div class="container">
    <h2>Add a New Book</h2>
    <form method="post" action="add_book.php" enctype="multipart/form-data">
        Title: <input type="text" name="title" required><br>
        Author: <input type="text" name="author" required><br>
        Price: <input type="text" name="price" required><br>
        Description: <textarea name="description" required></textarea><br>
        Image: <input type="file" name="image_path" accept="image/*" required><br>
        Category: <input type="text" name="category" required><br>
        Stock: <input type="text" name="stock" required><br>
        <input type="submit" value="Add Book">
    </form>
</div>

</body>
</html>
