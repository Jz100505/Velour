<?php
session_start();
// Use relative path from admin directory to database directory
include('../database/connection.php');

// --- Security Check: Ensure user is admin ---
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Access Denied: Admins only.'];
    header("Location: index.php?view_products=1");
    exit();
}

// --- Validate Product ID ---
if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
    $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Invalid Product ID provided for deletion.'];
    header("Location: index.php?view_products=1");
    exit();
}

$product_id_to_delete = (int)$_GET['product_id'];
$image_to_delete = null;
$error_message = '';
$success = false;

// --- Get Image Filename Before Deleting Record ---
$sql_get_image = "SELECT productImage FROM product WHERE productID = ?";
$stmt_get_image = $conn->prepare($sql_get_image);

if ($stmt_get_image) {
    $stmt_get_image->bind_param("i", $product_id_to_delete);
    if ($stmt_get_image->execute()) {
        $result_image = $stmt_get_image->get_result();
        if ($result_image->num_rows > 0) {
            $row_image = $result_image->fetch_assoc();
            $image_to_delete = $row_image['productImage'];
        } else {
             // Product might already be deleted, still try to proceed but log this
             error_log("Delete attempt: Product ID {$product_id_to_delete} not found when fetching image.");
        }
    } else {
        $error_message = "Error fetching product image: " . $stmt_get_image->error;
    }
    $stmt_get_image->close();
} else {
    $error_message = "Error preparing image fetch statement: " . $conn->error;
}

// --- Proceed with Deletion if no error yet ---
if (empty($error_message)) {
    $delete_query = "DELETE FROM product WHERE productID = ?";
    $stmt_delete = $conn->prepare($delete_query);

    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $product_id_to_delete);
        if ($stmt_delete->execute()) {
            if ($stmt_delete->affected_rows > 0) {
                $success = true;
                $_SESSION['admin_message'] = ['type' => 'success', 'text' => 'Product #' . $product_id_to_delete . ' deleted successfully.'];

                // --- Delete Image File ---
                if (!empty($image_to_delete)) {
                    $image_path = '../images/' . $image_to_delete;
                    if (file_exists($image_path)) {
                        if (!unlink($image_path)) {
                            // Log failure to delete image file, but don't override success message
                            error_log("Failed to delete image file: " . $image_path . " for deleted product ID: " . $product_id_to_delete);
                            $_SESSION['admin_message']['text'] .= ' (Note: Could not delete image file.)';
                        }
                    } else {
                         error_log("Image file not found for deletion: " . $image_path);
                    }
                }
            } else {
                // Product ID might not exist or was already deleted
                $error_message = "Product not found or already deleted.";
            }
        } else {
             $error_message = "Error executing delete: " . $stmt_delete->error;
        }
        $stmt_delete->close();
    } else {
         $error_message = "Error preparing delete statement: " . $conn->error;
    }
}

$conn->close();

// --- Set Error Message if Deletion Failed ---
if (!$success && !empty($error_message)) {
    $_SESSION['admin_message'] = ['type' => 'error', 'text' => $error_message];
} elseif (!$success && empty($error_message)) {
     // Generic error if success is false but no specific message was set
     $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Failed to delete product. Unknown error.'];
}


// --- Redirect back to the products list ---
header("Location: index.php?view_products=1");
exit();
?>