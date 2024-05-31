<?php
require 'config.php';

// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

// Проверка, является ли пользователь покупателем
if ($user['role'] !== 'customer') {
    echo "Only customers can purchase products.";
    exit();
}

// Проверка наличия ID товара в запросе
if (!isset($_POST['product_id'])) {
    echo "Product ID not provided.";
    exit();
}

$product_id = $_POST['product_id'];

// Получение информации о товаре
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Product not found.";
    exit();
}


$sql = "CALL create_order(?, ?)";
$stmt = $conn->prepare($sql);
echo($user["id"]);
$stmt->bind_param("ii", $user['id'], $product_id);
try {
    $stmt->execute();
    $_SESSION["success"] = "вы успешно купили товар";
    header("Location: my_orders.php");
} catch (\Throwable $th) {
    $_SESSION["error"] = $th->getMessage();
    header("Location: " . (isset($_POST['redirect_uri']) ? $_POST['redirect_uri'] : "index.php"));
}


exit();
