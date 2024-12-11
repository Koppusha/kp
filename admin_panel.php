<?php

include 'helpers.php';
require_once("db.php");
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    redirect("/index.php");
    exit();
}

$id = $_SESSION['user_id'];
$get_users_sql = "SELECT * FROM users 
JOIN user_roles 
ON users.id = user_roles.user_id 
JOIN roles 
ON roles.id = user_roles.role_id
WHERE users.id != $id";


try {
    $users_result = $conn->query($get_users_sql);
} catch (Exception $e) {
    echo ("Исключение $e было обработано");
}


$get_roles_sql = "SELECT * FROM roles";
try {
    $roles_result = $conn->query($get_roles_sql);
} catch (Exception $e) {
    echo ("Исключение $e было обработано");
}

?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель Администратора</title>
    <link rel="stylesheet" href="admin_panel.css">
</head>

<body>
    <a href="/">Выход</a>
    <div class="admin-container">
        <nav class="admin-nav">
            <h2>Панель Администратора</h2>
            <ul>
                <li><a href="#users">Управление пользователями</a></li>
            </ul>
        </nav>

        <div class="admin-content">


            <section id="users" class="admin-section">
                <h2>Управление пользователями</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Имя пользователя</th>
                            <th>Email</th>
                            <th>Роль</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($users_result->num_rows > 0) {
                            while ($row = $users_result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlentities($row['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['role_name']) . "</td>";
                                if ($row['is_active']) {
                                    echo "<td>Активен</td>";
                                } else {
                                    echo "<td>Заблокирован</td>";
                                }
                                echo "<td>
                                <button id=\"edit-btn\" class=\"edit-btn\"
                                    onClick=\"openEditModal('" . $row['user_id'] . "','" . htmlspecialchars($row['name']) . "', '" . htmlspecialchars($row['email']) . "', '" . htmlspecialchars($row['role_id']) . "')\">
                                    Редактировать пользователя
                                </button>
                            <form action = 'admin_handler.php' method='POST'>
                               <input type='hidden' name='account_id' value='" . $row['user_id'] . "'>
                               ";
                                if ($row['is_active']) {
                                    echo " <button type='submit' name='block' class=\"delete-btn\">Заблокировать</button>";
                                } else {
                                    echo " <button type='submit' name='unlock' class=\"unlock-btn\">Разблокировать</button>";
                                }
                                echo "</form>
                            </td>";

                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>

                <div id="addUserModal" class="modal">

                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Добавить пользователя</h2>

                        <form id="addUserForm">
                            <div class="form-group">
                                <label for="username">Имя пользователя</label>
                                <input type="text" id="username" name="username" placeholder="Введите имя" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" placeholder="Введите email" required>
                            </div>
                            <div class="form-group">
                                <label for="role">Роль</label>
                                <select id="role" name="role" required>
                                    <option value="" disabled selected>Выберите роль</option>
                                    <option value="user">Пользователь</option>
                                    <option value="admin">Администратор</option>
                                    <option value="moderator">Модератор</option>
                                </select>
                            </div>
                            <button type="submit" class="btn">Добавить</button>
                        </form>
                    </div>
                </div>


                <div id="editUserModal" class="modal">

                    <div class="modal-content">
                        <span onClick="closeEditModal()" class="close">&times;</span>
                        <h2>Редактировать пользователя</h2>


                        <form id="editUserModal" action="admin_handler.php" method="POST">
                            <input type='hidden' name='account_id' id='account_id'>
                            <div class="form-group">
                                <label for="edit-username">Имя пользователя</label>
                                <input type="text" id="edit-username" name="edit-username" placeholder="Введите имя" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-email">Email</label>
                                <input type="email" id="edit-email" name="edit-email" placeholder="Введите email" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-role">Роль</label>
                                <select id="edit-role" name="edit-role" required>
                                    <?php
                                    if ($roles_result->num_rows > 0) {
                                        while ($row = $roles_result->fetch_assoc()) {
                                            echo "<option value='{$row['id']}'>{$row['role_name']}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <button type="submit" name="save_edit" class="btn">Сохранить изменения</button>


                        </form>
                    </div>
                </div>


            </section>


        </div>
    </div>


    <script>
        const modal = document.getElementById("editUserModal");
        function openEditModal(userId, userName, email, role) {
            document.getElementById('account_id').value = userId;
            document.getElementById('edit-username').value = userName; 
            document.getElementById('edit-email').value = email;
            document.getElementById('edit-role').value = role;
            document.getElementById('editUserModal').style.display = 'block'; 
        }

        function closeEditModal() {
            document.getElementById('editUserModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                closeEditModal();
            }
        }
    </script>
</body>

</html>

