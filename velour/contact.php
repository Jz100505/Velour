<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Velour</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        .contacts {
            padding: 90px 80px 50px 50px;
        }
        h1 {
            font-weight: 300;
            color: #FFD700;
            font-size: 70px;
            margin: 0 0 25px 0;
        }

        .contact-info {
            display: flex;
            gap: 100px;
        }

        .info-item * {
            margin: 30px 0;
        }
        .info-item img {
            height: 45px;
        }

        .contact-info p {
            color: #ffffff;
            font-size: 20px;
            opacity: 75%;
        }

        hr {
            border: 1px solid #ffffff;
            opacity: 50%;
            border-radius: 100px;
            margin: 100px 0;
        }

        .icon {
            opacity: 50%;
            margin: 15px;
        }

        form {
            max-width: 100%;
            margin: 0;;
            display: flex;
            flex-direction: column;
            gap: 35px;
        }

        .form h1 {
            font-size: 50px;
        }

        .form p {
            color: #ffffff;
            font-style: italic;
            opacity: 50%;
            margin: 0 0 35px 0;
        }

        .input-group, .textarea-group {
            display: flex;
            align-items: center;
            background-color: black;
            border: 1px solid gray;
            padding: 12px;
            border-radius: 10px;
        }

        .input-group {
            height: 30px;
        }

        .input-group input {
            flex: 1;
            border: none;
            background: transparent;
            color: white;
            font-size: 16px;
            outline: none;
        }

        .textarea-group textarea {
            width: 100%;
            height: 130px;
            border: none;
            background: transparent;
            color: white;
            font-size: 16px;
            outline: none;
            resize: none;
        }

        button {
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
        button:hover {
            background-color: #FFD700;
            color: black;
            border: 0;
        }
    </style>

<body>
<?php @include 'header.php'; ?>

<section class="contacts">
    <section class="contact">
        <h1>Contact Us</h1>
        <div class="contact-info">
            <div class="info-item">
                <img src="images/phone_icon.png" alt="Phone Icon">
                <p>+63 000 000 0000</p>               
            </div>
            <div class="info-item">
                <img src="images/email_icon.png" alt="Email Icon">
                <p>placeholder@gmail.com</p>
            </div>
        </div>
    </section>

    <hr> 

    <section class="form">
        <h1>Form</h1>
        <p>Fill the form below and we'll get back to you soon!</p>
        <form action="#" method="POST">
            <div class="input-group">
                <img src="images/person_icon.png" alt="User Icon" class="icon">
                <input type="text" placeholder="Full Name" required>
            </div>
            <div class="input-group">
                <img src="images/email_icon_1.png" alt="Email Icon" class="icon">
                <input type="email" placeholder="Email Address" required>
            </div>
            <div class="textarea-group">
                <textarea placeholder="Please write your message here..." required></textarea>
            </div>
            <button type="submit">Submit</button>
        </form>
    </section>
</section>
</body>

<?php @include 'footer.php'; ?>
</body>
</html>