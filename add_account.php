<?php
include 'db.php';
var_dump($_POST); // Это покажет, что именно отправляется через форму


// Обработка создания нового счета
if ($_SERVER["REQUEST_METHOD"] == "POST") {
   // Получаем данные из формы и проверяем, что они не пустые
   $account_name = isset($_POST['account_name']) ? trim($_POST['account_name']) : null;
   $currency = isset($_POST['currency']) ? trim($_POST['currency']) : null;

   // Проверка, что поля не пустые
   if (!empty($account_name) && !empty($currency)) {
       // SQL-запрос для добавления нового счета
       $sql = "INSERT INTO account (name, currency, balance) VALUES (?, ?, 0)";

       // Подготовка SQL-запроса с использованием подготовленного выражения
       $stmt = $conn->prepare($sql);
       $stmt->bind_param("ss", $account_name, $currency);

       // Выполнение запроса
       if ($stmt->execute()) {
           // Перенаправление на страницу списка счетов
           header("Location: account.php");
           exit(); // Завершаем выполнение скрипта после редиректа
       } else {
           echo "Ошибка: " . $conn->error;
       }
   } else {
       echo "Пожалуйста, заполните все поля формы.";
   }
}
// Обработка удаления счета
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM account WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: accounts.php");
    exit();
}

// Получение списка счетов
$sql = "SELECT * FROM account";
$result = $conn->query($sql);
