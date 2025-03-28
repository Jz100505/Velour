<?php

$message = ''; 

if (isset($_POST['add_product'])) {
    $productName = $conn->real_escape_string(trim($_POST['productName']));
    $category = $conn->real_escape_string($_POST['category']);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);

    if (empty($productName) || empty($category) || $quantity === false || $price === false || !isset($_FILES['productImage']) || $_FILES['productImage']['error'] != UPLOAD_ERR_OK) {
        $message = '<div class="message-error">Insert Failed: Please fill all fields and upload a valid image.</div>';
    } else {
        $productImage = $_FILES['productImage'];
        $imageNameSanitized = preg_replace("/[^a-zA-Z0-9.\s_-]/", "", basename($productImage['name']));
        $imageTmpName = $productImage['tmp_name'];
        $imageSize = $productImage['size'];
        $imageError = $productImage['error'];
        $imageType = strtolower(pathinfo($imageNameSanitized, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($imageType, $allowedTypes)) {
            if ($imageError === 0) {
                if ($imageSize < 5000000) { 
                    $newImageName = uniqid('prod_', true) . "." . $imageType;
                    $uploadDir = '../images/';
                    $targetPath = $uploadDir . $newImageName;

                    $check_sql = "SELECT productID FROM product WHERE productName = ?";
                    $check_stmt = $conn->prepare($check_sql);
                    if ($check_stmt) {
                         $check_stmt->bind_param("s", $productName);
                         $check_stmt->execute();
                         $check_result = $check_stmt->get_result();
                         if ($check_result->num_rows > 0) {
                             $message = '<div class="message-error">Insert Failed: Product with this name already exists.</div>';
                         } else {
                             if (move_uploaded_file($imageTmpName, $targetPath)) {
                                 $insert_sql = "INSERT INTO product (productName, category, quantity, price, productImage) VALUES (?, ?, ?, ?, ?)";
                                 $stmt_insert = $conn->prepare($insert_sql);
                                 if ($stmt_insert) {
                                     $stmt_insert->bind_param("ssids", $productName, $category, $quantity, $price, $newImageName);
                                     if ($stmt_insert->execute()) {
                                         $message = '<div class="message-success">Product added successfully!</div>';
                                         $_POST = array();
                                     } else {
                                         $message = '<div class="message-error">Insert Failed: Error inserting product: ' . $stmt_insert->error . '</div>';
                                         if (file_exists($targetPath)) { unlink($targetPath); }
                                     }
                                     $stmt_insert->close();
                                 } else {
                                      $message = '<div class="message-error">Insert Failed: Error preparing statement: ' . $conn->error . '</div>';
                                      if (file_exists($targetPath)) { unlink($targetPath); }
                                 }
                             } else {
                                 $message = '<div class="message-error">Insert Failed: Error uploading image file. Check permissions for ' . $uploadDir . '</div>';
                             }
                         }
                         $check_stmt->close();
                    } else {
                         $message = '<div class="message-error">Insert Failed: Error preparing duplicate check: ' . $conn->error . '</div>';
                    }
                } else { $message = '<div class="message-error">Insert Failed: Image file is too large (Max 5MB).</div>'; }
            } else { $message = '<div class="message-error">Insert Failed: Error during image upload: Code ' . $imageError . '</div>'; }
        } else { $message = '<div class="message-error">Insert Failed: Invalid image file type. Allowed: jpg, jpeg, png, gif, webp.</div>'; }
    }
}


if (isset($_SESSION['admin_message'])) {
    $message_type = $_SESSION['admin_message']['type'] ?? 'info';
    $message_text = $_SESSION['admin_message']['text'] ?? 'Action processed.';
    $message = "<div class='message-" . htmlspecialchars($message_type) . "'>" . htmlspecialchars($message_text) . "</div>" . $message;
    unset($_SESSION['admin_message']);
}

$records_per_page = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;
$search_term = '';
$search_sql_condition = '';
$params = [];
$types = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_term = trim($_GET['search']);
    $like_term = "%" . $search_term . "%";
    $search_sql_condition = " WHERE productID LIKE ? OR productName LIKE ? OR category LIKE ?";
    $params = [$like_term, $like_term, $like_term];
    $types = 'sss';
}
$total_sql = "SELECT COUNT(*) AS total FROM product" . $search_sql_condition;
$stmt_count = $conn->prepare($total_sql);
$total_records = 0;
if ($stmt_count) {
    if (!empty($params)) { $stmt_count->bind_param($types, ...$params); }
    $stmt_count->execute();
    $total_result = $stmt_count->get_result();
    if ($total_result) { $total_row = $total_result->fetch_assoc(); $total_records = $total_row['total'] ?? 0; }
    else { $message .= "<div class='message-error'>Error fetching record count: " . $conn->error . "</div>"; }
    $stmt_count->close();
} else { $message .= "<div class='message-error'>Error preparing count query: " . $conn->error . "</div>"; }
$total_pages = ceil($total_records / $records_per_page);
$total_pages = max($total_pages, 1);
$sql = "SELECT productID, productName, category, quantity, price, productImage FROM product" . $search_sql_condition . " ORDER BY productID DESC LIMIT ?, ?";
$stmt_fetch = $conn->prepare($sql);
$result = null;
if ($stmt_fetch) {
    $limit_params = [$offset, $records_per_page]; $all_params = array_merge($params, $limit_params); $all_types = $types . 'ii';
    $stmt_fetch->bind_param($all_types, ...$all_params);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();
} else { $message .= "<div class='message-error'>Error preparing fetch query: " . $conn->error . "</div>"; }

