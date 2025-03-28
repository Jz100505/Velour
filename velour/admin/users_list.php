<?php
include('../database/connection.php');
// Pagination setup
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Search query setup
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
}

// Modify SQL Query based on the search input
if (!empty($search_query)) {
    $sql_count = "SELECT COUNT(*) AS total_records
                  FROM users
                  WHERE role != 'admin'
                     AND (ID LIKE '%$search_query%'
                     OR username LIKE '%$search_query%'
                     OR email LIKE '%$search_query%')";
    $sql = "SELECT ID, username, email
            FROM users
            WHERE role != 'admin'
              AND (ID LIKE '%$search_query%'
                   OR username LIKE '%$search_query%'
                   OR email LIKE '%$search_query%')
            LIMIT $offset, $records_per_page";
} else {
    $sql_count = "SELECT COUNT(*) AS total_records FROM users WHERE role != 'admin'";
    $sql = "SELECT ID, username, email
            FROM users
            WHERE role != 'admin'
            LIMIT $offset, $records_per_page";
}

// Fetch total number of records and execute query
$result_count = $conn->query($sql_count);
$total_records = $result_count->fetch_assoc()['total_records'];
$total_pages = ceil($total_records / $records_per_page);

$result = $conn->query($sql);


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Product</title>
  <style>
  </style>
</head>
<body>

<div class="container">
    <!-- Search Form -->
    <form action="index.php" method="get">
    <input type="hidden" name="users_list" value="1">
    <input class="search" type="text" name="search" placeholder="Search" value="<?php echo $search_query; ?>">
    <input type="submit" value="Search">
</form>

    <table border="1" style="width: 100%;">
        <tr class="field">
            <td>#</td>
            <td>Username</td>
            <td>Email</td>
        </tr>
        <?php
        // Check if any records exist
        if ($result->num_rows > 0) {
            $count = $offset + 1;
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>$count</td>";
                echo "<td>" . $row['username'] . "</td>";
                echo "<td>" . $row['email'] . "</td>";
                echo "</tr>";
                $count++;
            }
        } else {
            echo "<tr><td colspan='4'>No Records Found!</td></tr>";
        }
        ?>
    </table>

    <!-- Pagination Links -->
    <div class="pagination" style="margin-top: 20px; ">
    <?php
    for ($i = 1; $i <= $total_pages; $i++) {
        echo "<a href='index.php?users_list=1&page=$i&search=" . urlencode($search_query) . "' style='margin: 0 5px; text-decoration: none;'>" . ($i == $page ? "<strong>$i</strong>" : $i) . "</a>";
    }
    ?>
</div>
</div>
