<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float) $_POST['amount'];
    $payment_method = $_POST['payment_method'];
    $transaction_type = $_POST['transaction_type'];

    if ($transaction_type === 'deposit') {
        $new_balance = $user['balance'] + $amount;
    } elseif ($transaction_type === 'withdraw') {
        $new_balance = $user['balance'] - $amount;
    } else {
        header('Location: index.php');
        exit;
    }

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
        $stmt->bind_param("di", $new_balance, $user['id']);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, payment_system, status) VALUES (?, ?, ?, ?, 'completed')");
        $stmt->bind_param("isds", $user['id'], $transaction_type, $amount, $payment_method);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        $_SESSION['success'] = 'Transaction successful.';
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }

    header('Location: index.php');
}
