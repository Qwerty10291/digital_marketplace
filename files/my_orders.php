<?php
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'customer') {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];

// Получаем заказы пользователя
$sql = "SELECT o.id, o.price, o.status, p.name AS product_name, u.name AS seller_name 
        FROM orders o 
        JOIN products p ON o.product_id = p.id 
        JOIN users u ON o.seller_id = u.id 
        WHERE o.customer_id = ?
        ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <header class="bg-blue-600 text-white p-4 flex justify-between items-center">
        <h1 class="text-2xl"><a href="index.php">DigitalMarketplace</a></h1>
        <div>
            <span class="mr-4">Hello, <?= htmlspecialchars($user['name']) ?>!</span>
            <a href="my_orders.php" class="mr-4">My Orders</a>
            <a href="logout.php" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Logout</a>
        </div>
    </header>
    <div class="container mx-auto mt-10">
        <h2 class="text-2xl mb-4">My Orders</h2>
        <div class="bg-white p-4 rounded-lg shadow">
            <?php if ($result->num_rows > 0): ?>
                <table class="w-full table-auto">
                    <thead>
                        <tr>
                            <th class="px-4 py-2">Product Name</th>
                            <th class="px-4 py-2">Seller</th>
                            <th class="px-4 py-2">Price</th>
                            <th class="px-4 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="border px-4 py-2"><?= htmlspecialchars($row['product_name']) ?></td>
                                <td class="border px-4 py-2"><?= htmlspecialchars($row['seller_name']) ?></td>
                                <td class="border px-4 py-2">$<?= number_format($row['price'], 2) ?></td>
                                <td class="border px-4 py-2"><?= htmlspecialchars($row['status']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No orders found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
