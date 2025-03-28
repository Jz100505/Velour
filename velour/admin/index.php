<?php
session_start();
include('../database/connection.php');

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php"); 
    exit();
}

$active_page = 'dashboard'; 
if (isset($_GET['view_products'])) {
    $active_page = 'view_products';
} elseif (isset($_GET['update_product'])) {
    $active_page = 'view_products';
} elseif (isset($_GET['view_orders'])) {
    $active_page = 'view_orders';
} elseif (isset($_GET['users_list'])) {
    $active_page = 'users_list';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Velour - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap');

        body.admin-body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            background-color: black;
            color: white;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            box-sizing: border-box;
            overflow-x: hidden;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 50px;
            background-color: black;
            border-bottom: 1px solid #333; 
            position: sticky; 
            top: 0;
            left: 0; 
            right: 0; 
            z-index: 1000; 
            box-sizing: border-box;
        }

        .admin-header .logo img {
            height: 70px; 
        }

        .admin-nav-items {
            list-style: none;
            display: flex;
            gap: 35px; 
            margin: 0;
            padding: 0;
        }

        .admin-nav-items li a {
            text-decoration: none;
            color: white;
            font-weight: 400; 
            padding: 8px 0; 
            position: relative;
            font-size: 17px; 
            transition: color 0.3s ease; 
        }

        .admin-nav-items li a::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -3px; 
            width: 100%;
            height: 2px;
            background-color: #FFD700; 
            transform: scaleX(0); 
            transform-origin: center; 
            transition: transform 0.3s ease;
        }

        .admin-nav-items li a:hover,
        .admin-nav-items li a.active { 
            color: #FFD700; 
        }

        .admin-nav-items li a:hover::after,
        .admin-nav-items li a.active::after {
            transform: scaleX(1); 
        }

        .admin-nav-items li a i { 
             margin-left: 6px; 
             font-size: 0.9em; 
        }

        .admin-content-area {
            flex-grow: 1; 
            padding: 40px 50px; 
            max-width: 1300px; 
            margin: 30px auto; 
            width: 90%;
            box-sizing: border-box;
        }

        .admin-page-title {
            font-size: 2.8em; 
            color: #FFD700; 
            font-weight: 400; 
            margin-bottom: 35px; 
            border-bottom: 1px solid #555; 
            padding-bottom: 20px; 
            text-align: center; 
        }

        .admin-form {
            background-color: #111; 
            padding: 35px 40px; 
            border-radius: 8px; 
            border: 1px solid #444; 
            max-width: 750px; 
            margin: 20px auto;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); 
        }

        .admin-form .form-group {
            margin-bottom: 28px; 
        }

        .admin-form label {
            display: block;
            color: #ddd; 
            margin-bottom: 10px; 
            font-size: 15px;
            font-weight: 400;
        }

        .admin-form input[type="text"],
        .admin-form input[type="number"],
        .admin-form input[type="file"],
        .admin-form select,
        .admin-form textarea {
            width: 100%;
            padding: 14px 18px; 
            background: #000; 
            border: 1px solid #555; 
            border-radius: 5px; 
            color: #fff; 
            font-size: 16px;
            font-family: 'Montserrat', sans-serif;
            box-sizing: border-box;
            outline: none; 
            transition: border-color 0.3s ease, box-shadow 0.3s ease; 
        }
         .admin-form input[type="file"] {
             padding: 10px; 
             line-height: 1.5; 
         }
         .admin-form input[type="file"]::file-selector-button {
             background-color: #333;
             color: #FFD700;
             border: none;
             padding: 8px 12px;
             border-radius: 4px;
             cursor: pointer;
             margin-right: 10px;
             transition: background-color 0.2s ease;
         }
          .admin-form input[type="file"]::file-selector-button:hover {
             background-color: #555;
         }


        .admin-form input[type="text"]:focus,
        .admin-form input[type="number"]:focus,
        .admin-form input[type="file"]:focus,
        .admin-form select:focus,
        .admin-form textarea:focus {
            border-color: #FFD700; 
            box-shadow: 0 0 0 2px rgba(255, 215, 0, 0.3); 
        }

        .admin-form input::placeholder,
        .admin-form textarea::placeholder {
            color: #777; 
        }

        .admin-form button,
        .admin-form input[type="submit"] {
            background-color: rgba(0, 0, 0, 0); 
            color: #ffffff;
            border: 2px solid white;
            border-radius: 10px;
            height: 50px;
            padding: 0 30px; 
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            margin-top: 15px; 
            display: inline-block; 
        }

        .admin-form button:hover,
        .admin-form input[type="submit"]:hover {
            background-color: #FFD700; 
            color: black; 
            border-color: #FFD700; 
        }

        .action-links a, a.button-style {
             text-decoration: none;
             padding: 6px 12px;
             margin: 0 5px;
             border-radius: 5px;
             font-size: 14px;
             font-weight: 500;
             transition: background-color 0.2s ease, color 0.2s ease;
             border: 1px solid transparent; 
             display: inline-block;
        }
        a.update, a.button-style.update {
            color: #FFD700;
            border-color: #FFD700;
        }
        a.update:hover, a.button-style.update:hover {
            background-color: rgba(255, 215, 0, 0.1);
            color: #fff;
        }
         a.delete, a.button-style.delete {
            color: #ff6b6b; 
            border-color: #ff6b6b;
        }
         a.delete:hover, a.button-style.delete:hover {
            background-color: rgba(220, 53, 69, 0.1);
            color: #ff4d4d; 
        }
         .admin-content-area a {
             color: #FFD700;
             text-decoration: none;
         }
         .admin-content-area a:hover {
             text-decoration: underline;
             color: #fff;
         }


        .message-success, .message-error, .message-info {
            padding: 15px 20px;
            border-radius: 5px;
            margin: 20px 0; 
            text-align: center;
            font-size: 15px;
            border: 1px solid;
        }
        .message-success {
            color: #33cc66; 
            background-color: rgba(51, 204, 102, 0.1);
            border-color: rgba(51, 204, 102, 0.4);
        }
        .message-error {
            color: #ff6666; 
            background-color: rgba(255, 102, 102, 0.1);
            border-color: rgba(255, 102, 102, 0.4);
        }
         .message-info { 
            color: #5bc0de;
            background-color: rgba(91, 192, 222, 0.1);
            border-color: rgba(91, 192, 222, 0.4);
         }


        .admin-content-area table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px; 
            background-color: #111; 
            border: 1px solid #444;
            border-radius: 8px;
            overflow: hidden; 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .admin-content-area th,
        .admin-content-area td {
            border: 1px solid #444; 
            padding: 14px 18px; 
            text-align: left;
            color: #ccc; 
            vertical-align: middle; 
            font-size: 15px; 
        }
         .admin-content-area td {
             border-top: none; 
             border-bottom: 1px solid #444; 
         }
          .admin-content-area td:first-child { border-left: none; } 
          .admin-content-area td:last-child { border-right: none; } 
          .admin-content-area tr:last-child td { border-bottom: none; } 

        .admin-content-area th {
            background-color: #222; 
            color: #FFD700; 
            font-weight: bold;
            font-size: 16px; 
            border-bottom-width: 2px; 
            border-top: none; border-left: none; border-right: none; 
        }
        .admin-content-area td img.product-image-thumb {
            height: 60px;
            width: 60px;
            object-fit: contain;
            background-color: #000; 
            border-radius: 4px;
            border: 1px solid #555;
            vertical-align: middle;
            display: block; 
            margin: auto; 
        }

        .pagination {
            margin-top: 35px; 
            text-align: center;
        }
        .pagination a, .pagination strong {
            margin: 0 6px; 
            text-decoration: none;
            padding: 9px 14px; 
            border: 1px solid #555;
            color: #ccc;
            border-radius: 5px; 
            transition: background-color 0.3s, color 0.3s, border-color 0.3s;
            font-size: 15px;
        }
        .pagination strong { 
            background-color: #FFD700;
            color: black;
            border-color: #FFD700;
            font-weight: bold;
        }
        .pagination a:hover { 
            background-color: #333;
            color: #FFD700;
            border-color: #666;
        }

        .search-form {
            margin-bottom: 30px; 
            text-align: center; 
         }
        .search-form input[type="text"] {
            width: 350px; 
            padding: 12px 18px;
            background: #000;
            border: 1px solid #555;
            border-radius: 8px; 
            color: #fff;
            font-size: 15px;
            font-family: 'Montserrat', sans-serif;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            margin-right: 10px;
            vertical-align: middle;
        }
        .search-form input[type="text"]:focus {
             border-color: #FFD700;
             box-shadow: 0 0 0 2px rgba(255, 215, 0, 0.3);
        }
        .search-form input[type="submit"] { 
            background-color: rgba(0, 0, 0, 0);
            color: #ffffff;
            border: 2px solid white;
            border-radius: 8px; 
            height: 48px; 
            padding: 0 25px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            vertical-align: middle;
        }
        .search-form input[type="submit"]:hover {
             background-color: #FFD700;
             color: black;
             border-color: #FFD700;
        }
        .search-form a { 
            margin-left: 20px;
            color: #999; 
            text-decoration: none;
            font-size: 14px;
            vertical-align: middle;
        }
        .search-form a:hover {
             color: #FFD700;
             text-decoration: underline;
        }
        .order-list-items { padding: 0; margin: 0; list-style: none; }
        .order-list-items li { margin-bottom: 3px; }
        .order-list-items .more-items { font-size: 0.9em; color: #888; margin-top: 5px; }
        .status-pending { color: #ffc107; font-weight: bold; }
        .status-completed { color: #28a745; font-weight: bold; }
        .status-cancelled { color: #dc3545; font-weight: bold; }

    </style>
</head>
<body class="admin-body">

<header class="admin-header">
    <div class="logo">
        <a href="index.php?view_products=1"><img src="../images/velour_logo_full.png" alt="Velour Admin Logo"></a>
    </div>
    <nav>
        <ul class="admin-nav-items">
            <li><a href="index.php" class="<?php echo ($active_page == 'dashboard') ? 'active' : ''; ?>">Dashboard</a></li>
            <li><a href="index.php?view_products=1" class="<?php echo ($active_page == 'view_products') ? 'active' : ''; ?>">View Products</a></li>
            <li><a href="index.php?view_orders=1" class="<?php echo ($active_page == 'view_orders') ? 'active' : ''; ?>">All Orders</a></li>
            <li><a href="index.php?users_list=1" class="<?php echo ($active_page == 'users_list') ? 'active' : ''; ?>">Users List</a></li>
            <li><a href="../logout.php">Log-out <i class="fa-solid fa-right-from-bracket"></i></a></li>
        </ul>
    </nav>
</header>

<main class="admin-content-area">
    <?php
    if (isset($_GET['view_products'])) {
        include('view_products.php'); 
    } elseif (isset($_GET['update_product']) && isset($_GET['product_id'])) {
        include('update_product.php'); 
    } elseif (isset($_GET['view_orders'])) {
        include('view_orders.php'); 
    } elseif (isset($_GET['users_list'])) {
        include('users_list.php'); 
    } else {
        echo '<h1 class="admin-page-title">Admin Dashboard</h1>';
        echo '<p style="font-size: 1.2em; color: #ccc; text-align: center;">Welcome, ' . htmlspecialchars($_SESSION['username']) . '!</p>';
        echo '<p style="font-size: 1.1em; color: #bbb; text-align: center; margin-top: 15px;">Use the navigation menu above to manage products, orders, and users.</p>';
        echo '<p style="text-align: center; margin-top: 25px;"><a href="index.php?view_products=1" class="button-style update" style="border-width: 2px; padding: 10px 20px;">Manage Products</a></p>';
    }
    ?>
</main>
</body>
</html>