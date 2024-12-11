<?php
require_once 'db.php';  
include 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_application'])) {
    try {
        $offer_id = $_POST["offerId"];
        $amount = $_POST["amount"];
        $goal = $_POST["goal"];
        $period = $_POST["period"];
        $income = $_POST["salary"];
        $user_id = $_SESSION['user_id'];
        $status_id = 1;

        $sql = "INSERT INTO loan_applications (offer_id, user_id, amount, goal, period, user_salary, status_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Ошибка подготовки запроса: " . $conn->error);
        }


        $stmt->bind_param("iiisidi", $offer_id, $user_id, $amount, $goal, $period, $income, $status_id);

        if ($stmt->execute()) {
            echo "Заявка успешно добавлена.";
        } else {
            throw new Exception("Ошибка выполнения запроса: " . $stmt->error);
        }

        $stmt->close();
        $conn->close();

        redirect("credits.php");
        exit();
    } catch (Exception $e) {
        echo "Исключение обработано: " . $e->getMessage();
    }
}
