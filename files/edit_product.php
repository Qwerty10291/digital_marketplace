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

// Проверка наличия ID товара в запросе
if (!isset($_GET['id'])) {
    echo "Product ID not provided.";
    exit();
}

// Получение ID товара из запроса
$product_id = $_GET['id'];

// Проверка, принадлежит ли товар текущему продавцу
$sql = "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Product not found.";
    exit();
}

$product = $result->fetch_assoc();

if ($product['user_id'] !== $user['id']) {
    echo "You are not authorized to edit this product.";
    exit();
}

// Обработка отправки формы редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];

    $sql = "UPDATE products SET category_id = ?, name = ?, description = ?, price = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issdi", $category_id, $name, $description, $price, $product_id);
    $stmt->execute();

    header("Location: my_products.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<header class="bg-blue-600 text-white p-4 flex justify-between items-center">
    <h1 class="text-2xl">DigitalMarketplace</h1>
    <div>
        <span class="mr-4">Hello, <?= htmlspecialchars($user['name']) ?>!</span>
        <a href="index.php" class="mr-4">Home</a>
        <a href="logout.php" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Logout</a>
    </div>
</header>

<div class="container mx-auto mt-10">
    <h2 class="text-2xl mb-4">Edit Product</h2>
    <form action="" method="POST" class="bg-white p-4 shadow rounded">
        <div class="mb-4">
            <label for="name" class="block text-gray-700 font-bold mb-2">Name:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="mb-4">
            <label for="description" class="block text-gray-700 font-bold mb-2">Description:</label>
            <textarea id="description" name="description" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?= htmlspecialchars($product['description']) ?></textarea>
        </div>
        <div class="mb-4">
            <label for="price" class="block text-gray-700 font-bold mb-2">Price:</label>
            <input type="number" id="price" name="price" value="<?= htmlspecialchars($product['price']) ?>" step="0.01" min="0" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="mb-4">
            <label for="category_name" class="block text-gray-700 font-bold mb-2">Category:</label>
            <input type="text" id="category_name" name="category_name" value="<?= htmlspecialchars($product['category_name']) ?>" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <input type="hidden" id="category_id" value="<?= htmlspecialchars($product['category_id']) ?>" name="category_id">
            <div id="category-results" class="bg-white border border-gray-300 mt-1 rounded shadow"></div>
        </div>
        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Save</button>
        <a href="my_products.php" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Cancel</a>
    </form>
</div>
<script src="search_categories.js"></script>
</body>
</html>
