<?php
// admin/delete_order.php

session_start();
include('../database/connection.php'); // Adjust path if needed

// --- Security Check: Ensure user is admin ---
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    // Optional: Redirect to login or show an error
    die("Access Denied: Admins only."); // Simple stop
    // header("Location: ./login.php");
    // exit();
}

// --- Handle Order Deletion ---
$error_message = '';
$success = false;

if (isset($_GET['order_id']) && is_numeric($_GET['order_id'])) {
    $order_id_to_delete = (int)$_GET['order_id'];

    // Prepare DELETE statement
    $delete_query = "DELETE FROM orders WHERE orderID = ?";
    $stmt_delete = $conn->prepare($delete_query);

    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $order_id_to_delete);
        if ($stmt_delete->execute()) {
            // Check if any row was actually deleted
            if ($stmt_delete->affected_rows > 0) {
                 $success = true;
                 // Optional: Log deletion action
            } else {
                // Order ID might not exist or was already deleted
                $error_message = "Order not found or already deleted.";
            }
        } else {
             $error_message = "Error executing delete: " . $stmt_delete->error;
        }
        $stmt_delete->close();
    } else {
         $error_message = "Error preparing delete statement: " . $conn->error;
    }
} else {
    $error_message = "Invalid Order ID provided.";
}

$conn->close();

// --- Redirect back to the orders list ---
// You can use session flash messages to show success/error after redirect
if ($success) {
    // Example: Store success message in session (requires session_start() at top)
     $_SESSION['admin_message'] = ['type' => 'success', 'text' => 'Order #' . $order_id_to_delete . ' deleted successfully.'];
} elseif (!empty($error_message)) {
    // Example: Store error message in session
     $_SESSION['admin_message'] = ['type' => 'error', 'text' => $error_message];
}

// Redirect always, whether success or error (avoids showing blank page)
header("Location: index.php?view_orders=1");
exit(); // Important: stop script execution after redirect

?>