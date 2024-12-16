<?php
session_start();
include('db_connection.php');

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password_confirmation = $_POST['password_confirmation'];

    // Check if passwords match
    if ($password !== $password_confirmation) {
        echo "<p class='error-message'>Passwords do not match!</p>";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user into the database
        $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $role = 'user'; // Default role is "user"
        $stmt->bind_param('sss', $username, $hashed_password, $role);

        if ($stmt->execute()) {
            // Store user session data after successful registration
            $_SESSION['user_id'] = $conn->insert_id; // Store the user ID in the session
            $_SESSION['username'] = $username;

            // Redirect to the homepage after successful registration
            header("Location: login.php");
            exit();  // Ensure the script stops after the redirect
        } else {
            echo "<p class='error-message'>An error occurred during account creation.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Account</title>
    <style>
        /* General page settings */
        body {
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background-image: url('bib.jpg'); /* Add this line for background image */
    background-size: cover; /* Ensure it covers the entire background */
    background-position: center; /* Center the image */
    background-repeat: no-repeat; /* Prevent the image from repeating */
}

        /* Main container */
        .container {
    background-color: rgba(255, 255, 255, 0.5); /* جعل الشفافية 50% */
    width: 100%;
    max-width: 450px;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    text-align: center;
    transform: scale(0.95);
    animation: scaleUp 0.6s ease-out forwards;
    opacity: 1; /* إبقاء الشفافية كاملة في العنصر */
}



        /* Heading styles */
        h2 {
            font-size: 28px;
            margin-bottom: 30px;
            color: #1e90ff;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Label and input styles */
        label {
            font-size: 16px;
            margin-bottom: 5px;
            display: block;
            text-align: left;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 16px;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #1e90ff;
            box-shadow: 0 0 10px rgba(30, 144, 255, 0.2);
        }

        /* Submit button styles */
        input[type="submit"] {
            width: 100%;
            padding: 15px;
            background-color: #1e90ff;
            color: white;
            font-size: 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #4682b4;
        }

        /* Error message styling */
        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 20px;
            animation: fadeIn 0.5s ease-out;
        }

        /* Hover effect for the "Create Account" button */
        .create-account-link {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 25px;
            background-color: #1e90ff;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            transition: background-color 0.3s ease;
        }

        .create-account-link:hover {
            background-color: #4682b4;
        }

        /* Keyframes for animations */
        @keyframes scaleUp {
            0% {
                opacity: 0;
                transform: scale(0.9);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }

    </style>
</head>
<body>
    <div class="container">
        <h2>Create New Account</h2>
        <form method="POST" action="register.php">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>

            <label for="password_confirmation">Confirm Password:</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required><br>

            <input type="submit" value="Create Account">
        </form>
        
        <!-- Link to register page, styled as a button -->
        <a href="login.php" class="create-account-link">Already have an account? Login here</a>
    </div>
</body>
</html>
