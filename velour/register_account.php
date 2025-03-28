<?php
// Start session to store potential error messages
session_start(); 
include('database/connection.php');

// --- Clear previous errors ---
unset($_SESSION['register_error_username']);
unset($_SESSION['register_error_email']);
// Optional: Store submitted values to repopulate form on error
unset($_SESSION['register_posted_data']); 

// --- Enable error reporting for debugging (remove for production) ---
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ---

$usernameError = $emailError = "";
$hasError = false; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {

    // Store posted data (except password) to repopulate form on error
    $_SESSION['register_posted_data'] = $_POST;
    unset($_SESSION['register_posted_data']['password']);
    unset($_SESSION['register_posted_data']['confirm_password']);

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 'client'; // Default role for new registrations

    // Basic validation (length, password match - can also be done client-side)
    if (strlen($username) < 6) {
        // Handle length error if needed, though HTML5 'minlength' helps
    }
    if ($password !== $confirm_password) {
        // Handle password mismatch error if needed, though JS validation helps
    }
    if (strlen($password) < 8) {
         // Handle password length error if needed
    }

    // *** Check for duplicate Username using Prepared Statement ***
    $sql_check_user = "SELECT id FROM users WHERE username = ?";
    $stmt_check_user = $conn->prepare($sql_check_user);
    if ($stmt_check_user) {
        $stmt_check_user->bind_param("s", $username);
        $stmt_check_user->execute();
        $stmt_check_user->store_result();
        if ($stmt_check_user->num_rows > 0) {
            $usernameError = "Username already taken. Please choose another.";
            $_SESSION['register_error_username'] = $usernameError;
            $hasError = true;
        }
        $stmt_check_user->close();
    } else {
        // Log error: Failed to prepare username check
        error_log("Prepare failed (username check): " . $conn->error);
        // Optionally set a generic error message for the user
        $_SESSION['register_error'] = "An error occurred during validation. Please try again.";
        $hasError = true; 
    }

    // *** Check for duplicate Email using Prepared Statement ***
    if (!$hasError) { // Only check email if username was okay or check failed
        $sql_check_email = "SELECT id FROM users WHERE email = ?";
        $stmt_check_email = $conn->prepare($sql_check_email);
        if ($stmt_check_email) {
            $stmt_check_email->bind_param("s", $email);
            $stmt_check_email->execute();
            $stmt_check_email->store_result();
            if ($stmt_check_email->num_rows > 0) {
                $emailError = "Email address already registered. Please use another or login.";
                $_SESSION['register_error_email'] = $emailError;
                $hasError = true;
            }
            $stmt_check_email->close();
        } else {
             // Log error: Failed to prepare email check
             error_log("Prepare failed (email check): " . $conn->error);
             $_SESSION['register_error'] = "An error occurred during validation. Please try again.";
             $hasError = true;
        }
    }


    // *** If errors found, redirect back to register form ***
    if ($hasError) {
        $conn->close(); // Close connection before redirect
        header("Location: register.php");
        exit; // Stop script execution
    }

    // *** If NO errors, proceed with INSERT using Prepared Statement ***
    else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Include the 'role' field in the insert
        $sql_insert = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);

        if ($stmt_insert) {
            $stmt_insert->bind_param("ssss", $username, $email, $hashed_password, $role);

            if ($stmt_insert->execute()) {
                // SUCCESS! Clear potentially stored form data and redirect to login
                unset($_SESSION['register_posted_data']); 
                $conn->close();
                header("Location: login.php?registration=success"); // Add success param optionally
                exit;
            } else {
                // Log error: Insert failed
                error_log("Execute failed (insert user): " . $stmt_insert->error);
                $_SESSION['register_error'] = "Registration failed due to a server error. Please try again later.";
                 $conn->close();
                 header("Location: register.php"); // Redirect back even on insert error
                 exit;
            }
            $stmt_insert->close();
        } else {
            // Log error: Prepare insert failed
            error_log("Prepare failed (insert user): " . $conn->error);
            $_SESSION['register_error'] = "Registration failed due to a server error. Please try again later.";
             $conn->close();
             header("Location: register.php"); // Redirect back
             exit;
        }
    }
} else {
    // If accessed directly without POST, redirect away
    header("Location: register.php");
    exit;
}
?>