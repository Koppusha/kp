<?php
include 'helpers.php';
require_once('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['email']) && isset($_POST['password'])) {
        $username = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        $check_user_sql = "SELECT * FROM User WHERE email = '$email' ";
        $check_user = $conn->query($check_user_sql);

        if ($check_user->num_rows == 0) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO User (name, email, password) Values ('$username', '$email', '$hashedPassword')";

            if ($conn->query($sql) === TRUE) {
                redirect('/account.php');
            } else {
                echo "Error " . $conn->error;
            }

            $conn->close();
        } else {
            setMessage('errorRegist', "Такой пользователь уже есть");
            redirect('/account.php');
            
        }
    }
}
