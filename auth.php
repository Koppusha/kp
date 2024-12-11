<?php
include 'helpers.php';
require_once('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['email']) && isset($_POST['password'])) {
        try {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $sql = "SELECT u.id, u.name,u.password, u.is_active, ur.role_id AS role_id
                    FROM users u
                    JOIN user_roles ur ON u.id = ur.user_id
                    WHERE u.email = '$email' ";
            $stmt = $conn->prepare($sql);


            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($userId, $user_name,$hashedPassword, $is_active, $user_role);
                $stmt->fetch();

                if (password_verify($password, $hashedPassword)) {
                 
                        $_SESSION['user_id'] = $userId;
                        $_SESSION["role"] = $user_role;
                        $_SESSION["username"] = $user_name;
                        $_SESSION["is_active"] = $is_active;
        

                        switch ($user_role) {
                            case 1:
                                redirect("/admin_panel.php");
                                break;
                            case 2:
                                redirect("/moderator_dashboard.php");
                                break;
                            case 3:
                                redirect('/account.php');
                                break;
                            default:
                                $sql = "INSERT INTO user_roles VALUES ($new_user_id, 3)";
                                if ($conn->query($sql) === TRUE) {
                                    redirect('/account.php');
                                } else {
                                    echo "что-то с интернетом";
                                }
                                break;
                        }
                    
                } else {

                    setMessage("error", "Неправильный пароль!");
                    redirect("/");
                }
            } else {
                setMessage("error", "Пользователь не найден!");
                redirect("/");
            }

            $stmt->close();
        } catch (Exception $e) {
            echo ("Исключение $e было обработано");
        }
    }
}
