<?php
// Included by index.php - connection & session check assumed done.

$message = ''; // For success/error feedback
$product_data = null;
$product_id = null;

// --- Check if Product ID is provided ---
if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
    echo "<div class='message-error'>Invalid or missing Product ID.</div>";
    // Optionally redirect or prevent form display
    return; // Stop further execution if included
} else {
    $product_id = (int)$_GET['product_id'];
}

// --- Fetch Existing Product Data ---
$sql_fetch = "SELECT productName, category, quantity, price, productImage FROM product WHERE productID = ?";
$stmt_fetch = $conn->prepare($sql_fetch);
if ($stmt_fetch) {
    $stmt_fetch->bind_param("i", $product_id);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();
    if ($result->num_rows === 1) {
        $product_data = $result->fetch_assoc();
    } else {
        echo "<div class='message-error'>Product with ID " . $product_id . " not found.</div>";
        return; // Stop further execution
    }
    $stmt_fetch->close();
} else {
     echo "<div class='message-error'>Error preparing to fetch product data: " . $conn->error . "</div>";
     return; // Stop further execution
}

// --- Form Processing Logic for Update ---
if (isset($_POST['update_product']) && $product_data) {
    $productName = $conn->real_escape_string(trim($_POST['productName']));
    $category = $conn->real_escape_string($_POST['category']);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT); // Use FLOAT for price

    $newImageName = $product_data['productImage']; // Keep old image by default
    $oldImageName = $product_data['productImage'];
    $image_updated = false;

    // Basic Validation
    if (empty($productName) || empty($category) || $quantity === false || $price === false) {
        $message = '<div class="message-error">Please fill all required fields (Name, Category, Quantity, Price).</div>';
    } else {
        // Image handling (if a new image is uploaded)
        if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] == UPLOAD_ERR_OK && $_FILES['productImage']['size'] > 0) {
            $productImage = $_FILES['productImage'];
            $imageTmpName = $productImage['tmp_name'];
            $imageSize = $productImage['size'];
            $imageError = $productImage['error'];
            $imageNameSanitized = preg_replace("/[^a-zA-Z0-9.\s_-]/", "", basename($productImage['name'])); // Sanitize name
            $imageType = strtolower(pathinfo($imageNameSanitized, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($imageType, $allowedTypes)) {
                if ($imageError === 0) {
                    if ($imageSize < 5000000) { // 5MB limit
                        $newImageName = uniqid('prod_', true) . "." . $imageType;
                        $uploadDir = '../images/';
                        $targetPath = $uploadDir . $newImageName;

                        if (move_uploaded_file($imageTmpName, $targetPath)) {
                            $image_updated = true; // Flag that a new image was successfully uploaded
                        } else {
                            $message = '<div class="message-error">Error uploading new image file. Check permissions for ' . $uploadDir . '</div>';
                            // Revert to old image if upload fails mid-process
                            $newImageName = $oldImageName;
                            $image_updated = false;
                        }
                    } else { $message = '<div class="message-error">New image file is too large (Max 5MB).</div>'; $newImageName = $oldImageName; }
                } else { $message = '<div class="message-error">Error during new image upload: Code ' . $imageError . '</div>'; $newImageName = $oldImageName; }
            } else { $message = '<div class="message-error">Invalid new image file type. Allowed: jpg, jpeg, png, gif, webp.</div>'; $newImageName = $oldImageName; }
        } // End image handling

        // Proceed with update only if no upload error message was set
        if (empty($message)) {
            // Check for duplicate name (excluding current product)
            $check_sql = "SELECT productID FROM product WHERE productName = ? AND productID != ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("si", $productName, $product_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $message = '<div class="message-error">Another product with this name already exists.</div>';
            } else {
                // Update Database
                $update_sql = "UPDATE product SET productName = ?, category = ?, quantity = ?, price = ?, productImage = ? WHERE productID = ?";
                $stmt_update = $conn->prepare($update_sql);
                if ($stmt_update) {
                    $stmt_update->bind_param("ssidsi", $productName, $category, $quantity, $price, $newImageName, $product_id);
                    if ($stmt_update->execute()) {
                        $_SESSION['admin_message'] = ['type' => 'success', 'text' => 'Product updated successfully!'];

                        // Delete old image file *only if* a new one was successfully uploaded
                        if ($image_updated && !empty($oldImageName) && $oldImageName != $newImageName) {
                             $oldImagePath = '../images/' . $oldImageName;
                             if (file_exists($oldImagePath)) {
                                 if (!unlink($oldImagePath)) {
                                      error_log("Failed to delete old image file: " . $oldImagePath . " after updating product ID: " . $product_id);
                                     $_SESSION['admin_message']['text'] .= ' (Note: Could not delete old image file.)';
                                 }
                             }
                        }
                        // Redirect to view products after successful update
                        echo "<script>window.location.href='index.php?view_products=1';</script>";
                        exit; // Stop script execution after redirect

                    } else {
                        $message = '<div class="message-error">Error updating product: ' . $stmt_update->error . '</div>';
                    }
                    $stmt_update->close();
                } else {
                     $message = '<div class="message-error">Error preparing update statement: ' . $conn->error . '</div>';
                }
            }
            $check_stmt->close();
        }
    } // End basic validation check
} // End form processing check

