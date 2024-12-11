<?php
require_once 'db.php';  // Подключение к базе данных
include 'helpers.php';
$sql = "SELECT id, name, currency, balance FROM account";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $search_text = isset($_POST['search']) ? trim($_POST['search']) : "";
    $sql .= " WHERE name LIKE '%$search_text%' OR currency LIKE '%$search_text%'";
}
$result = $conn->query($sql);


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
    <div class="container">
        <h1>Мои банковские счета</h1>

        <!-- Форма создания счета -->
        <div class="account-creation">
            <h2>Создать новый счет</h2>
            <form id="create-account-form" action="add_account.php" method="POST">
                <label for="account-name">Название счета</label>
                <input type="text" id="account-name" name="account_name" placeholder="Введите название счета" required>

                <label for="currency">Тип валюты</label>
                <select id="currency" name="currency" required>
                    <option value="" disabled selected>Выберите валюту</option>
                    <option value="RUB">Рубль</option>
                    <option value="USD">Доллар США</option>
                    <option value="EUR">Евро</option>
                    <option value="GBP">Фунт стерлингов</option>
                </select>

                <button name="create_account" type="submit">Создать счет</button>
            </form>
        </div>

        <!-- Форма поиска -->
        <div class="account-search">
            <h2>Поиск счета</h2>
            <form method="POST">
                <input type="text" id="search-account" name="search" placeholder="Введите название счета или валюту...">
                <button type="submit">Искать</button>
            </form>
        </div>


        <!-- Список счетов -->
        <div class="account-list">
            <h2>Счета</h2>
            <ul id="accounts">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<li>";
                        echo "<div class='account-card'>";
                        
                        // Информация о счете
                        echo "<div class='account-info'>";
                        echo "<h3 class='account-name'>Название счета: " . htmlspecialchars($row['name']) . "</h3>";
                        echo "<p class='account-currency'>Валюта: <strong>" . htmlspecialchars($row['currency']) . "</strong></p>";
                        echo "</div>";
                        
                        // Действия (Редактировать / Удалить)
                        echo "<div class='account-actions'>";
                        
                        // Кнопка "Редактировать" с onclick-обработчиком
                        echo "<button class='edit-btn' onclick='openEditModal(" . $row['id'] . ", \"" . htmlspecialchars($row['name']) . "\")'><i class='fa fa-pencil-alt'></i> Редактировать</button>";
                        
                        // Кнопка "Удалить"
                        echo "<form action='accounts_handler.php' method='POST' style='display:inline'>";
                        echo "<input type='hidden' name='account_id' value='" . $row['id'] . "'>";
                        echo "<button type='submit' name='delete_account' class='delete-btn'><i class='fa fa-trash-alt'></i> Удалить</button>";
                        echo "</form>";
                        
                        echo "</div>"; // Закрываем actions
                        echo "</div>"; // Закрываем account-card
                        echo "</li>";
                    }
                } else {
                    echo "<li>Счетов пока нет</li>";
                }

                ?>

            </ul>
        </div>
    </div>
    <!-- Модальное окно для редактирования -->
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
    <script>
        // Открыть модальное окно с предзаполненными данными
        function openEditModal(accountId, accountName) {
            document.getElementById('edit-account-id').value = accountId; // Устанавливаем ID счёта
            document.getElementById('edit-account-name').value = accountName; // Устанавливаем текущее название счёта
            document.getElementById('editModal').style.display = 'block'; // Показываем модальное окно
        }

        // Закрыть модальное окно
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>
</body>

</html>