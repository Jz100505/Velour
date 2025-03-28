<?php
// Included by index.php - connection & session check assumed done.

// --- REMOVED Deletion Logic Block ---

// --- Display Session Messages (Optional) ---
// Place this near the top, before the table, to show feedback from delete_order.php
if (isset($_SESSION['admin_message'])) {
    $message_type = $_SESSION['admin_message']['type'] ?? 'info'; // Default to info
    $message_text = $_SESSION['admin_message']['text'] ?? 'Action processed.';
    // Use appropriate CSS class based on $message_type ('message-success' or 'message-error')
    echo "<div class='message-" . htmlspecialchars($message_type) . "'>" . htmlspecialchars($message_text) . "</div>";
    unset($_SESSION['admin_message']); // Clear message after displaying
}
// --- End Display Session Messages ---


// --- Simplified Status Update Logic (Optional - Same as before) ---
$update_status_query = "SELECT orderID FROM orders WHERE status = 'pending'";
$pending_orders_result = $conn->query($update_status_query);
if ($pending_orders_result && $pending_orders_result->num_rows > 0) {
    $pending_orders = [];
    while ($row_pending = $pending_orders_result->fetch_assoc()) { $pending_orders[] = $row_pending['orderID']; }
    $num_to_change = rand(0, 1);
    $num_to_change = min($num_to_change, count($pending_orders));
    if ($num_to_change > 0) {
        $keys_to_change = array_rand($pending_orders, $num_to_change);
        if (!is_array($keys_to_change)) { $keys_to_change = [$keys_to_change]; }
        foreach ($keys_to_change as $key) {
            $order_id_to_update = $pending_orders[$key];
            $new_status = (rand(1, 4) <= 3) ? 'completed' : 'cancelled';
            $update_sql = "UPDATE orders SET status = ? WHERE orderID = ?";
            $stmt_update = $conn->prepare($update_sql);
            if($stmt_update){
                $stmt_update->bind_param("si", $new_status, $order_id_to_update);
                $stmt_update->execute();
                $stmt_update->close();
            }
        }
    }
}
// --- End Optional Status Update ---

// --- Pagination & Search Setup (Same as before) ---
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
    $search_sql_condition = " WHERE username LIKE ? OR orderList LIKE ? OR status LIKE ? OR deliveryAddress LIKE ? OR orderID LIKE ?";
    $params = [$like_term, $like_term, $like_term, $like_term, $like_term];
    $types = 'sssss';
}
// --- Count & Fetch Queries (Same Prepared Statements as before) ---
$total_sql = "SELECT COUNT(*) AS total FROM orders" . $search_sql_condition;
$stmt_count = $conn->prepare($total_sql);
if ($stmt_count) {
    if (!empty($params)) { $stmt_count->bind_param($types, ...$params); }
    $stmt_count->execute();
    $total_result = $stmt_count->get_result();
    $total_row = $total_result->fetch_assoc();
    $total_records = $total_row['total'] ?? 0;
    $stmt_count->close();
} else {
    echo "<div class='message-error'>Error preparing count query: " . $conn->error . "</div>";
    $total_records = 0;
}
$total_pages = ceil($total_records / $records_per_page);
$total_pages = max($total_pages, 1);

$sql = "SELECT orderID, username, orderList, quantity, totalPrice, orderDate, status, deliveryAddress
        FROM orders"
     . $search_sql_condition
     . " ORDER BY orderDate DESC LIMIT ?, ?";
$stmt_fetch = $conn->prepare($sql);
$result = null;
if ($stmt_fetch) {
    $limit_params = [$offset, $records_per_page];
    $all_params = array_merge($params, $limit_params);
    $all_types = $types . 'ii';
    $stmt_fetch->bind_param($all_types, ...$all_params);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();
} else {
    echo "<div class='message-error'>Error preparing fetch query: " . $conn->error . "</div>";
}
?>

