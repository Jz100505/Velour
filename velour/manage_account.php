<?php
session_start();
include('database/connection.php');

$message = null;
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
$current_username = $_SESSION['username'];

$user_data = null;
$email = '';
$current_password_hash = '';
$username_display = $current_username;

$sql_fetch = "SELECT email, password FROM users WHERE username = ?";
$stmt_fetch = $conn->prepare($sql_fetch);
if ($stmt_fetch) {
    $stmt_fetch->bind_param("s", $current_username);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();
    if ($user_data_row = $result->fetch_assoc()) {
        $user_data = $user_data_row;
        $email = $user_data['email'];
        $current_password_hash = $user_data['password'];
        $username_display = $current_username;
    } else {
        session_destroy();
        header('Location: login.php?error=nouser');
        exit;
    }
    $stmt_fetch->close();
} else {
    error_log("Fetch Prepare failed: " . $conn->error);
    $message = ['type' => 'error', 'text' => 'Error fetching user data.'];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['update']) && $user_data) {
        $new_username = trim($_POST['username']);
        $new_email = trim($_POST['email']);
        $submitted_current_password = $_POST['current_password'];
        $new_password_plain = $_POST['password'];

        $update_username = !empty($new_username) ? $new_username : $current_username;
        $update_email = !empty($new_email) ? $new_email : $email;

        $username_changed = ($update_username !== $current_username);
        $email_changed = ($update_email !== $email);
        $password_being_changed = !empty($new_password_plain);
        $any_change_attempted = $username_changed || $email_changed || $password_being_changed;

        $proceed_update = true; 
        $update_fields = [];
        $update_params = [];
        $update_types = "";
        $hashed_password_to_update = null;

        
        if (empty($submitted_current_password)) {
             $message = ['type' => 'error', 'text' => 'Please enter your current password to make any changes.'];
             $proceed_update = false;
        } elseif (!password_verify($submitted_current_password, $current_password_hash)) {
             $message = ['type' => 'error', 'text' => 'Incorrect current password.'];
             $proceed_update = false;
        }

        
        if ($proceed_update && !$any_change_attempted) {
             $message = ['type' => 'info', 'text' => 'No changes were submitted.'];
             $proceed_update = false; 
        }

        
        if ($proceed_update && $password_being_changed) {
            $hashed_password_to_update = password_hash($new_password_plain, PASSWORD_DEFAULT);
            if ($hashed_password_to_update === false) {
                error_log("Password Hashing failed.");
                $message = ['type' => 'error', 'text' => 'Error processing new password. Update failed.'];
                $proceed_update = false;
            }
        }

        
        if ($proceed_update && $username_changed) {
            $sql_check = "SELECT username FROM users WHERE username = ? AND username != ?";
            $stmt_check = $conn->prepare($sql_check);
            if ($stmt_check) {
                $stmt_check->bind_param("ss", $update_username, $current_username);
                $stmt_check->execute();
                $stmt_check->store_result();
                if ($stmt_check->num_rows > 0) {
                    $message = ['type' => 'error', 'text' => 'Username already taken. Please choose another one.'];
                    $proceed_update = false;
                }
                $stmt_check->close();
            } else {
                 error_log("Username Check Prepare failed: " . $conn->error);
                 $message = ['type' => 'error', 'text' => 'Error checking username availability.'];
                 $proceed_update = false;
            }
        }

        
        if ($proceed_update) {
            $update_sql_parts = [];

            if ($username_changed) {
                $update_sql_parts[] = "username = ?";
                $update_params[] = $update_username;
                $update_types .= "s";
            }
            if ($email_changed) {
                 $update_sql_parts[] = "email = ?";
                 $update_params[] = $update_email;
                 $update_types .= "s";
            }
            if ($password_being_changed && $hashed_password_to_update !== null) {
                $update_sql_parts[] = "password = ?";
                $update_params[] = $hashed_password_to_update;
                $update_types .= "s";
            }

            
            if (!empty($update_sql_parts)) {
                $update_sql = "UPDATE users SET " . implode(", ", $update_sql_parts) . " WHERE username = ?";
                $update_params[] = $current_username; 
                $update_types .= "s";

                $stmt_update = $conn->prepare($update_sql);
                if ($stmt_update) {
                    $stmt_update->bind_param($update_types, ...$update_params);
                    if ($stmt_update->execute()) {
                        if ($stmt_update->affected_rows > 0) {
                            $_SESSION['message'] = ['type' => 'success', 'text' => 'Account updated successfully!'];
                            if ($username_changed) {
                                $_SESSION['username'] = $update_username; 
                            }
                            header('Location: manage_account.php'); 
                            exit;
                        } else {
                            
                            $_SESSION['message'] = ['type' => 'info', 'text' => 'No effective changes made or update failed execution.'];
                        }
                    } else {
                        error_log("Update Execute failed: " . $stmt_update->error);
                        $_SESSION['message'] = ['type' => 'error', 'text' => 'Error applying account updates.'];
                    }
                    $stmt_update->close();
                } else {
                    error_log("Update Prepare failed: " . $conn->error);
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Error preparing account update query.'];
                }
                
                header('Location: manage_account.php'); 
                exit;
            } 
            
        }
        
    }

    elseif (isset($_POST['delete'])) {
        
        $submitted_current_password_for_delete = $_POST['current_password']; 
        
        if (empty($submitted_current_password_for_delete)) {
             $_SESSION['message'] = ['type' => 'error', 'text' => 'Please enter your current password to delete your account.'];
        } elseif (!password_verify($submitted_current_password_for_delete, $current_password_hash)) {
             $_SESSION['message'] = ['type' => 'error', 'text' => 'Incorrect current password. Account not deleted.'];
        } else {
            
            $sql_delete = "DELETE FROM users WHERE username = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            if ($stmt_delete) {
                $stmt_delete->bind_param("s", $current_username);
                if ($stmt_delete->execute()) {
                    if ($stmt_delete->affected_rows > 0) {
                        session_destroy();
                        header('Location: login.php?deleted=1');
                        exit;
                    } else {
                        $_SESSION['message'] = ['type' => 'error', 'text' => 'Could not delete account. User may have already been deleted.'];
                    }
                } else {
                    error_log("Delete Execute failed: " . $stmt_delete->error);
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Error deleting account.'];
                }
                $stmt_delete->close();
            } else {
                 error_log("Delete Prepare failed: " . $conn->error);
                 $_SESSION['message'] = ['type' => 'error', 'text' => 'Error preparing account deletion.'];
            }
        }
        
        header('Location: manage_account.php'); 
        exit;
    }
}


