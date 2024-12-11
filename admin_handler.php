<?php
require_once 'db.php';  
include 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['block'])) {
    try {
        $accountId = $_POST['account_id'];
        $sql = "UPDATE users SET is_active = 0 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $accountId);

        if ($stmt->execute()) {
        } else {
            echo "Ошибка при удалении счета: " . $conn->error;
        }

        $stmt->close();
        $conn->close();

        redirect("admin_panel.php");
        exit();
    } catch (Exception $e) {
        echo ("Исключение $e было обработано");
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['unlock'])) {

    try {
        $accountId = $_POST['account_id'];
        $sql = "UPDATE users SET is_active = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $accountId);

        if ($stmt->execute()) {
        } else {
            echo "Ошибка при удалении счета: " . $conn->error;
        }

        $stmt->close();
        $conn->close();

        redirect("admin_panel.php");
        exit();
    } catch (Exception $e) {
        echo ("Исключение $e было обработано");
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_edit'])) {
    try {
        $accountId = $_POST['account_id'];
        $newAccountName = $_POST['edit-username'];
        $newAccountEmail = $_POST['edit-email'];
        $newAccountRole = $_POST['edit-role'];

        $conn->begin_transaction();

        $sql1 = "UPDATE users SET name = ?, email = ? WHERE id = ?";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("ssi", $newAccountName, $newAccountEmail, $accountId);
        $stmt1->execute();

        $sql2 = "UPDATE user_roles SET role_id = ? WHERE user_id = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("ii", $newAccountRole, $accountId);
        $stmt2->execute();

        $conn->commit();

        echo "Данные пользователя успешно обновлены.";

        $stmt1->close();
        $stmt2->close();
        $conn->close();

        redirect("admin_panel.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo ("Исключение: " . $e->getMessage());
    }
}
