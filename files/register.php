<?php
require 'config.php';
require 'utils.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];
    if ($role != "customer" && $role != "seller") {
        die(403);
    }

    // Проверка на уникальность email
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error = "Email already exists!";
    } else {
        // Вставка нового пользователя
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $password, $role);

        if ($stmt->execute()) {
            $_SESSION['user'] = ['name' => $name, 'email' => $email, 'role' => $role];
            header("Location: index.php");
            exit();
        } else {
            $error = "Registration failed!";
        }
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
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex justify-center items-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-1/3">
        <h2 class="text-2xl mb-4">Register</h2>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 text-red-700 p-4 mb-4 rounded">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <form action="register.php" method="post">
            <div class="mb-4">
                <label for="name" class="block text-gray-700">Name</label>
                <input type="text" name="name" id="name" class="w-full border-gray-300 rounded-md" required>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700">Email</label>
                <input type="email" name="email" id="email" class="w-full border-gray-300 rounded-md" required>
            </div>
            <div class="mb-4">
                <label for="role" class="block text-gray-700">Role</label>
                <select name="role" id="role">
                    <option value="customer" selected="selected">Покупатель</option>
                    <option value="seller">Продавец</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700">Password</label>
                <input type="password" name="password" id="password" class="w-full border-gray-300 rounded-md" required>
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Register</button>
        </form>
    </div>
</body>
</html>
