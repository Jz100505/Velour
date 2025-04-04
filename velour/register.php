<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('database/connection.php'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {

    unset($_SESSION['register_error_username']);
    unset($_SESSION['register_error_email']);
    unset($_SESSION['register_error']); 
    unset($_SESSION['register_posted_data']); 
    
    $posted_data_for_repopulation = $_POST;
    unset($posted_data_for_repopulation['password']);
    unset($posted_data_for_repopulation['confirm_password']);
    $_SESSION['register_posted_data'] = $posted_data_for_repopulation;


    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 'client'; 

    $usernameError = ""; 
    $emailError = "";
    $general_error = ""; 
    $hasError = false; 

    if (strlen($username) < 6) {
        $usernameError = "Username must be at least 6 characters.";
        $_SESSION['register_error_username'] = $usernameError;
        $hasError = true;
    }
    if ($password !== $confirm_password) {
        
        
        
        
    }
    if (strlen($password) < 8) {
         $general_error = "Password must be at least 8 characters.";
         $_SESSION['register_error'] = $general_error;
         $hasError = true;
    }

    if (!$hasError) {
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
            error_log("Prepare failed (username check): " . $conn->error);
            $_SESSION['register_error'] = "An error occurred during validation. Please try again.";
            $hasError = true; 
        }
    }

    if (!$hasError) { 
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
             error_log("Prepare failed (email check): " . $conn->error);
             $_SESSION['register_error'] = "An error occurred during validation. Please try again.";
             $hasError = true;
        }
    }


    if ($hasError) {
        
        $conn->close(); 
        header("Location: register.php"); 
        exit; 
    }

    else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        
        $sql_insert = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);

        if ($stmt_insert) {
            $stmt_insert->bind_param("ssss", $username, $email, $hashed_password, $role);

            if ($stmt_insert->execute()) {
                
                unset($_SESSION['register_posted_data']); 
                $conn->close();
                header("Location: login.php?registration=success"); 
                exit;
            } else {
                
                error_log("Execute failed (insert user): " . $stmt_insert->error);
                $_SESSION['register_error'] = "Registration failed due to a server error. Please try again later.";
                 $conn->close();
                 header("Location: register.php"); 
                 exit;
            }
            
            
        } else {
            
            error_log("Prepare failed (insert user): " . $conn->error);
            $_SESSION['register_error'] = "Registration failed due to a server error. Please try again later.";
             $conn->close();
             header("Location: register.php"); 
             exit;
        }
    }
} 



$username_error = $_SESSION['register_error_username'] ?? null;
$email_error = $_SESSION['register_error_email'] ?? null;
$general_error = $_SESSION['register_error'] ?? null; 
unset($_SESSION['register_error_username']); 
unset($_SESSION['register_error_email']);
unset($_SESSION['register_error']);

