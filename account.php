<?php
require_once 'db.php';  
include 'helpers.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 3) {
    redirect("/index.php");
    exit();
} else if ($_SESSION["is_active"] == false) {
    redirect("/account_blocked.php");
    exit();
}

$shouldShowGreeting = isset($_SESSION['greeting']) && $_SESSION['greeting'] === true;
$username = $_SESSION['username'] ?? '';
if (isset($_SESSION['greeting'])) {
    $_SESSION['greeting'] = false;
}

try {
    $currency_sql = "SELECT * FROM currency";
    $currency_result = $conn->query($currency_sql);
} catch (Exception $e) {
    echo ("Исключение $e было обработано");
}
$user_id = $_SESSION['user_id'];

$sql = "SELECT accounts.id, accounts.name, accounts.currency, accounts.balance 
        FROM accounts
        INNER JOIN user_accounts ON accounts.id = user_accounts.account_id
        WHERE user_accounts.user_id = $user_id";
        
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $search_text = isset($_POST['search']) ? trim($_POST['search']) : "";
    $sql .= " AND name LIKE '%$search_text%' OR currency LIKE '%$search_text%'";
}
try {
    $result = $conn->query($sql);
} catch (Exception $e) {
    echo ("Исключение $e было обработано");
}


$notification_sql = "CALL MarkNotificationsAsRead($user_id)";
try {
    $notification_result = $conn->query($notification_sql);
} catch (Exception $e) {
    echo ("Исключение $e было обработано");
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление банковскими счетами</title>
    <link rel="stylesheet" href="account.css">
</head>

<body>

    <?php include 'custom_alert.html'; ?>
    <script src="show_alert.js"></script>

    <?php
    if ($notification_result->num_rows > 0) {
        $notifications = [];

        while ($row = $notification_result->fetch_assoc()) {
            $notifications[] = [
                'title' => $row['title'],
                'message' => $row['message']
            ];
        }


        echo "<script>";
        echo "let notifications = " . json_encode($notifications) . ";";
        echo "showAllNotifications(notifications);";
        echo "</script>";
    }
    ?>

    <div class="header-container">
        <a href="/credits.php" class="menu-button">Мои кредиты</a>
        <a href="/profile.php" class="menu-button">Профиль</a>
        <a href="logout.php" class="logout-button">Выход</a>
    </div>

    <?php if ($shouldShowGreeting): ?>
        <div id="welcome-dialog" class="dialog">
            <div class="dialog-content">
                <h2>Добро пожаловать!</h2>
                <p>Рады видеть вас, <?php echo htmlspecialchars($username); ?>!</p>
                <button id="close-dialog">Ок</button>
            </div>
        </div>
    <?php endif; ?>


    <div class="container">
        <h1>Мои банковские счета</h1>


        <div class="account-creation">
            <h2>Создать новый счет</h2>
            <form id="create-account-form" action="add_account.php" method="POST">
                <label for="account-name">Название счета</label>
                <input type="text" id="account-name" name="account_name" placeholder="Введите название счета" required>

                <label for="currency">Тип валюты</label>
                <select id="currency" name="currency" required>
                    <option value="" disabled selected>Выберите валюту</option>
                    <?php
                    if ($currency_result->num_rows > 0) {
                        while ($row = $currency_result->fetch_assoc()) {
                            echo "<option value='{$row['currency']}'>{$row['name']}</option>";
                        }
                    }
                    ?>

                </select>

                <button name="create_account" type="submit">Создать счет</button>
            </form>
        </div>


        <div class="account-search">
            <h2>Поиск счета</h2>
            <form method="POST">
                <input type="text" id="search-account" name="search" placeholder="Введите название счета или валюту...">
                <button type="submit">Искать</button>
            </form>
        </div>



        <div class="account-list">
            <h2>Счета</h2>
            <ul id="accounts">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<li>";
                        echo "<div class='account-card'>";

                        echo "<div class='account-info'>";
                        echo "<h3 class='account-name'>Название счета: " . htmlspecialchars($row['name']) . "</h3>";
                        echo "<p class='account-currency'>Валюта: <strong>" . htmlspecialchars($row['currency']) . "</strong></p>";
                        echo "</div>";

                        echo "<div class='account-actions'>";

                        echo "<button class='edit-btn' onclick='openEditModal(" . $row['id'] . ", \"" . htmlspecialchars($row['name']) . "\")'><i class='fa fa-pencil-alt'></i> Редактировать</button>";

                        echo "<form action='accounts_handler.php' method='POST' style='display:inline'>";
                        echo "<input type='hidden' name='account_id' value='" . $row['id'] . "'>";
                        echo "<button type='submit' name='delete_account' class='delete-btn'><i class='fa fa-trash-alt'></i> Удалить</button>";
                        echo "</form>";

                        echo "</div>"; 
                        echo "</div>"; 
                        echo "</li>";
                    }
                } else {
                    echo "<li>Счетов пока нет</li>";
                }

                ?>

            </ul>
        </div>
    </div>
    <div id="editModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background:white; padding:20px; border:1px solid #ccc;">
        <h2>Изменить название счета</h2>
        <form id="edit-form" action="accounts_handler.php" method="POST">
            <input type="hidden" id="edit-account-id" name="account_id">
            <label for="edit-account-name">Новое название счета:</label>
            <input type="text" id="edit-account-name" name="new_account_name" required>
            <button type="submit" name="edit_account">Сохранить изменения</button>
            <button type="button" onclick="closeEditModal()">Отмена</button>
        </form>
    </div>
    <script src="theme.js"></script>
    <script>
        function openEditModal(accountId, accountName) {
            document.getElementById('edit-account-id').value = accountId; 
            document.getElementById('edit-account-name').value = accountName; 
            document.getElementById('editModal').style.display = 'block'; 
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }


        document.addEventListener("DOMContentLoaded", function() {
            const dialog = document.getElementById("welcome-dialog");
            const closeButton = document.getElementById("close-dialog");

            if (closeButton) {
                closeButton.addEventListener("click", function() {
                    dialog.remove(); 
                });
            }
        });
    </script>
    <script src="theme.js"></script>
</body>

</html>