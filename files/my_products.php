<?php
require 'config.php';

// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Проверка, является ли пользователь продавцом
$user = $_SESSION['user'];
if ($user['role'] !== 'seller') {
    echo "Access denied. Only sellers can access this page.";
    exit();
}

// Получение ID продавца из сессии
$seller_id = $user['id'];

// Подготовка и выполнение запроса для получения товаров продавца
$sql = "SELECT p.id, p.name, p.description, p.price, 
               p.rating_sum, p.rating_count 
        FROM products p 
        WHERE p.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$products_result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Products</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
        integrity="sha512-Fo3rlrZj/kTc1Ff2peAn43fGRsU3BZWNbb/sm4p3y3V3GpcWmld+Ar6e1zQMBzJ8XTCpSp62mSB9REya/A1XAA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <script>
        function confirmDeletion(productId) {
            if (confirm('Are you sure you want to delete this product?')) {
                window.location.href = 'delete_product.php?id=' + productId;
            }
        }
    </script>
</head>

<body class="bg-gray-100">

    <header class="bg-blue-600 text-white p-4 flex justify-between items-center">
        <h1 class="text-2xl">Marketplace</h1>
        <div>
            <span class="mr-4">Hello, <?= htmlspecialchars($user['name']) ?>!</span>
            <a href="index.php" class="mr-4">Home</a>
            <a href="logout.php" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Logout</a>
        </div>
    </header>

    <div class="container mx-auto mt-10">
        <div class="flex justify-between">
            <h2 class="text-2xl mb-4">My Products</h2>
            <a href="new_product.php">
                <button class="bg-green-400 hover:bg-green-500 text-white font-bold py-2 px-4 rounded mb-2">
                    New
                </button>
            </a>
        </div> 
        <div class="bg-white p-4 shadow rounded">
            <?php if ($products_result->num_rows > 0): ?>
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2">Name</th>
                            <th class="py-2">Description</th>
                            <th class="py-2">Price</th>
                            <th class="py-2">Rating</th>
                            <th class="py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = $products_result->fetch_assoc()): ?>
                            <tr>
                                <td class="border px-4 py-2"><?= htmlspecialchars($product['name']) ?></td>
                                <td class="border px-4 py-2"><?= htmlspecialchars($product['description']) ?></td>
                                <td class="border px-4 py-2">$<?= number_format($product['price'], 2) ?></td>
                                <td class="border px-4 py-2">
                                    <?= $product['rating_count'] > 0 ? number_format($product['rating_sum'] / $product['rating_count'], 2) : 'No ratings yet' ?>
                                </td>
                                <td class="border px-4 py-2 text-center">
                                    <a href="edit_product.php?id=<?= $product['id'] ?>" class="text-blue-500">
                                        <span class="material-symbols-outlined">
                                            edit
                                        </span>
                                    </a>
                                    <a onclick="confirmDeletion(<?= $product['id'] ?>);" class="text-red-500 ml-2">
                                        <span class="material-symbols-outlined">
                                            delete
                                        </span>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No products found.</p>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>