$posted_data = $_SESSION['register_posted_data'] ?? []; 
unset($_SESSION['register_posted_data']); 

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Velour - Create Account</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    .create {
        padding: 30px 80px 50px 50px;
    }
    .logo {
        position: absolute;
        left: 50%;
        top: 10%;
        transform: translate(-50%, -50%);
        height: 50px;
    }
    .container {
        width: 100%;
        text-align: center;
        padding-top: 100px;
    }
    .container h1 {
        font-weight: normal; font-size: 70px; color: #FFD700; margin: 10px;
    }
    .container p {
        font-weight: normal; font-size: 19px;
    }

    form {
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0px;
    
    }
    .input-group { 
        display: flex;
        align-items: center;
        background-color: black;
        border: 3px solid gray;
        padding: 12px;
        border-radius: 10px;
        height: 30px;
        gap: 20px;
        width: 50%;
        position: relative;
    }

    .input-group input { flex: 1; border: none; background: transparent; color: white; font-size: 16px; outline: none; }
    button { background-color: #FFD700; border: none; padding: 12px; width: 200px; text-align: center; color: black; border: 3px solid #FFD700; border-radius: 10px; height: 45px; font-weight: bold; cursor: pointer; transition: all 0.3s ease-in-out; margin-top: 20px; } 
    button:hover { background-color: #FFFFFF; color: black; border: 0; }
    .option2 { text-decoration: none; color: #ffffff; }
    .option2:hover { color: #FFD700; }

    .error-message-field {
        color: red;
        font-size: 14px;
        margin-top: 1px;
        margin-bottom: 1px;
        width: 50%;
        text-align: left;
        padding-left: 15px;
        height: 17px;
        line-height: 17px;
    }
    #error-message-password {
         color: red; font-size: 14px; margin-top: 1px; margin-bottom: 2px; height: 17px; line-height: 17px;
    }
     .error-message-general {
         color: red; font-size: 15px; background-color: rgba(255,0,0,0.1); border: 1px solid red; padding: 10px; border-radius: 5px; margin-bottom: 8px; width: 50%; text-align: center;
     }

    .toggle-password { cursor: pointer; margin-left: -30px; z-index: 2; }
    .form-spacer { 
       height: 17px; margin-top: 1px; margin-bottom: 1px;
    }
    </style>
</head>

<body>
<section class="create">
    <div class="logo">
        <img src="images/velour_logo_full.png" alt="Velour Logo" class="logo">
    </div>
    <div class="container">
        <h1>Create Account</h1>
        <p>Join Velour. Discover exclusive fragrances.</p>
    </div>

    
    <form action="" method="post" onsubmit="return validatePassword();">

        <?php if ($general_error): ?>
            <div class="error-message-general"><?php echo htmlspecialchars($general_error); ?></div>
        <?php endif; ?>

        <div class="input-group">
            <i class="fa-solid fa-user"></i>
            <input type="text" name="username" placeholder="Create a username (At least 6 characters)" required minlength="6" value="<?php echo htmlspecialchars($posted_data['username'] ?? ''); ?>">
        </div>
         <?php if ($username_error): ?>
            <div class="error-message-field"><?php echo htmlspecialchars($username_error); ?></div>
         <?php else: ?>
             <div class="form-spacer"></div>
         <?php endif; ?>


        <div class="input-group">
            <i class="fa-solid fa-envelope"></i>
            <input type="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($posted_data['email'] ?? ''); ?>">
        </div>
         <?php if ($email_error): ?>
            <div class="error-message-field"><?php echo htmlspecialchars($email_error); ?></div>
         <?php else: ?>
              <div class="form-spacer"></div>
         <?php endif; ?>

        <div class="input-group">
            <i class="fa-solid fa-lock"></i>
            <input type="password" name="password" id="password" placeholder="Create a password (At least 8 characters)" required minlength="8">
            <span class="toggle-password" onclick="togglePassword('password', 'eyeIcon1')">
                <i id="eyeIcon1" class="fa-solid fa-eye"></i>
            </span>
        </div>
         <div class="form-spacer"></div>

        <div class="input-group">
            <i class="fa-solid fa-lock"></i>
            <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm Password" required>
            <span class="toggle-password" onclick="togglePassword('confirm-password', 'eyeIcon2')">
                <i id="eyeIcon2" class="fa-solid fa-eye"></i>
            </span>
        </div>
        <span id="error-message-password" class="error"></span>
         <div class="form-spacer" style="margin-bottom: 2px;"></div>


        <button name="register" type="submit">Register</button>

        <p>Already have an account? <b><a class="option2" href="login.php">login now</a></b></p>
    </form>

<script>
    function validatePassword() {
        let password = document.getElementById("password").value;
        let confirmPassword = document.getElementById("confirm-password").value;
        let errorMessage = document.getElementById("error-message-password");

        if (password !== confirmPassword) {
            errorMessage.textContent = "Oops! Your passwords don’t match. Try again.";
            return false;
        }
        errorMessage.textContent = "";
        return true;
    }

    function togglePassword(inputId, eyeIconId) {
        let inputField = document.getElementById(inputId);
        let eyeIcon = document.getElementById(eyeIconId);

        if (inputField.type === "password") {
            inputField.type = "text";
            eyeIcon.classList.remove("fa-eye");
            eyeIcon.classList.add("fa-eye-slash");
        } else {
            inputField.type = "password";
            eyeIcon.classList.remove("fa-eye-slash");
            eyeIcon.classList.add("fa-eye");
        }
    }
</script>

</section>

<?php 

@include 'footer.php'; 


if (isset($conn) && $conn instanceof mysqli) {
   $conn->close();
}
?>
</body>
</html>
