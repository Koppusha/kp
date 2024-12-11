<?php
//include 'helpers.php';
require_once 'db.php'; // Подключение к базе данных

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_account'])) {
    $accountId = $_POST['account_id'];
    // Удаление счета из базы данных
    $sql = "DELETE FROM account WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $accountId);

    if ($stmt->execute()) {
        echo "Счет успешно удален.";
    } else {
        echo "Ошибка при удалении счета: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

    // Перенаправление на страницу со списком счетов
    header("Location: account.php");
    exit();
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_account'])) {
    $accountId = $_POST['account_id'];
    $newAccountName = $_POST['new_account_name'];

    // Обновляем название счёта в базе данных
    $sql = "UPDATE account SET name = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $newAccountName, $accountId);

    if ($stmt->execute()) {
        echo "Название счёта успешно изменено.";
    } else {
        echo "Ошибка при изменении названия счета: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

    // Перенаправление на страницу со списком счетов
    header("Location: account.php");
    exit();

}
