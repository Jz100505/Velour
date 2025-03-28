<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Velour - Shop</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
    .shop {
        padding: 120px 80px 50px 50px; 
    }
    .featured {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 40px;
    }
    .featured img {
        height: 680px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-radius: 10px;
    }
    .featured img:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 20px rgba(255, 215, 0, 0.8);
    }
    .featured-text {
        align-items: center;
    }
    .featured-text h1 {
        color: #FFD700;
        font-weight: 700;
        font-style: italic;
        font-size: 45px;
        margin-bottom: 1px;
    }
    .featured-text p {
        color: #ffffff;
        font-size: 28px;
        text-align: justify;
        width: 630px;
        margin-top: 2px;
    }
    .learn-more { 
        background-color: rgba(0, 0, 0, 0);
        color: #ffffff;
        border: 3px solid white;
        border-radius: 10px;
        height: 50px;
        width: 125px;
        font-size: 16px; 
        font-weight: bold;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease-in-out;
        margin-top: 20px;
    }
    .learn-more:hover {
        background-color: #FFD700;
        color: black;
        border: 0;
    }
    .shop-list h2{
        color: #FFD700;
        font-size: 35px;
        font-weight: normal;
        margin-top: 40px
        margin-bottom: 20px;
    }
    .perfume-image {
        height: 225px;
        display: block;
        margin: 15px auto;
        object-fit: contain;
    }
    h3 {
        color: #ffffff;
        font-size: 20px;
        text-align: center;
        min-height: 48px;
        margin: 10px 10px 15px 10px;
    }
    .price {
        font-size: 15px;
        color: #FFD700;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 5px;
        margin: 0 30px 20px;
    }
    .price img {
        height: 25px;
    }
    .list {
        width: 300px;
        border: 1px solid #ffffff7c;
        border-radius: 15px;
        display: flex;
        flex-direction: column;
    }
    .box {
        display: flex;
        flex-direction: column;
        height: 100%;
        justify-content: space-between;
    }
    .perfume-list {
        display: flex;
        flex-wrap: wrap;
        gap: 40px;
    }
    .divider {
        border: none;
        height: 1px;
        background-color: #ffffff7c;
        margin-top: 10px;
        margin-bottom: 0;
    }
    .add-to-cart-btn {
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        display: inline-block;
        line-height: 0;
    }
    .add-to-cart-btn img {
        transition: transform 0.2s ease;
    }
    .add-to-cart-btn:hover img {
        transform: scale(1.15);
    }
    .message {
        text-align: center;
        padding: 10px;
        margin: 10px auto;
        border-radius: 5px;
        max-width: 500px;
    }
    .success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    </style>
<body>
<?php @include 'header.php'; ?>

<section class="shop">

    <?php
    if (isset($_GET['added'])) {
        echo '<div class="message success">Added ' . htmlspecialchars($_GET['added']) . ' to cart!</div>';
    }
    if (isset($_GET['error'])) {
        $errorMsg = 'An error occurred.';
        if ($_GET['error'] == 'invalid_id') $errorMsg = 'Invalid product selected.';
        if ($_GET['error'] == 'db_prepare') $errorMsg = 'Database error. Please try again.';
        if ($_GET['error'] == 'not_found') $errorMsg = 'Product not found.';
        echo '<div class="message error">' . $errorMsg . '</div>';
    }
    ?>

    <div class="featured">
        <img src="images/stronger_with_you_parfum.jpg" alt="Stronger with you Parfum">
        <div class="featured-text">
            <h1>Stronger with you Parfum</h1>
            <p>A warm fusion of pink pepper, mandarin, lavender, and rich vanilla. Now available at <span>Velour</span></p>
            <form action="add_to_cart.php" method="post" style="display: inline;">
                 <input type="hidden" name="product_id" value="17"> <button type="submit" class="learn-more">Add to Cart.</button>
            </form>
        </div>
    </div>

    <div class="shop-list">
        <?php
        include('database/connection.php');

        function getProductsByCategory($conn, $category) {

            $stmt = $conn->prepare("SELECT productID, productName, price, productImage FROM product WHERE category = ?");
            if ($stmt === false) {
                error_log("Prepare failed in getProductsByCategory: (" . $conn->errno . ") " . $conn->error);
                return []; 
            }
            $stmt->bind_param("s", $category);
            $stmt->execute();
            $result = $stmt->get_result();
            $products = [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $products[] = $row;
                }
            }
            $stmt->close();
            return $products;
        }

        $categories = [
            'Armani Line-up',
            'Jean Paul Gaultier Line-up',
            'Creed Line-up'
        ];
        foreach ($categories as $categoryName) {
            $products = getProductsByCategory($conn, $categoryName);
            if (!empty($products)) {
                echo '<div class="' . strtolower(str_replace(' ', '-', $categoryName)) . '">'; 
                echo '<h2>' . htmlspecialchars($categoryName) . '</h2>';
                echo '<div class="perfume-list">';

                foreach ($products as $product) {
                    ?>
                    <div class="list">
                        <div class="box">
                            <div> <img src="images/<?php echo htmlspecialchars($product['productImage']); ?>" alt="<?php echo htmlspecialchars($product['productName']); ?>" class="perfume-image">
                                <hr class="divider">
                                <h3><?php echo htmlspecialchars($product['productName']); ?></h3>
                            </div>
                            <div class="price"> <span><strong>₱<?php echo number_format($product['price'], 2); ?></strong></span>
                                <form action="add_to_cart.php" method="post" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['productID']; ?>">
                                    <button type="submit" class="add-to-cart-btn">
                                        <img src="images/cart.png" alt="Add to Cart">
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php
                }

                echo '</div>';
                echo '</div>';
            }
        }

        $conn->close();
        ?>
    </div> </section>

<?php @include 'footer.php'; ?>
</body>
</html>