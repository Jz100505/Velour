<?php

include('database/connection.php');

session_start();

if (isset($_POST['login'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Create an SQL query to select the user from the database
    $sql_username = "SELECT * FROM users WHERE username= '$username'";

    // Execute the query
    $result = $conn->query($sql_username);

    // Check if the query returned any results
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verify the provided password against the stored hashed password
        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $row['role'];

            // Redirect the user to the appropriate dashboard
            if ($row['role'] == 'admin') {
                header("Location: ./admin/index.php");
            } elseif ($row['role'] == 'client') {
                header("Location: home.php");
            }
        } else {
            // Store error message in session
            $_SESSION['error_message'] = "Incorrect password";
            header("Location: login.php");
        }
    } else {
        // Store error message in session
        $_SESSION['error_message'] = "Username not found";
        header("Location: login.php");
    }
} else {
    header("location: login.php");
}
?>
