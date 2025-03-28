<?php
session_start();
$host = "localhost";
$user = "root";
$password = "";
$dbname = "velour";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");


if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
$username = $_SESSION['username'];

$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);

$order = null;
$orderItems = [];
$error_message = null;

if ($order_id === false || $order_id === null) {
    $error_message = "Invalid Order ID specified.";
} else {
    $sql = "SELECT orderID, orderList, totalPrice, orderDate, status, deliveryAddress
            FROM orders
            WHERE orderID = ? AND username = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Prepare failed for fetching order details (User: " . $username . ", OrderID: " . $order_id . "): (" . $conn->errno . ") " . $conn->error);
        $error_message = "An error occurred while fetching order details. Please try again later.";
    } else {
        $stmt->bind_param("is", $order_id, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $order = $result->fetch_assoc();
            $orderItems = json_decode($order['orderList'], true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($orderItems)) {
                 error_log("Failed to decode orderList JSON for orderID: " . $order['orderID'] . ". JSON Error: " . json_last_error_msg());
                 $error_message = "Could not load item details for this order.";
                 $orderItems = [];
            }
        } else {
            $error_message = "Order not found or access denied.";
        }
        $stmt->close();
    }
}

$displayStatus = "Status Unknown";
$statusClass = "status-unknown";
$orderDateTime = null;
if ($order && !$error_message) {
    $dbStatus = $order['status'];
    $orderDateStr = $order['orderDate'];
    try {
        $orderDateTime = new DateTime($orderDateStr);
        $currentDateTime = new DateTime();

        if ($dbStatus === 'completed' || $dbStatus === 'cancelled') {
            $displayStatus = ucfirst($dbStatus);
            $statusClass = 'status-' . $dbStatus;
        } elseif ($dbStatus === 'pending') {
            $deliveryEndDate = (clone $orderDateTime)->modify('+14 days');
            if ($currentDateTime > $deliveryEndDate) {
                $displayStatus = "Delivered";
                $statusClass = "status-delivered";
            } else {
                $estimateStartDate = (clone $orderDateTime)->modify('+7 days');
                $estimateEndDate = $deliveryEndDate;
                $format = "M j";
                $displayStatus = "Est. Delivery: " . $estimateStartDate->format($format) . " - " . $estimateEndDate->format($format);
                $statusClass = "status-pending";
            }
        }
    } catch (Exception $e) {
        error_log("Error processing date for orderID " . $order['orderID'] . ": " . $e->getMessage());
        $displayStatus = "Status Error";
        $statusClass = "status-error";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Velour - Order Details</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            padding-top: 100px;
            line-height: 1.6;
        }

        .details-container {
            padding: 20px;
            margin: 20px auto;
            max-width: 900px;
        }

        .details-container h1 {
            text-align: center;
            margin-bottom: 15px;
            color: #FFD700;
            font-weight: 400;
        }

        .details-container h1 .order-id-title {
            font-size: 0.6em;
            color: #ccc;
            font-weight: normal;
            display: block;
            margin-top: 5px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 25px;
        }

        .error-message {
            color: #dc3545;
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin-top: 20px;
        }

        .order-summary-box,
        .order-items-box,
        .shipping-box {
            border: 1px solid #555;
            border-radius: 8px;
            margin-bottom: 25px;
            padding: 20px 25px;
            background-color: rgba(255, 255, 255, 0.03);
        }

        .order-summary-box h2,
        .order-items-box h2,
        .shipping-box h2 {
            margin-top: 0;
            margin-bottom: 18px;
            font-size: 18px;
            color: #eee;
            border-bottom: 1px solid #444;
            padding-bottom: 12px;
            font-weight: 400;
        }

        .summary-details p,
        .shipping-box p {
            margin: 8px 0;
            font-size: 14px;
            color: #ccc;
        }

        .summary-details .label {
            display: inline-block;
            width: 120px;
            font-weight: bold;
            color: #ddd;
        }

        .summary-details .status {
            font-weight: bold;
        }

        .status-pending { color: #ffc107; }
        .status-delivered { color: #28a745; }
        .status-completed { color: #28a745; }
        .status-cancelled { color: #dc3545; }
        .status-unknown { color: #6c757d; }
        .status-error { color: #ff8080; }

        .order-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 15px 0;
            border-bottom: 1px solid #444;
        }

        .order-item:last-child {
            border-bottom: none;
            padding-bottom: 5px;
        }

        .order-item:first-child {
            padding-top: 5px;
        }

        .item-image img {
            height: 65px;
            width: 65px;
            object-fit: contain;
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 4px;
        }

        .item-info {
            flex-grow: 1;
        }

        .item-info h3 {
            margin: 0 0 5px 0;
            font-size: 15px;
            font-weight: normal;
            color: #fff;
        }

        .item-info p {
            margin: 2px 0;
            font-size: 13px;
            color: #aaa;
        }

        .item-price-qty {
            text-align: right;
            min-width: 110px;
            margin-left: auto;
        }

        .item-price-qty p {
            margin: 2px 0;
            font-size: 14px;
            color: #ccc;
        }

        .item-total {
            font-weight: bold;
            color: #eee;
            margin-top: 4px;
        }

        .order-items-box .grand-total {
            text-align: right;
            margin-top: 25px;
            font-size: 18px;
            font-weight: bold;
            color: #FFD700;
            border-top: 1px solid #555;
            padding-top: 18px;
        }
    </style>
</head>
<body>
    <?php @include 'header.php'; ?>

    <div class="details-container">
        <a href="orders.php" class="back-link"><img src="images/back_button.png" alt=""></a>

        <h1>
            Order Details
            <?php if ($order): ?>
                <span class="order-id-title">Order #<?php echo htmlspecialchars($order['orderID']); ?></span>
            <?php endif; ?>
        </h1>

        <?php if ($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>

        <?php elseif ($order): ?>
            <div class="order-summary-box">
                <h2>Order Summary</h2>
                <div class="summary-details">
                    <p><span class="label">Order Date:</span> <?php echo $orderDateTime ? $orderDateTime->format('F j, Y, g:i A') : 'N/A'; ?></p>
                    <p><span class="label">Order Status:</span> <span class="status <?php echo $statusClass; ?>"><?php echo $displayStatus; ?></span></p>
                    <p><span class="label">Order Total:</span> <strong>₱ <?php echo number_format($order['totalPrice'], 2); ?></strong></p>
                </div>
            </div>

            <div class="shipping-box">
                 <h2>Shipping Address</h2>
                 <p><?php echo nl2br(htmlspecialchars($order['deliveryAddress'])); ?></p>
            </div>

            <div class="order-items-box">
                 <h2>Items Ordered</h2>
                <?php if (empty($orderItems)): ?>
                    <p>Could not load item details for this order.</p>
                <?php else: ?>
                    <?php foreach ($orderItems as $item):
                        $itemName = $item['name'] ?? 'Unknown Item';
                        $itemImageFile = $item['image'] ?? null; // Get saved filename, if any
                        $itemQty = $item['quantity'] ?? 0;
                        $itemPrice = $item['price'] ?? 0;
                        $itemTotal = $itemQty * $itemPrice;
                        // Attempt to get product_id (use 'id' or 'product_id' depending on your JSON structure)
                        $productIdFromItem = $item['product_id'] ?? ($item['id'] ?? null);

                        // --- Fallback Query: Attempt to fetch image filename if missing AND product ID exists ---
                        if (empty($itemImageFile) && $productIdFromItem && isset($conn)) { // Check if $conn is still available
                             $sql_img = "SELECT productImage FROM product WHERE productID = ?";
                             $stmt_img = $conn->prepare($sql_img);
                             if ($stmt_img) {
                                 $stmt_img->bind_param("i", $productIdFromItem);
                                 if ($stmt_img->execute()) {
                                     $result_img = $stmt_img->get_result();
                                     if ($row_img = $result_img->fetch_assoc()) {
                                         $itemImageFile = $row_img['productImage']; // Overwrite with freshly fetched name
                                         error_log("Notice: Fetched missing image '{$itemImageFile}' for product ID {$productIdFromItem} in order {$order_id}"); // Log notice
                                     }
                                 } else {
                                     error_log("Error executing image fetch for product ID {$productIdFromItem}: " . $stmt_img->error);
                                 }
                                 $stmt_img->close();
                             } else {
                                 error_log("Error preparing image fetch for product ID {$productIdFromItem}: " . $conn->error);
                             }
                             // No need to reopen/close connection if $conn is kept open until end of script
                        }
                        // --- End Fallback Query ---

                        // Use placeholder if filename is still missing/empty after fallback attempt
                        $displayImage = $itemImageFile ?: 'placeholder.png';
                    ?>
                        <div class="order-item">
                            <div class="item-image">
                                <img src="images/<?php echo htmlspecialchars($displayImage); ?>" alt="<?php echo htmlspecialchars($itemName); ?>">
                            </div>
                            <div class="item-info">
                                <h3><?php echo htmlspecialchars($itemName); ?></h3>
                                <p>Quantity: <?php echo htmlspecialchars($itemQty); ?></p>
                            </div>
                            <div class="item-price-qty">
                                <p>₱ <?php echo number_format($itemPrice, 2); ?> each</p>
                                <p class="item-total">₱ <?php echo number_format($itemTotal, 2); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                     <div class="grand-total">
                         Grand Total: ₱ <?php echo number_format($order['totalPrice'], 2); ?>
                     </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
             <div class="error-message">Could not load order details.</div>
        <?php endif; ?>

    </div>

    <?php
        // Close the main database connection at the very end
        if (isset($conn) && $conn instanceof mysqli) {
            $conn->close();
        }
        @include 'footer.php';
    ?>
</body>
</html>