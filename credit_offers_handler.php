<?php
require_once 'db.php';  
include 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_credit_deal'])) {
    try {
       
        $loan_name = $_POST['loanName'];  
        $rate = $_POST['interestRate'];
        $max_amount = $_POST['maxAmount'];
        $period = $_POST['repaymentTerm'];

        $stmt = $conn->prepare("CALL AddCreditOffer(?, ?, ?, ?, ?)");
        $stmt->bind_param("isddi", $_SESSION['user_id'], $loan_name, $rate, $period, $max_amount);


        if ($stmt->execute()) {
            redirect("/moderator_dashboard.php");
        } else {
            echo "Ошибка: " . $conn->error;
        }
    } catch (Exception $e) {
        echo "Исключение: " . $e->getMessage() . " обработано";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_deal'])) {
    try {
        $offer_id = $_POST['credit_deal_id'];
        $sql = "CALL DeleteCreditOffer(?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $offer_id);

        if ($stmt->execute()) {
            echo "Счет успешно удален.";
        } else {
            echo "Ошибка при удалении счета: " . $conn->error;
        }

        $stmt->close();
        $conn->close();

        redirect("moderator_dashboard.php");
        exit();
    } catch (Exception $e) {
        echo ("Исключение $e было обработано");
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve'])) {
    $loan_id = $_POST['loan_id'];
    if (updateTable($conn, "loan_applications", ['status_id'], [2], $loan_id)) {
        redirect("moderator_dashboard.php");
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reject'])) {
    $loan_id = $_POST['loan_id'];
    if (updateTable($conn, "loan_applications", ['status_id'], [3], $loan_id)) {
        redirect("moderator_dashboard.php");
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply_edit'])) {
    $offer_id = $_POST['offer_id'];
    $name = $_POST['editloanName'];
    $rate = $_POST['editInterestRate'];
    $period = $_POST['editRepaymentTerm'];
    $max_amount = $_POST['editAmount'];

    if (updateTable($conn, "credit_offers", ['name', 'rate', 'period', 'max_amount'], [$name, $rate, $period, $max_amount], $offer_id)) {
        redirect("moderator_dashboard.php");
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply_weights'])) {
    try {
        $rate_weight = $_POST['rate_weight'];
        $period_weight = $_POST['period_weight'];
        $amount_weight = $_POST['amount_weight'];

 
        $stmt = $conn->prepare("CALL update_coefficients(?,?,?, ?)");
        $stmt->bind_param("dddi", $rate_weight, $period_weight, $amount_weight, $_SESSION['user_id']);


        if ($stmt->execute()) {
            redirect("/moderator_dashboard.php");
        } else {
            echo "Ошибка: " . $conn->error;
        }
    } catch (Exception $e) {
        echo "Исключение: " . $e->getMessage() . " обработано";
    }
}
