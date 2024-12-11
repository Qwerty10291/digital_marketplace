<?php
require 'config.php';

// Получаем пользователя
$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;

// Получаем категории
$parent_id = isset($_GET['category_id']) ? (int) $_GET['category_id'] : NULL;
$category_sql = "SELECT c.id, c.name, c.parent_id, COUNT(p.id) AS products_count 
                 FROM categories c 
                 LEFT JOIN products p ON c.id = p.category_id 
                 WHERE c.parent_id IS NULL
                 GROUP BY c.id";

if ($parent_id !== NULL) {
    $category_sql = "SELECT c.id, c.name, c.parent_id, COUNT(p.id) AS products_count 
                     FROM categories c 
                     LEFT JOIN products p ON c.id = p.category_id 
                     WHERE c.parent_id = ? 
                     GROUP BY c.id";
}

$stmt = $conn->prepare($category_sql);

if ($parent_id !== NULL) {
    $stmt->bind_param("i", $parent_id);
}

$stmt->execute();
$categories_result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DigitalMarketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <header class="bg-blue-600 text-white p-4 flex justify-between items-center">
        <h1 class="text-2xl"><a href="index.php">DigitalMarketplace</a></h1>
        <div>
            <?php if ($user): ?>
                <span class="mr-4">Hello, <?= htmlspecialchars($user['name']) ?>!</span>
                <span class="mr-4">Balance: $<?= number_format($user['balance'], 2) ?></span>
                <?php if ($user['role'] == 'customer'): ?>
                    <a href="my_orders.php" class="mr-4">My Purchases</a>
                    <button id="depositButton"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Deposit</button>
                <?php elseif ($user['role'] == 'seller'): ?>
                    <a href="my_products.php" class="mr-4">My Products</a>
                    <button id="withdrawButton"
                        class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">Withdraw</button>
                <?php endif; ?>
                <a href="logout.php" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Logout</a>
            <?php else: ?>
                <a href="login.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Login</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="container mx-auto flex mt-10">
    <aside class="w-1/4 bg-white p-4 shadow">
            <h2 class="text-xl mb-4">Filters</h2>
            <form id="filterForm">
                <div class="mb-4">
                    <label for="search" class="block text-gray-700">Search</label>
                    <input type="text" name="search" id="search" class="w-full border-gray-300 rounded-md"
                        placeholder="Enter product name"
                        value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                </div>
                <div class="mb-4">
                    <label for="min_price" class="block text-gray-700">Min Price</label>
                    <input type="number" name="min_price" id="min_price" class="w-full border-gray-300 rounded-md"
                        value="<?= isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : '' ?>">
                </div>
                <div class="mb-4">
                    <label for="max_price" class="block text-gray-700">Max Price</label>
                    <input type="number" name="max_price" id="max_price" class="w-full border-gray-300 rounded-md"
                        value="<?= isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : '' ?>">
                </div>
                <div class="mb-4">
                    <label for="sort" class="block text-gray-700">Sort by</label>
                    <select name="sort" id="sort" class="w-full border-gray-300 rounded-md">
                        <option value="price_asc" <?= isset($_GET['sort']) && $_GET['sort'] == 'price_asc' ? 'selected' : '' ?>>Price (low to high)</option>
                        <option value="price_desc" <?= isset($_GET['sort']) && $_GET['sort'] == 'price_desc' ? 'selected' : '' ?>>Price (high to low)</option>
                        <option value="date_asc" <?= isset($_GET['sort']) && $_GET['sort'] == 'date_asc' ? 'selected' : '' ?>>Date (oldest first)</option>
                        <option value="date_desc" <?= isset($_GET['sort']) && $_GET['sort'] == 'date_desc' ? 'selected' : '' ?>>Date (newest first)</option>
                    </select>
                </div>
                <input type="hidden" name="category_id" value="<?= isset($_GET['category_id']) ? htmlspecialchars($_GET['category_id']) : '' ?>">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Apply</button>
            </form>
            <h2 class="text-xl mt-6 mb-4">Categories</h2>
            <button id="backButton" class="bg-gray-300 hover:bg-gray-400 text-black font-bold py-2 px-4 rounded mb-2" style="display: none;">Back</button>
            <ul id="categoriesList"></ul>
        </aside>
        <main class="w-3/4 bg-white p-4 shadow">
            <h2 class="text-xl mb-4">Products</h2>
            <div id="products-container" class="grid grid-cols-1 gap-4">
                <!-- Products will be loaded here via AJAX -->
            </div>
        </main>
    </div>
    <div id="transactionModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-75 flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 id="modalTitle" class="text-xl mb-4">Transaction</h2>
            <form id="transactionForm" method="POST" action="transaction.php">
                <div class="mb-4">
                    <label for="amount" class="block text-gray-700">Amount</label>
                    <input type="number" name="amount" id="amount" class="w-full border-gray-300 rounded-md" required>
                </div>
                <div class="mb-4">
                    <label for="payment_method" class="block text-gray-700">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="w-full border-gray-300 rounded-md"
                        required>
                        <option value="credit_card">Credit Card</option>
                        <option value="paypal">PayPal</option>
                    </select>
                </div>
                <input type="hidden" name="transaction_type" id="transaction_type">
                <button type="submit" id="modalButton"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Submit</button>
            </form>
            <button id="closeModalButton"
                class="mt-4 bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Close</button>
        </div>
    </div>
    <script>
        <?php if (isset($_SESSION["error"])): ?>
            alert("<?= $_SESSION["error"] ?>")
            <?php unset($_SESSION["error"]); ?>
        <?php endif; ?>
    </script>
    <script src="modal.js"></script>
    <script src="filter.js"></script>
</body>

</html>