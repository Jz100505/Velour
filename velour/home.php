<?php
session_start();
include('database/connection.php');

// Check if the user is logged in and has the role of 'client'
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'client') {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Velour</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        /* Hero Section */
        .hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 60px 70px 0 70px;
        }
        .content h1 {
            font-weight: normal;
            font-size: 40px
        }
        .content p {
            font-size: 19px;
        }
        .content .highlight {
            font-size: 50px;
            color: #FFD700;
        }
        .hero .image-container img {
            padding-top: 20px;
            width: 600px;
            transform: rotate(23deg); 
            transition: transform 0.3s ease;
        }
        .hero .image-container img:hover {
            transform: rotate(0deg) scale(1.05);
        }

        /* Intensity Section */
        .intensity {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        padding: 0 70px 60px 70px;
        gap: 50px; 
        }
        .intensity p {
            font-size: 25px;
            text-align: justify;
            margin-top: 5px;
            margin-bottom: 18px;
        }
        .intensity .image-container {
            margin-left: -50px; 
        }
        .intensity .image-container img {
            position: relative;
            left: -50px; 
        }
        .intensity .highlight {
            font-weight: normal;
            font-style: italic;
            font-size: 100px;
        }
        .highlightt {
            font-size: 30px;
            color: #FFD700;
            font-style: italic;
            font-weight: bold;
        }
        .intensity h2 {
            font-size: 40px;
            color: #FFD700;
            font-style: italic;
            font-weight: normal;
            margin-bottom: 5px;
        }
        .intensity .image-container img {
            width: 600px;
            transform: rotate(-20deg);
            transition: transform 0.3s ease;
            text-align: left;
        }
        .intensity .image-container img:hover {
            transform: rotate(0deg) scale(1.05);
        }

        /* Univ */
        .hero .content, .intensity .content {
            max-width: 50%;
        }
        .hero .image-container, .intensity .image-container {
            flex: 1;
            display: flex;
            justify-content: flex-end;
        }
        

        /* Button */
        .shop-btn {
            background-color: rgba(0, 0, 0, 0);
            color: #ffffff;
            border: 3px solid white;
            border-radius: 10px;
            height: 50px;
            width: 125px;
            size: 20px;
            font-weight: normal;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            font-weight: bold;
        }

        .shop-btn:hover {
            background-color: #FFD700;
            color: black;
            border: 0;
        }

        /* Responsive Design */
        @media screen and (max-width: 1024px) {
            .hero, .intensity {
                flex-direction: column;
                text-align: center;
            }

            .hero .content, .intensity .content {
                max-width: 100%;
                text-align: center;
            }

            .hero .image-container, .intensity .image-container {
                justify-content: center;
                margin-top: 20px;
            }
        }

    </style>
</head>


<body>
<?php @include 'header.php'; ?>

<!-- CONTENTS-->
<section class="hero">
    <div class="content">
        <h1>Captivating scents, timeless elegance. <span class="highlight">VELOUR – Luxury in Every Drop</span></h1>
        <p>Discover exquisite fragrances crafted for elegance and allure. Let your scent leave a lasting impression.</p>
        <a href="shop.php">
            <button class="shop-btn">Shop now.</button>
        </a>
    </div>
    <div class="image-container">
        <img src="images/home_1_million.jpg" alt="Perfume" class="tilted-image">
    </div>
</section>
<section class="intensity">
    <div class="image-container">
        <img src="images/home_le_male.jpg" alt="Perfume Bottle" class="tilted-image">
    </div>
    <div class="content">
        <h2><span class="highlights">Intensity Redefined</span></h2>
        <p>A bold and seductive blend of warm vanilla, rich lavender, and deep woody notes. Experience the essence of power and allure in every spray. Now available at <span class="highlightt">Velour</span>.</p>
        <a href="shop.php">
            <button class="shop-btn">Shop now.</button>
        </a>
    </div>
</section>

<?php @include 'footer.php'; ?>
</body>
</html>
