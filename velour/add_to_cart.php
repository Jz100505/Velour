<?php
session_start();
include('database/connection.php'); // Ensure connection details are correct

// Check if product_id is sent via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {

    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

    // Validate product ID
    if ($product_id === false || $product_id === null) {
        // Handle invalid ID - maybe redirect with an error
        header('Location: shop.php?error=invalid_id');
        exit;
    }

    // Fetch product details from the database using prepared statement
    $stmt = $conn->prepare("SELECT productName, price, productImage FROM product WHERE productID = ?");
    if ($stmt === false) {
        // Handle prepare error
        error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        header('Location: shop.php?error=db_prepare');
        exit;
    }

    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();

        // Initialize cart if it doesn't exist
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Check if product is already in cart
        if (isset($_SESSION['cart'][$product_id])) {
            // Increment quantity
            $_SESSION['cart'][$product_id]['quantity']++;
        } else {
            // Add new product to cart
            $_SESSION['cart'][$product_id] = [
                'product_id'   => $product_id,
                'product_name' => $product['productName'],
                'price'        => $product['price'],
                'image'        => $product['productImage'], // Store image filename
                'quantity'     => 1
            ];
        }

        // Redirect back to shop page (or cart page) with success message
        header('Location: shop.php?added=' . urlencode($product['productName']));
        exit;

    } else {
        // Product not found in database
        header('Location: shop.php?error=not_found');
        exit;
    }

    $stmt->close();
    $conn->close();

} else {
    // Redirect if accessed directly or without product_id
    header('Location: shop.php');
    exit;
}
?>