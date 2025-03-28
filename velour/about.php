<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Velour</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        /* CSS */

/* ABOUT */
.about-section {
    text-align: center;
    padding: 50px 50px;
}

.about-container {
    align-items: center;
    margin: auto;
    padding: 100px 0 50px 0;
}

.about-content {
    text-align: center;
    width: 100%;
    display: flex;
    gap: 20px;
}

.about-content p {
    text-align: justify;
    font-size: 20px;
    font-weight: normal;
    line-height: 30px;
    padding-left: 450px;
    padding-right: 10px;
}

.about-content h2 {
    font-size: 70px;
    color: yellow;
    font-weight: normal;
    display: block;
}


/* VALUES */
.values-section {
    
}

.values-section h3 {
    font-size: 50px;
    color: #FFD700;
    margin-bottom: 40px;
    font-weight: normal;
}

.values-container {
    display: flex;
    justify-content: center;
    gap: 150px;
}

.value-box {
    text-align: center;
}

.value-box img {
    width: 100px;
    height: 100px;
}

.value-box p {
    margin-top: 10px;
    font-size: 16px;
    color: #FFD700;
}

/* MISSION */
.mission-vision-section {
    display: flex;
    justify-content: space-between;
    max-width: 1000px;
    margin: 50px auto 0;
    text-align: center;
    gap: 100px;
}

.mission, .vision {
    width: 45%;
}

.mission h3, .vision h3 {
    font-size: 50px;
    color: #FFD700;
    margin-bottom: 10px;
    font-weight: normal;
}

.mission p, .vision p {
    font-size: 19px;
    line-height: 1.6;
    text-align: justify;
}
    </style>
</head>

<body>
<?php @include 'header.php'; ?>

<!-- CONTENTS-->
<section class="about-section">
    <div class="about-container">
        <div class="about-content">
            <h2>About</h2><h2> Us</h2>
            <p>
                At Velour, we redefine luxury through the art of fragrance. Our curated collection features only the finest perfumes, crafted for those who appreciate sophistication, elegance, and timeless allure. Committed to authenticity and quality, we bring you scents from the world’s most renowned brands, ensuring an unparalleled experience with every drop. Step into a world of indulgence—where every fragrance tells a story, and every scent leaves a lasting impression.
            </p>
        </div>

</div>

    <div class="values-section">
        <h3>Values</h3>
        <div class="values-container">
            <div class="value-box">
                <img src="images/luxery_icon.png" alt="Luxury">
                <p>Luxury</p>
            </div>
            <div class="value-box">
                <img src="images/authenticity_icon.png" alt="Authenticity">
                <p>Authenticity</p>
            </div>
            <div class="value-box">
                <img src="images/elegance_icon.png" alt="Elegance">
                <p>Elegance</p>
            </div>
        </div>
    </div>

    <div class="mission-vision-section">
        <div class="mission">
            <h3>Mission</h3>
            <p>
                To be the premier destination for luxury fragrances, offering an unparalleled selection of authentic, high-quality scents that inspire confidence and elegance.
            </p>
        </div>
        <div class="vision">
            <h3>Vision</h3>
            <p>
                At Velour, we are committed to curating the finest perfumes from world-renowned brands, ensuring authenticity and excellence. Our goal is to provide a seamless and luxurious shopping experience, helping every customer find their signature scent that leaves a lasting impression.
            </p>
        </div>
    </div>
</section>

<?php @include 'footer.php'; ?>
</body>
</html>