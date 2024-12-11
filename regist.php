<?php
include 'helpers.php';
require_once('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['email']) && isset($_POST['password'])) {
        try {
            $username = $_POST['name'];
            $email = $_POST['email'];
            $password = $_POST['password'];

            $check_user_sql = "SELECT * FROM Users WHERE email = '$email' ";
            $check_user = $conn->query($check_user_sql);

            if ($check_user->num_rows == 0) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $sql = "INSERT INTO Users (name, email, password) Values ('$username', '$email', '$hashedPassword')";

                if ($conn->query($sql) === TRUE) {
                    $new_user_id = $conn->insert_id;
                    $sql = "INSERT INTO user_roles VALUES ($new_user_id, 3)";
                    if ($conn->query($sql) === TRUE) {
                        $_SESSION["user_id"] = $new_user_id;
                        $_SESSION["role"] = 3;
                        $_SESSION["username"] = $username;
                        $_SESSION["greeting"] = true;
                        redirect('/account.php');
                    }
                } else {
                    echo "Error " . $conn->error;
                }

                $conn->close();
            } else {
                setMessage('errorRegist', "Такой пользователь уже есть");
                redirect('/account.php');
            }
        } catch (Exception $e) {
            echo ("Исключение $e было обработано");
        }
    }
}
