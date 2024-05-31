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

// Обработка отправки формы редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    $sql = "INSERT INTO products (user_id, name, description, price) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issd", $user["id"], $name, $description, $price);
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
    <title>New Product</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
    <h2 class="text-2xl mb-4">New Product</h2>
    <form action="" method="POST" class="bg-white p-4 shadow rounded">
        <div class="mb-4">
            <label for="name" class="block text-gray-700 font-bold mb-2">Name:</label>
            <input type="text" id="name" name="name" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="mb-4">
            <label for="description" class="block text-gray-700 font-bold mb-2">Description:</label>
            <textarea id="description" name="description" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
        </div>
        <div class="mb-4">
            <label for="price" class="block text-gray-700 font-bold mb-2">Price:</label>
            <input type="number" id="price" name="price" step="0.01" min="0" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Create</button>
        <a href="seller_products.php" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Cancel</a>
    </form>
</div>

</body>
</html>