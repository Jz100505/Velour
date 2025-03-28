<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('database/connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $remove_id = filter_input(INPUT_POST, 'product_id_to_remove', FILTER_VALIDATE_INT);

    if ($remove_id !== false && isset($_SESSION['cart']) && array_key_exists($remove_id, $_SESSION['cart'])) {
        unset($_SESSION['cart'][$remove_id]);
        header('Location: cart.php?removed=1'); // Redirect to reload the page
        exit; // Stop script after redirect instruction
    } else {
        // Optional: Handle case where item wasn't found or ID was invalid
        error_log("Failed to remove item. ID: " . $remove_id . ", Cart Exists: " . isset($_SESSION['cart']));
        header('Location: cart.php?error=remove_failed'); // Redirect even on failure
        exit;
    }
}

// --- Checkout Logic ---
// (Keep the checkout logic from the previous correct version here - unchanged)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    // Ensure user is logged in AND cart exists and is not empty
    if (isset($_SESSION['username']) && isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $username = $_SESSION['username'];

        $order_list_details = [];
        $total_price = 0;
        $total_quantity = 0;

        foreach ($_SESSION['cart'] as $item) {
             if (isset($item['price'], $item['quantity'], $item['product_id'], $item['product_name'])) {
                $item_total = $item['price'] * $item['quantity'];
                $total_price += $item_total;
                $total_quantity += $item['quantity'];
                $order_list_details[] = [
                    'id' => $item['product_id'],
                    'name' => $item['product_name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity']
                ];
             } else {
                 error_log("Corrupted cart item found for user: " . $username);
                 echo "<script>alert('Cart item error. Please try removing and re-adding.'); window.location.href='cart.php';</script>";
                 exit;
             }
        }

        if (!empty($order_list_details)) {
            $order_list_json = json_encode($order_list_details);
            $delivery_address = filter_input(INPUT_POST, 'delivery_address', FILTER_SANITIZE_SPECIAL_CHARS);

             if (empty(trim($delivery_address))) {
                 echo "<script>alert('Delivery address cannot be empty.'); window.history.back();</script>";
                 exit;
             }

            $sql_insert_order = "INSERT INTO orders (username, orderList, quantity, totalPrice, deliveryAddress, status)
                                 VALUES (?, ?, ?, ?, ?, 'pending')";
            $stmt = $conn->prepare($sql_insert_order);

            if ($stmt === false) {
                 error_log("Prepare failed for order insert: (" . $conn->errno . ") " . $conn->error);
                 echo "<script>alert('Error preparing order. Please try again later.'); window.location.href='cart.php';</script>";
                 exit;
            } else {
                $stmt->bind_param("ssids", $username, $order_list_json, $total_quantity, $total_price, $delivery_address);

                if ($stmt->execute()) {
                    $_SESSION['cart'] = [];
                    $stmt->close();
                    $conn->close();
                    echo "<script>alert('Order placed successfully!'); window.location.href='orders.php';</script>";
                    exit;
                } else {
                     error_log("Execute failed for order insert: (" . $stmt->errno . ") " . $stmt->error);
                     $stmt_error_msg = $stmt->error;
                     $stmt->close();
                     $conn->close();
                     echo "<script>alert('Error placing order: " . addslashes($stmt_error_msg) . "'); window.location.href='cart.php';</script>";
                     exit;
                }
            }
         } else {
             echo "<script>alert('There was an issue with the items in your cart. Please remove them and try again.'); window.location.href='cart.php';</script>";
             exit;
         }
    } else {
         echo "<script>alert('Your cart is empty or you are not logged in.'); window.location.href='" . (isset($_SESSION['username']) ? 'cart.php' : 'login.php') . "';</script>";
         exit;
    }
}