<style>
    /* Status color styles */
    .status-pending { color: #ffc107; font-weight: bold; }
    .status-completed { color: #28a745; font-weight: bold; }
    .status-cancelled { color: #dc3545; font-weight: bold; }

    /* Table adjustments (Ensure these work with index.php styles) */
    .admin-content-area table { table-layout: auto; word-wrap: break-word; }
    .admin-content-area th, .admin-content-area td { vertical-align: middle; padding: 10px 12px; }
    .order-list-items { padding: 0; margin: 0; list-style: none; }
    .order-list-items li { margin-bottom: 3px; }
    .order-list-items .more-items { font-size: 0.9em; color: #888; margin-top: 5px; }
    .admin-content-area th:nth-child(8), .admin-content-area td:nth-child(8) { width: 80px; text-align: center; }

    /* Session Message Styles (Add to index.php's <style> or here) */
    .message-success { color: #15a13b; background-color: rgba(21, 161, 59, 0.1); border: 1px solid rgba(21, 161, 59, 0.3); padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
    .message-error { color: #dc3545; background-color: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.3); padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center; }

</style>

<h1 class="admin-page-title">View All Orders</h1>

<form action="index.php" method="get" class="search-form">
    <input type="hidden" name="view_orders" value="1">
    <input type="text" name="search" placeholder="Search Orders..." value="<?php echo htmlspecialchars($search_term); ?>">
    <input type="submit" value="Search">
     <?php if (!empty($search_term)): ?>
        <a href="index.php?view_orders=1">Clear Search</a>
     <?php endif; ?>
</form>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Products Ordered</th>
            <th>Qty (Total)</th>
            <th>Total Price</th>
            <th>Order Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                    // Determine status class (Same as before)
                    $status_class = '';
                    if ($row['status'] == 'pending') $status_class = 'status-pending';
                    elseif ($row['status'] == 'cancelled') $status_class = 'status-cancelled';
                    elseif ($row['status'] == 'completed') $status_class = 'status-completed';

                    // Decode JSON and prepare display string (Same as before)
                    $products_display = '(Error reading items)';
                    $items = json_decode($row['orderList'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($items) && !empty($items)) {
                         $product_names = [];
                         foreach ($items as $item) {
                             $product_names[] = isset($item['name']) ? htmlspecialchars($item['name']) : '(Unknown Item)';
                         }
                         if (count($product_names) == 1) { $products_display = $product_names[0]; }
                         elseif (count($product_names) > 1) { $products_display = $product_names[0] . '<div class="more-items">(+ ' . (count($product_names) - 1) . ' more)</div>'; }
                         else { $products_display = '(No items found)'; }
                    } elseif (empty($items)) { $products_display = '(No items listed)'; }
                ?>
                <tr>
                    <td><?php echo $row['orderID']; ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo $products_display; ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td>₱<?php echo number_format($row['totalPrice'], 2); ?></td>
                    <td><?php echo date("Y-m-d H:i", strtotime($row['orderDate'])); ?></td>
                    <td class="<?php echo $status_class; ?>"><?php echo htmlspecialchars(ucfirst($row['status'])); ?></td>
                    <td class="action-links">
                        <a href="delete_order.php?order_id=<?php echo $row['orderID']; ?>" class="delete" onclick="return confirm('Are you sure you want to permanently delete this order?');">Remove</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No orders found<?php echo !empty($search_term) ? ' matching your search.' : '.'; ?></td>
            </tr>
        <?php endif; ?>
         <?php if($stmt_fetch) $stmt_fetch->close(); ?>
    </tbody>
</table>

<div class="pagination">
    <?php if ($total_pages > 1): ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php $search_param = !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?>
            <?php if ($i == $page): ?>
                <strong><?php echo $i; ?></strong>
            <?php else: ?>
                <a href="index.php?view_orders=1&page=<?php echo $i . $search_param; ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>
    <?php endif; ?>
</div>