?>

<style>
    #add-product-container {
        display: none; 
        margin-bottom: 40px;
        padding-top: 20px;
        border-top: 1px solid #444;
    }
    .toggle-add-form-btn {
        background-color: #FFD700;
        color: black;
        border: none;
        border-radius: 50%; 
        width: 50px;
        height: 50px;
        font-size: 28px; 
        font-weight: bold;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease-in-out;
        margin-bottom: 30px; 
        display: block; 
        margin-left: auto;
        margin-right: auto;
        line-height: 48px; 
    }
    .toggle-add-form-btn:hover {
        background-color: #fff;
        transform: scale(1.1);
    }
    .toggle-add-form-btn.active {
         background-color: #dc3545; 
         color: white;
         transform: rotate(45deg); 
    }

</style>

<script>
function toggleAddForm() {
    var formContainer = document.getElementById('add-product-container');
    var toggleButton = document.getElementById('toggle-add-btn');
    if (formContainer.style.display === 'none' || formContainer.style.display === '') {
        formContainer.style.display = 'block';
        toggleButton.classList.add('active');
        toggleButton.innerHTML = '&times;'; 
    } else {
        formContainer.style.display = 'none';
        toggleButton.classList.remove('active');
        toggleButton.innerHTML = '+'; 
    }
}
document.addEventListener('DOMContentLoaded', function() {
    const messages = document.querySelectorAll('.message-error');
    let hasInsertError = false;
    messages.forEach(msg => {
        if (msg.textContent.includes('Insert Failed')) {
            hasInsertError = true;
        }
    });

    if (hasInsertError) {
         var formContainer = document.getElementById('add-product-container');
         var toggleButton = document.getElementById('toggle-add-btn');
         formContainer.style.display = 'block';
         toggleButton.classList.add('active');
         toggleButton.innerHTML = '&times;';
    }
});
</script>


<?php echo $message; ?>

<button id="toggle-add-btn" class="toggle-add-form-btn" onclick="toggleAddForm()">+</button>

<div id="add-product-container">
    <h2 style="color: #FFD700; font-weight: 400; margin-bottom: 20px;">Add New Product</h2>
    <form class="admin-form" action="index.php?view_products=1" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="productName">Product Name:</label>
            <input type="text" id="productName" name="productName" required value="<?php echo isset($_POST['productName']) ? htmlspecialchars($_POST['productName']) : ''; ?>">
        </div>
        <div class="form-group">
            <label for="category">Category:</label>
            <select name="category" id="category" required>
                <option value="" disabled <?php echo !isset($_POST['category']) ? 'selected' : ''; ?>>-- Select Category --</option>
                <?php
                 $categories = ['Armani Line-up', 'Jean Paul Gaultier Line-up', 'Creed Line-up', 'Other'];
                 foreach ($categories as $cat) {
                     $selected = (isset($_POST['category']) && $_POST['category'] == $cat) ? 'selected' : '';
                     echo "<option value=\"" . htmlspecialchars($cat) . "\" $selected>" . htmlspecialchars($cat) . "</option>";
                 }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" required min="0" value="<?php echo isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : '1'; ?>">
        </div>
        <div class="form-group">
            <label for="price">Price (₱):</label>
            <input type="number" id="price" name="price" required min="0" step="0.01" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
        </div>
        <div class="form-group">
            <label for="productImage">Product Image:</label>
            <input type="file" id="productImage" name="productImage" accept="image/*" required>
        </div>
        <div class="form-group">
            <button type="submit" name="add_product">Add Product</button>
        </div>
    </form>
</div>


<h1 class="admin-page-title" style="margin-top: 30px; border-top: 1px solid #444; padding-top: 30px;">View All Products</h1>

<form action="index.php" method="get" class="search-form">
    <input type="hidden" name="view_products" value="1">
    <input type="text" name="search" placeholder="Search Products (ID, Name, Category)..." value="<?php echo htmlspecialchars($search_term); ?>">
    <input type="submit" value="Search">
    <?php if (!empty($search_term)): ?>
        <a href="index.php?view_products=1">Clear Search</a>
    <?php endif; ?>
</form>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Name</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['productID']; ?></td>
                    <td>
                        <?php if (!empty($row['productImage']) && file_exists('../images/' . $row['productImage'])): ?>
                            <img src="../images/<?php echo htmlspecialchars($row['productImage']); ?>" alt="<?php echo htmlspecialchars($row['productName']); ?>" class="product-image-thumb">
                        <?php else: ?>
                            <span style="font-size: 0.8em; color: #888;">No Image</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['productName']); ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td>₱<?php echo number_format($row['price'], 2); ?></td>
                    <td class="action-links">
                        <a href="index.php?update_product=1&product_id=<?php echo $row['productID']; ?>" class="update">Update</a>
                        <a href="delete_product.php?product_id=<?php echo $row['productID']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this product? This cannot be undone.');">Remove</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if($stmt_fetch) $stmt_fetch->close(); ?>
        <?php else: ?>
            <tr>
                <td colspan="7">No products found<?php echo !empty($search_term) ? ' matching your search.' : '.'; ?></td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<div class="pagination">
    <?php if ($total_pages > 1): ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php $search_param = !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?>
            <?php if ($i == $page): ?>
                <strong><?php echo $i; ?></strong>
            <?php else: ?>
                <a href="index.php?view_products=1&page=<?php echo $i . $search_param; ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>
    <?php endif; ?>
</div>