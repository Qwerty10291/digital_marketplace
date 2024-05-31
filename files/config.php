<?php
$servername = "db";
$username = "root";
$password = "root";
$dbname = "shop";

// Создаем соединение
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверяем соединение
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Получаем пользователя (например, по сессии)
session_start();
$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
if ($user !== null) {
    $sql = 'SELECT balance FROM users where id = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $_SESSION['user']['balance'] = $result->fetch_assoc()['balance'];
    $stmt->close();
}

// if (isset($_SESSION['success'])) {
//     echo '<div class="bg-green-500 text-white p-4">' . $_SESSION['success'] . '</div>';
//     unset($_SESSION['success']);
// }
// if (isset($_SESSION['error'])) {
//     echo '<div class="bg-red-500 text-white p-4">' . $_SESSION['error'] . '</div>';
//     unset($_SESSION['error']);
// }