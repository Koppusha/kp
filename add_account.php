<?php
require_once 'db.php';  
include 'helpers.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $account_name = isset($_POST['account_name']) ? trim($_POST['account_name']) : null;
        $currency = isset($_POST['currency']) ? trim($_POST['currency']) : null;
        $user_id = $_SESSION['user_id'];

        if (!empty($account_name) && !empty($currency) && !empty($user_id)) {
            $sql = "INSERT INTO accounts (name, currency, balance) VALUES (?, ?, 0)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $account_name, $currency);

            if ($stmt->execute()) {
                $account_id = $conn->insert_id;

                $sql_link = "INSERT INTO user_accounts (user_id, account_id) VALUES (?, ?)";
                $stmt_link = $conn->prepare($sql_link);
                $stmt_link->bind_param("ii", $user_id, $account_id);

                if ($stmt_link->execute()) {
                    redirect("account.php");
                    exit(); 
                } else {
                    echo "Ошибка при добавлении связи пользователя с счётом: " . $conn->error;
                }
            } else {
                echo "Ошибка: " . $conn->error;
            }
        } else {
            echo "Пожалуйста, заполните все поля формы.";
        }
    } catch (Exception $e) {
        echo "Исключение $e было обработано";
    }
}

try {
    $sql = "SELECT * FROM accounts 
            JOIN user_accounts 
            ON user_account.account_id = accounts.id 
            WHERE user_account.user_id = $user_id";
    $result = $conn->query($sql);
} catch (Exception $e) {
    echo "Исключение $e было обработано";
}
