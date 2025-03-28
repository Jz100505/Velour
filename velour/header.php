<?php
// session_start();
include 'database/connection.php';

if (isset($_SESSION['id'])) {
    $username = $_SESSION['username'];
} else {
    $username = "Guest";
}
?>


<style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap');

    .dropdown {
    min-width: 10em;
    position: relative;
    margin: 2em;
    margin-left: 0;
    margin-right: 0;
    cursor: pointer;
}
.dropdown * {
    box-sizing: border-box;
}


.selected {
    font-family: 'Montserrat', sans-serif;
    font-weight: 400; 
    color: white; 
    font-size: 18px;
}

.selected {
    font-family: 'Montserrat', sans-serif;
    font-weight: 400;
}

.select {
    color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1em;
    transition: background 0.3s;
}

.select-clicked {
    border: 1px #ffffff solid;
    box-shadow: 0 0 0.8em #FFD700;
    border-radius: 10px;
}
.select:hover {
    background: #FFD700;
    color: black;
    border-radius: 10px;
}
.caret {
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 6px solid #fff;
    transition: 0.3s;
}
.caret-rotate {
    transform: rotate(180deg);
}
.menu {
    list-style: none;
    padding: 0.2em 0.5em;
    background: white;
    border: 1px white solid;
    box-shadow: 0 0.5em 1em rgba(0,0,0,0.2);
    border-radius: 0.5em;
    color: black;
    text-decoration: none;
    position: absolute;
    top: 3em;
    left: 50%;
    width: 100%;
    transform: translateX(-50%);
    display: none;
    opacity: 0;
    transition: opacity 0.2s ease-in-out;
    z-index: 1;
}

.menu-open {
    display: block;
    opacity: 1;
}

.menu li {
    padding: 0.7em 0.5em;
    margin: 0.3em 0;
    border-radius: 0.5em;
    cursor: pointer;
}

.menu li a {
    text-decoration: none; 
    color: black;
}
.menu li:hover {
    background: #FFD700; 
    color: black;
}
.active {
    background: black;
}

.account-box {
    padding: 10px;
    background: #f8f8f8;
    border-radius: 5px;
    text-align: left;
    font-size: 14px;
}

.account-box p {
    margin: 5px 0;
}

.account-box span {
    font-weight: bold;
}

</style>


<header>
    <!-- NAV -->
    <div class="logo">
        <a href="home.php"><img src="images/velour_logo_full.png" alt="Velour Logo"></a>
    </div>
    <nav>
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="shop.php">Shop</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact Us</a></li>
        </ul>
    </nav>
    <div class="icons">
        <a href="cart.php">
            <img src="images/cart.png" alt="Shopping Cart" class="cart-icon">
        </a>
        <a href="orders.php" class="login">Orders</a>
    
    <div class="dropdown">
        <div class="select">
            <span class="selected">Account</span>
            <div class="caret"></div>
        </div>
        <ul class="menu">
            <div class="account-info">
                <p><span><?php echo $_SESSION['username']; ?></span></p>
            </div>
            <li><a href="manage_account.php">Manage Account</a></li>
            <li><a href="logout.php">Log Out</a></li>
        </ul>
    </div>
</div>

</header>


    <script>
    const dropdowns = document.querySelectorAll('.dropdown');

dropdowns.forEach(dropdown => {
    const select = dropdown.querySelector('.select');
    const caret = dropdown.querySelector('.caret');
    const menu = dropdown.querySelector('.menu');
    const options = dropdown.querySelectorAll('.menu li');
    const selected = dropdown.querySelector('.selected');

    select.addEventListener('click', (event) => {
        event.stopPropagation();
        select.classList.toggle('select-clicked');
        caret.classList.toggle('caret-rotate');
        menu.classList.toggle('menu-open');
    });

    options.forEach((option) => {
        option.addEventListener('click', (event) => {
        
            if (option.querySelector('a')) {
                return; 
            }

           
            selected.innerText = option.innerText;
            select.classList.remove('select-clicked');
            caret.classList.remove('caret-rotate');
            menu.classList.remove('menu-open');

            options.forEach((opt) => opt.classList.remove('active'));
            option.classList.add('active');
        });
    });
});


document.addEventListener('click', (event) => {
    dropdowns.forEach(dropdown => {
        const select = dropdown.querySelector('.select');
        const menu = dropdown.querySelector('.menu');
        const caret = dropdown.querySelector('.caret');

        if (!dropdown.contains(event.target)) {
            select.classList.remove('select-clicked');
            caret.classList.remove('caret-rotate');
            menu.classList.remove('menu-open');
        }
    });
});

</script>



