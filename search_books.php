<?php
session_start();
include('db_connection.php');

// التحقق إذا تم إرسال طلب البحث
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['search']);  // تنظيف النص للبحث
}

// استعلام لجلب الكتب من قاعدة البيانات بناءً على البحث
$query = "SELECT * FROM books WHERE title LIKE '%$search_query%'";
$result = mysqli_query($conn, $query);

// التحقق من وجود كتب في قاعدة البيانات
$books = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $books[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Book Library</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #1E90FF;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .search-container {
            margin-top: 20px;
            text-align: center;
        }
        .search-container input {
            padding: 10px;
            width: 300px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .search-container button {
            padding: 10px 15px;
            background-color: #1E90FF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .search-container button:hover {
            background-color: #4682b4;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            padding: 20px;
        }
        .book-card {
            width: 250px;
            margin: 15px;
            background-color: white;
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
        .book-card .content h3 {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }
        .book-card .content p {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        .book-card .content .author {
            font-size: 14px;
            color: #1E90FF;
            font-weight: bold;
        }
        .book-card .content .btn {
            background-color: #1E90FF;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .book-card .content .btn:hover {
            background-color: #4682b4;
        }
    </style>
</head>
<body>

    <header>
        <h1>Search Results</h1>
    </header>

    <!-- نموذج البحث -->
    <div class="search-container">
        <form action="search_books.php" method="get">
            <input type="text" name="search" placeholder="Search for books..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="container">
        <?php
        // التحقق من وجود كتب لعرضها بعد البحث
        if (!empty($books)) {
            foreach ($books as $book) {
                // تحديد صورة الكتاب بناءً على العنوان
                switch ($book['title']) {
                    case 'Alone':
                        $cover_image = 'alone.jpg';
                        break;
                    case 'How is me ?':
                        $cover_image = 'book.jpg';
                        break;
                    case 'HTML/CSS':
                        $cover_image = 'html.jpg';
                        break;
                    case 'Me and Her':
                        $cover_image = 'meher.jpg';
                        break;
                    default:
                        $cover_image = 'default.jpg';  // Use a default image if the title doesn't match
                        break;
                }

                echo "
                <div class='book-card'>
                    <img src='" . $cover_image . "' alt='" . $book['title'] . "'>
                    <div class='content'>
                        <h3>" . $book['title'] . "</h3>
                        <p>" . substr($book['description'], 0, 100) . "...</p>
                        <p class='author'>by " . $book['author'] . "</p>
                        <a href='book_details.php?id=" . $book['id'] . "' class='btn'>View Details</a>
                    </div>
                </div>";
            }
        } else {
            echo "<p>No books found for your search.</p>";
        }
        ?>
    </div>

</body>
</html>
