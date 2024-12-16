<?php 
session_start();
include('db_connection.php');

// Check if user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Logout logic
if (isset($_POST['logout'])) {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header('Location: login_admin.php');
    exit();
}

// Search logic
$search_term = isset($_GET['search_term']) ? mysqli_real_escape_string($conn, $_GET['search_term']) : '';

// SQL query with search conditions
$sql = "SELECT * FROM books WHERE title LIKE '%$search_term%' OR author LIKE '%$search_term%'";
$result = mysqli_query($conn, $sql);

// Check if query was successful
if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}

if (isset($_GET['delete'])) {
    $book_id = $_GET['delete'];

    // الاتصال بقاعدة البيانات
    include 'db_connection.php';

    // التحقق إذا كان الكتاب موجودًا
    $stmt = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // تنفيذ الحذف
        $stmt = $conn->prepare("DELETE FROM books WHERE book_id = ?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();

        // إعادة توجيه إلى نفس الصفحة بعد الحذف
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "الكتاب غير موجود.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
       

       
       body {
    font-family: 'Inter', 'Segoe UI', Roboto, Arial, sans-serif;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    background-attachment: fixed;
    color: #2c3e50;
    line-height: 1.6;
    margin: 0;
    padding: 40px 20px;
    transition: background 0.5s ease-in-out;
}

h1 {
    color: #34495e;
    text-align: center;
    font-weight: 600;
    letter-spacing: -0.5px;
    margin-bottom: 30px;
    animation: subtleSlideDown 1s ease-out;
}

@keyframes subtleSlideDown {
    0% {
        opacity: 0;
        transform: translateY(-20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    background-color: rgba(255, 255, 255, 0.9);
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    padding: 30px;
    transition: all 0.3s ease;
}

.container:hover {
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

table {
    width: 100%;
    border-spacing: 0;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

table thead {
    background-color: #3498db;
    color: white;
}

table th, table td {
    padding: 15px;
    text-align: center;
    border-bottom: 1px solid #e0e0e0;
    transition: background-color 0.2s ease;
}

table tr:nth-child(even) {
    background-color: #f8f9fa;
}

table tr:hover {
    background-color: #e9ecef;
}

/* Button Styles */
.button {
    display: inline-block;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    cursor: pointer;
    border: none;
}
.button-log {
    display: inline-block;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    cursor: pointer;
    border: none;
}
.button-log:hover {
    background-color: red;
    transform: translateY(-5px);
    box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.1);
}

.button:hover {
    transform: translateY(-5px);
    box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
}

.delete-button {
    background-color: #e74c3c;
    color: white;
}

.edit-price-button {
    background-color: #2980b9;
    color: white;
}

.add-book-button {
    background-color: #27ae60;
    color: white;
    margin-bottom: 30px;
}

.search-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 30px 0;
}

.search-bar input {
    flex-grow: 1;
    padding: 12px 15px;
    border: 2px solid #bdc3c7;
    border-radius: 6px;
    font-size: 16px;
    margin-right: 15px;
    transition: border-color 0.3s ease;
}

.search-bar input:focus {
    outline: none;
    border-color: #3498db;
}

.search-bar button {
    display: inline-block;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    cursor: pointer;
    border: none;
    background-color: #3498db;
}

.search-bar button:hover {
    background-color: #2980b9;
    transform: translateY(-5px);
    box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.1);


}

.admin-logo {
    width: 120px;
    height: 120px;
    display: block;
    margin: 0 auto 30px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* SVG and Home Link Styles */
.home-link {
    position: fixed;  /* Fixed positioning relative to the viewport */
    top: 20px;        /* Distance from the top of the page */
    right: 20px;      /* Distance from the right side of the page */
    z-index: 1000;    /* Ensure it stays on top of other elements */
}

.home-link svg {
    transition: all 0.3s ease;
    width: 50px;      /* Adjust size as needed */
    height: 50px;     /* Adjust size as needed */
    color: #3498db;   /* Optional: set a specific color */
}

.home-link svg:hover {
    transform: scale(1.2) rotate(5deg);
    color: #2980b9;   /* Slightly darker shade on hover */
}

</style>



    </style>
</head>
<body>


<svg class="svg-container" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="0 0 474.565 474.565" style="enable-background:new 0 0 474.565 474.565;" xml:space="preserve" width="50" height="50">
<g>
	<path d="M255.204,102.3c-0.606-11.321-12.176-9.395-23.465-9.395C240.078,95.126,247.967,98.216,255.204,102.3z"/>
	<path d="M134.524,73.928c-43.825,0-63.997,55.471-28.963,83.37c11.943-31.89,35.718-54.788,66.886-63.826
		C163.921,81.685,150.146,73.928,134.524,73.928z"/>
	<path d="M43.987,148.617c1.786,5.731,4.1,11.229,6.849,16.438L36.44,179.459c-3.866,3.866-3.866,10.141,0,14.015l25.375,25.383
		c1.848,1.848,4.38,2.888,7.019,2.888c2.61,0,5.125-1.04,7.005-2.888l14.38-14.404c2.158,1.142,4.55,1.842,6.785,2.827
		c0-0.164-0.016-0.334-0.016-0.498c0-11.771,1.352-22.875,3.759-33.302c-17.362-11.174-28.947-30.57-28.947-52.715
		c0-34.592,28.139-62.739,62.723-62.739c23.418,0,43.637,13.037,54.43,32.084c11.523-1.429,22.347-1.429,35.376,1.033
		c-1.676-5.07-3.648-10.032-6.118-14.683l14.396-14.411c1.878-1.856,2.918-4.38,2.918-7.004c0-2.625-1.04-5.148-2.918-7.004
		l-25.361-25.367c-1.94-1.941-4.472-2.904-7.003-2.904c-2.532,0-5.063,0.963-6.989,2.904l-14.442,14.411
		c-5.217-2.764-10.699-5.078-16.444-6.825V9.9c0-5.466-4.411-9.9-9.893-9.9h-35.888c-5.451,0-9.909,4.434-9.909,9.9v20.359
		c-5.73,1.747-11.213,4.061-16.446,6.825L75.839,22.689c-1.942-1.941-4.473-2.904-7.005-2.904c-2.531,0-5.077,0.963-7.003,2.896
		L36.44,48.048c-1.848,1.864-2.888,4.379-2.888,7.012c0,2.632,1.04,5.148,2.888,7.004l14.396,14.403
		c-2.75,5.218-5.063,10.708-6.817,16.438H23.675c-5.482,0-9.909,4.441-9.909,9.915v35.889c0,5.458,4.427,9.908,9.909,9.908H43.987z"
		/>
	<path d="M354.871,340.654c15.872-8.705,26.773-25.367,26.773-44.703c0-28.217-22.967-51.168-51.184-51.168
		c-9.923,0-19.118,2.966-26.975,7.873c-4.705,18.728-12.113,36.642-21.803,52.202C309.152,310.022,334.357,322.531,354.871,340.654z
		"/>
	<path d="M460.782,276.588c0-5.909-4.799-10.693-10.685-10.693H428.14c-1.896-6.189-4.411-12.121-7.393-17.75l15.544-15.544
		c2.02-2.004,3.137-4.721,3.137-7.555c0-2.835-1.118-5.553-3.137-7.563l-27.363-27.371c-2.08-2.09-4.829-3.138-7.561-3.138
		c-2.734,0-5.467,1.048-7.547,3.138l-15.576,15.552c-5.623-2.982-11.539-5.481-17.751-7.369v-21.958
		c0-5.901-4.768-10.685-10.669-10.685H311.11c-2.594,0-4.877,1.04-6.739,2.578c3.26,11.895,5.046,24.793,5.046,38.552
		c0,8.735-0.682,17.604-1.956,26.423c7.205-2.656,14.876-4.324,22.999-4.324c36.99,0,67.086,30.089,67.086,67.07
		c0,23.637-12.345,44.353-30.872,56.303c13.48,14.784,24.195,32.324,31.168,51.976c1.148,0.396,2.344,0.684,3.54,0.684
		c2.733,0,5.467-1.04,7.563-3.13l27.379-27.371c2.004-2.004,3.106-4.721,3.106-7.555s-1.102-5.551-3.106-7.563l-15.576-15.552
		c2.982-5.621,5.497-11.555,7.393-17.75h21.957c2.826,0,5.575-1.118,7.563-3.138c2.004-1.996,3.138-4.72,3.138-7.555
		L460.782,276.588z"/>
	<path d="M376.038,413.906c-16.602-48.848-60.471-82.445-111.113-87.018c-16.958,17.958-37.954,29.351-61.731,29.351
		c-23.759,0-44.771-11.392-61.713-29.351c-50.672,4.573-94.543,38.17-111.145,87.026l-9.177,27.013
		c-2.625,7.773-1.368,16.338,3.416,23.007c4.783,6.671,12.486,10.631,20.685,10.631h315.853c8.215,0,15.918-3.96,20.702-10.631
		c4.767-6.669,6.041-15.234,3.4-23.007L376.038,413.906z"/>
	<path d="M120.842,206.782c0,60.589,36.883,125.603,82.352,125.603c45.487,0,82.368-65.014,82.368-125.603
		C285.563,81.188,120.842,80.939,120.842,206.782z"/>
</g>
</svg>


</div>
    <!-- Home Link -->
    <a href="index.php" class="home-link">
    <svg fill="#000000" width="50px" height="50px" viewBox="0 0 24 24" version="1.2" baseProfile="tiny" xmlns="http://www.w3.org/2000/svg"><path d="M12 3s-6.186 5.34-9.643 8.232c-.203.184-.357.452-.357.768 0 .553.447 1 1 1h2v7c0 .553.447 1 1 1h3c.553 0 1-.448 1-1v-4h4v4c0 .552.447 1 1 1h3c.553 0 1-.447 1-1v-7h2c.553 0 1-.447 1-1 0-.316-.154-.584-.383-.768-3.433-2.892-9.617-8.232-9.617-8.232z"/></svg>
    </a>

    <div class="container">
        <h1>Admin Dashboard</h1>

        <!-- Add Book Button -->
        <a href="add_book.php" class="button add-book-button">Add New Book</a>
        <a href="orders.php" class="button add-book-button">Manage Orders</a> <!-- New button -->
        <!-- Search Bar -->
        <div class="search-bar">
            <form method="get" action="admin_dashboard.php">
                <input type="text" name="search_term" placeholder="Search by Title or Author" value="<?= $search_term ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <!-- Logout Button -->
        <form method="POST" action="admin_dashboard.php">
            <button type="submit" name="logout" class="button-log">Logout</button>
        </form>

        <h2>Manage Books</h2>
        <table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Author</th>
            <th>Price</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (mysqli_num_rows($result) > 0) {
            while ($book = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . $book['id'] . "</td>";
                echo "<td>" . $book['title'] . "</td>";
                echo "<td>" . $book['author'] . "</td>";
                echo "<td>" . $book['price'] . "</td>";
                echo "<td>
                        <a href='edit_price.php?id=" . $book['id'] . "' class='button edit-price-button'>Edit Price</a>
                        <a href='#' class='button delete-button' onclick='confirmDelete(" . $book['id'] . ")'>Delete</a>
                        <a href='book_details.php?id=" . $book['id'] . "' class='button'>View Details</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No books available</td></tr>";
        }
        ?>
    </tbody>
</table>

<script>
    function confirmDelete(bookId) {
        if (confirm("Are you sure you want to delete this book?")) {
            window.location.href = "delete_book.php?id=" + bookId;  // Redirect to delete_book.php
        }
    }
</script>
    </div>

    <script>
        function confirmDelete(bookId) {
            if (confirm("Are you sure you want to delete this book?")) {
                window.location.href = "delete_book.php?id=" + bookId;
            }
        }
    </script>

</body>
</html>