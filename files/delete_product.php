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
$sql = "SELECT user_id FROM products WHERE id = ?";
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
    echo "You are not authorized to delete this product.";
    exit();
}

// Удаление товара
$sql = "DELETE FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();

// Перенаправление на страницу со списком товаров продавца
header("Location: my_products.php");
exit();