if (isset($conn) && $conn instanceof mysqli) {
   $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Velour - Manage Account</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            padding-top: 120px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .content-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }
        .account-container {
            background-color: rgba(10, 10, 10, 0.8);
            padding: 40px;
            border-radius: 10px;
            border: 1px solid #444;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
        }
        .account-container h2 {
            color: #FFD700;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 400;
        }
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        .form-group label {
            display: block;
            color: #ccc;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .input-wrapper {
             display: flex;
             align-items: center;
             background-color: #000;
             border: 1px solid #555;
             border-radius: 5px;
             padding: 0 10px;
        }
         .input-wrapper i {
             color: #888;
             margin-right: 10px;
         }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px 15px 12px 0;
            background: transparent;
            border: none;
            color: #fff;
            font-size: 16px;
            outline: none;
            font-family: 'Montserrat', sans-serif;
        }
         .input-wrapper:focus-within {
             border-color: #FFD700;
             box-shadow: 0 0 5px rgba(255, 215, 0, 0.5);
         }
        .form-group input::placeholder {
            color: #888;
        }
        .button-group {
             display: flex;
             justify-content: space-between;
             gap: 15px;
             margin-top: 30px;
             flex-wrap: wrap;
         }
        .btn, .btn-danger, .btn-secondary {
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            flex-grow: 1;
            min-width: 120px;
        }
        .btn {
            background-color: #FFD700;
            color: #000;
        }
        .btn:hover {
            background-color: #fff;
            color: #000;
        }
        .btn-danger {
            background-color: #dc3545;
            color: #fff;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .btn-secondary {
            background-color: #555;
            color: #fff;
            text-decoration: none;
            display: inline-block;
        }
        .btn-secondary:hover {
            background-color: #666;
            color: #fff;
        }
        .back-link-container {
             margin-top: 20px;
             text-align: center;
        }
        .message-container {
             padding: 15px;
             margin: 0 auto 20px auto;
             border: 1px solid transparent;
             border-radius: 4px;
             max-width: 480px;
             text-align: center;
             position: absolute;
             top: 80px;
             left: 0;
             right: 0;
             z-index: 1001;
        }
        .message-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
        .message-error { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
        .message-info { color: #0c5460; background-color: #d1ecf1; border-color: #bee5eb; }
    </style>
</head>
<body>
    <?php @include 'header.php'; ?>

     <?php if ($message): ?>
         <div class="message-container message-<?php echo htmlspecialchars($message['type']); ?>">
             <?php echo htmlspecialchars($message['text']); ?>
         </div>
     <?php endif; ?>

    <div class="content-wrapper">
        <div class="account-container">
            <h2>Manage Account</h2>
            
            <form action="manage_account.php" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                     <div class="input-wrapper">
                         <i class="fa-solid fa-user"></i>
                         <input type="text" name="username" id="username"
                                placeholder="Enter New Username (Current: <?php echo htmlspecialchars($username_display); ?>)"
                                value="">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                     <div class="input-wrapper">
                         <i class="fa-solid fa-envelope"></i>
                         <input type="email" name="email" id="email"
                                placeholder="Enter New Email (Current: <?php echo htmlspecialchars($email); ?>)"
                                value="">
                     </div>
                </div>

                 <div class="form-group">
                    <label for="current_password">Current Password</label>
                     <div class="input-wrapper">
                         <i class="fa-solid fa-key"></i>
                         <input type="password" name="current_password" id="current_password" placeholder="Required to make changes or delete" required>
                     </div>
                </div>

                <div class="form-group">
                    <label for="password">New Password</label>
                     <div class="input-wrapper">
                         <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" id="password" placeholder="Enter New Password (Optional)">
                     </div>
                </div>

                 <div class="button-group">
                     <button type="submit" name="update" class="btn">Update Account</button>
                     <button type="submit" name="delete" class="btn-danger" onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone. You MUST enter your current password.');">Delete Account</button>
                 </div>
            </form>
             <div class="back-link-container">
                 <a href="home.php" class="btn-secondary">Back to Home</a>
             </div>
        </div>
    </div>

    <?php @include 'footer.php'; ?>

    
    <script>
         
    </script>
</body>
</html>