<?php
require_once 'db.php'; 
include 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_account'])) {
    try {
        $accountId = $_POST['account_id'];
        $sql = "DELETE FROM accounts WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $accountId);

        if ($stmt->execute()) {
            echo "Счет успешно удален.";
        } else {
            echo "Ошибка при удалении счета: " . $conn->error;
        }

        $stmt->close();
        $conn->close();

        redirect("account.php");
        exit();
    } catch (Exception $e) {
        echo ("Исключение $e было обработано");
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_account'])) {
    try {
        $accountId = $_POST['account_id'];
        $newAccountName = $_POST['new_account_name'];

        $sql = "UPDATE accounts SET name = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $newAccountName, $accountId);

        if ($stmt->execute()) {
            echo "Название счёта успешно изменено.";
        } else {
            echo "Ошибка при изменении названия счета: " . $conn->error;
        }

        $stmt->close();
        $conn->close();

        redirect("account.php");
        exit();
    } catch (Exception $e) {
        echo ("Исключение $e было обработано");
    }
}

