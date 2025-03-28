<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$dbname = "velour";

$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    if (!isset($_SESSION['username'])) {
        header('Location: login.php');
        exit;
    }
    $username_cancel = $_SESSION['username'];
    $order_id_to_cancel = filter_input(INPUT_POST, 'order_id_to_cancel', FILTER_VALIDATE_INT);

    if ($order_id_to_cancel) {
        $conn_cancel = new mysqli($host, $user, $password, $dbname);
        if ($conn_cancel->connect_error) {
            error_log("Cancel Connection failed: " . $conn_cancel->connect_error);
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Database connection error. Could not cancel order.'];
        } else {
            $conn_cancel->set_charset("utf8mb4");
            $sql_cancel = "UPDATE orders SET status = 'cancelled'
                           WHERE orderID = ? AND username = ? AND status = 'pending'";
            $stmt_cancel = $conn_cancel->prepare($sql_cancel);
            if ($stmt_cancel) {
                $stmt_cancel->bind_param("is", $order_id_to_cancel, $username_cancel);
                if ($stmt_cancel->execute()) {
                    if ($stmt_cancel->affected_rows > 0) {
                        $_SESSION['message'] = ['type' => 'success', 'text' => "Order #" . $order_id_to_cancel . " successfully cancelled."];
                    } else {
                        $_SESSION['message'] = ['type' => 'error', 'text' => "Could not cancel Order #" . $order_id_to_cancel . ". It might not be pending or does not belong to you."];
                    }
                } else {
                     error_log("Cancel Execute failed: " . $stmt_cancel->error);
                     $_SESSION['message'] = ['type' => 'error', 'text' => 'Failed to cancel order due to a database error.'];
                }
                $stmt_cancel->close();
            } else {
                error_log("Cancel Prepare failed: " . $conn_cancel->error);
                $_SESSION['message'] = ['type' => 'error', 'text' => 'Failed to prepare order cancellation.'];
            }
            $conn_cancel->close();
        }
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Invalid order ID provided for cancellation.'];
    }
    header('Location: orders.php');
    exit;
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
$username = $_SESSION['username'];
$orders = [];
$conn_fetch = new mysqli($host, $user, $password, $dbname);
if ($conn_fetch->connect_error) {
     error_log("Fetch Connection failed: " . $conn_fetch->connect_error);
     $message = ['type' => 'error', 'text' => 'Could not connect to database to fetch orders.'];
} else {
    $conn_fetch->set_charset("utf8mb4");
    $sql_fetch = "SELECT orderID, orderList, totalPrice, orderDate, status
                  FROM orders WHERE username = ? ORDER BY orderDate DESC";
    $stmt_fetch = $conn_fetch->prepare($sql_fetch);

    if ($stmt_fetch === false) {
        error_log("Prepare failed for fetching orders: (" . $conn_fetch->errno . ") " . $conn_fetch->error);
        $message = ['type' => 'error', 'text' => 'Error preparing to fetch orders.'];
    } else {
        $stmt_fetch->bind_param("s", $username);
        if ($stmt_fetch->execute()) {
            $result = $stmt_fetch->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $orders[] = $row;
                }
            }
        } else {
             error_log("Execute failed for fetching orders: " . $stmt_fetch->error);
             $message = ['type' => 'error', 'text' => 'Error fetching orders.'];
        }
        $stmt_fetch->close();
    }
    $conn_fetch->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Velour - My Orders</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            padding-top: 100px;
        }
        .orders-page-container {
            padding: 20px;
            margin: 20px auto;
            max-width: 900px;
        }
        .orders-page-container h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #FFD700;
        }
        .order-container {
            display: flex;
            border: 1px solid #555;
            border-radius: 10px;
            padding: 20px 15px;
            margin-bottom: 20px;
            background-color: rgba(255, 255, 255, 0.03);
            gap: 15px;
            align-items: flex-start;
            flex-wrap: wrap;
        }
        .product-info {
            flex: 1;
            min-width: 200px;
        }
        .product-info h2 {
            font-size: 16px;
            font-style: normal;
            font-weight: bold;
            color: #fff;
            margin: 0 0 5px 0;
        }
        .product-info p {
            margin: 3px 0;
            font-size: 13px;
            color: #ccc;
        }
        .product-info .order-id {
            font-size: 11px;
            color: #aaa;
            margin-bottom: 8px;
        }
        .item-count-info {
            font-size: 12px;
            color: #aaa;
        }
        .status-section {
            text-align: right;
            min-width: 180px;
            margin-left: auto;
            padding-left: 15px;
        }
        .status-section .price {
            font-size: 17px;
            font-weight: bold;
            color: #FFD700;
            margin-bottom: 8px;
        }
        .order-status {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .status-pending { color: #ffc107; }
        .status-delivered { color: #28a745; }
        .status-completed { color: #28a745; }
        .status-cancelled { color: #dc3545; }
        .status-unknown { color: #6c757d; }
        .view-details-btn, .cancel-btn {
            display: inline-block;
            color: #fff;
            padding: 6px 12px;
            font-size: 13px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.2s ease;
            vertical-align: middle;
        }
        .view-details-btn {
            background-color: #444;
        }
        .view-details-btn:hover {
            background-color: #555;
            color: #fff;
        }
        .cancel-btn {
             background-color: #c82333;
             margin-left: 5px;
        }
        .cancel-btn:hover {
             background-color: #dc3545;
             color: #fff;
        }
        .no-orders {
            text-align: center;
            font-size: 18px;
            color: #aaa;
            margin-top: 50px;
        }
        .message-container {
             padding: 15px;
             margin: 0 auto 20px auto;
             border: 1px solid transparent;
             border-radius: 4px;
             max-width: 860px;
             text-align: center;
        }
        .message-success {
             color: #155724;
             background-color: #d4edda;
             border-color: #c3e6cb;
        }
        .message-error {
             color: #721c24;
             background-color: #f8d7da;
             border-color: #f5c6cb;
        }
        @media (max-width: 700px) {
            .status-section {
                margin-left: 0;
                text-align: left;
                width: 100%;
                margin-top: 15px;
                padding-left: 0;
                border-top: 1px solid #444;
                padding-top: 15px;
            }
             .order-container {
                 padding: 15px;
            }
        }
        @media (max-width: 450px) {
             .product-info {
                 text-align: center;
                 width: 100%;
                 margin-bottom: 10px;
             }
             .status-section {
                 text-align: center;
             }
         }
    </style>
</head>
<body>
    <?php @include 'header.php'; ?>

    <div class="orders-page-container">
        <h1>My Orders</h1>

        <?php if ($message): ?>
            <div class="message-container message-<?php echo htmlspecialchars($message['type']); ?>">
                <?php echo htmlspecialchars($message['text']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <p class="no-orders">You haven't placed any orders yet.</p>
        <?php else: ?>
            <?php foreach ($orders as $order):
                $orderItems = json_decode($order['orderList'], true);
                $firstItem = null;
                $itemCount = 0;
                if (json_last_error() === JSON_ERROR_NONE && is_array($orderItems) && !empty($orderItems)) {
                    $firstItem = $orderItems[0];
                    $itemCount = count($orderItems);
                } else {
                    error_log("Failed to decode orderList JSON for orderID: " . $order['orderID']);
                    $firstItem = ['name' => 'Order Item(s)'];
                    $itemCount = $order['quantity'] ?? 1;
                }

                $displayStatus = "Status Unknown";
                $statusClass = "status-unknown";
                $dbStatus = $order['status'];
                $orderDateStr = $order['orderDate'];
                $orderDateTime = null;

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
                    $statusClass = "status-unknown";
                }

                ?>
                <div class="order-container">
                    <div class="product-info">
                        <p class="order-id">Order ID: <?php echo htmlspecialchars($order['orderID']); ?></p>
                        <h2><?php echo htmlspecialchars($firstItem['name'] ?? 'Unknown Product'); ?></h2>
                        <?php if ($itemCount > 1) : ?>
                           <p class="item-count-info">(<?php echo $itemCount; ?> items total)</p>
                        <?php endif; ?>
                        <p>Order Date: <?php echo $orderDateTime ? $orderDateTime->format('F j, Y') : 'N/A'; ?></p>
                    </div>

                    <div class="status-section">
                        <p class="price">₱ <?php echo number_format($order['totalPrice'], 2); ?></p>
                        <p class="order-status <?php echo $statusClass; ?>"><?php echo $displayStatus; ?></p>
                        <a href="order_details.php?order_id=<?php echo $order['orderID']; ?>" class="view-details-btn">
                            View Details
                        </a>
                        <?php if ($dbStatus === 'pending'): ?>
                            <form method="POST" action="orders.php" style="display: inline;" onsubmit="return confirmCancel(<?php echo $order['orderID']; ?>)">
                                <input type="hidden" name="order_id_to_cancel" value="<?php echo $order['orderID']; ?>">
                                <input type="hidden" name="action" value="cancel">
                                <button type="submit" class="cancel-btn">Cancel Order</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
    <?php @include 'footer.php'; ?>

    <script>
        function confirmCancel(orderId) {
            return confirm('Are you sure you want to cancel Order #' + orderId + '? This action cannot be undone.');
        }
    </script>
</body>
</html>