?>

<h1 class="admin-page-title">Update Product (ID: <?php echo $product_id; ?>)</h1>

<?php echo $message; // Display feedback messages ?>

<?php if ($product_data): ?>
<form class="admin-form" action="index.php?update_product=1&product_id=<?php echo $product_id; ?>" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label for="productName">Product Name:</label>
        <input type="text" id="productName" name="productName" value="<?php echo htmlspecialchars($product_data['productName']); ?>" required>
    </div>

    <div class="form-group">
        <label for="category">Category:</label>
        <select name="category" id="category" required>
            <option value="" disabled>-- Select Category --</option>
            <?php
            $categories = ['Armani Line-up', 'Jean Paul Gaultier Line-up', 'Creed Line-up', 'Other'];
            foreach ($categories as $cat) {
                $selected = ($product_data['category'] == $cat) ? 'selected' : '';
                echo "<option value=\"" . htmlspecialchars($cat) . "\" $selected>" . htmlspecialchars($cat) . "</option>";
            }
            ?>
        </select>
    </div>

    <div class="form-group">
        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($product_data['quantity']); ?>" required min="0">
    </div>

    <div class="form-group">
        <label for="price">Price (₱):</label>
        <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($product_data['price']); ?>" required min="0" step="0.01">
    </div>

    <div class="form-group">
        <label>Current Image:</label>
        <?php if (!empty($product_data['productImage']) && file_exists('../images/' . $product_data['productImage'])): ?>
            <img src="../images/<?php echo htmlspecialchars($product_data['productImage']); ?>" alt="Current Image" style="max-height: 100px; max-width: 100px; border: 1px solid #555; border-radius: 4px; display: block; margin-bottom: 10px;">
        <?php else: ?>
            <p style="color: #888; font-size: 0.9em;">No current image available.</p>
        <?php endif; ?>
        <label for="productImage" style="margin-top: 10px;">Upload New Image (Optional):</label>
        <input type="file" id="productImage" name="productImage" accept="image/*">
        <small style="color: #888; display: block; margin-top: 5px;">Leave blank to keep the current image.</small>
    </div>

    <div class="form-group">
        <button type="submit" name="update_product">Update Product</button>
        <a href="index.php?view_products=1" style="margin-left: 15px; color: #ccc; text-decoration: none;">Cancel</a>
    </div>
</form>
<?php else: ?>
    <?php // Message already shown if product not found ?>
    <p><a href="index.php?view_products=1">Back to Product List</a></p>
<?php endif; ?>