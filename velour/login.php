<?php

session_start();
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Velour</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    .create {
        padding: 30px 80px 50px 50px;
    }
    .logo 
    {
        position: absolute;
        right: 45%;
        top: 15%;
        transform: translateY(-50%);
        height: 50px;
    }
    .container {
        width: 100%;
        text-align: center;
        padding-top: 100px;
    }
    .container h1 {
        font-weight: normal;
        font-size: 70px;
        color: #FFD700;
        margin: 10px;
    }
    .container p {
        font-weight: normal;
        font-size; 19px;
    }

    
    .form-container {
        width: 90%;
        max-width: 400px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    
    form {
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 15px;
    }

    .btn2 {
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 15px;
        text-decoration: none;
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
    }

    .input-group input {
        flex: 1;
        border: none;
        background: transparent;
        color: white;
        font-size: 16px;
        outline: none;
    }

    
    .btn2 {
        background-color: rgba(0, 0, 0, 0);
        color: #ffffff;
        border: 3px solid white;
        border-radius: 10px;
        height: 50px;
        width: 200px;
        size: 20px;
        font-weight: normal;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease-in-out;
        font-weight: bold;
    
    }

    .btn2:hover {
        background-color: #FFD700;
        color: black;
        border: 0;
    }

    .btn {
        background-color: #FFD700;
        border: none;
        padding: 12px;
        width: 200px;
        text-align: center;
        color: black;
        border: 3px #FFD700;
        border-radius: 10px;
        height: 45px;
        size: 20px;
        font-weight: normal;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease-in-out;
        font-weight: bold;
        margin-top: 18px;
    
    }

    .btn:hover {
        background-color: #FFFFFF;
        color: black;
        border: 0;
    }

    /* Center Logo */
    .logo {
        position: absolute;
        left: 50%;
        top: 10%;
        transform: translate(-50%, -50%);
        height: 50px;
    }
</style>
    </style>
</head>

<body>

<section class="create">
    
        <div class="logo"> 
            <img src="images/velour_logo_full.png" alt="Velour Logo" class="logo">
        </div>
    <div class="container">
        <h1>Log-in</h1>
        <p>Join Velour. Discover exclusive fragrances.</p>
    </div>

    <form action="authenticate.php" method="post" onsubmit="return validatePassword();">
        <div class="input-group">
            <i class="fa-solid fa-user"></i>
            <input type="text" name="username" placeholder="Username" required minlength="6">
        </div>

        <div class="input-group">
            <i class="fa-solid fa-lock"></i>
            <input type="password" name="password" id="password" placeholder="Password" required>
            <span class="toggle-password" onclick="togglePassword('password', 'eyeIcon2')">
                <i id="eyeIcon2" class="fa-solid fa-eye"></i>
            </span>
        </div>

        <?php
        if (isset($_SESSION['error_message'])) {
            echo '<div class="error-container"><span class="error-message">' . $_SESSION['error_message'] . '</span></div>';
            unset($_SESSION['error_message']);
        }
        ?>
        <button name="login" class="btn">Login</button>
        <p>New here? Create your Velour account.</p>
        <button type="button" class="btn2" onclick="window.location.href='register.php';">Create account</button>
    </form>


    <script>
    function togglePassword(inputId, eyeIconId) {
        let inputField = document.getElementById(inputId);
        let eyeIcon = document.getElementById(eyeIconId);

        if (inputField.type === "password") {
            inputField.type = "text";
            eyeIcon.classList.replace("fa-eye", "fa-eye-slash");
        } else {
            inputField.type = "password";
            eyeIcon.classList.replace("fa-eye-slash", "fa-eye");
        }
    }
</script>

<style>
    .input-group {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-group input {
        width: 100%;
        padding-right: 40px; /* Space for the eye icon */
    }

    .toggle-password {
        position: absolute;
        right: 10px;
        cursor: pointer;
    }
</style>



</section>
<?php @include 'footer.php'; ?>
</body>
</html>