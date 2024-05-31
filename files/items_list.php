<?php
require 'config.php';

// Получаем категории
$parent_id = isset($_GET['category_id']) ? (int) $_GET['category_id'] : NULL;

// Получаем товары
$product_sql = "SELECT p.id, p.name AS product_name, p.price, p.rating_sum, p.rating_count, u.name AS seller_name 
                FROM products p 
                JOIN users u ON p.user_id = u.id";

$product_params = [];
$product_types = '';
$product_conditions = [];

if ($parent_id !== NULL) {
    $substmt = $conn->prepare('CALL GetSubCategories(?)');
    $substmt->bind_param('d', $parent_id);
    $substmt->execute();
    $subres = $substmt->get_result();
    $subcategories = [];

    while ($sub = $subres->fetch_assoc()) {
        $subcategories[] = $sub['id'];
    }

    $substmt->close();
    if (count($subcategories) > 0) {
        $subcategories = join(',', $subcategories);
        $product_conditions[] = 'p.category_id IN (' . $subcategories . ')';
    }
}

if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
    $product_conditions[] = 'p.price >= ?';
    $product_params[] = (float)$_GET['min_price'];
    $product_types .= 'd';
}

if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $product_conditions[] = 'p.price <= ?';
    $product_params[] = (float)$_GET['max_price'];
    $product_types .= 'd';
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $product_conditions[] = 'p.name LIKE ?';
    $product_params[] = '%' . $_GET['search'] . '%';
    $product_types .= 's';
}

if (count($product_conditions) > 0) {
    $product_sql .= ' WHERE ' . implode(' AND ', $product_conditions);
}

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';

$sort_column = 'p.id';
$sort_order = 'DESC';

switch ($sort) {
    case 'price_asc':
        $sort_column = 'p.price';
        $sort_order = 'ASC';
        break;
    case 'price_desc':
        $sort_column = 'p.price';
        $sort_order = 'DESC';
        break;
    case 'date_asc':
        $sort_column = 'p.id';
        $sort_order = 'ASC';
        break;
    case 'date_desc':
        $sort_column = 'p.id';
        $sort_order = 'DESC';
        break;
}

$product_sql .= " ORDER BY $sort_column $sort_order";
$finalstmt = $conn->prepare($product_sql);

if (count($product_params) > 0) {
    $finalstmt->bind_param($product_types, ...$product_params);
}

$finalstmt->execute();
$products_result = $finalstmt->get_result();
$finalstmt->close();
?>

<div class="grid grid-cols-1 gap-4">
    <?php if ($products_result->num_rows > 0): ?>
        <?php while ($row = $products_result->fetch_assoc()): ?>
            <div class="border p-4 rounded-lg shadow-sm flex items-center">
                <div class="flex-1">
                    <h3 class="text-lg font-bold"><?= htmlspecialchars($row['product_name']) ?></h3>
                    <p class="text-gray-700">Seller: <?= htmlspecialchars($row['seller_name']) ?></p>
                    <p class="text-gray-700">Price: $<?= number_format($row['price'], 2) ?></p>
                    <p class="text-yellow-500">Rating:
                        <?= $row['rating_count'] > 0 ? number_format($row['rating_sum'] / $row['rating_count'], 2) : 'No ratings yet' ?>
                    </p>
                </div>
                <?php if ($user && $user['role'] == 'customer'): ?>
                    <form action="purchase.php" method="POST">
                        <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Buy</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No products found.</p>
    <?php endif; ?>
</div>