// --- Include header AFTER processing ---
include('header.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Velour - Shopping Cart</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
             padding-top: 100px;
        }
        .cart-container {
            padding: 20px;
            margin: 20px auto;
            max-width: 900px;
        }
        .cart-container h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #FFD700;
            font-weight: normal;
        }
        .cart-item {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #555;
            padding: 15px 0;
            gap: 20px;
        }
        .cart-item img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.05);
        }
        .cart-item-details {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
         .cart-item-details h3 {
            margin: 0 0 5px 0;
            font-size: 18px;
            color: #fff;
             min-height: auto;
             text-align: left;
             font-weight: bold;
        }
         .cart-item-details p {
            margin: 2px 0;
            font-size: 14px;
            color: #ccc;
        }
        .item-actions {
             margin-left: auto;
             text-align: right;
             padding-left: 15px;
        }

        .cart-total {
            text-align: right;
            margin-top: 30px;
            font-size: 20px;
            font-weight: bold;
            color: #FFD700;
        }
        .checkout-form {
            margin-top: 30px;
            border-top: 1px solid #555;
            padding-top: 20px;
        }
        .checkout-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #ddd;
        }
         .checkout-form textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid gray;
            background-color: #333;
            color: white;
            min-height: 80px;
            resize: vertical;
             margin-bottom: 20px;
             box-sizing: border-box;
             font-family: 'Montserrat', sans-serif;
             font-size: 15px;
        }
        .checkout-btn, .remove-btn {
             color: black;
             padding: 10px 20px;
             border: none;
             border-radius: 5px;
             cursor: pointer;
             font-weight: bold;
             transition: background-color 0.2s ease;
             font-family: 'Montserrat', sans-serif;
        }
        .checkout-btn {
            background-color: #FFD700;
            display: block;
            width: 100%;
            max-width: 300px;
            margin: 20px auto 0 auto;
            font-size: 16px;
        }
         .checkout-btn:hover {
             background-color: #fff;
        }

        .remove-btn {
            background-color: #dc3545;
             color: white;
             padding: 5px 10px;
             font-size: 12px;
             margin-top: 10px;
        }
        .remove-btn:hover {
            background-color: #c82333;
        }
        .empty-cart {
            text-align: center;
            font-size: 18px;
            color: #aaa;
            margin-top: 50px;
        }
        .message {
            text-align: center;
            padding: 10px 15px;
            margin: 15px auto 25px auto;
            border-radius: 5px;
            max-width: 860px;
            border: 1px solid transparent;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
    </style>
</head>

<body>
    <div class="cart-container">
        <h1>Shopping Cart</h1>

         <?php
        if (isset($_GET['removed']) && $_GET['removed'] == 1) {
             echo '<div class="message success">Item removed from cart.</div>';
        }
         ?>

        <?php
        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            $grand_total = 0;
            foreach ($_SESSION['cart'] as $product_id => $item) {
                if (isset($item['price'], $item['quantity'], $item['product_name'], $item['image'])) {
                    $item_total = $item['price'] * $item['quantity'];
                    $grand_total += $item_total;
                ?>
                <div class="cart-item">
                    <img src="images/<?php echo htmlspecialchars($item['image']); ?>"
                         alt="<?php echo htmlspecialchars($item['product_name']); ?>">

                    <div class="cart-item-details">
                        <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                        <p>Price: ₱<?php echo number_format($item['price'], 2); ?></p>
                        <p>Quantity: <?php echo htmlspecialchars($item['quantity']); ?></p>
                        <p>Total: ₱<?php echo number_format($item_total, 2); ?></p>
                    </div>
                     <div class="item-actions">
                         <form method="post" action="cart.php" style="display: inline;">
                             <input type="hidden" name="product_id_to_remove" value="<?php echo $product_id; ?>">
                             <button type="submit" name="remove_item" class="remove-btn">Remove</button>
                         </form>
                     </div>
                </div>
                <?php
                }
            }
            ?>
            <div class="cart-total">
                <strong>Grand Total: ₱<?php echo number_format($grand_total, 2); ?></strong>
            </div>

            <div class="checkout-form">
                <form method="post" action="cart.php">
                    <label for="delivery_address">Delivery Address:</label>
                    <textarea id="delivery_address" name="delivery_address" rows="4" required></textarea>
                    <input type="submit" name="checkout" class="checkout-btn" value="Proceed to Checkout">
                </form>
            </div>
            <?php
        } else {
            echo "<p class='empty-cart'>Your cart is empty.</p>";
        }
        ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>