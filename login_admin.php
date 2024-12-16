<?php
session_start();
include('db_connection.php');

// Check if user has submitted the form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role']; // Get the role (admin or user)

    // Sanitize inputs to prevent SQL injection
    $username = mysqli_real_escape_string($conn, $username);
    $password = mysqli_real_escape_string($conn, $password);

    // Check if user exists in the database
    $sql = "SELECT * FROM users WHERE username = ? AND role = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Save user data in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirect based on the user role
        if ($_SESSION['role'] == 'admin') {
            // Redirect the admin to the admin dashboard
            header('Location: admin_dashboard.php');
            exit();
        } else {
            // Redirect regular users to the main page (index.php or any other page)
            header('Location: login.php');
            exit();
        }
    } else {
        echo "<p class='error-message'>Invalid username or password!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
     /* Global styles */
body {
    font-family: 'Roboto', sans-serif; /* Use modern fonts */
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background: linear-gradient(135deg, #eee, #3b4cf4); /* Subtle gradient for background */
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    animation: backgroundMove 10s ease infinite;
    cursor: url('custom-cursor.png'), auto; /* Custom cursor */
}

@keyframes backgroundMove {
    0% { background-position: left top; }
    50% { background-position: right bottom; }
    100% { background-position: left top; }
}

/* Login container */
.login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 30px;
    max-width: 500px;
    width: 100%;
    background-color: rgba(213, 248, 244, 0.8); /* Semi-transparent background */
    backdrop-filter: blur(10px); /* Frosted glass effect */
    border-radius: 25px;
    box-shadow: 0 15px 50px rgba(4, 120, 237, 0.1);
    padding: 19px;
    overflow: hidden;
    transition: transform 0.5s ease;
    border: 2px solid #eee;
}

/* Admin & User Login Box Styling */


.user-box {
    background: linear-gradient(135deg, #67c8ff, #43a6d1);
}

/* Hover effect for login container */
.login-container:hover {
    transform: translateY(-10px);
}

/* Login box */
.login-box {
    width: 100%;
    max-width: 450px;
    padding: 30px;
    text-align: center;
    border-radius: 20px;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease-in-out;
}

/* Heading styling */
.login-box h2 {
    font-size: 28px;
    color: #333;
    margin-bottom: 20px;
    font-weight: 600;
}

/* Input and label styles */
label {
    font-size: 16px;
    margin-bottom: 8px;
    color: #555;
    font-weight: 500;
    display: block;
}

input[type="text"], input[type="password"], select {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 15px;
    border-radius: 10px;
    border: 1px solid #ccc;
    font-size: 16px;
    background-color: #f8f8f8;
    box-sizing: border-box;
    transition: border 0.3s ease;
}

/* Floating label effect */
input[type="text"]:focus ~ label, input[type="password"]:focus ~ label {
    top: -10px;
    left: 10px;
    font-size: 14px;
    color: #4caf50;
}

/* Input hover/focus effect */
input[type="text"]:hover, input[type="password"]:hover {
    border-color: #4caf50;
}

input[type="text"]:focus, input[type="password"]:focus {
    border-color: #4caf50;
    outline: none;
    box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
}

/* Submit button */
button[type="submit"] {
    width: 100%;
    padding: 14px;
    background-color: #4caf50;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 18px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease-in-out;
}

/* Hover effect for submit button */
button[type="submit"]:hover {
    background-color: #45a049;
    transform: scale(1.05);
}

/* Loader (for progress indication) */
.loader {
    border: 5px solid #f3f3f3; /* Light grey background */
    border-top: 5px solid #3498db; /* Blue color */
    border-radius: 50%;
    width: 30px;
    height: 30px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Error message */
.error-message {
    bottom: 0%;
    color: #f44336;
    font-size: 24px;
    margin-top: 20px;
}

/* Create Account Button */
.login-as-button {
    display: inline-block;
    padding: 14px 20px;
    background-color:rgb(5, 245, 17); 
    color: #c1351d;
    text-align: center;
    font-size: 16px;
    font-weight: bold;
    border-radius: 10px;
    text-decoration: none;
    cursor: pointer;
    width: 100%;
    max-width: 200px; /* Limit the width */
    transition: transform 0.3s ease, background-color 0.3s ease, box-shadow 0.3s ease;
    margin-top: 20px;
    box-shadow: 0 4px 15px rgba(2, 172, 234, 0.77); /* Add shadow for depth */
    border: none;
}

/* Hover effect for Create Account Button */
.login-as-button:hover {  
    background-color:rgb(5, 243, 195); /* Darker blue on hover */
    transform: scale(1.05); /* Slight zoom-in effect */
    box-shadow: 0 6px 20px rgba(2, 173, 246, 0.95); /* Increase shadow on hover */

}

/* Focus effect (for accessibility) */
.create-account-button:focus {
    outline: none;
    box-shadow: 0 0 5px rgba(30, 144, 255, 0.5); /* Blue outline when focused */
}

/* Active effect (when clicked) */
.create-account-button:active {
    transform: scale(0.98); /* Slight shrink when button is clicked */
}

/* Optional animation for the button appearance */
@keyframes fadeInUp {
    0% { opacity: 0; transform: translateY(30px); }
    100% { opacity: 1; transform: translateY(0); }
}

.create-account-button {
    animation: fadeInUp 1s ease-out;
}


/* Eye icon (SVG icon) */
.eye-icon {
    position: absolute;
    right: 10px;
    top: 45%;
    transform: translateY(-50%);
    cursor: pointer;
    width: 22px;
    height: 22px;
    z-index: 10;
    transition: transform 0.3s ease-in-out;
}

/* Eye icon hover */
.eye-icon:hover {
    transform: translateY(-50%) scale(1.2);
}

/* Social Media Login Buttons */
.social-buttons {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 30px;
}

.social-buttons a {
    padding: 12px 20px;
    background-color: #3b5998; /* Facebook */
    color: white;
    border-radius: 5px;
    text-decoration: none;
    font-size: 16px;
    text-align: center;
    width: 100%;
    transition: background-color 0.3s ease;
}

.social-buttons a:hover {
    background-color: #2d4373;
}

.social-buttons a.google {
    background-color: #db4437; /* Google */
}

.social-buttons a.google:hover {
    background-color: #c1351d;
}


    </style>
    <script>
        function togglePassword(role) {
            var passwordField = document.getElementById('password_' + role);
            var eyeIcon = document.getElementById('eyeIcon_' + role);
            if (passwordField.type === "password") {
                passwordField.type = "text"; // Show password
                eyeIcon.src = "https://img.icons8.com/ios-filled/50/000000/visible.png"; // Eye open icon
            } else {
                passwordField.type = "password"; // Hide password
                eyeIcon.src = "https://img.icons8.com/ios-filled/50/000000/invisible.png"; // Eye closed icon
            }
        }
    </script>
</head>
<body>

    <div class="login-container">
        <!-- Admin Login -->
        <div class="login-box admin-box">
            <h2>Admin Login</h2>
            <form method="POST" action="login.php">
                <input type="hidden" name="role" value="admin">
                <label for="username">Username (Admin):</label>
                <input type="text" id="username_admin" name="username" required><br>

                <label for="password">Password:</label>
                <div style="position: relative;">
                    <input type="password" id="password_admin" name="password" required><br>
                    <img id="eyeIcon_admin" src="https://img.icons8.com/ios-filled/50/000000/invisible.png" class="eye-icon" onclick="togglePassword('admin')" alt="eye icon">
                </div>

                <button type="submit">Login as Admin</button>
            </form>
        </div>

    </div>
    <div>
   <button class="login-as-button"> <a href="Login.php" >Login as User</a></button>

    </div>
</body>
</html>
