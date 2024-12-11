<?php
include 'helpers.php';
require_once('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $sql = "SELECT id, password FROM user WHERE email = '$email' ";
        $stmt = $conn->prepare($sql);

       
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($userId, $hashedPassword);
            $stmt->fetch();

            // Проверяем, совпадает ли введённый пароль с хэшированным
            if (password_verify($password, $hashedPassword)) {
                redirect("/account.php");
            } else {
            
                setMessage("error", "Неправильный пароль!");
                redirect("/");
            }
        } else {
            setMessage("error", "Пользователь не найден!");
            redirect("/");
        }

        $stmt->close();
    }
}
