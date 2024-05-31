<?php
require 'config.php'; // Файл с настройками подключения к базе данных
require 'utils.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Проверка email и пароля
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = ['name' => $user['name'], 'email' => $user['email'], 'role' => $user['role'], 'id' => $user['id']];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
} else {
    if (isset($user)) {
        redirect("index.php");
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex justify-center items-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-1/3">
        <h2 class="text-2xl mb-4">Login</h2>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 text-red-700 p-4 mb-4 rounded">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <form action="login.php" method="post">
            <div class="mb-4">
                <label for="email" class="block text-gray-700">Email</label>
                <input type="email" name="email" id="email" class="w-full border-gray-300 rounded-md" required>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700">Password</label>
                <input type="password" name="password" id="password" class="w-full border-gray-300 rounded-md" required>
            </div>
            <div class="mb-4 flex justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Login</button>
                <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"><a href="register.php">Register</a></button>
            </div>
        </form>
    </div>
</body>
</html>
